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
| Web Routes
|--------------------------------------------------------------------------
|
| Di sini adalah tempat di mana Anda dapat mendaftarkan rute web untuk aplikasi.
| Rute-rute ini dimuat oleh RouteServiceProvider dan semuanya akan
| ditetapkan ke grup middleware "web".
|
*/

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
    
    // Custom action untuk download ID Card & Kelola Password dari sisi Admin
    Route::get('/manajemen-karyawan/{id}/download-id-card', [ManajemenKaryawanController::class, 'downloadIdCard'])
        ->name('manajemen-karyawan.download-id-card');

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

    // --- PAYROLL SYSTEM (Sistem Penggajian Lengkap) ---
    Route::prefix('payroll')->group(function () {
        
        /**
         * Rute Utama Payroll
         */
        Route::get('/', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/main', [PayrollController::class, 'index'])->name('admin.payroll');
        
        /**
         * CONFIG GLOBAL (Tunjangan & Rate Absensi)
         */
        Route::post('/config', [PayrollController::class, 'store'])->name('payroll.config');
        Route::post('/update-tunjangan', [PayrollController::class, 'store'])->name('payroll.update_tunjangan');
        Route::post('/update-global-rate', [PayrollController::class, 'updateRateAbsensi'])->name('payroll.update_rate_absensi');
        
        /**
         * UPDATE DATA MASSAL / GRUP
         */
        Route::post('/update-gaji-jabatan', [PayrollController::class, 'updateGajiJabatan'])->name('payroll.update_gaji_jabatan');
        
        /**
         * UPDATE DATA INDIVIDU & AJAX
         */
        // Ambil detail bonus/potongan yang sudah ada via AJAX
        Route::get('/get-details', [PayrollController::class, 'getDetails'])->name('payroll.get_details');

        // Update Gaji Pokok / Rate Individu (Handled by update method)
        Route::post('/update-rate', [PayrollController::class, 'update'])->name('payroll.update_hourly_rate');
        Route::post('/update-gaji-pokok', [PayrollController::class, 'update'])->name('payroll.update_gaji_pokok');
        
        // Update Bonus Tambahan (Individu/Grup)
        Route::post('/update-bonus', [PayrollController::class, 'updateBonus'])->name('payroll.update_bonus');

        // Update Potongan Gaji (Individu/Grup)
        Route::post('/update-potongan', [PayrollController::class, 'updatePotongan'])->name('payroll.update_potongan');

        // Menghapus Item Bonus/Potongan Tertentu (Misal: menghapus b1 saja)
        Route::post('/delete-item', [PayrollController::class, 'deleteItem'])->name('payroll.delete_item');

        // Update Jumlah Tanggungan Keluarga Individu
        Route::post('/update-tanggungan', [PayrollController::class, 'updateTanggungan'])->name('payroll.update_tanggungan');

        /**
         * FINALISASI & EXPORT
         */
        Route::post('/lock/{id}', [PayrollController::class, 'lockPayroll'])->name('payroll.lock');
        Route::post('/lock-all', [PayrollController::class, 'lockAll'])->name('payroll.lock_all');
        Route::delete('/destroy', [PayrollController::class, 'destroy'])->name('payroll.destroy'); // Reset Periode
        Route::get('/export', [PayrollController::class, 'export'])->name('payroll.export');

        /**
         * AJAX UTILITY
         */
        Route::get('/get-jabatan/{divisiId}', [PayrollController::class, 'getJabatanByDivisi'])->name('payroll.get_jabatan');
    });

    // --- MANAJEMEN DIVISI (Dinamis & Kuota Jabatan) ---
    Route::resource('divisi', DivisiController::class);
    
    Route::prefix('divisi-action')->name('divisi.')->group(function() {
        // Menambahkan anggota ke divisi
        Route::post('/{id}/tambah-anggota', [DivisiController::class, 'tambahAnggota'])->name('tambah-anggota');
        
        // Melepaskan anggota dari divisi
        Route::post('/hapus-anggota/{karyawan_id}', [DivisiController::class, 'hapusAnggota'])->name('hapus-anggota');
        
        // Update Struktur Jabatan: kuota, nama jabatan, dan nominal gaji via JSON
        Route::patch('/{id}/update-jabatan', [DivisiController::class, 'updateJabatan'])->name('update-jabatan');
    });
});

/*
|--------------------------------------------------------------------------
| 5. GRUP KARYAWAN (PREFIX: karyawan)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:karyawan'])->prefix('karyawan')->group(function () {

    // Dashboard Karyawan
    Route::get('/dashboard', [AbsensiController::class, 'dashboardKaryawan'])->name('karyawan.dashboard');
    
    // Digital ID Card & Profil Mandiri
    Route::get('/id-card', [ManajemenKaryawanController::class, 'showSelf'])->name('karyawan.id-card');
    
    // Update Foto Profil
    Route::post('/update-foto', [ManajemenKaryawanController::class, 'updateFoto'])->name('karyawan.update-foto');

    // Riwayat Absensi Pribadi
    Route::get('/absensi', [AbsensiController::class, 'riwayatSaya'])->name('karyawan.absensi');

    // Modul Pengajuan Izin/Sakit (Self-Service)
    Route::get('/perizinan', function () { 
        return view('karyawan.perizinan'); 
    })->name('karyawan.perizinan');
    
    Route::post('/perizinan/store', [AbsensiController::class, 'storeIzin'])
        ->name('karyawan.perizinan.store');

    // Informasi Struktur Organisasi & Detail Jabatan Saya
    Route::get('/jabatan', function () { 
        return view('karyawan.jabatan'); 
    })->name('karyawan.jabatan');

    // Slip Gaji Bulanan (Akses Mandiri Karyawan)
    Route::get('/slip-gaji', [PayrollController::class, 'slipSaya'])->name('karyawan.slip-gaji');
});