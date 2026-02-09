<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  (Parameter dinamis: admin, karyawan, atau scanner)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = $user->role;

        // 2. Cek apakah role user saat ini ada dalam daftar role yang diizinkan (...$roles)
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // 3. Logika Pengalihan (Redirect) jika akses ditolak:
        
        // A. Jika akun SCANNER mencoba akses yang bukan haknya
        if ($userRole === 'scanner') {
            return redirect()->route('absensi.scan')
                ->with('error', 'Akses ditolak! Akun ini hanya untuk Scanner.');
        }

        // B. Jika user KARYAWAN mencoba akses route Admin/Scanner
        if ($userRole === 'karyawan') {
            return redirect()->route('karyawan.dashboard')
                ->with('error', 'Akses ditolak! Anda tidak memiliki izin untuk halaman tersebut.');
        }

        // C. Jika user ADMIN mencoba akses route yang khusus Karyawan
        if ($userRole === 'admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Halaman tersebut khusus untuk akses personel/karyawan.');
        }

        // D. Jika role tidak dikenal sama sekali atau tidak terdaftar dalam sistem
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('error', 'Sesi tidak valid atau role tidak dikenali.');
    }
}