<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel divisis dengan atribut lengkap untuk mendukung fitur 
     * dashboard organisasi modern, manajemen jabatan dinamis, kuota, dan sistem payroll.
     */
    public function up(): void
    {
        // 1. BUAT TABEL DIVISIS TERLEBIH DAHULU
        Schema::create('divisis', function (Blueprint $table) {
            $table->id();
            
            // Nama Divisi (Contoh: IT Solution, Human Resources)
            $table->string('nama');
            
            // Kode Divisi (Singkatan unik, Contoh: ITS, HRD)
            $table->string('kode')->unique();
            
            // Deskripsi detail mengenai divisi tersebut
            $table->text('deskripsi')->nullable();
            
            // Tugas-tugas utama (Disimpan dalam format string/text, bisa dipisahkan koma)
            $table->text('tugas_utama')->nullable();

            /**
             * KOLOM: daftar_jabatan (Tipe JSON)
             * MODIFIKASI: Sekarang menyimpan Kuota DAN Gaji Pokok.
             * Format yang disimpan: 
             * {
             * "Manager": {"kuota": 1, "gaji": 7000000}, 
             * "Staff": {"kuota": 10, "gaji": 4500000}
             * }
             */
            $table->json('daftar_jabatan')->nullable();
            
            // Identitas visual: Icon FontAwesome dan Warna Aksen (blue, orange, green, dll)
            $table->string('icon')->default('fas fa-users');
            $table->string('warna')->default('blue');
            
            $table->timestamps();
        });

        /**
         * 2. HUBUNGKAN TABEL KARYAWANS KE TABEL DIVISIS.
         * Sinkronisasi struktur tabel karyawans agar memiliki relasi ke divisi.
         * Menggunakan pemeriksaan Schema::hasTable dan hasColumn untuk keamanan data.
         */
        if (Schema::hasTable('karyawans')) {
            Schema::table('karyawans', function (Blueprint $table) {
                // Tambahkan kolom divisi_id sebagai Foreign Key jika belum ada
                if (!Schema::hasColumn('karyawans', 'divisi_id')) {
                    $table->foreignId('divisi_id')
                          ->nullable()
                          ->after('id') 
                          ->constrained('divisis')
                          ->onDelete('set null'); // Jika divisi dihapus, karyawan tetap ada
                }

                // Tambahkan kolom jabatan jika belum ada
                if (!Schema::hasColumn('karyawans', 'jabatan')) {
                    $table->string('jabatan')->nullable()->after('divisi_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     * Mengembalikan database ke kondisi semula (Rollback).
     */
    public function down(): void
    {
        // Lepas foreign key dan hapus kolom di tabel karyawans terlebih dahulu
        if (Schema::hasTable('karyawans')) {
            Schema::table('karyawans', function (Blueprint $table) {
                if (Schema::hasColumn('karyawans', 'divisi_id')) {
                    // Penting: Lepas foreign key constraint sebelum drop kolom
                    $table->dropForeign(['divisi_id']);
                    $table->dropColumn(['divisi_id']);
                }
                
                if (Schema::hasColumn('karyawans', 'jabatan')) {
                    $table->dropColumn(['jabatan']);
                }
            });
        }

        // Terakhir hapus tabel utama
        Schema::dropIfExists('divisis');
    }
};