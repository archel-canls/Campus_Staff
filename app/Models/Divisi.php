<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Divisi
 * Mengelola data departemen/divisi organisasi dan relasinya dengan personel (Karyawan).
 * Dilengkapi dengan fitur manajemen jabatan dinamis, sistem kuota, dan nominal gaji bulanan.
 */
class Divisi extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang dikelola oleh model ini.
     */
    protected $table = 'divisis';

    /**
     * Kolom-kolom yang dapat diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'nama',            // Contoh: IT Development
        'kode',            // Contoh: ITS
        'deskripsi',       // Penjelasan detail divisi
        'tugas_utama',     // Daftar tugas (disimpan sebagai string dipisah koma)
        'daftar_jabatan',  // Data jabatan, kuota, & GAJI (disimpan sebagai JSON)
        'icon',            // Class FontAwesome (fas fa-code)
        'warna',           // Kode warna (blue, orange, dll)
    ];

    /**
     * Casting atribut.
     * Mengubah kolom JSON daftar_jabatan otomatis menjadi array saat diakses.
     */
    protected $casts = [
        'daftar_jabatan' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS (Hubungan Antar Tabel)
    |--------------------------------------------------------------------------
    */

    /**
     * Relasi: Satu Divisi memiliki banyak Karyawan.
     * Memungkinkan pemanggilan anggota tim melalui: $divisi->karyawans
     */
    public function karyawans(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'divisi_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Atribut Virtual / Format Data)
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor: Menghitung jumlah total anggota dalam divisi secara keseluruhan.
     * Penggunaan di View: {{ $divisi->jumlah_anggota }}
     */
    public function getJumlahAnggotaAttribute()
    {
        return $this->karyawans()->count();
    }

    /**
     * Accessor: Memproses string tugas_utama menjadi array.
     */
    public function getDaftarTugasAttribute()
    {
        if (!$this->tugas_utama) {
            return [];
        }
        return array_map('trim', explode(',', $this->tugas_utama));
    }

    /**
     * Accessor: Mengambil hanya nama-nama jabatan (keys) dari array daftar_jabatan.
     */
    public function getJabatansAttribute()
    {
        if (empty($this->daftar_jabatan)) {
            return ['General Staff'];
        }
        return array_keys($this->daftar_jabatan);
    }

    /**
     * Accessor: Memberikan class background Tailwind berdasarkan atribut warna.
     */
    public function getBgColorClassAttribute()
    {
        $colors = [
            'blue'   => 'bg-blue-500',
            'orange' => 'bg-orange-500',
            'green'  => 'bg-green-500',
            'red'    => 'bg-red-500',
            'purple' => 'bg-purple-500',
            'indigo' => 'bg-indigo-500',
        ];

        return $colors[$this->warna] ?? 'bg-gray-500';
    }

    /**
     * Accessor: Memberikan class text Tailwind berdasarkan atribut warna.
     */
    public function getTextColorClassAttribute()
    {
        $colors = [
            'blue'   => 'text-blue-500',
            'orange' => 'text-orange-500',
            'green'  => 'text-green-500',
            'red'    => 'text-red-500',
            'purple' => 'text-purple-500',
            'indigo' => 'text-indigo-500',
        ];

        return $colors[$this->warna] ?? 'text-gray-500';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS (Fungsi Pembantu Logika Bisnis & Payroll)
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil Nominal Gaji Bulanan berdasarkan Nama Jabatan.
     * Mengambil data dari key nominal atau value langsung di JSON.
     * * @param string $namaJabatan
     * @return int
     */
    public function getGajiJabatan($namaJabatan)
    {
        $data = $this->daftar_jabatan[$namaJabatan] ?? 0;
        
        // Jika format JSON adalah {"Manager": 5000000}
        if (is_numeric($data)) {
            return (int) $data;
        }
        
        // Jika format JSON lebih kompleks {"Manager": {"gaji": 5000000, "kuota": 2}}
        return (int) ($data['gaji'] ?? 0);
    }

    /**
     * Menghitung sisa kuota yang tersedia untuk jabatan tertentu.
     * * @param string $namaJabatan
     * @return int
     */
    public function getSisaKuota($namaJabatan)
    {
        $data = $this->daftar_jabatan[$namaJabatan] ?? 0;
        
        // Ambil nilai kuota (jika format kompleks ambil key 'kuota', jika simpel gunakan value langsung)
        $max = is_array($data) ? ($data['kuota'] ?? 0) : $data;
        
        $terisi = $this->karyawans()->where('jabatan', $namaJabatan)->count();
        
        return max(0, $max - $terisi);
    }

    /**
     * Cek apakah divisi memiliki anggota.
     */
    public function hasMembers(): bool
    {
        return $this->karyawans()->exists();
    }
}