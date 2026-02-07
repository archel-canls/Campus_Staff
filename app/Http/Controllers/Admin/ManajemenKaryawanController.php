<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
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
     * Menampilkan daftar semua staf/karyawan.
     */
    public function index(Request $request)
    {
        $query = Karyawan::query();

        // Fitur Pencarian
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('nip', 'like', '%' . $request->search . '%')
                  ->orWhere('divisi', 'like', '%' . $request->search . '%');
            });
        }

        $karyawans = $query->latest()->get();
        return view('admin.manajemen-karyawan.index', compact('karyawans'));
    }

    /**
     * Menampilkan form tambah karyawan.
     */
    public function create()
    {
        return view('admin.manajemen-karyawan.create');
    }

    /**
     * Menyimpan data karyawan baru beserta foto.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'nip'      => 'required|string|unique:karyawans,nip',
            'nik'      => 'nullable|string|unique:karyawans,nik',
            'divisi'   => 'required|string',
            'status'   => 'required|in:tetap,magang',
            'instansi' => 'nullable|string|max:255',
            'foto'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'nama'          => $request->nama,
                'nip'           => $request->nip,
                'nik'           => $request->nik,
                'divisi'        => $request->divisi,
                'status'        => $request->status,
                'instansi'      => $request->instansi,
                'barcode_token' => 'CDI-' . strtoupper(Str::random(10)),
            ];

            // Proses Upload Foto
            if ($request->hasFile('foto')) {
                $filename = time() . '_' . Str::slug($request->nama) . '.' . $request->foto->extension();
                $path = $request->file('foto')->storeAs('karyawan_foto', $filename, 'public');
                $data['foto'] = $path;
            }

            Karyawan::create($data);
            
            DB::commit();
            return redirect()->route('manajemen-karyawan.index')
                             ->with('success', 'Personel baru berhasil didaftarkan!');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan profil detail karyawan.
     */
    public function show(string $id)
    {
        $karyawan = Karyawan::with(['perizinans' => function($q) {
            $q->latest()->limit(5);
        }])->findOrFail($id);

        return view('admin.manajemen-karyawan.show', compact('karyawan'));
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(string $id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return view('admin.manajemen-karyawan.edit', compact('karyawan'));
    }

    /**
     * Memperbarui data karyawan.
     */
    public function update(Request $request, string $id)
    {
        $karyawan = Karyawan::findOrFail($id);
        
        $request->validate([
            'nama'     => 'required|string|max:255',
            'nip'      => ['required', 'string', Rule::unique('karyawans')->ignore($karyawan->id)],
            'divisi'   => 'required|string',
            'status'   => 'required|in:tetap,magang',
            'foto'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only(['nama', 'nip', 'divisi', 'status', 'instansi', 'nik', 'alamat']);

            if ($request->hasFile('foto')) {
                if ($karyawan->foto) {
                    Storage::disk('public')->delete($karyawan->foto);
                }

                $filename = time() . '_' . Str::slug($request->nama) . '.' . $request->foto->extension();
                $path = $request->file('foto')->storeAs('karyawan_foto', $filename, 'public');
                $data['foto'] = $path;
            }

            $karyawan->update($data);

            // Update nama di tabel users jika ada akunnya
            User::where('karyawan_id', $karyawan->id)->update(['name' => $request->nama]);

            DB::commit();
            return redirect()->route('manajemen-karyawan.index')
                             ->with('success', 'Data personel berhasil diperbarui!');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data.');
        }
    }

    /**
     * Menghapus data karyawan dan file fisik fotonya.
     */
    public function destroy(string $id)
    {
        $karyawan = Karyawan::findOrFail($id);
        
        DB::beginTransaction();
        try {
            if ($karyawan->foto) {
                Storage::disk('public')->delete($karyawan->foto);
            }

            // Hapus User terkait
            User::where('karyawan_id', $karyawan->id)->delete();

            $karyawan->delete();

            DB::commit();
            return redirect()->route('manajemen-karyawan.index')
                             ->with('success', 'Data personel dan akun terkait telah dihapus.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }

    /**
     * Menampilkan ID Card sisi Karyawan (Self Profile).
     */
    public function showSelf()
    {
        $user = Auth::user();

        if (!$user || !$user->karyawan_id) {
            return redirect()->route('karyawan.dashboard')
                             ->with('error', 'Akun Anda tidak tertaut dengan data karyawan.');
        }

        $karyawan = Karyawan::find($user->karyawan_id);

        if (!$karyawan) {
            return redirect()->back()->with('error', 'Data profil tidak ditemukan.');
        }

        return view('karyawan.id-card', compact('karyawan'));
    }

    /**
     * Fitur Simpan Foto AJAX (Update dari ID Card Profile).
     */
    public function updateFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $user = Auth::user();
            $karyawan = Karyawan::findOrFail($user->karyawan_id);

            if ($request->hasFile('foto')) {
                // Hapus foto lama
                if ($karyawan->foto) {
                    Storage::disk('public')->delete($karyawan->foto);
                }

                // Simpan foto baru
                $filename = 'self_' . time() . '_' . Str::random(5) . '.' . $request->foto->extension();
                $path = $request->file('foto')->storeAs('karyawan_foto', $filename, 'public');
                
                $karyawan->update(['foto' => $path]);

                return response()->json([
                    'success' => true,
                    'message' => 'Foto profil berhasil diperbarui!',
                    'path'    => asset('storage/' . $path)
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah foto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export / Download ID Card.
     */
    public function downloadIdCard($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return back()->with('info', 'Fitur download sedang dalam pengembangan.');
    }
}