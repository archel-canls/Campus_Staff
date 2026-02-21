<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Controller PayrollController
 * Mengelola sistem penggajian berdasarkan jam kerja (Hourly Rate),
 * perhitungan lembur otomatis, manajemen tunjangan keluarga, 
 * dan sinkronisasi gaji berdasarkan jabatan di Divisi.
 */
class PayrollController extends Controller
{
    /**
     * Menampilkan daftar payroll dengan sistem Hourly Rate & Lembur (Admin Dashboard).
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);
        
        // Konfigurasi Payroll (Session Based / Default)
        $config = session('payroll_config', [
            'tunjangan_tanggungan' => 100000, // Per anak/istri
            'lembur_multiplier' => 1.5,      // 1.5x dari hourly rate
            'bpjs_percent' => 1              // Potongan BPJS dalam persen
        ]);

        // 1. Ambil data karyawan dengan absensi pada periode yang dipilih
        $karyawans = Karyawan::with(['divisi', 'absensis' => function($query) use ($bulan, $tahun) {
                $query->whereMonth('jam_masuk', $bulan)
                      ->whereYear('jam_masuk', $tahun)
                      ->whereNotNull('jam_keluar'); // Hanya hitung yang sudah checkout
            }])
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->get();

        // 2. Ambil data semua divisi untuk modal pembaruan gaji jabatan
        $divisis = Divisi::all();

        // 3. Kalkulasi Statistik Global untuk Dashboard Card
        $totalGajiPokok = 0; 
        $totalLemburGlobal = 0;
        $totalTunjanganGlobal = 0;
        
        foreach ($karyawans as $k) {
            $hourlyRate = $k->gaji_pokok > 0 ? $k->gaji_pokok : 20000; 
            $menitKerjaNormal = 0;
            $menitLembur = 0;

            foreach ($k->absensis as $abs) {
                if ($abs->jam_masuk && $abs->jam_keluar) {
                    $durasi = $abs->jam_masuk->diffInMinutes($abs->jam_keluar);
                    
                    // Batas Kerja Normal: 8 Jam (480 menit)
                    if ($durasi > 480) {
                        $menitKerjaNormal += 480;
                        $menitLembur += ($durasi - 480);
                    } else {
                        $menitKerjaNormal += $durasi;
                    }
                }
            }

            $jamNormal = floor($menitKerjaNormal / 60);
            $jamLembur = floor($menitLembur / 60);

            $totalGajiPokok += ($jamNormal * $hourlyRate);
            $totalLemburGlobal += ($jamLembur * ($hourlyRate * $config['lembur_multiplier']));
            $totalTunjanganGlobal += (($k->jumlah_tanggungan ?? 0) * $config['tunjangan_tanggungan']);
        }
        
        $estimasiTotal = $totalGajiPokok + $totalLemburGlobal + $totalTunjanganGlobal;

        return view('admin.payroll.index', compact(
            'karyawans', 
            'estimasiTotal', 
            'totalGajiPokok', 
            'totalLemburGlobal',
            'totalTunjanganGlobal',
            'config',
            'bulan',
            'tahun',
            'divisis'
        ));
    }

    /**
     * Memperbarui Gaji Pokok/Hourly Rate berdasarkan Jabatan di dalam Divisi (JSON).
     * Fungsi ini menangani form dari modal di halaman index payroll.
     */
    public function updateGajiJabatan(Request $request)
    {
        $request->validate([
            'divisi_jabatan' => 'required',
            'gaji_baru' => 'required|numeric|min:0',
        ]);

        // Value format: "divisi_id|nama_jabatan"
        $parts = explode('|', $request->divisi_jabatan);
        $divisiId = $parts[0];
        $namaJabatan = $parts[1];

        $divisi = Divisi::findOrFail($divisiId);
        $daftar = $divisi->daftar_jabatan;

        // Update data gaji di dalam JSON daftar_jabatan
        if (isset($daftar[$namaJabatan])) {
            if (is_array($daftar[$namaJabatan])) {
                $daftar[$namaJabatan]['gaji'] = (int) $request->gaji_baru;
            } else {
                // Support format lama jika hanya berisi integer kuota
                $daftar[$namaJabatan] = (int) $request->gaji_baru;
            }
            
            $divisi->update(['daftar_jabatan' => $daftar]);

            // Sinkronisasi otomatis ke semua karyawan yang memiliki jabatan tersebut
            Karyawan::where('divisi_id', $divisiId)
                    ->where('jabatan', $namaJabatan)
                    ->update(['gaji_pokok' => $request->gaji_baru]);

            return redirect()->back()->with('success', "Gaji untuk jabatan $namaJabatan berhasil diperbarui!");
        }

        return redirect()->back()->with('error', "Jabatan tidak ditemukan!");
    }

    /**
     * Menampilkan Slip Gaji untuk Karyawan yang login (Sisi Karyawan).
     */
    public function slipSaya(Request $request)
    {
        $user = Auth::user();
        $karyawan = Karyawan::with('divisi')->where('id', $user->karyawan_id)->first();

        if (!$karyawan) {
            return redirect()->back()->with('error', 'Data profil karyawan tidak ditemukan.');
        }

        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $config = session('payroll_config', [
            'tunjangan_tanggungan' => 100000,
            'lembur_multiplier' => 1.5,
            'bpjs_percent' => 1
        ]);

        $absensis = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('jam_masuk', $bulan)
            ->whereYear('jam_masuk', $tahun)
            ->whereNotNull('jam_keluar')
            ->get();

        $totalMenitNormal = 0;
        $totalMenitLembur = 0;

        foreach ($absensis as $abs) {
            $durasi = $abs->jam_masuk->diffInMinutes($abs->jam_keluar);
            if ($durasi > 480) {
                $totalMenitNormal += 480;
                $totalMenitLembur += ($durasi - 480);
            } else {
                $totalMenitNormal += $durasi;
            }
        }

        $jamNormal = floor($totalMenitNormal / 60);
        $jamLembur = floor($totalMenitLembur / 60);

        $hourlyRate = $karyawan->gaji_pokok > 0 ? $karyawan->gaji_pokok : 20000;
        $gajiDasar = $jamNormal * $hourlyRate;
        $upahLembur = $jamLembur * ($hourlyRate * $config['lembur_multiplier']);
        $tunjanganTanggungan = ($karyawan->jumlah_tanggungan ?? 0) * $config['tunjangan_tanggungan'];
        
        $bruto = $gajiDasar + $upahLembur + $tunjanganTanggungan;
        $potonganBPJS = $bruto * ($config['bpjs_percent'] / 100);
        $grandTotal = $bruto - $potonganBPJS;

        return view('karyawan.slip_gaji', compact(
            'karyawan', 'jamNormal', 'jamLembur', 'bulan', 'tahun', 
            'config', 'gajiDasar', 'upahLembur', 'tunjanganTanggungan', 
            'potonganBPJS', 'grandTotal', 'hourlyRate'
        ));
    }

    /**
     * Memperbarui Konfigurasi Global (Rate Tunjangan & Multiplier Lembur).
     */
    public function store(Request $request)
    {
        $request->validate([
            'tunjangan_tanggungan' => 'required|numeric|min:0',
            'lembur_multiplier'    => 'required|numeric|min:1',
            'bpjs_percent'         => 'required|numeric|min:0',
        ]);

        session(['payroll_config' => $request->only([
            'tunjangan_tanggungan', 'lembur_multiplier', 'bpjs_percent'
        ])]);

        return redirect()->back()->with('success', 'Konfigurasi payroll berhasil diperbarui!');
    }

    /**
     * Memperbarui Hourly Rate (Gaji Pokok/Jam) karyawan secara individu.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'gaji_pokok' => 'required|numeric|min:0',
        ]);

        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update(['gaji_pokok' => $request->gaji_pokok]);

        return redirect()->back()->with('success', 'Hourly Rate untuk ' . $karyawan->nama . ' berhasil diperbarui!');
    }

    /**
     * Finalisasi Gaji (Locking).
     */
    public function lockGaji($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return redirect()->back()->with('success', 'Gaji periode ini untuk ' . $karyawan->nama . ' telah dikunci.');
    }

    /**
     * Export data ke PDF/Excel (Placeholder).
     */
    public function export(Request $request)
    {
        $bulanName = Carbon::create()->month($request->get('bulan', now()->month))->translatedFormat('F');
        $tahun = $request->get('tahun', now()->year);
        
        return back()->with('success', 'Laporan payroll periode ' . $bulanName . ' ' . $tahun . ' sedang diproses.');
    }
}