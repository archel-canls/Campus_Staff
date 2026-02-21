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
 * Filenya lengkap mencakup seluruh fungsi CRUD dan manajemen anggota.
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
            /**
             * daftar_jabatan disimpan dalam format string dipisahkan koma 
             * atau JSON (tergantung preferensi implementasi Model)
             * Contoh input: "Manager, Senior Staff, Junior Staff"
             */
            'daftar_jabatan' => 'nullable|string', 
            'warna'          => 'required|string',
            'icon'           => 'nullable|string'
        ]);

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
     * Memperbarui data divisi yang sudah ada.
     */
    public function update(Request $request, Divisi $divisi)
    {
        $data = $request->validate([
            'nama'           => 'required|string|max:255',
            'kode'           => 'required|string|max:10|unique:divisis,kode,' . $divisi->id,
            'deskripsi'      => 'nullable|string',
            'tugas_utama'    => 'nullable|string',
            'daftar_jabatan' => 'nullable|string',
            'warna'          => 'required|string',
            'icon'           => 'nullable|string'
        ]);

        $divisi->update($data);

        return back()->with('success', 'Informasi divisi ' . $divisi->nama . ' berhasil diperbarui!');
    }

    /**
     * Menghapus data divisi.
     * Berdasarkan migration onDelete('set null'), data karyawan tidak akan hilang,
     * hanya kolom divisi_id di tabel karyawans yang akan menjadi NULL secara otomatis.
     */
    public function destroy(Divisi $divisi)
    {
        $namaDivisi = $divisi->nama;
        $divisi->delete();

        return redirect()->route('divisi.index')->with('success', "Divisi $namaDivisi telah dihapus dari sistem.");
    }

    /**
     * Menambahkan karyawan ke dalam divisi (Assign Personel).
     * Fungsi ini menghubungkan karyawan ke divisi_id dan memberikan jabatan tertentu.
     */
    public function tambahAnggota(Request $request, $id)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'jabatan'     => 'required|string|max:100'
        ]);

        try {
            DB::beginTransaction();

            $karyawan = Karyawan::findOrFail($request->karyawan_id);
            
            $karyawan->update([
                'divisi_id' => $id,
                'jabatan'   => $request->jabatan
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
     * Mengosongkan divisi_id dan jabatan tanpa menghapus identitas karyawan.
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