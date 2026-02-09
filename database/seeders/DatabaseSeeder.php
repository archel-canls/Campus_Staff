<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Mengisi data awal untuk testing sistem management staff CDI dengan format NIP baru.
     */
    public function run(): void
    {
        // 1. BUAT ATAU UPDATE AKUN ADMIN UTAMA
        // Admin biasanya tidak memiliki profil di tabel karyawans (karyawan_id = null)
        User::updateOrCreate(
            ['username' => 'admin'], // Kunci unik untuk pengecekan
            [
                'name' => 'Administrator CDI',
                'email' => 'admin@cdi.id',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'karyawan_id' => null,
            ]
        );

        // 2. BUAT AKUN KHUSUS SCANNER (Perangkat di Lobby)
        // Akun ini hanya bisa akses halaman scan saja
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

        // 3. DATA DUMMY KARYAWAN & MAGANG
        // Struktur NIP Baru: YYMM (Masuk) + G (Gender) + YYMM (Lahir) + RRR (Urut)
        $dataKaryawan = [
            [
                'nama' => 'Archel Arisandi',
                'username' => 'archel',
                // Masuk: 2602, JK: 1 (L), Lahir: 9505 (Mei 1995), Urut: 001
                'nip' => '260219505001', 
                'nik' => '3301234567890001',
                'email' => 'archel@cdi.id',
                'jk' => 'L',
                'telepon' => '081222333444',
                'tempat_lahir' => 'Semarang',
                'tgl_lahir' => '1995-05-20',
                'divisi' => 'IT Development',
                'jabatan' => 'Lead Developer',
                'status' => 'tetap',
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
                // Masuk: 2602, JK: 1 (L), Lahir: 0308 (Agustus 2003), Urut: 002
                'nip' => '260210308002', 
                'nik' => '3301234567890002',
                'email' => 'budi@cdi.id',
                'jk' => 'L',
                'telepon' => '081333444555',
                'tempat_lahir' => 'Solo',
                'tgl_lahir' => '2003-08-12',
                'divisi' => 'Marketing',
                'jabatan' => 'Social Media Specialist',
                'status' => 'magang_kampus',
                'instansi' => 'Universitas Diponegoro',
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
                // Masuk: 2602, JK: 2 (P), Lahir: 0401 (Januari 2004), Urut: 003
                'nip' => '260220401003', 
                'nik' => '3301234567890003',
                'email' => 'siti@cdi.id',
                'jk' => 'P',
                'telepon' => '081444555666',
                'tempat_lahir' => 'Demak',
                'tgl_lahir' => '2004-01-25',
                'divisi' => 'Human Resource',
                'jabatan' => 'HR Staff Intern',
                'status' => 'magang_mandiri',
                'instansi' => 'Politeknik Negeri Semarang',
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
                // A. Simpan/Update Profil Karyawan (Cek berdasarkan NIK)
                $karyawan = Karyawan::updateOrCreate(
                    ['nik' => $data['nik']], 
                    [
                        'nama'                  => $data['nama'],
                        'nip'                   => $data['nip'],
                        'tempat_lahir'          => $data['tempat_lahir'],
                        'tanggal_lahir'         => $data['tgl_lahir'],
                        'jenis_kelamin'         => $data['jk'],
                        'golongan_darah'        => $data['goldar'],
                        'alamat_ktp'            => $data['alamat_ktp'],
                        'alamat_domisili'       => $data['alamat_domisili'],
                        'telepon'               => $data['telepon'],
                        'status'                => $data['status'],
                        'instansi'              => $data['instansi'] ?? null,
                        'divisi'                => $data['divisi'],
                        'jabatan'               => $data['jabatan'],
                        'tanggal_masuk'         => '2026-02-01', // Sesuaikan dengan YYMM NIP (Feb 2026)
                        
                        // Kontak Darurat Utama (Emergency 1)
                        'emergency_1_nama'      => $data['emergency']['nama'],
                        'emergency_1_hubungan'  => $data['emergency']['hub'],
                        'emergency_1_telp'      => $data['emergency']['telp'],
                        
                        // Kontak Darurat Cadangan (Emergency 2) - Nullable
                        'emergency_2_nama'      => null,
                        'emergency_2_hubungan'  => null,
                        'emergency_2_telp'      => null,

                        // Data Tanggungan & Finansial
                        'jumlah_tanggungan'     => $data['tanggungan'] ?? 0,
                        'bukti_tanggungan'      => null,
                        'barcode_token'         => $data['nip'],
                        'gaji_pokok'            => ($data['status'] == 'tetap') ? 5000000 : 2000000,
                        'foto'                  => null,
                    ]
                );

                // B. Simpan/Update Akun User (Cek berdasarkan Username)
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
                $this->command->error("Gagal melakukan seeding untuk: " . $data['nama'] . ". Error: " . $e->getMessage());
            }
        }
        
        // Memberikan feedback di terminal
        $this->command->info('✅ Database Berhasil Di-seed/Update dengan NIP Baru!');
        $this->command->warn('--------------------------------------------------');
        $this->command->info('Login Admin    : admin    | password123');
        $this->command->info('Login Scanner  : scanner1 | password123');
        $this->command->info('Login Karyawan : archel   | password123');
        $this->command->info('Login Magang   : budi     | password123');
        $this->command->warn('--------------------------------------------------');
    }
}