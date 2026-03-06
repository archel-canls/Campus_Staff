<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\Perizinan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AbsensiController extends Controller
{
    /**
     * Menampilkan halaman scanner barcode (Admin).
     */
    public function scan()
    {
        return view('admin.absensi.scan');
    }

    /**
     * Memproses data dari scanner via AJAX (Admin).
     * UPDATED: Lokasi tetap dicatat untuk pemantauan, namun pembatasan radius dihapus.
     * Karyawan kini bisa absen dari mana saja (WFH/Tugas Luar).
     */
    public function submit(Request $request)
    {
        $request->validate([
            'nip'  => 'required|string',
            'tipe' => 'nullable|in:masuk,keluar',
            'lat'  => 'nullable|numeric',
            'lng'  => 'nullable|numeric',
        ]);

        try {
            /**
             * SOLUSI B: Fleksibilitas Pencarian
             * Mencari karyawan berdasarkan:
             * 1. NIP murni
             * 2. Barcode Token apa adanya
             * 3. Barcode Token dengan prefix 'BC-'
             */
            $karyawan = Karyawan::where('nip', $request->nip)
                                ->orWhere('barcode_token', $request->nip)
                                ->orWhere('barcode_token', 'BC-' . $request->nip)
                                ->first();

            if (!$karyawan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Identitas [' . $request->nip . '] tidak terdaftar!'
                ], 404);
            }

            // --- PENCATATAN LOKASI (TANPA PEMBATASAN RADIUS) ---
            // Logika validasi $distance > $radiusMeter telah dihapus agar bisa absen di mana saja.
            // Koordinat $request->lat dan $request->lng akan langsung disimpan ke database.

            $jamSekarang = Carbon::now();
            $hariIni = Carbon::today();
            $tipe = $request->tipe ?? 'masuk'; 
            
            // Batas jam masuk kantor (08:00)
            $jamMasukKantor = Carbon::today()->setHour(8)->setMinute(0)->setSecond(0);
            
            // ================= LOGIKA ABSEN MASUK =================
            if ($tipe === 'masuk') {
                $sudahMasuk = Absensi::where('karyawan_id', $karyawan->id)
                                    ->whereDate('jam_masuk', $hariIni)
                                    ->first();

                if ($sudahMasuk) {
                    return response()->json([
                        'status'  => 'warning',
                        'message' => "Halo {$karyawan->nama}, Anda sudah absen MASUK pukul " . Carbon::parse($sudahMasuk->jam_masuk)->format('H:i'),
                        'data'    => [
                            'nama'   => $karyawan->nama,
                            'waktu'  => Carbon::parse($sudahMasuk->jam_masuk)->format('H:i:s'),
                            'status' => $sudahMasuk->keterangan
                        ]
                    ]);
                }

                $keterangan = 'Hadir';
                if ($jamSekarang->gt($jamMasukKantor)) {
                    $menit = $jamMasukKantor->diffInMinutes($jamSekarang);
                    $keterangan = "Terlambat ({$menit} Menit)";
                }

                // Simpan data absensi beserta lokasi GPS yang dikirim perangkat
                Absensi::create([
                    'karyawan_id' => $karyawan->id,
                    'jam_masuk'   => $jamSekarang,
                    'keterangan'  => $keterangan,
                    'latitude'    => $request->lat, 
                    'longitude'   => $request->lng,
                ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => "Berhasil MASUK: {$karyawan->nama}. " . ($keterangan != 'Hadir' ? $keterangan : 'Tepat Waktu!'),
                    'data'    => [
                        'nama'   => $karyawan->nama,
                        'nip'    => $karyawan->nip,
                        'waktu'  => $jamSekarang->format('H:i:s'),
                        'status' => $keterangan,
                        'info'   => 'Lokasi berhasil dicatat'
                    ]
                ]);
            }

            // ================= LOGIKA ABSEN KELUAR =================
            if ($tipe === 'keluar') {
                $absensi = Absensi::where('karyawan_id', $karyawan->id)
                                    ->whereDate('jam_masuk', $hariIni)
                                    ->first();

                if (!$absensi) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda belum absen MASUK hari ini!'
                    ], 400);
                }

                if ($absensi->jam_keluar) {
                    return response()->json([
                        'status'  => 'warning',
                        'message' => "Halo {$karyawan->nama}, Anda sudah absen KELUAR pukul " . Carbon::parse($absensi->jam_keluar)->format('H:i'),
                    ]);
                }

                $absensi->update(['jam_keluar' => $jamSekarang]);

                return response()->json([
                    'status'  => 'success',
                    'message' => "Berhasil KELUAR: {$karyawan->nama}.",
                    'data'    => [
                        'nama'   => $karyawan->nama,
                        'waktu'  => $jamSekarang->format('H:i:s'),
                        'status' => 'Pulang'
                    ]
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Hitung jarak antara dua koordinat (Haversine Formula).
     * Tetap dipertahankan jika sewaktu-waktu dibutuhkan kembali untuk reporting.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    /**
     * Menampilkan riwayat absensi & perizinan (Halaman utama log admin).
     */
    public function riwayat()
    {
        $today = Carbon::today();

        $allKaryawan = Karyawan::with(['absensis' => function($q) use ($today) {
            $q->whereDate('jam_masuk', $today);
        }, 'perizinans' => function($q) use ($today) {
            $q->where('status', 'disetujui')
              ->whereDate('tanggal_mulai', '<=', $today)
              ->whereDate('tanggal_selesai', '>=', $today);
        }])->get();

        $perizinanPending = Perizinan::where('status', 'pending')
                            ->with('karyawan')
                            ->latest()
                            ->get();
        
        $pendingCount = $perizinanPending->count();

        $historyPerizinan = Perizinan::where('status', '!=', 'pending')
                            ->with('karyawan')
                            ->latest()
                            ->take(10)
                            ->get();

        return view('admin.absensi.riwayat', compact(
            'allKaryawan', 
            'perizinanPending', 
            'historyPerizinan', 
            'pendingCount'
        ));
    }

    /**
     * Konfirmasi Izin oleh Admin (Disetujui/Ditolak).
     */
    public function konfirmasiIzin($id, $status)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $izin = Perizinan::findOrFail($id);
            $izin->update(['status' => $status]);

            if ($status === 'disetujui') {
                $today = Carbon::today();
                $mulai = Carbon::parse($izin->tanggal_mulai)->startOfDay();
                $selesai = Carbon::parse($izin->tanggal_selesai)->endOfDay();

                if ($today->between($mulai, $selesai)) {
                    $exists = Absensi::where('karyawan_id', $izin->karyawan_id)
                                    ->whereDate('jam_masuk', $today)
                                    ->exists();

                    if (!$exists) {
                        Absensi::create([
                            'karyawan_id' => $izin->karyawan_id,
                            'jam_masuk'   => Carbon::now(),
                            'jam_keluar'  => Carbon::now(),
                            'keterangan'  => 'Izin: ' . $izin->jenis_izin,
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Status perizinan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Laporan bulanan (Admin).
     */
    public function laporan(Request $request)
    {
        $bulan = $request->get('bulan', Carbon::now()->month);
        $tahun = $request->get('tahun', Carbon::now()->year);

        $laporan = Karyawan::with(['absensis' => function($query) use ($bulan, $tahun) {
            $query->whereMonth('jam_masuk', $bulan)->whereYear('jam_masuk', $tahun);
        }, 'perizinans' => function($query) use ($bulan, $tahun) {
            $query->where('status', 'disetujui')
                  ->where(function($q) use ($bulan, $tahun) {
                      $q->whereMonth('tanggal_mulai', $bulan)->whereYear('tanggal_mulai', $tahun)
                        ->orWhereMonth('tanggal_selesai', $bulan)->whereYear('tanggal_selesai', $tahun);
                  });
        }])->get();

        return view('admin.absensi.laporan', compact('laporan', 'bulan', 'tahun'));
    }

    /**
     * Dashboard khusus karyawan.
     */
    public function dashboardKaryawan()
    {
        $user = Auth::user();
        $karyawan = Karyawan::find($user->karyawan_id);
        
        if (!$karyawan) {
            return redirect()->route('login')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $now = Carbon::now();
        
        $totalHadir = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('jam_masuk', $now->month)
            ->whereYear('jam_masuk', $now->year)
            ->where('keterangan', 'NOT LIKE', 'Izin%')
            ->count();

        $absenHariIni = Absensi::where('karyawan_id', $karyawan->id)
            ->whereDate('jam_masuk', Carbon::today())
            ->first();

        $riwayatAbsensi = Absensi::where('karyawan_id', $karyawan->id)
            ->orderBy('jam_masuk', 'desc')
            ->take(7)
            ->get();

        $insentifHarian = 25000;
        $uangMakan = 15000;
        $gajiPokok = $karyawan->gaji_pokok ?? 0;
        $estimasiGaji = $gajiPokok + ($totalHadir * ($insentifHarian + $uangMakan));

        return view('karyawan.dashboard', compact('totalHadir', 'absenHariIni', 'riwayatAbsensi', 'estimasiGaji'));
    }

    /**
     * Riwayat absensi pribadi karyawan.
     */
    public function riwayatSaya(Request $request)
    {
        $user = Auth::user();
        $bulan = (int) $request->get('bulan', date('m'));
        $tahun = (int) $request->get('tahun', date('Y'));
    
        $absensis = Absensi::where('karyawan_id', $user->karyawan_id)
                    ->whereMonth('jam_masuk', $bulan)
                    ->whereYear('jam_masuk', $tahun)
                    ->orderBy('jam_masuk', 'desc')
                    ->get();
    
        $perizinans = Perizinan::where('karyawan_id', $user->karyawan_id)
                    ->whereMonth('tanggal_mulai', $bulan)
                    ->whereYear('tanggal_mulai', $tahun)
                    ->latest()
                    ->get();
    
        return view('karyawan.absensi', compact('absensis', 'perizinans', 'bulan', 'tahun'));
    }

    /**
     * Menyimpan pengajuan izin karyawan.
     */
    public function storeIzin(Request $request)
    {
        $request->validate([
            'jenis_izin' => 'required|in:Sakit,Keperluan Mendesak,Cuti',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'lampiran_pdf' => 'nullable|mimes:pdf|max:2048',
        ]);

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $lamaHari = $mulai->diffInDays($selesai) + 1;

        if ($lamaHari > 3 && !$request->hasFile('lampiran_pdf')) {
            return back()->with('error', 'Izin lebih dari 3 hari wajib melampirkan dokumen PDF.')->withInput();
        }

        $data = [
            'karyawan_id' => Auth::user()->karyawan_id,
            'jenis_izin' => $request->jenis_izin,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'lama_hari' => $lamaHari,
            'status' => 'pending'
        ];

        if ($request->hasFile('lampiran_pdf')) {
            $folderPath = public_path('uploads/perizinan');
            if (!File::isDirectory($folderPath)) {
                File::makeDirectory($folderPath, 0777, true, true);
            }

            $filename = time() . '_izin_' . Auth::id() . '.' . $request->file('lampiran_pdf')->getClientOriginalExtension();
            $request->file('lampiran_pdf')->move($folderPath, $filename);
            $data['lampiran_pdf'] = $filename;
        }

        Perizinan::create($data);
        return redirect()->back()->with('success', 'Permohonan izin berhasil dikirim!');
    }
}