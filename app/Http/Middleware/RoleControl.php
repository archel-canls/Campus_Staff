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
     * @param  string  $role  (admin atau karyawan)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role;

        // 2. Cek apakah role user sesuai dengan parameter middleware ($role)
        if ($userRole !== $role) {
            
            // Logika Pengalihan (Redirect):
            // Jika user adalah Karyawan tapi mencoba akses route Admin
            if ($userRole === 'karyawan') {
                return redirect()->route('karyawan.dashboard')
                    ->with('error', 'Akses ditolak! Anda tidak memiliki izin Admin.');
            }

            // Jika user adalah Admin tapi mencoba akses route Karyawan
            if ($userRole === 'admin') {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Admin dialihkan ke dashboard utama.');
            }

            // Jika role tidak dikenal sama sekali
            Auth::logout();
            return redirect()->route('login')->with('error', 'Role tidak valid.');
        }

        return $next($request);
    }
}