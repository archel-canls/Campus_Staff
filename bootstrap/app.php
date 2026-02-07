<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * Mendaftarkan Alias Middleware
         * Ini agar Anda bisa menggunakan middleware 'role:admin' atau 'role:karyawan'
         * di dalam file routes/web.php secara ringkas.
         */
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleControl::class,
        ]);

        /**
         * Mengatur Redirect untuk Guest
         * Jika user belum login dan mencoba mengakses halaman terproteksi (middleware auth),
         * mereka akan diarahkan secara otomatis ke halaman login.
         */
        $middleware->redirectTo(
            guests: '/login',
            users: '/',
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /**
         * Tempat untuk kustomisasi penanganan error/exception jika diperlukan.
         * Misalnya: mencatat log khusus atau mengubah tampilan error 404/500.
         */
    })->create();