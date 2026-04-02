<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Exception;

class ManajemenKaryawanController extends Controller
{
    /**
     * Menampilkan daftar semua staf/karyawan yang sudah aktif.
     */
    public function index(Request $request)
    {
        // Menggunakan eager loading 'divisi' untuk efisiensi
        $query = Karyawan::with('divisi');

        // Fitur Pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('nip', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%')
                    ->orWhereHas('divisi', function ($sq) use ($search) {
                        $sq->where('nama', 'like', '%' . $search . '%');
                    });
            });
        }

        // Ambil data yang usernya sudah aktif
        $karyawans = $query->whereHas('user', function ($q) {
            $q->where('is_active', true);
        })->latest()->get();

        return view('admin.manajemen-karyawan.index', compact('karyawans'));
    }

    /**
     * Menampilkan form tambah karyawan.
     */
    public function create()
    {
        $divisis = Divisi::all();
        return view('admin.manajemen-karyawan.create', compact('divisis'));
    }

    /**
     * Menyimpan data karyawan baru secara manual dari Admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'          => 'required|string|max:255',
            'nip'           => 'required|string|unique:karyawans,nip',
            'nik'           => 'required|string|unique:karyawans,nik|max:16',
            'divisi_id'     => 'required|exists:divisis,id',
            'jabatan'       => 'required|string',
            'status'        => 'required|in:tetap,kontrak,magang_mbkm,magang_ppl,magang_mandiri',
            'email'         => 'required|email|unique:users,email',
            'username'      => 'required|string|unique:users,username',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // 1. Ambil Divisi & Cek Kuota
            $divisi = Divisi::findOrFail($request->divisi_id);

            if ($divisi->getSisaKuota($request->jabatan) <= 0) {
                return redirect()->back()->withInput()->with('error', 'Kuota untuk jabatan ' . $request->jabatan . ' di divisi ini sudah penuh!');
            }

            $gajiPokok = $divisi->getGajiJabatan($request->jabatan);

            // 2. Buat Data Karyawan
            $karyawanData = $request->all();
            $karyawanData['barcode_token'] = 'CDI-' . strtoupper(Str::random(10));
            $karyawanData['gaji_pokok']    = $gajiPokok;
            $karyawanData['tanggal_masuk'] = now();

            if ($request->hasFile('foto')) {
                $filename = time() . '_' . Str::slug($request->nama) . '.' . $request->foto->extension();
                $request->file('foto')->storeAs('karyawan/foto', $filename, 'public');
                $karyawanData['foto'] = $filename;
            }

            $karyawan = Karyawan::create($karyawanData);

            // 3. Buat Akun User (Otomatis Aktif jika dibuat oleh Admin)
            User::create([
                'name'        => $request->nama,
                'username'    => $request->username,
                'email'       => $request->email,
                'password'    => bcrypt('12345678'), // Password default
                'role'        => 'karyawan',
                'is_active'   => true,
                'karyawan_id' => $karyawan->id,
            ]);

            DB::commit();
            return redirect()->route('manajemen-karyawan.index')
                ->with('success', 'Personel baru berhasil didaftarkan!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan profil detail lengkap.
     */
    public function show(string $id)
    {
        $karyawan = Karyawan::with(['divisi', 'user', 'absensis', 'payrollHistories'])->findOrFail($id);
        return view('admin.manajemen-karyawan.show', compact('karyawan'));
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(string $id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $divisis = Divisi::all();
        return view('admin.manajemen-karyawan.edit', compact('karyawan', 'divisis'));
    }

    /**
     * Memperbarui data karyawan.
     */
    public function update(Request $request, string $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $request->validate([
            'nama'      => 'required|string|max:255',
            'nip'       => ['required', 'string', Rule::unique('karyawans')->ignore($karyawan->id)],
            'divisi_id' => 'required|exists:divisis,id',
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->all();

            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($karyawan->foto) {
                    Storage::disk('public')->delete('karyawan/foto/' . $karyawan->foto);
                }
                $filename = time() . '_' . Str::slug($request->nama) . '.' . $request->foto->extension();
                $request->file('foto')->storeAs('karyawan/foto', $filename, 'public');
                $data['foto'] = $filename;
            }

            $karyawan->update($data);

            // Update nama di tabel users
            if ($karyawan->user) {
                $karyawan->user->update(['name' => $request->nama]);
            }

            DB::commit();
            return redirect()->route('manajemen-karyawan.index')
                ->with('success', 'Data karyawan berhasil diperbarui!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data.');
        }
    }

    /**
     * Menghapus data karyawan (Cleanup ditangani oleh Boot Function di Model).
     */
    public function destroy(string $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        try {
            // Note: Logic penghapusan file & user sudah ada di static::deleting model Karyawan
            $karyawan->delete();
            return redirect()->route('manajemen-karyawan.index')
                ->with('success', 'Data karyawan dan akun terkait telah dihapus.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI PENDAFTARAN (DARI WEB LUAR)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan pendaftar yang status is_active = false.
     */
    public function permohonan(Request $request)
    {
        $search = $request->search;

        // Mengambil data Divisi untuk dikirim ke View (Digunakan di Form Modal Approve)
        $divisis = Divisi::all();

        $permohonans = User::where('is_active', false)
            ->whereNotNull('karyawan_id')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            })
            ->with('karyawan.divisi')
            ->latest()
            ->get();

        return view('admin.manajemen-karyawan.permohonan', compact('permohonans', 'divisis'));
    }

    /**
     * Menyetujui pendaftaran.
     * Mengatur Divisi, Jabatan, Gaji Pokok, dan mengaktifkan akun.
     */
    public function approve(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validasi input dari modal permohonan
        $request->validate([
            'divisi_id' => 'required|exists:divisis,id',
            'jabatan'   => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            if ($user->karyawan) {
                $divisi = Divisi::findOrFail($request->divisi_id);

                // CEK SISA KUOTA (Mencegah pendaftaran melebihi kapasitas struktur organisasi)
                if ($divisi->getSisaKuota($request->jabatan) <= 0) {
                    return redirect()->back()->with('error', 'Gagal: Kuota untuk jabatan ' . $request->jabatan . ' di divisi ' . $divisi->nama . ' sudah penuh!');
                }

                // Ambil gaji pokok otomatis berdasarkan standar divisi & jabatan
                $gajiPokok = $divisi->getGajiJabatan($request->jabatan);

                // Update data karyawan: Pastikan kolom 'status' tidak berubah (tetap sesuai pilihan saat daftar)
                $user->karyawan->update([
                    'divisi_id'     => $request->divisi_id,
                    'jabatan'       => $request->jabatan,
                    'gaji_pokok'    => $gajiPokok,
                    'tanggal_masuk' => now(),
                    // 'status' tidak dimasukkan di sini agar tetap menggunakan nilai saat registrasi awal
                ]);
            }

            // Aktifkan akun agar bisa login
            $user->update(['is_active' => true]);

            DB::commit();
            return redirect()->route('manajemen-karyawan.permohonan')
                ->with('success', 'Akun ' . $user->name . ' sekarang aktif dengan jabatan ' . $request->jabatan);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Menolak dan menghapus data pendaftar.
     */
    public function tolak($id)
    {
        $user = User::findOrFail($id);
        DB::beginTransaction();
        try {
            if ($user->karyawan) {
                // Menghapus karyawan akan otomatis menghapus user & foto (via Boot Model Karyawan)
                $user->karyawan->delete();
            } else {
                $user->delete();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Permohonan pendaftaran ditolak.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses penolakan.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SELF SERVICE (SISI KARYAWAN)
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan ID Card atau profil mandiri karyawan yang sedang login.
     */
    public function showSelf()
    {
        $karyawan = Auth::user()->karyawan;
        if (!$karyawan) return redirect()->route('dashboard')->with('error', 'Profil tidak ditemukan.');

        return view('karyawan.id-card', compact('karyawan'));
    }

    /**
     * Karyawan memperbarui foto profil mereka sendiri (via AJAX/Form).
     */
    public function updateFoto(Request $request)
    {
        $request->validate(['foto' => 'required|image|mimes:jpeg,png,jpg|max:2048']);

        try {
            $karyawan = Auth::user()->karyawan;

            // Hapus foto lama jika ada
            if ($karyawan->foto) {
                Storage::disk('public')->delete('karyawan/foto/' . $karyawan->foto);
            }

            $filename = 'self_' . time() . '_' . Str::random(5) . '.' . $request->foto->extension();
            $request->file('foto')->storeAs('karyawan/foto', $filename, 'public');

            $karyawan->update(['foto' => $filename]);

            return response()->json([
                'success' => true,
                'message' => 'Foto diperbarui!',
                'path'    => asset('storage/karyawan/foto/' . $filename)
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
