<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Divisi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Mengisi data awal untuk sistem management staff CDI secara lengkap.
     * Mendukung sistem kuota jabatan dan penggajian dalam format JSON.
     */
    public function run(): void
    {
        // 1. BUAT DATA MASTER DIVISI (Dengan Struktur Jabatan, Kuota, dan Gaji JSON)
        $masterDivisi = [
            [
                'nama' => 'IT Development',
                'kode' => 'ITS',
                'deskripsi' => 'Pengembangan software dan infrastruktur digital.',
                'tugas_utama' => 'Software Development, Maintenance Server, IT Support',
                'daftar_jabatan' => [
                    'Lead Developer' => ['kuota' => 1, 'gaji' => 9000000],
                    'Senior Developer' => ['kuota' => 3, 'gaji' => 7500000],
                    'Junior Developer' => ['kuota' => 5, 'gaji' => 5500000],
                    'UI/UX Designer' => ['kuota' => 2, 'gaji' => 6000000]
                ],
                'warna' => 'blue',
                'icon' => 'fas fa-code'
            ],
            [
                'nama' => 'Marketing',
                'kode' => 'MKT',
                'deskripsi' => 'Strategi branding dan kampanye digital.',
                'tugas_utama' => 'Branding, Social Media, Ads Optimization',
                'daftar_jabatan' => [
                    'Marketing Manager' => ['kuota' => 1, 'gaji' => 8500000],
                    'Social Media Specialist' => ['kuota' => 2, 'gaji' => 5000000],
                    'Content Creator' => ['kuota' => 3, 'gaji' => 4500000],
                    'SEO Specialist' => ['kuota' => 1, 'gaji' => 5500000]
                ],
                'warna' => 'orange',
                'icon' => 'fas fa-ad'
            ],
            [
                'nama' => 'Human Resource',
                'kode' => 'HRD',
                'deskripsi' => 'Manajemen SDM dan kesejahteraan staf.',
                'tugas_utama' => 'Recruitment, Payroll, Employee Engagement',
                'daftar_jabatan' => [
                    'HR Manager' => ['kuota' => 1, 'gaji' => 8000000],
                    'HR Staff Intern' => ['kuota' => 4, 'gaji' => 3000000],
                    'Payroll Officer' => ['kuota' => 1, 'gaji' => 5000000]
                ],
                'warna' => 'green',
                'icon' => 'fas fa-user-tie'
            ]
        ];

        $divisiIds = [];
        foreach ($masterDivisi as $d) {
            $createdDivisi = Divisi::updateOrCreate(
                ['kode' => $d['kode']], 
                [
                    'nama' => $d['nama'],
                    'deskripsi' => $d['deskripsi'],
                    'tugas_utama' => $d['tugas_utama'],
                    'daftar_jabatan' => $d['daftar_jabatan'], // Eloquent cast otomatis ke JSON
                    'warna' => $d['warna'],
                    'icon' => $d['icon'],
                ]
            );
            $divisiIds[$d['nama']] = $createdDivisi->id;
        }

        // 2. BUAT AKUN ADMIN & SCANNER
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator CDI',
                'email' => 'admin@cdi.id',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'karyawan_id' => null,
            ]
        );

        User::updateOrCreate(
            ['username' => 'scanner1'],
            [
                'name' => 'Mesin Scanner Lobby',
                'email' => 'scanner@cdi.id',
                'password' => Hash::make('password123'),
                'role' => 'scanner',
                'karyawan_id' => null,
            ]
        );

        // 3. DATA KARYAWAN & MAGANG LENGKAP
        $dataKaryawan = [
            [
                'nama' => 'Archel Arisandi',
                'username' => 'archel',
                'nip' => '260219505001', 
                'nik' => '3301234567890001',
                'email' => 'archel@cdi.id',
                'jk' => 'L',
                'telepon' => '081222333444',
                'tempat_lahir' => 'Semarang',
                'tgl_lahir' => '1995-05-20',
                'divisi_nama' => 'IT Development',
                'jabatan' => 'Lead Developer',
                'status' => 'tetap',
                'instansi' => 'PT. Citra Digital Indonesia',
                'pendidikan' => 'S1 Teknik Informatika',
                'status_pendidikan' => 'Lulus',
                'alamat_ktp' => 'Jl. Digital Raya No. 1, Semarang',
                'alamat_domisili' => 'Perumahan Dev Baru Blok A, Semarang',
                'emergency' => [
                    'nama' => 'Ibu Archel',
                    'hub' => 'Ibu',
                    'telp' => '08123456789'
                ],
                'goldar' => 'O',
                'tanggungan' => 2
            ],
            [
                'nama' => 'Budi Santoso',
                'username' => 'budi',
                'nip' => '260210308002', 
                'nik' => '3301234567890002',
                'email' => 'budi@cdi.id',
                'jk' => 'L',
                'telepon' => '081333444555',
                'tempat_lahir' => 'Solo',
                'tgl_lahir' => '2003-08-12',
                'divisi_nama' => 'Marketing',
                'jabatan' => 'Social Media Specialist',
                'status' => 'magang_kampus',
                'instansi' => 'Universitas Diponegoro',
                'pendidikan' => 'S1 Ilmu Komunikasi',
                'status_pendidikan' => 'Mahasiswa Aktif',
                'alamat_ktp' => 'Jl. Pemuda No. 10, Semarang',
                'alamat_domisili' => 'Kost Nyaman Gajahmungkur, Semarang',
                'emergency' => [
                    'nama' => 'Bp. Santoso',
                    'hub' => 'Ayah',
                    'telp' => '08771234567'
                ],
                'goldar' => 'A',
                'tanggungan' => 0
            ],
            [
                'nama' => 'Siti Aminah',
                'username' => 'sitia',
                'nip' => '260220401003', 
                'nik' => '3301234567890003',
                'email' => 'siti@cdi.id',
                'jk' => 'P',
                'telepon' => '081444555666',
                'tempat_lahir' => 'Demak',
                'tgl_lahir' => '2004-01-25',
                'divisi_nama' => 'Human Resource',
                'jabatan' => 'HR Staff Intern',
                'status' => 'magang_mandiri',
                'instansi' => 'Politeknik Negeri Semarang',
                'pendidikan' => 'D3 Administrasi Bisnis',
                'status_pendidikan' => 'Lulus',
                'alamat_ktp' => 'Jl. Kaligawe No. 5, Semarang',
                'alamat_domisili' => 'Jl. Kaligawe No. 5, Semarang',
                'emergency' => [
                    'nama' => 'Ahmad',
                    'hub' => 'Saudara / Kerabat',
                    'telp' => '08551234567'
                ],
                'goldar' => 'B',
                'tanggungan' => 0
            ]
        ];

        foreach ($dataKaryawan as $data) {
            DB::beginTransaction();
            try {
                // A. Simpan Profil Karyawan Lengkap
                $divisiId = $divisiIds[$data['divisi_nama']] ?? null;
                
                // Cari nominal gaji dari struktur JSON divisi
                $gajiNominal = 0;
                if ($divisiId) {
                    $div = Divisi::find($divisiId);
                    $gajiNominal = $div->daftar_jabatan[$data['jabatan']]['gaji'] ?? 0;
                }

                $karyawan = Karyawan::updateOrCreate(
                    ['nik' => $data['nik']], 
                    [
                        'nama'                => $data['nama'],
                        'nip'                 => $data['nip'],
                        'tempat_lahir'        => $data['tempat_lahir'],
                        'tanggal_lahir'       => $data['tgl_lahir'],
                        'jenis_kelamin'       => $data['jk'],
                        'golongan_darah'      => $data['goldar'],
                        'alamat_ktp'          => $data['alamat_ktp'],
                        'alamat_domisili'     => $data['alamat_domisili'],
                        'telepon'             => $data['telepon'],
                        'status'              => $data['status'],
                        'instansi'            => $data['instansi'],
                        'pendidikan_terakhir' => $data['pendidikan'],
                        'status_pendidikan'   => $data['status_pendidikan'],
                        'divisi_id'           => $divisiId,
                        'jabatan'             => $data['jabatan'],
                        'tanggal_masuk'       => now()->format('Y-m-d'),
                        
                        'emergency_1_nama'      => $data['emergency']['nama'],
                        'emergency_1_hubungan'  => $data['emergency']['hub'],
                        'emergency_1_telp'      => $data['emergency']['telp'],
                        
                        'jumlah_tanggungan'     => $data['tanggungan'],
                        'barcode_token'         => $data['nip'],
                        // Gaji pokok diambil dari master jabatan divisi, jika tidak ada baru pakai default
                        'gaji_pokok'            => $gajiNominal > 0 ? $gajiNominal : (($data['status'] == 'tetap') ? 5000000 : 2000000),
                    ]
                );

                // B. Simpan Akun User untuk Login Karyawan
                User::updateOrCreate(
                    ['username' => strtolower($data['username'])],
                    [
                        'name'        => $karyawan->nama,
                        'email'       => $data['email'],
                        'password'    => Hash::make('password123'), 
                        'role'        => 'karyawan',
                        'karyawan_id' => $karyawan->id,
                    ]
                );

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("Gagal seeding: " . $data['nama'] . ". Error: " . $e->getMessage());
            }
        }
        
        $this->command->info('✅ Database Full Seeding Selesai (Struktur Gaji & Jabatan Sinkron)!');
    }
}