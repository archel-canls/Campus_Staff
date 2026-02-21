<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Model Karyawan
 * Struktur data sinkron dengan Form Registrasi CDI, Migration, Payroll Hourly System, dan Digital ID Card.
 */
class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawans';

    /**
     * Mass Assignment Protection
     * Menampung semua input dari form registrasi multi-step (Identitas, Lokasi, Pekerjaan, Kontak Darurat).
     */
    protected $fillable = [
        'nama',
        'nip',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'golongan_darah',
        'alamat_ktp',
        'alamat_domisili',
        'telepon',
        'status',               // tetap, kontrak, magang_kampus, magang_mandiri
        'instansi',             // Asal Sekolah/Kampus/Perusahaan
        'divisi_id',            // Relasi ke tabel divisis
        'jabatan',
        'tanggal_masuk',
        
        // Kontak Darurat Utama (Emergency 1)
        'emergency_1_nama',
        'emergency_1_hubungan',
        'emergency_1_telp',
        
        // Kontak Darurat Cadangan (Emergency 2)
        'emergency_2_nama',
        'emergency_2_hubungan',
        'emergency_2_telp',

        // Data Pendidikan & Finansial
        'pendidikan_terakhir',
        'status_pendidikan',    // Lulus, Mahasiswa Aktif, dsb.
        'jumlah_tanggungan',
        'bukti_tanggungan',
        'barcode_token',
        'gaji_pokok',           // Dalam sistem ini berfungsi sebagai HOURLY RATE (Upah per Jam)
        'foto',
    ];

    /**
     * Casting atribut agar otomatis menjadi tipe data yang sesuai saat diakses.
     */
    protected $casts = [
        'tanggal_lahir'     => 'date',
        'tanggal_masuk'     => 'date',
        'gaji_pokok'        => 'double',
        'jumlah_tanggungan' => 'integer',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Boot function untuk handling Delete Cascade dan Cleanup File.
     * Memastikan file fisik dan data relasi terhapus saat record karyawan dihapus.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($karyawan) {
            // 1. Hapus relasi yang bergantung pada id karyawan
            $karyawan->absensis()->delete();
            $karyawan->perizinans()->delete();
            
            // 2. Hapus file fisik (Foto & Bukti Tanggungan) dari storage agar tidak membebani server
            if ($karyawan->foto) {
                Storage::disk('public')->delete('karyawan/foto/' . $karyawan->foto);
            }
            if ($karyawan->bukti_tanggungan) {
                Storage::disk('public')->delete('karyawan/bukti_tanggungan/' . $karyawan->bukti_tanggungan);
            }

            // 3. Hapus akun user terkait agar tidak ada data yatim (orphaned data)
            if ($karyawan->user) {
                $karyawan->user()->delete();  
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS (Hubungan Antar Tabel)
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi ke tabel Divisi (Banyak Karyawan dimiliki oleh satu Divisi).
     */
    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    /**
     * Relasi ke tabel Users (Satu Karyawan memiliki satu Akun Login).
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'karyawan_id');
    }

    /**
     * Relasi ke data Absensi.
     */
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'karyawan_id');
    }

    /**
     * Relasi ke data Perizinan.
     */
    public function perizinans(): HasMany
    {
        return $this->hasMany(Perizinan::class, 'karyawan_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Atribut Virtual / Format Data)
    |--------------------------------------------------------------------------
    | Akses di Blade: $karyawan->nama_atribut
    */

    /**
     * Mendapatkan umur saat ini secara otomatis. Akses: $karyawan->age
     */
    public function getAgeAttribute()
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : null;
    }

    /**
     * Menghitung Masa Kerja (Sejak tanggal masuk hingga sekarang).
     */
    public function getMasaKerjaAttribute()
    {
        if (!$this->tanggal_masuk) return "-";
        return $this->tanggal_masuk->diff(now())->format('%y Tahun, %m Bulan');
    }

    /**
     * Format Rupiah untuk Hourly Rate. Akses: $karyawan->formatted_hourly_rate
     */
    public function getFormattedHourlyRateAttribute()
    {
        return 'Rp ' . number_format((float) $this->gaji_pokok, 0, ',', '.') . ' /jam';
    }

    /**
     * URL Foto Profil dengan Fallback UI-Avatars. Akses: $karyawan->profile_picture
     */
    public function getProfilePictureAttribute()
    {
        if ($this->foto && Storage::disk('public')->exists('karyawan/foto/' . $this->foto)) {
            return asset('storage/karyawan/foto/' . $this->foto);
        }
        
        // Fallback: Jika foto kosong, buat avatar otomatis berdasarkan nama menggunakan warna Navy CDI (#003366)
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nama) . '&background=003366&color=fff&bold=true';
    }

    /**
     * URL Bukti Tanggungan. Akses: $karyawan->bukti_tanggungan_url
     */
    public function getBuktiTanggunganUrlAttribute()
    {
        if ($this->bukti_tanggungan && Storage::disk('public')->exists('karyawan/bukti_tanggungan/' . $this->bukti_tanggungan)) {
            return asset('storage/karyawan/bukti_tanggungan/' . $this->bukti_tanggungan);
        }
        return null;
    }

    /**
     * Inisial Nama (Contoh: "Archel Arisandi" -> "AA"). Akses: $karyawan->initials
     */
    public function getInitialsAttribute()
    {
        if (!$this->nama) return "??";
        $words = explode(' ', $this->nama);
        $initials = collect($words)->map(fn($w) => mb_substr($w, 0, 1))->take(2)->join('');
        return strtoupper($initials);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS (Fungsi Pembantu Logika Bisnis)
    |--------------------------------------------------------------------------
    */

    /**
     * Cek apakah statusnya adalah kelompok magang.
     */
    public function isMagang(): bool
    {
        $statusLower = strtolower($this->status);
        return str_contains($statusLower, 'magang');
    }

    /**
     * Cek apakah statusnya adalah karyawan tetap.
     */
    public function isTetap(): bool
    {
        return $this->status === 'tetap';
    }

    /**
     * Scope pencarian cepat berdasarkan NIP, NIK, atau Barcode Token.
     * Penggunaan di Controller: Karyawan::byIdentifier('12345')->first();
     */
    public function scopeByIdentifier($query, $identifier)
    {
        return $query->where('nip', $identifier)
                     ->orWhere('nik', $identifier)
                     ->orWhere('barcode_token', $identifier);
    }

    /**
     * Scope untuk mengambil karyawan aktif (bisa dikembangkan jika ada kolom is_active)
     */
    public function scopeAktif($query)
    {
        return $query->whereNotNull('tanggal_masuk');
    }
}