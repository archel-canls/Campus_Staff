<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * File ini menciptakan tabel perizinans untuk mencatat pengajuan izin dan cuti karyawan.
     */
    public function up(): void
    {
        Schema::create('perizinans', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel karyawans
            // onDelete('cascade') memastikan jika data karyawan dihapus, data izinnya ikut terhapus
            $table->foreignId('karyawan_id')
                  ->constrained('karyawans')
                  ->onDelete('cascade');
            
            // Detail Izin
            $table->string('jenis_izin'); // Contoh: Sakit, Keperluan Mendesak, Cuti
            
            // Menggunakan index pada tanggal untuk mempercepat pencarian rentang waktu
            $table->date('tanggal_mulai')->index();
            $table->date('tanggal_selesai')->index();
            
            $table->integer('lama_hari'); // Kalkulasi total hari izin
            
            // Alasan dan Lampiran
            $table->text('alasan');
            $table->string('lampiran_pdf')->nullable(); // Path file PDF di storage
            
            // Status Persetujuan Admin
            // Menambahkan index pada status karena admin sering melakukan filter status 'pending'
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])
                  ->default('pending')
                  ->index();
                  
            $table->text('catatan_admin')->nullable(); // Feedback dari admin
            
            $table->timestamps();

            /**
             * Catatan Optimasi:
             * Indexing pada (tanggal_mulai, tanggal_selesai, status) sangat krusial 
             * karena AbsensiController akan sering melakukan query WHERE pada kolom ini 
             * untuk sinkronisasi otomatis status kehadiran.
             */
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pastikan untuk menghapus constraint foreign key terlebih dahulu jika diperlukan
        // Namun dropIfExists sudah cukup untuk tabel tunggal
        Schema::dropIfExists('perizinans');
    }
};