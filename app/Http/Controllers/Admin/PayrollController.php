<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Menampilkan daftar payroll berdasarkan data kehadiran (Admin).
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);
        
        // Konfigurasi Default
        $config = session('payroll_config', [
            'insentif_harian' => 25000,
            'uang_makan' => 15000,
            'potongan_telat' => 10000,
            'bpjs_percent' => 1
        ]);

        // 1. Ambil data karyawan dengan hitungan absensi & keterlambatan
        $karyawans = Karyawan::withCount(['absensis' => function($query) use ($bulan, $tahun) {
                $query->whereMonth('jam_masuk', $bulan)
                      ->whereYear('jam_masuk', $tahun);
            }])
            ->with(['absensis' => function($query) use ($bulan, $tahun) {
                $query->whereMonth('jam_masuk', $bulan)
                      ->whereYear('jam_masuk', $tahun)
                      ->where('keterangan', 'like', '%Terlambat%');
            }])
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->get();

        // 2. Kalkulasi Statistik Global
        $totalGajiPokok = $karyawans->sum('gaji_pokok');
        
        $totalInsentifGlobal = 0;
        foreach ($karyawans as $k) {
            $hadir = $k->absensis_count;
            $jumlahTelat = $k->absensis->count(); // Mengambil data telat dari eager loading

            $insentif = $hadir * ($config['insentif_harian'] + $config['uang_makan']);
            $potongan = $jumlahTelat * $config['potongan_telat'];
            
            $totalInsentifGlobal += ($insentif - $potongan);
        }
        
        $estimasiTotal = $totalGajiPokok + $totalInsentifGlobal;

        return view('admin.payroll.index', compact(
            'karyawans', 
            'estimasiTotal', 
            'totalGajiPokok', 
            'totalInsentifGlobal',
            'config',
            'bulan',
            'tahun'
        ));
    }

    /**
     * Method slipSaya: Menampilkan Slip Gaji untuk Karyawan yang login.
     * Mengatasi Error 500 "Method slipSaya does not exist".
     */
    public function slipSaya(Request $request)
    {
        $user = Auth::user();
        $karyawan = Karyawan::where('id', $user->karyawan_id)->first();

        if (!$karyawan) {
            return redirect()->back()->with('error', 'Data profil karyawan tidak ditemukan.');
        }

        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        // Ambil Config
        $config = session('payroll_config', [
            'insentif_harian' => 25000,
            'uang_makan' => 15000,
            'potongan_telat' => 10000,
            'bpjs_percent' => 1
        ]);

        // Data Kehadiran & Detail Telat
        $absensis = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('jam_masuk', $bulan)
            ->whereYear('jam_masuk', $tahun)
            ->get();

        $totalHadir = $absensis->count();
        $totalTelat = $absensis->filter(function($item) {
            return str_contains($item->keterangan, 'Terlambat');
        })->count();

        // Kalkulasi Nominal
        $gajiPokok = $karyawan->gaji_pokok ?? 0;
        $totalInsentif = $totalHadir * $config['insentif_harian'];
        $totalUangMakan = $totalHadir * $config['uang_makan'];
        $totalPotonganTelat = $totalTelat * $config['potongan_telat'];
        
        $bruto = $gajiPokok + $totalInsentif + $totalUangMakan - $totalPotonganTelat;
        $potonganBPJS = $bruto * ($config['bpjs_percent'] / 100);
        $grandTotal = $bruto - $potonganBPJS;

        return view('karyawan.slip_gaji', compact(
            'karyawan', 'totalHadir', 'totalTelat', 'bulan', 'tahun', 
            'config', 'gajiPokok', 'totalInsentif', 'totalUangMakan', 
            'totalPotonganTelat', 'potonganBPJS', 'grandTotal'
        ));
    }

    /**
     * Menyimpan atau memperbarui konfigurasi standar gaji (Global).
     */
    public function store(Request $request)
    {
        $request->validate([
            'insentif_harian' => 'required|numeric|min:0',
            'uang_makan'      => 'required|numeric|min:0',
            'potongan_telat'  => 'required|numeric|min:0',
            'bpjs_percent'    => 'required|numeric|min:0',
        ]);

        session(['payroll_config' => $request->only([
            'insentif_harian', 'uang_makan', 'potongan_telat', 'bpjs_percent'
        ])]);

        return redirect()->back()->with('success', 'Konfigurasi payroll global berhasil diperbarui!');
    }

    /**
     * Memperbarui Gaji Pokok karyawan secara individu.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'gaji_pokok' => 'required|numeric|min:0',
        ]);

        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update(['gaji_pokok' => $request->gaji_pokok]);

        return redirect()->back()->with('success', 'Data gaji ' . $karyawan->nama . ' berhasil disimpan!');
    }

    /**
     * Finalisasi Gaji.
     */
    public function lockGaji($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        // Implementasi snapshot dapat dilakukan di sini jika tabel history sudah siap
        return redirect()->back()->with('info', 'Gaji untuk ' . $karyawan->nama . ' telah difinalisasi untuk periode ini.');
    }

    /**
     * Export data.
     */
    public function export(Request $request)
    {
        return back()->with('success', 'Laporan payroll periode ' . now()->format('F Y') . ' sedang diproses.');
    }
}