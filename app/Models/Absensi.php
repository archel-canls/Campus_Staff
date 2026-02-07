<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    /**
     * Kolom yang dapat diisi secara mass-assignment.
     * jam_keluar WAJIB ada di sini agar fitur Scan Keluar berfungsi.
     */
    protected $fillable = [
        'karyawan_id',
        'jam_masuk',
        'jam_keluar',
        'keterangan'
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
     * Berguna untuk label/badge di halaman Riwayat atau Laporan.
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
     * Helper: Menghitung total jam kerja jika sudah absen keluar.
     */
    public function getDurasiKerjaAttribute()
    {
        if ($this->jam_masuk && $this->jam_keluar) {
            return $this->jam_masuk->diff($this->jam_keluar)->format('%H jam %I menit');
        }
        return '-';
    }
}