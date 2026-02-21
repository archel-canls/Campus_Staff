@extends('layouts.app')

@section('title', 'Info Jabatan & Divisi')
@section('page_title', 'Struktur Pekerjaan')

@section('content')
<div class="space-y-10 pb-20">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="bg-cdi text-white text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-widest">
                    {{ Auth::user()->karyawan->divisi->kode ?? 'N/A' }}
                </span>
                <span class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">Informasi Penempatan</span>
            </div>
            <h3 class="text-5xl font-black text-cdi uppercase italic leading-none">
                {{ Auth::user()->karyawan->divisi->nama ?? 'Belum Ada Divisi' }}
            </h3>
        </div>
        <div class="bg-white px-8 py-4 rounded-[2rem] border-2 border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-cdi-orange/10 text-cdi-orange rounded-2xl flex items-center justify-center">
                <i class="fas fa-id-badge text-xl"></i>
            </div>
            <div>
                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Nomor Induk Pegawai</p>
                <p class="text-sm font-black text-cdi italic">{{ Auth::user()->karyawan->nip }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        {{-- Kartu Kiri: Status & Jabatan --}}
        <div class="space-y-8">
            <div class="bg-cdi p-10 rounded-[3.5rem] shadow-2xl shadow-blue-900/20 relative overflow-hidden group">
                <div class="absolute bottom-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-10 -mb-10 transition-transform group-hover:scale-150 duration-700"></div>
                
                <div class="relative z-10">
                    <h5 class="text-[10px] font-black text-white/50 uppercase tracking-[0.2em] mb-8">Posisi Saat Ini</h5>
                    
                    <div class="space-y-6">
                        <div>
                            <p class="text-white/40 text-[9px] font-bold uppercase tracking-widest mb-2">Jabatan Struktural</p>
                            <h4 class="text-2xl font-black text-white uppercase italic tracking-tight leading-tight">
                                {{ Auth::user()->karyawan->jabatan ?? 'General Staff' }}
                            </h4>
                        </div>

                        <div class="pt-6 border-t border-white/10 flex items-center justify-between">
                            <div>
                                <p class="text-white/40 text-[9px] font-bold uppercase tracking-widest mb-1">Status Pegawai</p>
                                <span class="px-3 py-1 bg-cdi-orange text-white text-[9px] font-black uppercase italic rounded-lg">
                                    {{ Auth::user()->karyawan->status }}
                                </span>
                            </div>
                            <i class="fas fa-shield-alt text-white/20 text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Kontak Divisi --}}
            <div class="bg-white p-8 rounded-[2.5rem] border-2 border-slate-100 shadow-sm">
                <h5 class="text-[10px] font-black text-cdi uppercase tracking-widest mb-6 flex items-center">
                    <span class="w-2 h-2 bg-cdi-orange rounded-full mr-3"></span> Ringkasan Unit
                </h5>
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400">
                            <i class="fas fa-calendar-check text-xs"></i>
                        </div>
                        <div>
                            <p class="text-[8px] font-black text-slate-300 uppercase tracking-tighter">Bergabung Sejak</p>
                            <p class="text-[11px] font-bold text-cdi">{{ Auth::user()->karyawan->created_at->format('d F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu Kanan: Deskripsi & Rekan Tim --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Deskripsi Divisi --}}
            <div class="bg-white p-10 rounded-[3.5rem] border-2 border-slate-100 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <h5 class="text-xs font-black text-cdi uppercase tracking-widest flex items-center">
                        <i class="fas fa-info-circle text-cdi-orange mr-3"></i> Deskripsi Operasional Divisi
                    </h5>
                </div>
                
                <p class="text-slate-500 text-sm leading-relaxed font-bold italic">
                    "{{ Auth::user()->karyawan->divisi->deskripsi ?? 'Belum ada deskripsi operasional yang dicantumkan untuk divisi ini.' }}"
                </p>

                <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php 
                        $tasks = Auth::user()->karyawan->divisi->tugas_utama ? 
                                explode(',', Auth::user()->karyawan->divisi->tugas_utama) : 
                                ['Manajemen Proyek', 'Koordinasi Tim', 'Pelaporan Rutin']; 
                    @endphp
                    @foreach($tasks as $task)
                    <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100 hover:border-cdi-orange transition-colors group">
                        <i class="fas fa-check-circle text-cdi-orange text-xs"></i>
                        <span class="text-[10px] font-black text-cdi uppercase tracking-tight">{{ trim($task) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Daftar Rekan Se-Divisi --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-6">
                    <h4 class="text-sm font-black text-cdi uppercase italic tracking-widest">Rekan <span class="text-slate-300">Satu Divisi</span></h4>
                    <span class="text-[10px] font-black text-slate-400 uppercase bg-slate-100 px-4 py-1 rounded-full">
                        {{ (Auth::user()->karyawan->divisi->karyawans->count() ?? 1) - 1 }} Rekan Lainnya
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse(Auth::user()->karyawan->divisi->karyawans as $rekan)
                        @if($rekan->id != Auth::user()->karyawan->id)
                        <div class="bg-white p-6 rounded-[2rem] border-2 border-slate-50 shadow-sm hover:shadow-md transition-all flex items-center gap-5">
                            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-cdi font-black text-xs">
                                {{ substr($rekan->nama, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-[11px] font-black text-cdi uppercase italic">{{ $rekan->nama }}</p>
                                <p class="text-[9px] font-bold text-slate-300 uppercase tracking-widest mt-1">{{ $rekan->jabatan ?? 'Staff' }}</p>
                            </div>
                        </div>
                        @endif
                    @empty
                        <div class="col-span-2 py-10 text-center bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200">
                            <i class="fas fa-user-friends text-slate-200 text-3xl mb-3"></i>
                            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Tidak ada rekan kerja lain di divisi ini</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection