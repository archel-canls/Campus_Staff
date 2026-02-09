<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\ManajemenKaryawanController;
use App\Http\Controllers\Admin\PayrollController;
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
| Role 'scanner' hanya boleh mengakses halaman ini. 
| Admin juga diberi akses untuk keperluan monitoring/testing.
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

    // CRUD Karyawan (Data Personel)
    Route::resource('manajemen-karyawan', ManajemenKaryawanController::class);

    // Fitur Absensi (Hanya Laporan & Riwayat untuk Admin)
    Route::prefix('absensi')->group(function () {
        Route::get('/riwayat', [AbsensiController::class, 'riwayat'])->name('absensi.riwayat');
        Route::get('/laporan', [AbsensiController::class, 'laporan'])->name('absensi.laporan');
    });

    // Fitur Perizinan (Konfirmasi Admin)
    Route::post('/perizinan/konfirmasi/{id}/{status}', [AbsensiController::class, 'konfirmasiIzin'])
        ->name('admin.perizinan.konfirmasi');

    // Payroll System (Pengaturan Gaji & Bonus)
    Route::prefix('payroll')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('payroll.index');
        Route::post('/global-config', [PayrollController::class, 'store'])->name('payroll.store');
        Route::patch('/update/{id}', [PayrollController::class, 'update'])->name('payroll.update');
        Route::get('/export', [PayrollController::class, 'export'])->name('payroll.export');
        Route::post('/lock/{id}', [PayrollController::class, 'lockGaji'])->name('payroll.lock');
    });

    // Menu Divisi
    Route::get('/divisi', function () { 
        return view('admin.divisi.index'); 
    })->name('divisi.index');
});

/*
|--------------------------------------------------------------------------
| 5. GRUP KARYAWAN (PREFIX: karyawan)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:karyawan'])->prefix('karyawan')->group(function () {

    // Dashboard Karyawan
    Route::get('/dashboard', [AbsensiController::class, 'dashboardKaryawan'])->name('karyawan.dashboard');
    
    // Profile & ID Card Digital
    Route::get('/id-card', [ManajemenKaryawanController::class, 'showSelf'])->name('karyawan.id-card');
    Route::post('/update-foto', [ManajemenKaryawanController::class, 'updateFoto'])->name('karyawan.update-foto');

    // Riwayat Absensi Pribadi
    Route::get('/absensi', [AbsensiController::class, 'riwayatSaya'])->name('karyawan.absensi');

    // Pengajuan Izin/Sakit
    Route::get('/perizinan', function () { 
        return view('karyawan.perizinan'); 
    })->name('karyawan.perizinan');
    
    Route::post('/perizinan/store', [AbsensiController::class, 'storeIzin'])
        ->name('karyawan.perizinan.store');

    // Menu Jabatan
    Route::get('/jabatan', function () { 
        return view('karyawan.jabatan'); 
    })->name('karyawan.jabatan');

    // Slip Gaji Bulanan
    Route::get('/slip-gaji', [PayrollController::class, 'slipSaya'])->name('karyawan.slip-gaji');
});