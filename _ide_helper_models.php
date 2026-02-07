<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $karyawan_id
 * @property \Illuminate\Support\Carbon $jam_masuk
 * @property \Illuminate\Support\Carbon|null $jam_keluar
 * @property string $keterangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $durasi_kerja
 * @property-read mixed $status_color
 * @property-read \App\Models\Karyawan $karyawan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi hariIni()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi whereJamKeluar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi whereJamMasuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi whereKaryawanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absensi whereUpdatedAt($value)
 */
	class Absensi extends \Eloquent {}
}

namespace App\Models{
/**
 * Model Karyawan
 * * @property int $id
 *
 * @property string $nama
 * @property string $nip
 * @property string $nik
 * @property string $alamat
 * @property \Illuminate\Support\Carbon $tanggal_lahir
 * @property string $divisi
 * @property string $jabatan
 * @property string $status
 * @property string $instansi
 * @property string $barcode_token
 * @property float $gaji_pokok
 * @property string $foto
 * @property-read int|null $age
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Absensi> $absensis
 * @property-read int|null $absensis_count
 * @property-read mixed $formatted_gaji
 * @property-read mixed $initials
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Perizinan> $perizinans
 * @property-read int|null $perizinans_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan byIdentifier($identifier)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereBarcodeToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereFoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereGajiPokok($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereInstansi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereTanggalLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereUpdatedAt($value)
 */
	class Karyawan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $karyawan_id
 * @property string $jenis_izin
 * @property \Illuminate\Support\Carbon $tanggal_mulai
 * @property \Illuminate\Support\Carbon $tanggal_selesai
 * @property int $lama_hari
 * @property string $alasan
 * @property string|null $lampiran_pdf
 * @property string $status
 * @property string|null $catatan_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $pdf_url
 * @property-read mixed $status_color
 * @property-read \App\Models\Karyawan $karyawan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan activeToday()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereAlasan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereCatatanAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereJenisIzin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereKaryawanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereLamaHari($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereLampiranPdf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereTanggalMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereTanggalSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Perizinan whereUpdatedAt($value)
 */
	class Perizinan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property int|null $karyawan_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $initials
 * @property-read mixed $profile_photo
 * @property-read \App\Models\Karyawan|null $karyawan
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereKaryawanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 */
	class User extends \Eloquent {}
}

