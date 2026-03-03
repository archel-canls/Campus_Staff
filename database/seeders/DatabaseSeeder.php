<?php

namespace Database\Seeders;
 
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Absensi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Mengisi data awal untuk sistem management staff CDI secara lengkap.
     * LOGIKA: Gaji pokok, divisi, absensi per jam, dan tunjangan dibuat BERBEDA tiap bulan.
     */
    public function run(): void
    {
        // Mendapatkan bulan berjalan untuk simulasi (Misal: 1 untuk Januari, 2 untuk Februari)
        $currentMonth = now()->month;

        // 1. BUAT DATA MASTER DIVISI (Gaji Divisi Berbeda tiap Bulan)
        // Logika: Jika Februari, gaji standar jabatan naik/turun dibanding Januari
        $itGaji = ($currentMonth == 2) ? 10000000 : 9000000; // Februari 10jt, Januari 9jt
        $mktGaji = ($currentMonth == 2) ? 7000000 : 8500000;  // Februari 7jt, Januari 8.5jt

        $masterDivisi = [
            [
                'nama' => 'IT Development',
                'kode' => 'ITS',
                'deskripsi' => 'Pengembangan software dan infrastruktur digital.',
                'tugas_utama' => 'Software Development, Maintenance Server, IT Support',
                'daftar_jabatan' => [
                    'Lead Developer' => ['kuota' => 1, 'gaji' => $itGaji],
                    'Senior Developer' => ['kuota' => 3, 'gaji' => $itGaji - 1500000],
                    'Junior Developer' => ['kuota' => 5, 'gaji' => $itGaji - 3500000],
                    'UI/UX Designer' => ['kuota' => 2, 'gaji' => $itGaji - 3000000]
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
                    'Marketing Manager' => ['kuota' => 1, 'gaji' => $mktGaji],
                    'Social Media Specialist' => ['kuota' => 2, 'gaji' => $mktGaji - 3500000],
                    'Content Creator' => ['kuota' => 3, 'gaji' => $mktGaji - 4000000],
                    'SEO Specialist' => ['kuota' => 1, 'gaji' => $mktGaji - 3000000]
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
                    'daftar_jabatan' => $d['daftar_jabatan'],
                    'warna' => $d['warna'],
                    'icon' => $d['icon'],
                ]
            );
            $divisiIds[$d['nama']] = $createdDivisi->id;
        }

        // 2. BUAT AKUN NON-KARYAWAN
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

        // 3. DATA KARYAWAN (Dengan Logika Perubahan Gaji Individu tiap Bulan)
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
                'emergency' => ['nama' => 'Ibu Archel', 'hub' => 'Ibu', 'telp' => '08123456789'],
                'goldar' => 'O',
                'tanggungan' => 2,
                'gaji_custom_januari' => 2000000,
                'gaji_custom_februari' => 3000000 
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
                'emergency' => ['nama' => 'Bp. Santoso', 'hub' => 'Ayah', 'telp' => '08771234567'],
                'goldar' => 'A',
                'tanggungan' => 0,
                'gaji_custom_januari' => 1500000,
                'gaji_custom_februari' => 1800000 
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
                'emergency' => ['nama' => 'Ahmad', 'hub' => 'Saudara', 'telp' => '08551234567'],
                'goldar' => 'B',
                'tanggungan' => 3,
                'gaji_custom_januari' => 3500000,
                'gaji_custom_februari' => 4000000
            ]
        ];

        // 4. LOGIKA PERBEDAAN TUNJANGAN DAN ABSENSI PER JAM
        $tunjanganPerKepala = ($currentMonth == 2) ? 300000 : 200000; // Februari 300k, Jan 200k

        foreach ($dataKaryawan as $data) {
            DB::beginTransaction();
            try {
                $divisiId = $divisiIds[$data['divisi_nama']] ?? null;
                
                // Pilih Gaji Pokok Individu berdasarkan bulan running
                $gajiFinal = ($currentMonth == 2) ? $data['gaji_custom_februari'] : $data['gaji_custom_januari'];

                // B. Simpan Profil Karyawan
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
                        'tanggal_masuk'       => '2024-01-01',
                        'emergency_1_nama'     => $data['emergency']['nama'],
                        'emergency_1_hubungan' => $data['emergency']['hub'],
                        'emergency_1_telp'     => $data['emergency']['telp'],
                        
                        // Tunjangan berbeda tiap bulan
                        'jumlah_tanggungan'        => $data['tanggungan'],
                        'tunjangan_per_tanggungan' => $tunjanganPerKepala, 
                        
                        'barcode_token'            => $data['nip'], 
                        'gaji_pokok'               => $gajiFinal, // Gaji Pokok Individu berbeda
                    ]
                );

                // C. Simpan Akun User
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

                // D. BUAT HISTORI ABSENSI (Agar total jam kerja & upah per jam berbeda)
                // FIX: Menghapus angka 0 di depan angka 8 (08) untuk menghindari ParseError
                
                // Seed untuk Januari
                for ($d = 1; $d <= 20; $d++) {
                    Absensi::create([
                        'karyawan_id' => $karyawan->id,
                        'jam_masuk' => Carbon::create(2025, 1, $d, 8, 0, 0),
                        'jam_keluar' => Carbon::create(2025, 1, $d, 16, 0, 0), // 8 jam
                        'keterangan' => 'Hadir'
                    ]);
                }

                // Seed untuk Februari
                for ($d = 1; $d <= 20; $d++) {
                    Absensi::create([
                        'karyawan_id' => $karyawan->id,
                        'jam_masuk' => Carbon::create(2025, 2, $d, 8, 0, 0),
                        'jam_keluar' => Carbon::create(2025, 2, $d, 18, 0, 0), // 10 jam
                        'keterangan' => 'Hadir'
                    ]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("Gagal seeding: " . $data['nama'] . ". Error: " . $e->getMessage());
            }
        }
        
        $this->command->info('✅ Database Full Seeder Selesai!');
        $this->command->warn("Simulasi: Bulan Saat Ini (" . Carbon::now()->format('F') . ")");
        $this->command->info("- Gaji Pokok & Divisi: BERBEDA");
        $this->command->info("- Upah per Jam: Januari 25k vs Februari 30k");
        $this->command->info("- Tunjangan: Januari 200k vs Februari 300k");
    }
}