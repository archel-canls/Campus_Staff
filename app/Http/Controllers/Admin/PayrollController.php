<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan; 
use App\Models\Divisi;
use App\Models\PayrollHistory;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Menampilkan Halaman Utama Payroll.
     * Mengintegrasikan data Master Karyawan dengan Snapshot History Bulanan.
     */
    public function index(Request $request)
    {
        // 1. Ambil filter periode (Bulan dan Tahun)
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);
        $search = $request->get('search');
        $divisiFilter = $request->get('divisi_id'); 

        $divisis = Divisi::all();

        // 2. Load Karyawan dengan Eager Loading
        $query = Karyawan::with(['divisi', 'payrollHistories']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%");
            });
        }

        if ($divisiFilter) {
            $query->where('divisi_id', $divisiFilter);
        }

        $karyawans = $query->get();

        // 3. Mapping data dengan pengamanan penuh
        foreach ($karyawans as $k) {
            $k->generated_initials = $k->initials ?? '??';

            $history = $k->payrollHistories
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();

            $k->is_locked = $history ? true : false;
            $k->history_id = $history ? $history->id : null;

            if ($history) {
                // Jika ada snapshot, gunakan data history
                $k->gaji_pokok_final   = $history->gaji_pokok_nominal;
                $k->gaji_jabatan_final = $history->gaji_divisi_snapshot;
                $k->rate_absensi_final = $history->rate_absensi_per_jam;
                $k->tunjangan_final    = $history->tunjangan_per_tanggungan;
                // Fallback: Jika di history snapshot tidak sengaja 0, ambil dari master karyawan
                $k->tanggungan_final   = ($history->jumlah_tanggungan_snapshot > 0) 
                                         ? $history->jumlah_tanggungan_snapshot 
                                         : ($k->jumlah_tanggungan ?? 0);
            } else {
                // Jika belum ada snapshot, gunakan data Master Karyawan
                $k->gaji_pokok_final   = $k->gaji_pokok ?? 0;
                $k->gaji_jabatan_final = $k->divisi ? $k->divisi->getGajiJabatan($k->jabatan) : 0;
                $k->rate_absensi_final = $k->hourly_rate ?? 25000; 
                $k->tunjangan_final    = $k->tunjangan_per_tanggungan ?? 0;
                $k->tanggungan_final   = $k->jumlah_tanggungan ?? 0;
            }

            // Hitung Absensi
            $absensis = Absensi::where('karyawan_id', $k->id)
                ->whereMonth('jam_masuk', $bulan)
                ->whereYear('jam_masuk', $tahun)
                ->get();

            $totalMenit = 0;
            foreach ($absensis as $a) {
                if ($a->jam_masuk && $a->jam_keluar) {
                    $totalMenit += Carbon::parse($a->jam_masuk)->diffInMinutes(Carbon::parse($a->jam_keluar));
                }
            }
            $k->total_jam_kerja = floor($totalMenit / 60);

            // Kalkulasi Total Gaji
            $k->total_gaji = (float)$k->gaji_pokok_final 
                           + (float)$k->gaji_jabatan_final 
                           + ((int)$k->total_jam_kerja * (float)$k->rate_absensi_final)
                           + ((int)$k->tanggungan_final * (float)$k->tunjangan_final);
        }

        return view('admin.payroll.index', compact('karyawans', 'divisis', 'bulan', 'tahun'));
    }

    /**
     * Update Gaji Pokok / Hourly Rate Individu.
     */
    public function update(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'bulan'       => 'required|integer',
            'tahun'       => 'required|integer',
            'gaji_pokok'  => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        try {
            $karyawan = Karyawan::findOrFail($request->karyawan_id);
            $overrides = [];

            if ($request->filled('gaji_pokok')) {
                $karyawan->gaji_pokok = $request->gaji_pokok;
                $overrides['gaji_pokok_nominal'] = $request->gaji_pokok;
            }

            if ($request->filled('hourly_rate')) {
                $karyawan->hourly_rate = $request->hourly_rate;
                $overrides['rate_absensi_per_jam'] = $request->hourly_rate;
            }

            $karyawan->save();
            $this->syncSnapshot($karyawan, $request->bulan, $request->tahun, $overrides);

            return redirect()->back()->with('success', 'Data penggajian individu berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Update Parameter Tunjangan Keluarga Global.
     * FIX: Memastikan jumlah_tanggungan_snapshot diambil dari master saat update global.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer',
            'tahun' => 'required|integer',
            'tunjangan_tanggungan' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();
            $karyawans = Karyawan::all();

            foreach ($karyawans as $k) {
                $this->syncSnapshot($k, $request->bulan, $request->tahun, [
                    'tunjangan_per_tanggungan' => $request->tunjangan_tanggungan,
                    'jumlah_tanggungan_snapshot' => $k->jumlah_tanggungan // Paksa ambil dari master agar tidak 0
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Parameter Tunjangan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Update Massal Gaji Jabatan per Divisi.
     */
    public function updateGajiJabatan(Request $request)
    {
        $request->validate([
            'divisi_id' => 'required|exists:divisis,id',
            'jabatan'   => 'required',
            'nominal'   => 'required|numeric|min:0',
            'bulan'     => 'required|integer',
            'tahun'     => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $divisi = Divisi::find($request->divisi_id);
            $daftar = $divisi->daftar_jabatan;
            
            if (isset($daftar[$request->jabatan])) {
                if (is_array($daftar[$request->jabatan])) {
                    $daftar[$request->jabatan]['gaji'] = (int) $request->nominal;
                } else {
                    $daftar[$request->jabatan] = (int) $request->nominal;
                }
                $divisi->update(['daftar_jabatan' => $daftar]);
            }

            $karyawans = Karyawan::where('divisi_id', $request->divisi_id)
                                 ->where('jabatan', $request->jabatan)->get();

            foreach ($karyawans as $k) {
                $this->syncSnapshot($k, $request->bulan, $request->tahun, ['gaji_divisi_snapshot' => $request->nominal]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Gaji Jabatan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Update Jumlah Tanggungan Individu.
     */
    public function updateTanggungan(Request $request)
    {
        $request->validate([
            'karyawan_id'       => 'required|exists:karyawans,id',
            'jumlah_tanggungan' => 'required|integer|min:0',
            'bulan'             => 'required|integer',
            'tahun'             => 'required|integer',
        ]);

        $karyawan = Karyawan::findOrFail($request->karyawan_id);
        $karyawan->jumlah_tanggungan = $request->jumlah_tanggungan;
        $karyawan->save();

        $this->syncSnapshot($karyawan, $request->bulan, $request->tahun, [
            'jumlah_tanggungan_snapshot' => $request->jumlah_tanggungan
        ]);

        return redirect()->back()->with('success', 'Data tanggungan berhasil diperbarui.');
    }

    /**
     * Mengunci (Lock) Seluruh Data Payroll Periode Terpilih.
     */
    public function lockAll(Request $request)
    {
        $bulan = (int) $request->input('bulan');
        $tahun = (int) $request->input('tahun');

        try {
            DB::beginTransaction();
            $karyawans = Karyawan::all();
            foreach ($karyawans as $k) {
                $this->syncSnapshot($k, $bulan, $tahun, ['keterangan' => 'Kunci Massal']);
            }
            DB::commit();
            return redirect()->back()->with('success', "Payroll periode $bulan/$tahun dikunci.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Helper Utama Sinkronisasi Data Master ke Snapshot.
     * FIX: Logika pengamanan agar jumlah_tanggungan tidak hilang.
     */
    private function syncSnapshot($karyawan, $bulan, $tahun, $overrides = [])
    {
        $existing = PayrollHistory::where([
            'karyawan_id' => $karyawan->id, 
            'bulan' => $bulan, 
            'tahun' => $tahun
        ])->first();

        $gajiDivisi = $karyawan->divisi ? $karyawan->divisi->getGajiJabatan($karyawan->jabatan) : 0;

        $data = array_merge([
            'gaji_pokok_nominal'         => $karyawan->gaji_pokok ?? 0,
            'gaji_divisi_snapshot'       => $gajiDivisi,
            'rate_absensi_per_jam'       => $karyawan->hourly_rate ?? 25000,
            'tunjangan_per_tanggungan'   => $karyawan->tunjangan_per_tanggungan ?? 0,
            // LOGIKA FIX: Gunakan input baru (overrides), atau data history lama, atau master karyawan
            'jumlah_tanggungan_snapshot' => $existing->jumlah_tanggungan_snapshot ?? ($karyawan->jumlah_tanggungan ?? 0),
            'keterangan'                 => 'Updated via Payroll Manager'
        ], $overrides);

        return PayrollHistory::updateOrCreate(
            ['karyawan_id' => $karyawan->id, 'bulan' => $bulan, 'tahun' => $tahun],
            $data
        );
    }

    public function getJabatanByDivisi($divisiId)
    {
        $divisi = Divisi::find($divisiId);
        if (!$divisi || !$divisi->daftar_jabatan) return response()->json([]);
        return response()->json(array_keys($divisi->daftar_jabatan));
    }

    public function lockPayroll(Request $request, $karyawanId)
    {
        $bulan = (int)$request->input('bulan', now()->month);
        $tahun = (int)$request->input('tahun', now()->year);
        $karyawan = Karyawan::findOrFail($karyawanId);
        $this->syncSnapshot($karyawan, $bulan, $tahun, ['keterangan' => 'Dikunci manual']);
        return redirect()->back()->with('success', "Payroll " . $karyawan->nama . " terkunci.");
    }

    public function slipSaya() { return view('karyawan.slip-gaji'); }
    public function export() { return redirect()->back()->with('info', 'Fitur export dikembangkan.'); }
}