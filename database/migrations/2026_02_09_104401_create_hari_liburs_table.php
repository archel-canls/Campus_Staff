<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel untuk menyimpan data hari libur perusahaan atau tanggal merah.
     */
    public function up(): void
    {
        Schema::create('hari_liburs', function (Blueprint $table) {
            $table->id();
            
            // Kolom tanggal dibuat unik agar tidak ada duplikasi hari libur di tanggal yang sama
            $table->date('tanggal')->unique(); 
            
            // Keterangan alasan libur (contoh: Lebaran, Cuti Bersama, dll)
            $table->string('keterangan'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hari_liburs');
    }
};