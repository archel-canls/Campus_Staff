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
 * Struktur data sinkron dengan Form Registrasi CDI, Migration, Payroll System, dan Digital ID Card.
 * UPDATE: Mendukung histori penggajian dinamis per bulan dan relasi divisi yang solid.
 * @property \App\Models\Divisi|null $divisi
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
        'status_pendidikan',     // Lulus, Mahasiswa Aktif, dsb.
        'jumlah_tanggungan',
        'barcode_token',
        'foto',
        
        // Catatan: Kolom gaji_pokok & tunjangan di bawah ini kini berfungsi sebagai 
        // "Nilai Master/Default Saat Ini". Histori riil per bulan ada di relasi payrollHistories.
        'gaji_pokok',           
        'tunjangan_per_tanggungan', 
        'bukti_tanggungan',
    ];

    /**
     * Casting atribut agar otomatis menjadi tipe data yang sesuai saat diakses.
     */
    protected $casts = [
        'tanggal_lahir'            => 'date',
        'tanggal_masuk'            => 'date',
        'gaji_pokok'               => 'double',
        'jumlah_tanggungan'        => 'integer',
        'tunjangan_per_tanggungan' => 'double',
        'created_at'               => 'datetime',
        'updated_at'               => 'datetime',
    ];

    /**
     * Boot function untuk handling Delete Cascade dan Cleanup File.
     * Memastikan data terkait (User, Absensi, History) terhapus saat karyawan dihapus.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($karyawan) {
            // 1. Hapus relasi yang bergantung pada id karyawan
            $karyawan->absensis()->delete();
            $karyawan->payrollHistories()->delete(); 
            
            if (method_exists($karyawan, 'perizinans')) {
                $karyawan->perizinans()->delete();
            }
            
            // 2. Hapus file fisik (Foto & Bukti Tanggungan)
            if ($karyawan->foto) {
                Storage::disk('public')->delete('karyawan/foto/' . $karyawan->foto);
            }
            if ($karyawan->bukti_tanggungan) {
                Storage::disk('public')->delete('karyawan/bukti_tanggungan/' . $karyawan->bukti_tanggungan);
            }

            // 3. Hapus akun user terkait agar tidak menjadi data sampah
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
     * Relasi ke Tabel Divisi.
     */
    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    /**
     * Relasi ke Akun Login User.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'karyawan_id');
    }

    /**
     * Relasi ke Riwayat Absensi.
     */
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'karyawan_id');
    }

    /**
     * Relasi ke Riwayat Izin/Sakit.
     */
    public function perizinans(): HasMany
    {
        return $this->hasMany(Perizinan::class, 'karyawan_id');
    }

    /**
     * Relasi Histori Penggajian Per Bulan (Snapshot).
     * Mencakup data gaji pokok, tunjangan, bonus, dan potongan pada periode terkait.
     */
    public function payrollHistories(): HasMany
    {
        return $this->hasMany(PayrollHistory::class, 'karyawan_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & DYNAMIC GETTERS
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil Nama Divisi dengan proteksi jika divisi dihapus (Null-safe).
     */
    public function getNamaDivisiAttribute()
    {
        return $this->divisi?->nama ?? 'Tanpa Divisi';
    }

    /**
     * Logika Cerdas: Ambil Gaji Pokok dari Histori Bulan Terkait.
     * Jika histori tidak ditemukan, ambil dari Gaji Pokok Master di profil.
     */
    public function getGajiByPeriode($bulan, $tahun)
    {
        $history = $this->payrollHistories()
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        return $history ? (float) $history->gaji_pokok_nominal : (float) $this->gaji_pokok;
    }

    /**
     * Logika Cerdas: Ambil Rate Per Jam dari Histori Bulan Terkait.
     */
    public function getHourlyRateByPeriode($bulan, $tahun)
    {
        $history = $this->payrollHistories()
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        return $history ? (float) $history->rate_absensi_per_jam : 25000;
    }

    public function getAgeAttribute()
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : null;
    }

    public function getMasaKerjaAttribute()
    {
        if (!$this->tanggal_masuk) return "-";
        return $this->tanggal_masuk->diff(now())->format('%y Tahun, %m Bulan');
    }

    public function getFormattedGajiPokokAttribute()
    {
        return 'Rp ' . number_format((float) $this->gaji_pokok, 0, ',', '.');
    }

    /**
     * Menghitung total tunjangan berdasarkan jumlah tanggungan di profil master.
     */
    public function getTotalTunjanganTanggunganAttribute()
    {
        return (float) ($this->jumlah_tanggungan * $this->tunjangan_per_tanggungan);
    }

    /**
     * Mengambil URL Foto Profil. Fallback ke UI-Avatars jika foto kosong.
     */
    public function getProfilePictureAttribute()
    {
        if ($this->foto && Storage::disk('public')->exists('karyawan/foto/' . $this->foto)) {
            return asset('storage/karyawan/foto/' . $this->foto);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nama) . '&background=003366&color=fff&bold=true';
    }

    /**
     * Mengambil URL Bukti Tanggungan (KK/Akta).
     */
    public function getBuktiTanggunganUrlAttribute()
    {
        if ($this->bukti_tanggungan && Storage::disk('public')->exists('karyawan/bukti_tanggungan/' . $this->bukti_tanggungan)) {
            return asset('storage/karyawan/bukti_tanggungan/' . $this->bukti_tanggungan);
        }
        return null;
    }

    /**
     * Menghasilkan inisial nama (Contoh: Budi Santoso -> BS).
     */
    public function getInitialsAttribute()
    {
        if (!$this->nama) return "??";
        $words = explode(' ', trim($this->nama));
        $initials = collect($words)->map(fn($w) => mb_substr($w, 0, 1))->take(2)->join('');
        return strtoupper($initials);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS (Logika Bisnis)
    |--------------------------------------------------------------------------
    | Digunakan untuk validasi status atau filter di Controller/View.
    |--------------------------------------------------------------------------
    */

    public function isMagang(): bool
    {
        $statusLower = strtolower($this->status);
        return str_contains($statusLower, 'magang');
    }

    public function isTetap(): bool
    {
        return strtolower($this->status) === 'tetap';
    }

    /**
     * Scope untuk mempermudah pencarian karyawan berdasarkan identitas apapun.
     */
    public function scopeByIdentifier($query, $identifier)
    {
        return $query->where('nip', $identifier)
                     ->orWhere('nik', $identifier)
                     ->orWhere('barcode_token', $identifier);
    }

    /**
     * Scope untuk filter karyawan yang masih aktif bekerja.
     */
    public function scopeAktif($query)
    {
        return $query->whereNotNull('tanggal_masuk');
    } 
}