<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Model Karyawan
 * Struktur data sinkron dengan Form Registrasi CDI & Migration Terbaru.
 */
class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawans';

    /**
     * Mass Assignment Protection
     * Menampung semua input dari form registrasi multi-step.
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
        'status',              // tetap, kontrak, magang_kampus, magang_mandiri
        'instansi',            // Asal Sekolah/Kampus
        'divisi',
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

        // Data Legacy & Finansial
        'jumlah_tanggungan',
        'bukti_tanggungan',
        'pendidikan_terakhir',
        'barcode_token',
        'gaji_pokok',
        'foto',
    ];

    /**
     * Casting atribut agar otomatis menjadi tipe data yang sesuai.
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
     * Boot function untuk handling Delete Cascade.
     * Memastikan file fisik dan data relasi terhapus saat record karyawan dihapus.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($karyawan) {
            // 1. Hapus relasi yang bergantung pada id karyawan
            // Kita gunakan try-catch atau check method untuk keamanan
            if ($karyawan->absensis()) {
                $karyawan->absensis()->delete();
            }
            if ($karyawan->perizinans()) {
                $karyawan->perizinans()->delete();
            }
            
            // 2. Hapus file fisik (Foto & Bukti Tanggungan) dari storage
            // Sesuai dengan path di AuthController: karyawan/foto dan karyawan/bukti_tanggungan
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
    | RELATIONS
    |--------------------------------------------------------------------------
    */

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
    | ACCESSORS (Virtual Attributes)
    |--------------------------------------------------------------------------
    */

    /**
     * Mendapatkan umur saat ini. Akses: $karyawan->age
     */
    public function getAgeAttribute()
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : null;
    }

    /**
     * Format Rupiah untuk Gaji. Akses: $karyawan->formatted_gaji
     */
    public function getFormattedGajiAttribute()
    {
        return 'Rp ' . number_format((float) $this->gaji_pokok, 0, ',', '.');
    }

    /**
     * URL Foto Profil dengan Fallback UI-Avatars. Akses: $karyawan->profile_picture
     */
    public function getProfilePictureAttribute()
    {
        if ($this->foto && Storage::disk('public')->exists('karyawan/foto/' . $this->foto)) {
            return asset('storage/karyawan/foto/' . $this->foto);
        }
        
        // Jika foto kosong, buat avatar otomatis berdasarkan nama
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nama) . '&background=003366&color=fff';
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
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Cek apakah statusnya adalah magang.
     */
    public function isMagang(): bool
    {
        return str_contains(strtolower($this->status), 'magang');
    }

    /**
     * Cek apakah statusnya adalah karyawan tetap.
     */
    public function isTetap(): bool
    {
        return $this->status === 'tetap';
    }

    /**
     * Scope pencarian cepat berdasarkan NIP, NIK, atau Barcode.
     * Penggunaan: Karyawan::byIdentifier('12345')->first();
     */
    public function scopeByIdentifier($query, $identifier)
    {
        return $query->where('nip', $identifier)
                     ->orWhere('nik', $identifier)
                     ->orWhere('barcode_token', $identifier);
    }
}