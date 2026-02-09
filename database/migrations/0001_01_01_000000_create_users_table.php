<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel users sebagai entitas autentikasi utama.
     */
    public function up(): void
    {
        // 1. Tabel Utama: Users
        // Tabel ini menangani data kredensial untuk login dan otorisasi.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique(); // Identitas login unik (digunakan saat login)
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            /**
             * Role user untuk RBAC (Role Based Access Control)
             * - 'admin': Akses penuh ke dashboard, laporan, dan manajemen.
             * - 'karyawan': Akses ke dashboard pribadi dan slip gaji.
             * - 'scanner': Akses terbatas hanya untuk halaman scanner barcode.
             */
            $table->enum('role', ['admin', 'karyawan', 'scanner'])->default('karyawan');
            
            /**
             * Relasi ke tabel karyawans:
             * Penting: Migration 'create_karyawans_table' harus dijalankan sebelum ini 
             * agar foreign key dapat terbentuk tanpa error.
             * onDelete('set null') memastikan akun login tidak ikut terhapus secara paksa 
             * jika data profil karyawan sedang dimanipulasi, namun tetap sinkron.
             */
            $table->foreignId('karyawan_id')
                  ->nullable()
                  ->constrained('karyawans')
                  ->onDelete('set null');
            
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Tabel: Password Reset Tokens (Standar Laravel)
        // Digunakan untuk menyimpan token saat user melakukan permintaan "Lupa Password".
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. Tabel: Sessions (Dibutuhkan jika SESSION_DRIVER=database di file .env)
        // Digunakan untuk melacak user yang sedang login, IP Address, dan device yang digunakan.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations (Hapus tabel secara bersih).
     */
    public function down(): void
    {
        // Urutan drop dilakukan dengan menghapus tabel dependensi terlebih dahulu
        // untuk menghindari error constraint integrity.
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};