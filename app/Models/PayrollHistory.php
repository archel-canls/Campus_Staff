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
     * UPDATED: Menambahkan bonus_tambahan dan potongan_gaji.
     */
    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'gaji_pokok_nominal',        // Snapshot Tab: Gaji Pokok Individu
        'gaji_divisi_snapshot',      // Snapshot Tab: Gaji Per Jabatan (dari Divisi)
        'rate_absensi_per_jam',      // Snapshot Tab: Rate Per Jam
        'tunjangan_per_tanggungan',   // Snapshot Tab: Tunjangan Keluarga (Per Jiwa)
        'bonus_tambahan',            // Input Dinamis: Bonus/Insentif
        'potongan_gaji',             // Input Dinamis: Potongan/Kasbon/Denda
        'jumlah_tanggungan_snapshot', // Snapshot jumlah tanggungan saat periode ini
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
        'bonus_tambahan' => 'decimal:2',
        'potongan_gaji' => 'decimal:2',
        'bulan' => 'integer',
        'tahun' => 'integer',
        'jumlah_tanggungan_snapshot' => 'integer',
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
     * Menghitung total tunjangan keluarga.
     * Menggunakan jumlah_tanggungan_snapshot jika tersedia, 
     * jika tidak (data lama) maka fallback ke data master karyawan.
     */
    public function getTotalTunjanganKeluargaAttribute()
    {
        $jumlah = $this->jumlah_tanggungan_snapshot > 0 
                  ? $this->jumlah_tanggungan_snapshot 
                  : ($this->karyawan->jumlah_tanggungan ?? 0);

        return $this->tunjangan_per_tanggungan * $jumlah;
    }

    /**
     * Menghitung Take Home Pay (THP) murni dari data snapshot history ini.
     * UPDATED: Sekarang menghitung Bonus Tambahan dan Potongan Gaji.
     * * Rumus: (Gaji Pokok + Gaji Jabatan + Bonus Absensi + Tunjangan Keluarga + Bonus Lainnya) - Potongan
     * * @param float|int $totalJamKerja
     * @return float
     */
    public function hitungTotalGaji($totalJamKerja = 0)
    {
        $bonusAbsensi = $totalJamKerja * $this->rate_absensi_per_jam;
        $totalTunjangan = $this->total_tunjangan_keluarga;

        // Total Pendapatan
        $pendapatan = $this->gaji_pokok_nominal + 
                      $this->gaji_divisi_snapshot + 
                      $bonusAbsensi + 
                      $totalTunjangan + 
                      $this->bonus_tambahan;

        // Total Akhir (Pendapatan - Potongan)
        return $pendapatan - $this->potongan_gaji;
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope untuk mempermudah filter berdasarkan periode di Controller.
     */
    public function scopePeriode($query, $bulan, $tahun)
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }
}