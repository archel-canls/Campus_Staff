<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * * Tabel 'karyawans' ini dirancang sebagai sumber data tunggal (Single Source of Truth)
     * yang menyinkronkan data dari Form Registrasi CDI, Digital ID Card, 
     * hingga parameter Master untuk Sistem Payroll Bulanan.
     */
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            
            // --- 01 DATA IDENTITAS UTAMA (Sesuai KTP & Standar Perusahaan) ---
            $table->string('nama');
            
            // Nomor Induk Pegawai (Format Unik: Contoh 260219505001)
            $table->string('nip')->unique(); 
            
            // NIK KTP (Wajib 16 digit untuk keperluan BPJS & Pajak)
            $table->string('nik', 16)->unique(); 
            
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('golongan_darah', 5)->default('-');
            
            // --- 02 DATA LOKASI & KONTAK ---
            $table->text('alamat_ktp');
            $table->text('alamat_domisili');
            
            // Nomor HP/WhatsApp (Gunakan string karena diawali angka 0 atau kode +62)
            $table->string('telepon'); 
            
            // --- 03 PEKERJAAN & STRUKTUR ORGANISASI ---
            // Status: tetap, kontrak, magang_kampus, magang_mandiri
            $table->string('status'); 
            
            // Asal Kampus/Sekolah/Instansi (Terutama untuk status magang)
            $table->string('instansi')->nullable(); 
            
            /**
             * Relasi ke tabel divisis (Foreign Key)
             * Menghubungkan karyawan dengan departemennya.
             * onDelete('set null') memastikan jika divisi dihapus, data karyawan tetap ada.
             */
            $table->foreignId('divisi_id')
                  ->nullable()
                  ->constrained('divisis')
                  ->onDelete('set null'); 
            
            // Jabatan mengikuti key yang ada di JSON daftar_jabatan pada tabel divisis
            $table->string('jabatan')->nullable(); 
            $table->date('tanggal_masuk')->nullable();
            
            // --- 04 KONTAK DARURAT (Emergency Contacts) ---
            // Kontak Utama
            $table->string('emergency_1_nama')->nullable();
            $table->string('emergency_1_hubungan')->nullable();
            $table->string('emergency_1_telp')->nullable();
            
            // Kontak Cadangan
            $table->string('emergency_2_nama')->nullable();
            $table->string('emergency_2_hubungan')->nullable();
            $table->string('emergency_2_telp')->nullable();
            
            // --- 05 DATA PENDIDIKAN & TANGGUNGAN (Dasar Perhitungan Payroll) ---
            $table->string('pendidikan_terakhir')->nullable(); 
            $table->string('status_pendidikan')->nullable();   
            
            // Jumlah orang yang ditanggung (Digunakan untuk kalkulasi Tunjangan Keluarga)
            $table->integer('jumlah_tanggungan')->default(0);

            /** * NOMINAL TUNJANGAN PER TANGGUNGAN (MASTER VALUE)
             * Menyimpan nilai rupiah per 1 jiwa sebagai acuan master.
             * Nilai ini akan di-snapshot ke tabel payroll_histories setiap bulannya.
             */
            $table->decimal('tunjangan_per_tanggungan', 15, 2)->default(0);

            // Path file bukti (Scan KK / Surat Nikah) untuk validasi tunjangan
            $table->string('bukti_tanggungan')->nullable();    
            
            // --- 06 SISTEM IDENTITAS DIGITAL & FINANSIAL ---
            // Token unik untuk generate Barcode/QR Code pada ID Card Digital
            $table->string('barcode_token')->unique(); 
            
            /** * GAJI POKOK (MASTER VALUE)
             * Menyimpan nilai Gaji Pokok Individu (Rate dasar diluar gaji jabatan).
             * Digunakan sebagai acuan default saat generate data payroll bulanan.
             */
            $table->decimal('gaji_pokok', 15, 2)->default(0); 
            
            // Path file foto profil (Simpan di storage/app/public/karyawan/foto/...)
            $table->string('foto')->nullable(); 
            
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXING
            |--------------------------------------------------------------------------
            | Menambahkan index pada kolom yang sering digunakan dalam pencarian/filter
            | untuk mengoptimalkan performa database.
            */
            $table->index('nama');
            $table->index('status');
            $table->index('nip');
        });
    }

    /**
     * Reverse the migrations.
     * Menghapus tabel secara bersih termasuk pelepasan foreign key.
     */
    public function down(): void
    {
        if (Schema::hasTable('karyawans')) {
            Schema::table('karyawans', function (Blueprint $table) {
                // Lepas foreign key constraint terlebih dahulu
                if (Schema::hasColumn('karyawans', 'divisi_id')) {
                    $table->dropForeign(['divisi_id']);
                }
            });
        }
        
        Schema::dropIfExists('karyawans');
    }
};