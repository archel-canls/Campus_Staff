<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Absensi;
use App\Models\PayrollHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Mengisi data awal untuk sistem management staff CDI secara lengkap.
     */
    public function run(): void
    {
        // Mendapatkan bulan berjalan untuk simulasi (Misal: 1 untuk Januari, 2 untuk Februari)
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // 1. BUAT DATA MASTER DIVISI (Gaji Divisi Berbeda tiap Bulan)
        $itGaji = ($currentMonth == 2) ? 10000000 : 9000000;
        $mktGaji = ($currentMonth == 2) ? 7000000 : 8500000;

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

        // 2. BUAT AKUN NON-KARYAWAN (Otomatis Aktif & Tanpa OTP)
        User::updateOrCreate(['username' => 'admin'], [
            'name' => 'Administrator CDI',
            'email' => 'admin@cdi.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        User::updateOrCreate(['username' => 'scanner1'], [
            'name' => 'Mesin Scanner Lobby',
            'email' => 'scanner@cdi.id',
            'password' => Hash::make('password123'),
            'role' => 'scanner',
            'is_active' => true,
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // 3. DATA KARYAWAN
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
                'nama' => 'Reza Kurniawan',
                'username' => 'reza',
                'nip' => '260219505004',
                'nik' => '3301234567890004',
                'email' => 'reza@cdi.id',
                'jk' => 'L',
                'telepon' => '081299887766',
                'tempat_lahir' => 'Jakarta',
                'tgl_lahir' => '1998-10-15',
                'divisi_nama' => 'IT Development',
                'jabatan' => 'Senior Developer',
                'status' => 'tetap',
                'instansi' => 'PT. Citra Digital Indonesia',
                'pendidikan' => 'S1 Sistem Informasi',
                'status_pendidikan' => 'Lulus',
                'alamat_ktp' => 'Jl. Merdeka No. 5, Jakarta',
                'alamat_domisili' => 'Kost IT Elite, Semarang',
                'emergency' => ['nama' => 'Ayah Reza', 'hub' => 'Ayah', 'telp' => '08122222222'],
                'goldar' => 'B',
                'tanggungan' => 1,
                'gaji_custom_januari' => 1800000,
                'gaji_custom_februari' => 2500000
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

        // 4. PROSES SEEDING KARYAWAN & RELASI
        foreach ($dataKaryawan as $data) {
            DB::beginTransaction();
            try {
                $divisiId = $divisiIds[$data['divisi_nama']] ?? null;
                $divisi = Divisi::find($divisiId);

                $gajiPokok = ($currentMonth == 2) ? $data['gaji_custom_februari'] : $data['gaji_custom_januari'];
                $ratePerJam = ($currentMonth == 2) ? 30000 : 25000;
                $tunjanganPerKepala = ($currentMonth == 2) ? 300000 : 200000;

                // A. Simpan Profil Karyawan
                $karyawan = Karyawan::updateOrCreate(
                    ['nik' => $data['nik']],
                    [
                        'nama' => $data['nama'],
                        'nip' => $data['nip'],
                        'tempat_lahir' => $data['tempat_lahir'],
                        'tanggal_lahir' => $data['tgl_lahir'],
                        'jenis_kelamin' => $data['jk'],
                        'golongan_darah' => $data['goldar'],
                        'alamat_ktp' => $data['alamat_ktp'],
                        'alamat_domisili' => $data['alamat_domisili'],
                        'telepon' => $data['telepon'],
                        'status' => $data['status'],
                        'instansi' => $data['instansi'],
                        'pendidikan_terakhir' => $data['pendidikan'],
                        'status_pendidikan' => $data['status_pendidikan'],
                        'divisi_id' => $divisiId,
                        'jabatan' => $data['jabatan'],
                        'tanggal_masuk' => '2024-01-01',
                        'emergency_1_nama' => $data['emergency']['nama'],
                        'emergency_1_hubungan' => $data['emergency']['hub'],
                        'emergency_1_telp' => $data['emergency']['telp'],
                        'jumlah_tanggungan' => $data['tanggungan'],
                        'tunjangan_per_tanggungan' => $tunjanganPerKepala,
                        'barcode_token' => $data['nip'],
                        'gaji_pokok' => $gajiPokok,
                    ]
                );

                // B. Simpan Akun User
                // is_active diset true agar data seeder bisa langsung login tanpa verifikasi admin
                User::updateOrCreate(
                    ['username' => strtolower($data['username'])],
                    [
                        'name' => $karyawan->nama,
                        'email' => $data['email'],
                        'password' => Hash::make('password123'),
                        'role' => 'karyawan',
                        'karyawan_id' => $karyawan->id,
                        'is_active' => true,
                        'otp_code' => null,
                        'otp_expires_at' => null,
                    ]
                );

                // C. Buat Snapshot Histori Penggajian
                PayrollHistory::updateOrCreate(
                    ['karyawan_id' => $karyawan->id, 'bulan' => $currentMonth, 'tahun' => $currentYear],
                    [
                        'gaji_pokok_nominal' => $gajiPokok,
                        'gaji_divisi_snapshot' => $divisi ? $divisi->getGajiJabatan($data['jabatan']) : 0,
                        'rate_absensi_per_jam' => $ratePerJam,
                        'tunjangan_per_tanggungan' => $tunjanganPerKepala,
                        'jumlah_tanggungan_snapshot' => $data['tanggungan'],
                        'bonus_tambahan' => 0,
                        'potongan_gaji' => 0,
                        'keterangan' => 'Generated by System Seeder'
                    ]
                );

                // D. LOGIKA ABSENSI KHUSUS REZA KURNIAWAN
                if ($data['nama'] === 'Reza Kurniawan') {
                    $yesterday = Carbon::yesterday();

                    // Absen Kemarin (Jam 8 - 4)
                    Absensi::updateOrCreate(
                        [
                            'karyawan_id' => $karyawan->id,
                            'jam_masuk' => $yesterday->copy()->setHour(8)->setMinute(0)->setSecond(0)
                        ],
                        [
                            'jam_keluar' => $yesterday->copy()->setHour(16)->setMinute(0)->setSecond(0),
                            'keterangan' => 'Hadir'
                        ]
                    );

                    // Absen Februari Tanggal 20 (Jam 8 - 4)
                    Absensi::updateOrCreate(
                        [
                            'karyawan_id' => $karyawan->id,
                            'jam_masuk' => Carbon::create($currentYear, 2, 20, 8, 0, 0)
                        ],
                        [
                            'jam_keluar' => Carbon::create($currentYear, 2, 20, 16, 0, 0),
                            'keterangan' => 'Hadir'
                        ]
                    );
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("Gagal seeding: " . $data['nama'] . ". Error: " . $e->getMessage());
            }
        }

        $this->command->info('✅ Database Full Seeder Selesai!');
        $this->command->info("- Akun Master (Admin/Scanner) & Karyawan Default telah diaktifkan.");
        $this->command->info("- Data OTP diset kosong untuk user seeder.");
    }
}
