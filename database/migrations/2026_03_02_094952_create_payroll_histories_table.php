<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel ini menyimpan histori atau "Snapshot" parameter penggajian.
     * Hal ini memungkinkan perubahan gaji di masa depan tidak merubah laporan gaji masa lalu.
     */
    public function up(): void
    {
        Schema::create('payroll_histories', function (Blueprint $table) {
            $table->id();
            
            // 1. Relasi ke tabel karyawans
            // Menggunakan onDelete('cascade') agar jika data karyawan dihapus, historinya ikut terhapus.
            $table->foreignId('karyawan_id')
                  ->constrained('karyawans')
                  ->onDelete('cascade');

            // 2. Periode Penggajian
            // Digunakan sebagai kunci utama pencarian laporan bulanan
            $table->integer('bulan'); // 1-12
            $table->integer('tahun'); // Contoh: 2026

            /* |--------------------------------------------------------------------------
            | DATA FINANSIAL SNAPSHOT (Parameter yang bisa diatur di Modal Payroll)
            |--------------------------------------------------------------------------
            | Tipe data decimal(15,2) digunakan untuk akurasi nilai mata uang.
            */
            
            // Tab: Gaji Pokok Individu (Snapshot gaji_pokok dari tabel karyawans)
            $table->decimal('gaji_pokok_nominal', 15, 2)->default(0);
            
            // Tab: Gaji Per Jabatan (Snapshot Gaji dari JSON daftar_jabatan di tabel divisis)
            $table->decimal('gaji_divisi_snapshot', 15, 2)->default(0);
            
            // Tab: Rate Per Jam (Snapshot Rate Absensi Global saat periode tersebut)
            $table->decimal('rate_absensi_per_jam', 15, 2)->default(0);
            
            // Tab: Tunjangan Keluarga (Parameter rupiah per 1 jiwa tanggungan)
            $table->decimal('tunjangan_per_tanggungan', 15, 2)->default(0);

            /**
             * PERUBAHAN TERBARU: Bonus dan Potongan (Nominal & Keterangan Spesifik)
             * Memisahkan keterangan bonus dan potongan agar pelaporan lebih detil.
             */
            $table->decimal('bonus_tambahan', 15, 2)->default(0); 
            $table->text('keterangan_bonus')->nullable(); // Menyimpan rincian asal bonus (Misal: Lembur, THR)
            
            $table->decimal('potongan_gaji', 15, 2)->default(0);  
            $table->text('keterangan_potongan')->nullable(); // Menyimpan rincian alasan potongan (Misal: Kasbon, Atribut)

            // 3. Data Tambahan untuk Audit
            // Menyimpan jumlah tanggungan saat bulan tersebut untuk keperluan histori yang akurat
            $table->integer('jumlah_tanggungan_snapshot')->default(0); 
            
            // Kolom keterangan umum (Catatan tambahan admin jika ada)
            $table->text('keterangan')->nullable(); 
            
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXING
            |--------------------------------------------------------------------------
            | Menambahkan index pada kombinasi karyawan dan periode untuk mempercepat 
            | query saat dashboard atau laporan payroll dibuka.
            */
            $table->index(['karyawan_id', 'bulan', 'tahun'], 'idx_payroll_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_histories');
    }
};