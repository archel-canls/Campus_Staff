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
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

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
     * Mengecek kecocokan kredensial dan status aktivasi akun.
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

                // Proteksi: Cek apakah akun sudah diaktifkan oleh admin
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
     * Helper Function: Redirect berdasarkan Role setelah login.
     */
    protected function redirectBasedOnRole($user)
    {
        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->role === 'scanner') {
            return redirect()->intended(route('absensi.scan'));
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

    /*
    |--------------------------------------------------------------------------
    | FITUR OTP VERIFIKASI (EMAIL REGISTRASI)
    |--------------------------------------------------------------------------
    */

    /**
     * Mengirim kode OTP ke email pendaftar untuk validasi kepemilikan email.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email'
        ], [
            'email.unique' => 'Email sudah terdaftar dalam sistem.'
        ]);

        $otp = rand(100000, 999999);

        // Simpan data OTP di session sementara (Berlaku 5 Menit)
        session([
            'register_otp'   => $otp,
            'otp_email'      => $request->email,
            'otp_expires_at' => Carbon::now()->addMinutes(5)
        ]);

        try {
            Mail::raw("Kode OTP verifikasi Anda untuk pendaftaran di CDI Staff Management adalah: $otp. Kode ini berlaku selama 5 menit. Jangan berikan kode ini kepada siapapun.", function ($message) use ($request) {
                $message->to($request->email)->subject('Kode OTP Verifikasi Registrasi CDI');
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()]);
        }
    }

    /**
     * Verifikasi kode OTP yang diinputkan user pada modal pendaftaran.
     */
    public function verifyOtp(Request $request)
    {
        $sessionOtp = session('register_otp');
        $expiresAt  = session('otp_expires_at');

        if ($sessionOtp && $request->otp == $sessionOtp && Carbon::now()->lessThan($expiresAt)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Kode OTP salah atau telah kedaluwarsa.']);
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR LUPA USERNAME & PASSWORD
    |--------------------------------------------------------------------------
    */

    /**
     * Menangani permintaan awal Lupa Username atau Lupa Password.
     */
    public function handleForgotFetch(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Email tidak ditemukan dalam sistem.']);
        }

        if ($request->type === 'username') {
            try {
                Mail::raw("Halo {$user->name}, Username Anda untuk login di CDI Staff adalah: {$user->username}", function ($message) use ($user) {
                    $message->to($user->email)->subject('Informasi Username Akun CDI');
                });
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Gagal mengirim email username.']);
            }
        } else {
            // Lupa Password: Kirim OTP ke database users (kolom reset_otp_code)
            $otp = rand(100000, 999999);
            $user->update([
                'reset_otp_code' => $otp,
                'reset_otp_expires_at' => Carbon::now()->addMinutes(10)
            ]);

            try {
                Mail::raw("Kode OTP Reset Password Anda: $otp. Berlaku 10 menit. Masukkan kode ini pada aplikasi untuk melanjutkan reset password.", function ($message) use ($user) {
                    $message->to($user->email)->subject('Kode Reset Password CDI');
                });
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Gagal mengirim email OTP reset.']);
            }
        }
    }

    /**
     * Verifikasi OTP Reset Password.
     */
    public function verifyResetOtp(Request $request)
    {
        $user = User::where('email', $request->email)
            ->where('reset_otp_code', $request->otp)
            ->where('reset_otp_expires_at', '>', Carbon::now())
            ->first();

        if ($user) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Kode OTP tidak valid atau kedaluwarsa.']);
    }

    /**
     * Finalisasi perubahan password baru.
     */
    public function finalizeReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->update([
                'password' => Hash::make($request->password),
                'reset_otp_code' => null,
                'reset_otp_expires_at' => null
            ]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    /*
    |--------------------------------------------------------------------------
    | PROSES REGISTRASI FINAL
    |--------------------------------------------------------------------------
    */

    /**
     * Proses pendaftaran personel baru.
     * Menggabungkan data Profil Karyawan dan Akun User dalam satu transaksi.
     */
    public function register(Request $request)
    {
        $request->validate([
            // Identitas Utama
            'name'                  => 'required|string|max:255',
            'nik'                   => 'required|string|size:16|unique:karyawans,nik',
            'nip'                   => 'required|string|unique:karyawans,nip',
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
            // 1. Upload Foto Profil
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $namaFoto = $request->nip . '_profil_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('karyawan/foto', $namaFoto, 'public');
            }

            // 2. Upload Bukti Tanggungan
            if ($request->hasFile('bukti_tanggungan')) {
                $fileBukti = $request->file('bukti_tanggungan');
                $namaBukti = $request->nip . '_bukti_' . time() . '.' . $fileBukti->getClientOriginalExtension();
                $fileBukti->storeAs('karyawan/bukti_tanggungan', $namaBukti, 'public');
            }

            // 3. Simpan ke tabel Karyawan (Data Profil)
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
                'status_pendidikan'     => $request->status_pendidikan,
                'foto'                  => $namaFoto,

                // Tanggungan
                'jumlah_tanggungan'     => $request->jumlah_tanggungan ?? 0,
                'bukti_tanggungan'      => $namaBukti,

                // Kontak Darurat
                'emergency_1_nama'      => $request->emergency_1_nama,
                'emergency_1_hubungan'  => $request->emergency_1_hubungan,
                'emergency_1_telp'      => $request->emergency_1_telp,
                'emergency_2_nama'      => $request->emergency_2_nama,
                'emergency_2_hubungan'  => $request->emergency_2_hubungan,
                'emergency_2_telp'      => $request->emergency_2_telp,

                // Default Values (Ditetapkan Admin kemudian)
                'tanggal_masuk'         => now()->format('Y-m-d'),
                'divisi_id'             => null,
                'jabatan'               => null,
                'barcode_token'         => $request->nip,
                'gaji_pokok'            => 0,
            ]);

            // 4. Simpan ke tabel Users (Data Akun Login)
            User::create([
                'name'           => $request->name,
                'username'       => strtolower($request->username),
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'role'           => 'karyawan',
                'karyawan_id'    => $karyawan->id,
                'is_active'      => false, // Menunggu persetujuan admin
                'otp_code'       => session('register_otp'), // Mencatat OTP registrasi terakhir
                'otp_expires_at' => session('otp_expires_at'),
            ]);

            // Bersihkan Session OTP setelah berhasil mendaftar
            session()->forget(['register_otp', 'otp_email', 'otp_expires_at']);

            DB::commit();

            return redirect()->route('login')->with('success', 'Pendaftaran Berhasil! Akun Anda sedang diverifikasi oleh Admin. Silakan tunggu konfirmasi.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Hapus file jika database gagal menyimpan untuk menghemat storage
            if ($namaFoto) Storage::disk('public')->delete('karyawan/foto/' . $namaFoto);
            if ($namaBukti) Storage::disk('public')->delete('karyawan/bukti_tanggungan/' . $namaBukti);

            return back()->withErrors(['error' => 'Gagal mendaftar: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Proses logout user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
