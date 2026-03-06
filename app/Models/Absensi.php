<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     */
    protected $table = 'absensis';

    /**
     * Kolom yang dapat diisi secara mass-assignment.
     * jam_keluar WAJIB ada di sini agar fitur Scan Keluar berfungsi.
     * UPDATE: Menambahkan latitude dan longitude untuk melacak lokasi absen.
     */
    protected $fillable = [
        'karyawan_id',
        'jam_masuk',
        'jam_keluar',
        'keterangan',
        'latitude',
        'longitude'
    ];

    /**
     * Casting atribut ke tipe data Carbon.
     * Sangat penting agar kita bisa menggunakan fungsi ->format() di view/blade.
     */
    protected $casts = [
        'jam_masuk' => 'datetime',
        'jam_keluar' => 'datetime',
    ];

    /**
     * Relasi: Setiap data Absensi dimiliki oleh satu Karyawan.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Scope: Helper untuk mempermudah query absensi hari ini di Controller.
     * Cara pakai di Controller: Absensi::hariIni()->get();
     */
    public function scopeHariIni($query)
    {
        return $query->whereDate('jam_masuk', now()->toDateString());
    }

    /**
     * Accessor: Menentukan warna status berdasarkan keterangan/kondisi.
     * Berguna untuk menentukan warna label/badge secara otomatis di UI.
     */
    public function getStatusColorAttribute()
    {
        if ($this->keterangan === 'Terlambat') {
            return 'orange';
        }
        
        if ($this->jam_masuk && $this->jam_keluar) {
            return 'blue'; // Sudah pulang
        }

        return 'green'; // Hadir tepat waktu & belum pulang
    }

    /**
     * Helper: Menghitung selisih waktu antara jam masuk dan jam keluar.
     * Menghasilkan format string seperti "08 jam 30 menit".
     */
    public function getDurasiKerjaAttribute()
    {
        if ($this->jam_masuk && $this->jam_keluar) {
            return $this->jam_masuk->diff($this->jam_keluar)->format('%H jam %I menit');
        }
        return '-';
    }

    /**
     * Helper: Menghasilkan link Google Maps berdasarkan koordinat GPS yang tersimpan.
     * Memungkinkan Admin untuk mengklik link dan langsung melihat lokasi karyawan saat scan.
     */
    public function getGoogleMapsLinkAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        return null;
    }
}