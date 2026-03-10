<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        
        return view('auth.login');
    }

    /**
     * Proses autentikasi login.
     * Dimodifikasi untuk mengecek status is_active.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password tidak boleh kosong.',
        ]);

        // Cari user berdasarkan username
        $user = User::where('username', $request->username)->first();

        if ($user) {
            // Cek apakah password cocok
            if (Hash::check($request->password, $user->password)) {
                
                // PERUBAHAN: Cek apakah akun sudah diaktifkan oleh admin
                if (!$user->is_active) {
                    return back()->withErrors([
                        'username' => 'Akun Anda belum dikonfirmasi oleh Admin. Silakan hubungi bagian HRD atau cek berkala.',
                    ])->withInput($request->only('username'));
                }

                Auth::login($user, $request->has('remember'));
                $request->session()->regenerate();
                
                return $this->redirectBasedOnRole($user);
            }
            
            // Jika password salah
            return back()->withErrors([
                'username' => 'Password yang Anda masukkan salah.',
            ])->withInput($request->only('username'));
        }

        // Jika username tidak ditemukan
        return back()->withErrors([
            'username' => 'Username tidak terdaftar dalam sistem.',
        ])->withInput($request->only('username'));
    }

    /**
     * Helper Function: Redirect berdasarkan Role.
     */
    protected function redirectBasedOnRole($user)
    {
        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }
        
        return redirect()->intended(route('karyawan.dashboard'));
    }

    /**
     * Tampilkan halaman registrasi.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.register');
    }

    /**
     * Proses pendaftaran personel baru.
     * Dimodifikasi agar akun baru berstatus is_active = false dan tidak login otomatis.
     * Jabatan dan Divisi dikosongkan (null) untuk ditentukan Admin nantinya.
     */
    public function register(Request $request)
    {
        $request->validate([
            // Identitas Utama
            'name'                  => 'required|string|max:255',
            'nik'                   => 'required|string|size:16|unique:karyawans,nik',
            'nip'                   => 'required|string|digits:12|unique:karyawans,nip', 
            'tempat_lahir'          => 'required|string|max:255',
            'tanggal_lahir'         => 'required|date',
            'jenis_kelamin'         => 'required|in:L,P',
            'golongan_darah'        => 'nullable|string|max:5',

            // Lokasi & Kontak
            'alamat_ktp'            => 'required|string',
            'alamat_domisili'       => 'required|string',
            'telepon'               => 'required|string|max:20',

            // Jalur Pendaftaran & Pendidikan
            'status'                => 'required|string', 
            'instansi'              => 'nullable|string|max:255',
            'pendidikan_terakhir'   => 'nullable|string',
            'status_pendidikan'     => 'nullable|string',
            'foto'                  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Tanggungan
            'jumlah_tanggungan'     => 'nullable|integer|min:0',
            'bukti_tanggungan'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',

            // Kontak Darurat
            'emergency_1_nama'      => 'nullable|string|max:255',
            'emergency_1_hubungan'  => 'nullable|string',
            'emergency_1_telp'      => 'nullable|string|max:20',
            'emergency_2_nama'      => 'nullable|string|max:255',
            'emergency_2_hubungan'  => 'nullable|string',
            'emergency_2_telp'      => 'nullable|string|max:20',

            // Keamanan Akun
            'username'              => 'required|string|max:255|unique:users,username',
            'email'                 => 'required|string|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
        ], [
            'nik.size'               => 'NIK harus berjumlah 16 digit.',
            'nik.unique'             => 'NIK sudah terdaftar dalam sistem.',
            'nip.digits'             => 'NIP harus berjumlah tepat 12 digit.',
            'nip.unique'             => 'NIP sudah terdaftar, silakan coba kirim ulang form.',
            'username.unique'        => 'Username sudah digunakan.',
            'email.unique'           => 'Email sudah terdaftar.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
            'foto.image'             => 'File foto harus berupa gambar.',
            'bukti_tanggungan.mimes' => 'Bukti tanggungan harus berupa PDF, JPG, atau PNG.',
            'bukti_tanggungan.max'   => 'Ukuran bukti tanggungan maksimal 2MB.',
        ]);

        DB::beginTransaction();

        $namaFoto = null;
        $namaBukti = null;

        try {
            // 1. Handling Upload Foto Profil
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $namaFoto = $request->nip . '_profil_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('karyawan/foto', $namaFoto, 'public');
            }

            // 2. Handling Upload Bukti Tanggungan
            if ($request->hasFile('bukti_tanggungan')) {
                $fileBukti = $request->file('bukti_tanggungan');
                $namaBukti = $request->nip . '_bukti_' . time() . '.' . $fileBukti->getClientOriginalExtension();
                $fileBukti->storeAs('karyawan/bukti_tanggungan', $namaBukti, 'public');
            }

            // 3. Simpan ke tabel Karyawan
            $karyawan = Karyawan::create([
                'nama'                  => $request->name,
                'nik'                   => $request->nik,
                'nip'                   => $request->nip,
                'tempat_lahir'          => $request->tempat_lahir,
                'tanggal_lahir'         => $request->tanggal_lahir,
                'jenis_kelamin'         => $request->jenis_kelamin,
                'golongan_darah'        => $request->golongan_darah ?? '-',
                'alamat_ktp'            => $request->alamat_ktp,
                'alamat_domisili'       => $request->alamat_domisili,
                'telepon'               => $request->telepon,
                'status'                => $request->status,
                'instansi'              => $request->instansi,
                'pendidikan_terakhir'   => $request->pendidikan_terakhir,
                'foto'                  => $namaFoto,
                
                // Field Tanggungan
                'jumlah_tanggungan'     => $request->jumlah_tanggungan ?? 0,
                'bukti_tanggungan'      => $namaBukti,
                
                // Kontak Darurat 1
                'emergency_1_nama'      => $request->emergency_1_nama,
                'emergency_1_hubungan'  => $request->emergency_1_hubungan,
                'emergency_1_telp'      => $request->emergency_1_telp,
                
                // Kontak Darurat 2
                'emergency_2_nama'      => $request->emergency_2_nama,
                'emergency_2_hubungan'  => $request->emergency_2_hubungan,
                'emergency_2_telp'      => $request->emergency_2_telp,

                // Default Values & Logic
                // PERUBAHAN: Divisi dan Jabatan di-set null agar kosong sebelum diverifikasi Admin
                'tanggal_masuk'         => now()->format('Y-m-d'),
                'divisi_id'             => null, 
                'jabatan'               => null, 
                'barcode_token'         => 'BC-' . $request->nip,
                'gaji_pokok'            => 0, // Gaji di-set 0, nanti dihitung saat Admin menentukan jabatan
            ]);

            // 4. Simpan ke tabel Users
            $user = User::create([
                'name'        => $request->name,
                'username'    => strtolower($request->username),
                'email'       => $request->email,
                'password'    => Hash::make($request->password), 
                'role'        => 'karyawan',
                'karyawan_id' => $karyawan->id,
                'is_active'   => false, 
            ]);

            DB::commit();

            // Pendaftar diarahkan kembali ke login dengan pesan sukses verifikasi.
            return redirect()->route('login')->with('success', 'Registrasi berhasil! Akun Anda sedang diverifikasi oleh Admin. Silakan cek berkala.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Cleanup: Hapus file jika database gagal menyimpan
            if ($namaFoto) {
                Storage::disk('public')->delete('karyawan/foto/' . $namaFoto);
            }
            if ($namaBukti) {
                Storage::disk('public')->delete('karyawan/bukti_tanggungan/' . $namaBukti);
            }

            return back()->withErrors(['error' => 'Sistem gagal memproses pendaftaran: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}