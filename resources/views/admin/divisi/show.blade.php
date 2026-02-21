@extends('layouts.app')

@section('title', 'Detail Divisi ' . $divisi->nama)

@section('content')
<div class="space-y-10 pb-20" x-data="{ showMemberModal: false, showJobModal: false }">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center gap-8">
        <a href="{{ route('divisi.index') }}" class="w-16 h-16 bg-white border-2 border-slate-100 rounded-[1.5rem] flex items-center justify-center text-cdi hover:bg-cdi hover:text-white transition-all shadow-sm">
            <i class="fas fa-chevron-left"></i>
        </a>
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="bg-cdi text-white text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-widest">{{ $divisi->kode }}</span>
                <span class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">Departemen Overview</span>
            </div>
            <h3 class="text-5xl font-black text-cdi uppercase italic leading-none">{{ $divisi->nama }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        {{-- Sisi Kiri: Informasi --}}
        <div class="space-y-8">
            <div class="bg-white p-10 rounded-[3.5rem] border-2 border-slate-100 shadow-sm">
                <h5 class="text-xs font-black text-cdi uppercase tracking-widest mb-6 flex items-center">
                    <span class="w-2 h-2 bg-cdi-orange rounded-full mr-3"></span> Misi & Deskripsi
                </h5>
                <p class="text-slate-500 text-sm leading-relaxed font-bold italic">
                    "{{ $divisi->deskripsi ?? 'Optimalisasi kinerja struktural ' . $divisi->nama . '.' }}"
                </p>
            </div>

            {{-- Panel Jabatan & Kuota --}}
            <div class="bg-cdi p-10 rounded-[3.5rem] shadow-xl text-white">
                <div class="flex justify-between items-start mb-8">
                    <h5 class="text-xs font-black uppercase tracking-widest flex items-center">
                        <span class="w-2 h-2 bg-cdi-orange rounded-full mr-3"></span> Struktur Jabatan
                    </h5>
                    <button @click="showJobModal = true" class="text-[10px] font-black uppercase tracking-widest bg-white/10 hover:bg-white/20 px-4 py-2 rounded-full transition-all">
                        Kelola
                    </button>
                </div>
                
                <div class="space-y-3">
                    @forelse($divisi->daftar_jabatan ?? [] as $jabatan => $kuota)
                    <div class="flex justify-between items-center bg-white/5 p-4 rounded-2xl border border-white/10">
                        <span class="text-[10px] font-black uppercase tracking-tight">{{ $jabatan }}</span>
                        <div class="text-right">
                            <span class="text-[10px] font-black text-cdi-orange">{{ $divisi->karyawans()->where('jabatan', $jabatan)->count() }} / {{ $kuota }}</span>
                            <p class="text-[8px] uppercase opacity-50 tracking-tighter">Personel</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-[10px] opacity-50 italic">Belum ada jabatan diatur.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Daftar Anggota --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between px-4">
                <h4 class="text-xl font-black text-cdi uppercase italic">Daftar <span class="text-slate-300">Anggota</span></h4>
                <button @click="showMemberModal = true" class="bg-slate-900 text-white px-6 py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:bg-cdi-orange transition-all shadow-xl">
                    <i class="fas fa-user-plus mr-2"></i> Tambah Anggota
                </button>
            </div>

            <div class="bg-white rounded-[3.5rem] border-2 border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b-2 border-slate-100">
                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama / NIP</th>
                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Jabatan</th>
                            <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Opsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-slate-50">
                        @forelse($divisi->karyawans as $k)
                        <tr class="group hover:bg-slate-50/50 transition-all">
                            <td class="px-10 py-8">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 bg-cdi rounded-2xl flex items-center justify-center font-black text-white text-sm shadow-md">
                                        {{ strtoupper(substr($k->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-black text-cdi uppercase italic text-[13px] tracking-tight group-hover:text-cdi-orange transition-colors">{{ $k->nama }}</p>
                                        <p class="text-[9px] font-bold text-slate-300 uppercase tracking-widest mt-1">NIP. {{ $k->nip }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-10 py-8 text-center">
                                <span class="px-4 py-2 bg-slate-900 text-white text-[9px] font-black uppercase italic rounded-xl tracking-widest shadow-lg">
                                    {{ $k->jabatan ?? 'General Staff' }}
                                </span>
                            </td>
                            <td class="px-10 py-8 text-right">
                                <form action="{{ route('divisi.hapus-anggota', $k->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Keluarkan dari divisi?')" class="w-10 h-10 bg-slate-100 text-slate-300 rounded-xl hover:bg-red-500 hover:text-white transition-all">
                                        <i class="fas fa-user-minus text-[10px]"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-10 py-20 text-center text-slate-300 text-[10px] font-black uppercase tracking-[0.5em]">Belum ada anggota</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL KELOLA JABATAN & KUOTA --}}
    <div x-show="showJobModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-xl rounded-[3rem] shadow-2xl p-12 overflow-hidden" 
             x-data="{ 
                rows: [
                    @if($divisi->daftar_jabatan)
                        @foreach($divisi->daftar_jabatan as $j => $k)
                            { nama: '{{ $j }}', kuota: '{{ $k }}' },
                        @endforeach
                    @else
                        { nama: '', kuota: 1 }
                    @endif
                ],
                addRow() { this.rows.push({ nama: '', kuota: 1 }) },
                removeRow(index) { this.rows.splice(index, 1) }
             }">
            <h3 class="text-2xl font-black text-cdi uppercase italic mb-8">Setup <span class="text-cdi-orange">Struktur</span></h3>
            
            <form action="{{ route('divisi.update-jabatan', $divisi->id) }}" method="POST" class="space-y-4">
                @csrf @method('PATCH')
                
                <div class="max-h-60 overflow-y-auto space-y-3 pr-2">
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="flex gap-3 items-center">
                            <input type="text" name="nama_jabatan[]" x-model="row.nama" placeholder="Nama Jabatan" class="flex-1 bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black uppercase outline-none focus:border-cdi">
                            <input type="number" name="kuota_jabatan[]" x-model="row.kuota" min="1" class="w-24 bg-slate-50 border-2 border-slate-100 rounded-2xl px-4 py-4 text-xs font-black outline-none focus:border-cdi">
                            <button type="button" @click="removeRow(index)" class="w-12 h-12 text-red-400 hover:text-red-600">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <button type="button" @click="addRow()" class="w-full border-2 border-dashed border-slate-200 py-4 rounded-2xl text-[10px] font-black uppercase text-slate-400 hover:bg-slate-50 transition-all">
                    + Tambah Baris Jabatan
                </button>

                <div class="pt-6 flex gap-3">
                    <button type="submit" class="flex-1 bg-cdi text-white py-5 rounded-2xl font-black uppercase italic text-xs tracking-widest hover:bg-cdi-orange transition-all shadow-xl">Simpan Struktur</button>
                    <button type="button" @click="showJobModal = false" class="px-8 bg-slate-100 text-slate-400 rounded-2xl font-black uppercase text-[9px]">Tutup</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TAMBAH ANGGOTA --}}
    <div x-show="showMemberModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-md rounded-[3rem] shadow-2xl p-12" @click.away="showMemberModal = false">
            <h3 class="text-2xl font-black text-cdi uppercase italic mb-8">Tambah <span class="text-cdi-orange">Personel</span></h3>
            
            <form action="{{ route('divisi.tambah-anggota', $divisi->id) }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Pilih Karyawan</label>
                    <select name="karyawan_id" required class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black outline-none appearance-none focus:border-cdi">
                        <option value="">-- PILIH --</option>
                        @foreach($karyawanTersedia as $kt)
                            <option value="{{ $kt->id }}">{{ $kt->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Pilih Jabatan (Kuota Tersedia)</label>
                    <select name="jabatan" required class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-xs font-black outline-none appearance-none focus:border-cdi">
                        <option value="">-- PILIH JABATAN --</option>
                        @foreach($divisi->daftar_jabatan ?? [] as $jab => $kuota)
                            @php $sisa = $divisi->getSisaKuota($jab); @endphp
                            <option value="{{ $jab }}" {{ $sisa <= 0 ? 'disabled' : '' }}>
                                {{ strtoupper($jab) }} (Sisa: {{ $sisa }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-4 flex gap-3">
                    <button type="submit" class="flex-1 bg-cdi text-white py-5 rounded-2xl font-black uppercase italic text-xs tracking-widest hover:bg-cdi-orange transition-all shadow-xl">Konfirmasi</button>
                    <button type="button" @click="showMemberModal = false" class="px-8 bg-slate-100 text-slate-400 rounded-2xl font-black uppercase text-[9px]">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection