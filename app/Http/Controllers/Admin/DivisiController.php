<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller Divisi
 * Mengelola operasional departemen termasuk pengaturan jabatan dan keanggotaan personel.
 * Filenya lengkap mencakup seluruh fungsi CRUD, manajemen anggota, dan struktur jabatan JSON.
 */
class DivisiController extends Controller
{
    /**
     * Menampilkan daftar semua divisi beserta jumlah karyawannya.
     */
    public function index()
    {
        // Menggunakan withCount untuk performa yang lebih baik saat menghitung relasi karyawan
        $divisis = Divisi::withCount('karyawans')->get();
        
        return view('admin.divisi.index', compact('divisis'));
    }

    /**
     * Menyimpan data divisi baru ke database.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'           => 'required|string|max:255',
            'kode'           => 'required|string|max:10|unique:divisis,kode',
            'deskripsi'      => 'nullable|string',
            'tugas_utama'    => 'nullable|string',
            'warna'          => 'required|string',
            'icon'           => 'nullable|string'
        ]);

        // Inisialisasi daftar_jabatan kosong jika baru dibuat
        $data['daftar_jabatan'] = [];

        Divisi::create($data);

        return back()->with('success', 'Divisi ' . $request->nama . ' berhasil ditambahkan ke struktur organisasi!');
    }

    /**
     * Menampilkan detail satu divisi, daftar anggotanya, 
     * dan daftar karyawan yang tersedia untuk direkrut ke divisi tersebut.
     */
    public function show($id)
    {
        // Mengambil data divisi beserta relasi karyawannya
        $divisi = Divisi::with('karyawans')->findOrFail($id);

        // Mengambil karyawan yang belum memiliki divisi (Unassigned) agar bisa ditarik ke divisi ini
        $karyawanTersedia = Karyawan::whereNull('divisi_id')
                            ->orderBy('nama', 'asc')
                            ->get();

        return view('admin.divisi.show', compact('divisi', 'karyawanTersedia'));
    }

    /**
     * Memperbarui data dasar divisi (nama, kode, deskripsi, dll).
     */
    public function update(Request $request, Divisi $divisi)
    {
        $data = $request->validate([
            'nama'           => 'required|string|max:255',
            'kode'           => 'required|string|max:10|unique:divisis,kode,' . $divisi->id,
            'deskripsi'      => 'nullable|string',
            'tugas_utama'    => 'nullable|string',
            'warna'          => 'required|string',
            'icon'           => 'nullable|string'
        ]);

        $divisi->update($data);

        return back()->with('success', 'Informasi divisi ' . $divisi->nama . ' berhasil diperbarui!');
    }

    /**
     * Memperbarui struktur jabatan (Nama, Kuota, & Gaji) dalam format JSON.
     * Fungsi ini memperbaiki error "Undefined method updateJabatan"
     */
    public function updateJabatan(Request $request, $id)
    {
        $request->validate([
            'nama_jabatan'    => 'required|array',
            'nama_jabatan.*'  => 'required|string',
            'kuota_jabatan'   => 'required|array',
            'kuota_jabatan.*' => 'required|integer|min:0',
            'gaji_jabatan'    => 'nullable|array', // Tambahkan jika ada input gaji di form
        ]);

        try {
            $divisi = Divisi::findOrFail($id);
            
            $newJabatan = [];
            foreach ($request->nama_jabatan as $index => $nama) {
                // Ambil gaji dari input jika ada, jika tidak ambil gaji lama dari database
                $gajiLama = $divisi->getGajiJabatan($nama);
                $gajiBaru = isset($request->gaji_jabatan[$index]) 
                            ? (int) str_replace(['.', ','], '', $request->gaji_jabatan[$index]) 
                            : $gajiLama;

                $newJabatan[$nama] = [
                    'kuota' => (int) $request->kuota_jabatan[$index],
                    'gaji'  => $gajiBaru
                ];
            }

            $divisi->update([
                'daftar_jabatan' => $newJabatan
            ]);

            return back()->with('success', 'Struktur jabatan divisi ' . $divisi->nama . ' berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus data divisi.
     * Berdasarkan migration onDelete('set null'), data karyawan tidak akan hilang.
     */
    public function destroy(Divisi $divisi)
    {
        $namaDivisi = $divisi->nama;
        $divisi->delete();

        return redirect()->route('divisi.index')->with('success', "Divisi $namaDivisi telah dihapus dari sistem.");
    }

    /**
     * Menambahkan karyawan ke dalam divisi (Assign Personel).
     */
    public function tambahAnggota(Request $request, $id)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'jabatan'     => 'required|string|max:100'
        ]);

        try {
            DB::beginTransaction();

            $divisi = Divisi::findOrFail($id);
            $karyawan = Karyawan::findOrFail($request->karyawan_id);
            
            // Cek sisa kuota sebelum menambahkan
            if ($divisi->getSisaKuota($request->jabatan) <= 0) {
                return back()->with('error', 'Kuota untuk jabatan ' . $request->jabatan . ' sudah penuh!');
            }

            $karyawan->update([
                'divisi_id'  => $id,
                'jabatan'    => $request->jabatan,
                // Otomatis sinkronkan gaji pokok karyawan dengan standar gaji jabatan di divisi tsb
                'gaji_pokok' => $divisi->getGajiJabatan($request->jabatan)
            ]);

            DB::commit();
            return back()->with('success', 'Personel ' . $karyawan->nama . ' kini resmi bergabung!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan anggota ke divisi. Silakan coba lagi.');
        }
    }

    /**
     * Mengeluarkan anggota dari divisi (Unassign Personel).
     */
    public function hapusAnggota($karyawan_id)
    {
        $karyawan = Karyawan::findOrFail($karyawan_id);
        
        $karyawan->update([
            'divisi_id' => null,
            'jabatan'   => null
        ]);

        return back()->with('info', 'Status ' . $karyawan->nama . ' kini tidak terikat divisi (Unassigned).');
    }
}