<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * File ini menciptakan tabel absensis lengkap dengan kolom jam_masuk dan jam_keluar.
     * UPDATE: Menambahkan dukungan koordinat GPS (latitude & longitude) untuk pelacakan lokasi.
     * Tidak ada fitur yang dikurangi, kolom koordinat menggunakan presisi tinggi (decimal).
     */
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            
            // Menghubungkan ke tabel karyawans melalui ID
            // onDelete('cascade') memastikan jika data karyawan dihapus, data absen ikut terhapus secara bersih.
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            
            // Mencatat waktu absen masuk (Wajib diisi saat scan masuk)
            $table->dateTime('jam_masuk');
            
            // Mencatat waktu absen keluar (Dibuat nullable karena saat masuk kolom ini belum terisi)
            $table->dateTime('jam_keluar')->nullable();
            
            // Status kehadiran (Contoh: Hadir, Terlambat)
            $table->string('keterangan')->default('Hadir');

            /**
             * KOLOM LOKASI (GPS)
             * Latitude: Titik lintang (Gunakan decimal 10,8 untuk akurasi hingga centimeter)
             * Longitude: Titik bujur (Gunakan decimal 11,8 untuk akurasi hingga centimeter)
             * Dibuat nullable agar sistem tetap berjalan meskipun GPS perangkat gagal didapatkan.
             */
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Mencatat kapan baris data ini dibuat dan diperbarui
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Menghapus tabel secara keseluruhan jika migrasi di-rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};