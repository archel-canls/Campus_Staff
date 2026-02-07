<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * File ini menciptakan tabel absensis lengkap dengan kolom jam_masuk dan jam_keluar.
     */
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            
            // Menghubungkan ke tabel karyawans melalui ID
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            
            // Mencatat waktu absen masuk
            $table->dateTime('jam_masuk');
            
            // Mencatat waktu absen keluar (dibuat nullable karena saat masuk kolom ini belum terisi)
            $table->dateTime('jam_keluar')->nullable();
            
            // Status kehadiran (Contoh: Hadir, Terlambat)
            $table->string('keterangan')->default('Hadir');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};