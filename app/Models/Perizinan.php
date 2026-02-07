<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Perizinan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang didefinisikan di database.
     */
    protected $table = 'perizinans';

    /**
     * Kolom yang dapat diisi melalui mass-assignment.
     */
    protected $fillable = [
        'karyawan_id',
        'jenis_izin',
        'tanggal_mulai',
        'tanggal_selesai',
        'lama_hari',
        'alasan',
        'lampiran_pdf',
        'status', // pending, disetujui, ditolak
        'catatan_admin'
    ];

    /**
     * Casting atribut ke tipe data tertentu.
     * Sangat penting untuk manipulasi tanggal di Controller & Blade.
     */
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'lama_hari' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi: Setiap data Perizinan dimiliki oleh satu Karyawan.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * SCOPE: Memudahkan filter perizinan yang sedang berlangsung hari ini.
     * Digunakan di Controller/Blade: Perizinan::activeToday()->get();
     */
    public function scopeActiveToday($query)
    {
        $today = Carbon::today();
        return $query->where('status', 'disetujui')
                     ->whereDate('tanggal_mulai', '<=', $today)
                     ->whereDate('tanggal_selesai', '>=', $today);
    }

    /**
     * ACCESSOR: Mendapatkan URL lengkap untuk file PDF.
     * Memudahkan di Blade: <a href="{{ $izin->pdf_url }}">
     */
    public function getPdfUrlAttribute()
    {
        if ($this->lampiran_pdf) {
            return asset('uploads/perizinan/' . $this->lampiran_pdf);
        }
        return null;
    }

    /**
     * ACCESSOR: Label status dengan warna Tailwind (Versi diperkuat).
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'disetujui' => 'bg-green-100 text-green-700 border-green-200',
            'ditolak'   => 'bg-red-100 text-red-700 border-red-200',
            default     => 'bg-orange-100 text-orange-700 border-orange-200',
        };
    }

    /**
     * HELPER: Cek apakah izin masih berlaku.
     */
    public function isExpired()
    {
        return Carbon::today()->gt($this->tanggal_selesai);
    }
}