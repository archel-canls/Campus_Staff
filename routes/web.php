<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\ManajemenKaryawanController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\HariLiburController;
use App\Http\Controllers\Admin\DivisiController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| 1. REDIRECTOR UTAMA
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role;
        
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'scanner') {
            return redirect()->route('absensi.scan');
        } else {
            return redirect()->route('karyawan.dashboard');
        }
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATION (GUEST ONLY)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| 3. GRUP SCANNER & ADMIN (Akses Khusus Mesin Scanner)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,scanner'])->group(function () {
    Route::prefix('absensi')->group(function () {
        Route::get('/scan', [AbsensiController::class, 'scan'])->name('absensi.scan');
        Route::post('/submit', [AbsensiController::class, 'submit'])->name('absensi.submit');
    });
});

/*
|--------------------------------------------------------------------------
| 4. GRUP ADMIN (PREFIX: admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {

    // Dashboard Admin
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // CRUD Karyawan (Data Personel Lengkap)
    Route::resource('manajemen-karyawan', ManajemenKaryawanController::class);

    // Fitur Absensi (Laporan & Riwayat untuk Admin)
    Route::prefix('absensi')->group(function () {
        Route::get('/riwayat', [AbsensiController::class, 'riwayat'])->name('absensi.riwayat');
        Route::get('/laporan', [AbsensiController::class, 'laporan'])->name('absensi.laporan');
    });

    // Fitur Pengaturan Hari Libur Nasional/Kantor
    Route::prefix('libur')->name('libur.')->group(function () {
        Route::post('/store', [HariLiburController::class, 'store'])->name('store');
        Route::delete('/destroy/{id}', [HariLiburController::class, 'destroy'])->name('destroy');
    });

    // Fitur Perizinan (Konfirmasi Admin terhadap ajuan Karyawan)
    Route::post('/perizinan/konfirmasi/{id}/{status}', [AbsensiController::class, 'konfirmasiIzin'])
        ->name('admin.perizinan.konfirmasi');

    // --- PAYROLL SYSTEM (Hourly Rate, Tunjangan & Lock System) ---
    Route::prefix('payroll')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('payroll.index');
        
        // Konfigurasi Bonus Absensi per Jam & Tunjangan (Modal 2)
        Route::post('/config', [PayrollController::class, 'store'])->name('payroll.config');
        
        // Update Gaji Pokok per Jabatan di dalam Divisi (Modal 1)
        Route::post('/update-gaji-jabatan', [PayrollController::class, 'updateGajiJabatan'])->name('payroll.update_gaji_jabatan');
        
        // Update Hourly Rate individu (Kustom per karyawan jika diperlukan)
        Route::patch('/update/{id}', [PayrollController::class, 'update'])->name('payroll.update');
        
        // Finalisasi & Export Data Gaji
        Route::post('/lock/{id}', [PayrollController::class, 'lockGaji'])->name('payroll.lock');
        Route::get('/export', [PayrollController::class, 'export'])->name('payroll.export');
    });

    // --- MANAJEMEN DIVISI (Dinamis & Kuota Jabatan) ---
    // Resource route untuk: index, create, store, show, edit, update, destroy
    Route::resource('divisi', DivisiController::class);
    
    // Custom Action khusus untuk pengelolaan anggota di dalam divisi
    Route::prefix('divisi-action')->name('divisi.')->group(function() {
        // Menambahkan anggota ke divisi (Assign)
        Route::post('/{id}/tambah-anggota', [DivisiController::class, 'tambahAnggota'])->name('tambah-anggota');
        
        // Melepaskan anggota dari divisi (Unassign)
        Route::post('/hapus-anggota/{karyawan_id}', [DivisiController::class, 'hapusAnggota'])->name('hapus-anggota');
        
        /**
         * Route untuk update daftar pilihan jabatan.
         * Digunakan untuk memproses input JSON jabatan, kuota maksimal, dan nominal gaji.
         */
        Route::patch('/{id}/update-jabatan', [DivisiController::class, 'updateJabatan'])->name('update-jabatan');
    });
});

/*
|--------------------------------------------------------------------------
| 5. GRUP KARYAWAN (PREFIX: karyawan)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:karyawan'])->prefix('karyawan')->group(function () {

    // Dashboard Karyawan (Info Shift & Jam Kerja)
    Route::get('/dashboard', [AbsensiController::class, 'dashboardKaryawan'])->name('karyawan.dashboard');
    
    // Profile & ID Card Digital (Dapat diakses mandiri)
    Route::get('/id-card', [ManajemenKaryawanController::class, 'showSelf'])->name('karyawan.id-card');
    Route::post('/update-foto', [ManajemenKaryawanController::class, 'updateFoto'])->name('karyawan.update-foto');

    // Riwayat Absensi Pribadi (Melihat jam masuk/pulang sendiri)
    Route::get('/absensi', [AbsensiController::class, 'riwayatSaya'])->name('karyawan.absensi');

    // Pengajuan Izin/Sakit (Step: Upload Bukti -> Menunggu Konfirmasi Admin)
    Route::get('/perizinan', function () { 
        return view('karyawan.perizinan'); 
    })->name('karyawan.perizinan');
    
    Route::post('/perizinan/store', [AbsensiController::class, 'storeIzin'])
        ->name('karyawan.perizinan.store');

    // Informasi Jabatan & Struktur Organisasi
    Route::get('/jabatan', function () { 
        return view('karyawan.jabatan'); 
    })->name('karyawan.jabatan');

    // Slip Gaji Bulanan (Detail perhitungan jam kerja x rate)
    Route::get('/slip-gaji', [PayrollController::class, 'slipSaya'])->name('karyawan.slip-gaji');
});