@extends('layouts.app')

@section('title', 'Manajemen Divisi')
@section('page_title', 'Struktur Organisasi')

@section('content')
<div class="space-y-8 pb-12">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h3 class="text-4xl font-black italic uppercase tracking-tighter text-cdi leading-none">
                Struktur <span class="text-cdi-orange">Divisi</span>
            </h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-3 flex items-center">
                <span class="w-12 h-[3px] bg-cdi mr-3"></span>
                Total: 8 Departemen Terdaftar
            </p>
        </div>
        <button class="bg-cdi text-white px-8 py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:bg-cdi-orange transition-all shadow-xl shadow-blue-900/20">
            <i class="fas fa-plus-circle mr-2"></i> Tambah Divisi Baru
        </button>
    </div>

    {{-- Grid Divisi --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @php
            $divisis = [
                ['nama' => 'IT Solution', 'kode' => 'ITS', 'count' => 12, 'color' => 'blue'],
                ['nama' => 'Human Resources', 'kode' => 'HRD', 'count' => 4, 'color' => 'pink'],
                ['nama' => 'Finance', 'kode' => 'FIN', 'count' => 6, 'color' => 'green'],
                ['nama' => 'Marketing', 'kode' => 'MKT', 'count' => 15, 'color' => 'orange'],
            ];
        @endphp

        @foreach($divisis as $d)
        <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm group hover:border-cdi transition-all relative overflow-hidden">
            <div class="relative z-10">
                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Departemen</span>
                <h4 class="text-xl font-black text-cdi uppercase italic mt-1">{{ $d['nama'] }}</h4>
                
                <div class="mt-6 flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-2xl font-black text-cdi">{{ $d['count'] }}</span>
                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">Personel Aktif</span>
                    </div>
                    <div class="flex gap-2">
                        <button class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl hover:bg-cdi hover:text-white transition-all">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
            {{-- Watermark Kode Divisi --}}
            <span class="absolute -right-4 -bottom-6 text-8xl font-black italic opacity-[0.03] text-cdi group-hover:opacity-[0.05] transition-all">
                {{ $d['kode'] }}
            </span>
        </div>
        @endforeach
    </div>
</div>
@endsection