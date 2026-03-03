<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollHistory extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     */
    protected $table = 'payroll_histories';

    /**
     * Atribut yang dapat diisi secara massal.
     * Mencakup semua parameter penggajian yang bersifat snapshot per periode.
     */
    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'gaji_pokok_nominal',      // Snapshot Tab: Gaji Pokok Individu
        'gaji_divisi_snapshot',    // Snapshot Tab: Gaji Per Jabatan
        'rate_absensi_per_jam',    // Snapshot Tab: Rate Per Jam
        'tunjangan_per_tanggungan', // Snapshot Tab: Tunjangan Keluarga
        'keterangan'
    ];

    /**
     * Casting tipe data agar lebih konsisten saat perhitungan matematika.
     */
    protected $casts = [
        'gaji_pokok_nominal' => 'decimal:2',
        'gaji_divisi_snapshot' => 'decimal:2',
        'rate_absensi_per_jam' => 'decimal:2',
        'tunjangan_per_tanggungan' => 'decimal:2',
        'bulan' => 'integer',
        'tahun' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi balik ke Karyawan.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Perhitungan Otomatis)
    |--------------------------------------------------------------------------
    */

    /**
     * Menghitung total tunjangan keluarga berdasarkan jumlah tanggungan karyawan
     * dikalikan nilai snapshot tunjangan per jiwa pada periode ini.
     */
    public function getTotalTunjanganKeluargaAttribute()
    {
        $jumlahTanggungan = $this->karyawan->jumlah_tanggungan ?? 0;
        return $this->tunjangan_per_tanggungan * $jumlahTanggungan;
    }

    /**
     * Menghitung Take Home Pay (THP) murni dari data snapshot history ini.
     * Catatan: total_jam_kerja biasanya diambil dari tabel absensi di periode terkait.
     */
    public function hitungTotalGaji($totalJamKerja = 0)
    {
        $bonusAbsensi = $totalJamKerja * $this->rate_absensi_per_jam;
        $totalTunjangan = $this->total_tunjangan_keluarga;

        return $this->gaji_pokok_nominal + $this->gaji_divisi_snapshot + $bonusAbsensi + $totalTunjangan;
    }

    /**
     * Scope untuk mempermudah filter berdasarkan periode di Controller.
     */
    public function scopePeriode($query, $bulan, $tahun)
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }
}