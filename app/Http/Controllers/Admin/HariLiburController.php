<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use Illuminate\Http\Request;

class HariLiburController extends Controller
{
    /**
     * Menyimpan hari libur baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:hari_liburs,tanggal',
            'keterangan' => 'required|string|max:255',
        ]);

        HariLibur::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Hari libur berhasil ditambahkan!');
    }

    /**
     * Menghapus hari libur.
     */
    public function destroy($id)
    {
        $libur = HariLibur::findOrFail($id);
        $libur->delete();

        return back()->with('success', 'Hari libur berhasil dihapus!');
    }
}