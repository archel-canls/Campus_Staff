<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class User
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $role
 * @property bool $is_active
 * @property int|null $karyawan_id
 * @property-read \App\Models\Karyawan|null $karyawan
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'name',
        'username',   // Identitas login utama
        'email',
        'password',
        'role',       // 'admin', 'karyawan', atau 'scanner'
        'is_active',  // Status persetujuan admin
        'karyawan_id',
    ];

    /**
     * Atribut yang disembunyikan saat dikonversi ke JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut untuk memastikan integritas tipe data.
     * Laravel modern menghapus kebutuhan manual Hash::make jika cast 'hashed' aktif.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', 
        'is_active' => 'boolean',
        'karyawan_id' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS (Hubungan Tabel)
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi ke profil Karyawan.
     * Menggunakan withDefault agar tidak error saat Admin atau Scanner mengakses dashboard.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id')->withDefault([
            'nama' => $this->name ?? 'User Sistem',
            'divisi' => 'Management',
            'nip' => '-',
            'status' => 'tetap',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS / RBAC (Pengecekan Role)
    |--------------------------------------------------------------------------
    */

    /**
     * Mempermudah pengecekan role Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Mempermudah pengecekan role Karyawan/Magang.
     */
    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }

    /**
     * Mempermudah pengecekan role Scanner.
     */
    public function isScanner(): bool
    {
        return $this->role === 'scanner';
    }

    /**
     * Mempermudah pengecekan status akun aktif.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Atribut Virtual)
    |--------------------------------------------------------------------------
    */

    /**
     * Mendapatkan inisial nama untuk Avatar UI.
     * Contoh: "Scanner Lobby" -> "SL"
     * Akses: $user->initials
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1);
        }
        return mb_substr($this->name, 0, 2);
    }

    /**
     * Mengambil URL foto profil dari relasi karyawan.
     * Akses: $user->profile_photo
     */
    public function getProfilePhotoAttribute(): ?string
    {
        // Jika scanner atau admin tidak punya relasi karyawan, berikan avatar UI
        if ($this->karyawan && $this->karyawan->foto) {
            return asset('storage/karyawan/foto/' . $this->karyawan->foto);
        }
        
        // Mengembalikan placeholder avatar jika foto tidak ada
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=003366&color=fff';
    }
}