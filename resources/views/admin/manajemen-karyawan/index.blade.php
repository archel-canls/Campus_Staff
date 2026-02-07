@extends('layouts.app')

@section('title', 'Manajemen Staf')
@section('page_title', 'Database Personel')

@section('content')
<div class="space-y-8 pb-10">
    {{-- HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h3 class="text-3xl font-black italic uppercase tracking-tighter text-cdi leading-none">
                Database <span class="text-cdi-orange">Personel</span>
            </h3>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mt-2 flex items-center">
                <span class="w-8 h-[2px] bg-cdi-orange mr-2"></span>
                Manajemen data staf tetap dan peserta magang
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('manajemen-karyawan.create') }}" class="inline-flex items-center justify-center bg-cdi text-white px-8 py-4 rounded-2xl font-black uppercase italic text-[11px] tracking-widest hover:bg-cdi-orange transition-all shadow-xl shadow-blue-900/10 group">
                <i class="fas fa-plus-circle mr-2 group-hover:rotate-90 transition-transform"></i> Tambah Anggota
            </a>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-[0.03] text-7xl group-hover:scale-110 transition-transform"><i class="fas fa-users"></i></div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Personel</p>
            <p class="text-3xl font-black text-cdi mt-1">{{ $karyawans->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm group">
            <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Staf Tetap</p>
            <p class="text-3xl font-black text-cdi mt-1">{{ $karyawans->where('status', 'tetap')->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm group">
            <p class="text-[10px] font-black text-cdi-orange uppercase tracking-widest">Anak Magang</p>
            <p class="text-3xl font-black text-cdi mt-1">{{ $karyawans->where('status', 'magang')->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm group">
            <p class="text-[10px] font-black text-green-400 uppercase tracking-widest">Aktif Bulan Ini</p>
            <p class="text-3xl font-black text-cdi mt-1">{{ $karyawans->count() }} <span class="text-[10px] text-slate-300 italic font-normal">Personel</span></p>
        </div>
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-wrap gap-4 items-center">
        <div class="relative flex-1 min-w-[300px]">
            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
            <input type="text" id="searchInput" placeholder="CARI NAMA ATAU NIP..." class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-12 pr-4 text-[11px] font-bold text-cdi outline-none focus:ring-2 focus:ring-cdi-orange/20 transition-all uppercase">
        </div>
        <select class="bg-slate-50 border-none rounded-2xl py-4 px-6 text-[11px] font-bold text-cdi outline-none cursor-pointer uppercase">
            <option value="">Semua Divisi</option>
            @foreach($karyawans->pluck('divisi')->unique() as $div)
                <option value="{{ $div }}">{{ $div }}</option>
            @endforeach
        </select>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-100 text-green-600 px-6 py-4 rounded-2xl flex items-center" role="alert">
        <i class="fas fa-check-circle mr-3"></i>
        <p class="font-black text-[10px] uppercase italic tracking-widest">{{ session('success') }}</p>
    </div>
    @endif

    {{-- MAIN TABLE --}}
    <div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="employeeTable">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Info Personel</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Divisi & Peran</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Status Kontrak</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($karyawans as $k)
                    <tr class="hover:bg-slate-50/80 transition-all group table-row-item">
                        <td class="px-8 py-6">
                            <div class="flex items-center space-x-5">
                                <div class="relative">
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center font-black text-cdi border-2 border-white shadow-sm overflow-hidden uppercase italic">
                                        @if($k->foto)
                                            <img src="{{ asset('storage/'.$k->foto) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($k->nama, 0, 1) }}
                                        @endif
                                    </div>
                                    <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full" title="Aktif"></span>
                                </div>
                                <div>
                                    <p class="font-black text-cdi uppercase italic text-sm tracking-tight group-hover:text-cdi-orange transition-colors">{{ $k->nama }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">{{ $k->nip }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-cdi uppercase italic">{{ $k->divisi }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Anggota Tim</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            @if($k->status == 'tetap')
                                <div class="flex flex-col gap-1">
                                    <span class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest w-fit border border-blue-100">
                                        <i class="fas fa-shield-alt mr-1"></i> Staf Tetap
                                    </span>
                                    <span class="text-[8px] font-bold text-slate-300 uppercase italic ml-1">Kontrak Permanen</span>
                                </div>
                            @else
                                <div class="flex flex-col gap-2">
                                    <span class="bg-orange-50 text-cdi-orange px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest w-fit border border-orange-100">
                                        <i class="fas fa-user-graduate mr-1"></i> Peserta Magang
                                    </span>
                                    <div class="w-32 h-1 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="bg-cdi-orange h-full rounded-full" style="width: 65%"></div> {{-- Contoh progress --}}
                                    </div>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase italic">{{ $k->instansi }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center justify-center gap-2 opacity-40 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('manajemen-karyawan.show', $k->id) }}" class="w-10 h-10 bg-white text-slate-400 rounded-xl flex items-center justify-center hover:bg-cdi hover:text-white transition-all shadow-sm border border-slate-100" title="Detail">
                                    <i class="fas fa-eye text-[10px]"></i>
                                </a>
                                <a href="{{ route('manajemen-karyawan.edit', $k->id) }}" class="w-10 h-10 bg-white text-slate-400 rounded-xl flex items-center justify-center hover:bg-cdi-orange hover:text-white transition-all shadow-sm border border-slate-100" title="Edit">
                                    <i class="fas fa-edit text-[10px]"></i>
                                </a>
                                <form action="{{ route('manajemen-karyawan.destroy', $k->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus personel ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-10 h-10 bg-white text-slate-400 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm border border-slate-100" title="Hapus">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-32 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-24 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center text-slate-200 text-4xl mb-6 border border-slate-100 shadow-inner">
                                    <i class="fas fa-user-ninja"></i>
                                </div>
                                <h4 class="font-black text-cdi italic uppercase tracking-tighter text-xl">Database Kosong</h4>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-[0.2em] mt-2">Belum ada personel yang terdaftar di sistem</p>
                                <a href="{{ route('manajemen-karyawan.create') }}" class="mt-8 text-cdi-orange font-black text-[10px] uppercase italic tracking-widest hover:underline">
                                    Mulai Input Data Pertama <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Live Search Functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toUpperCase();
        let rows = document.querySelectorAll('.table-row-item');
        
        rows.forEach(row => {
            let text = row.innerText.toUpperCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
</script>

<style>
    /* Custom scrollbar untuk table horizontal pada mobile */
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }
</style>
@endsection