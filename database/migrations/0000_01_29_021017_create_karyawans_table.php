<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel karyawans dengan sinkronisasi penuh terhadap form registrasi CDI 
     * dan kebutuhan Digital Identity Card.
     */
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            
            // --- 01 DATA IDENTITAS UTAMA ---
            $table->string('nama');
            // Nomor Induk Pegawai / Kode Personel (12 Digit: YYMM-JK-MMDD-RAND)
            $table->string('nip')->unique(); 
            // NIK KTP (Wajib 16 digit sesuai standar Dukcapil)
            $table->string('nik', 16)->unique(); 
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('golongan_darah', 5)->default('-');
            
            // --- 02 DATA LOKASI & KONTAK ---
            $table->text('alamat_ktp');
            $table->text('alamat_domisili');
            // Nomor HP Aktif/WhatsApp untuk koordinasi
            $table->string('telepon'); 
            
            // --- 03 JALUR PENDAFTARAN & PEKERJAAN ---
            // Menampung status: tetap, kontrak, magang_mbkm, magang_ppl, magang_mandiri
            $table->string('status'); 
            // Asal Kampus/Sekolah (Contoh: "Universitas Gadjah Mada")
            $table->string('instansi')->nullable(); 
            $table->string('divisi')->default('Belum Ditentukan');
            // Jabatan fungsional (Contoh: "STAFF", "MANAGER", atau "INTERN")
            $table->string('jabatan')->nullable(); 
            $table->date('tanggal_masuk')->nullable();
            
            // --- 04 KONTAK DARURAT (Emergency Contacts) ---
            // Kontak Utama (Emergency 1)
            $table->string('emergency_1_nama')->nullable();
            $table->string('emergency_1_hubungan')->nullable();
            $table->string('emergency_1_telp')->nullable();
            
            // Kontak Cadangan (Emergency 2)
            $table->string('emergency_2_nama')->nullable();
            $table->string('emergency_2_hubungan')->nullable();
            $table->string('emergency_2_telp')->nullable();
            
            // --- 05 DATA PENDIDIKAN & TANGGUNGAN ---
            $table->string('pendidikan_terakhir')->nullable(); // SD, SMP, SMA, S1, dsb.
            $table->integer('jumlah_tanggungan')->default(0);
            $table->string('bukti_tanggungan')->nullable(); // Path file dokumen pendukung (KK/Surat)
            
            // --- 06 SISTEM IDENTITAS DIGITAL & FINANSIAL ---
            // barcode_token digunakan untuk generate QR Code unik pada kartu digital/absensi (Link ke NIP)
            $table->string('barcode_token')->unique(); 
            // Menggunakan decimal untuk akurasi nilai mata uang (IDR)
            $table->decimal('gaji_pokok', 15, 2)->default(0); 
            // Nama file foto profil (Path: storage/app/public/karyawan/foto/...)
            $table->string('foto')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Menghapus tabel jika dilakukan rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};