@extends('layouts.app')

@section('title', 'Profil Detail: ' . $karyawan->nama)
@section('page_title', 'Profil Lengkap Personel')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-10">
    {{-- NAVIGATION & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <a href="{{ route('manajemen-karyawan.index') }}" class="inline-flex items-center text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] hover:text-cdi transition-colors group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali ke Daftar
        </a>
        <div class="flex gap-3">
            <a href="{{ route('manajemen-karyawan.edit', $karyawan->id) }}" class="bg-slate-100 text-slate-600 px-6 py-3 rounded-xl font-black uppercase italic text-[10px] hover:bg-slate-200 transition-all">
                <i class="fas fa-edit mr-2"></i> Edit Profil
            </a>
            <a href="{{ route('karyawan.id-card', $karyawan->id) }}" target="_blank" class="bg-cdi text-white px-6 py-3 rounded-xl font-black uppercase italic text-[10px] shadow-lg shadow-blue-900/20 hover:bg-cdi-orange transition-all">
                <i class="fas fa-id-card mr-2"></i> Preview ID Card
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- LEFT COLUMN: AVATAR & QUICK STATS --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-[3rem] p-8 border border-slate-100 shadow-sm text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6">
                    <span class="flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                </div>

                <div class="w-40 h-40 bg-gradient-to-tr from-slate-100 to-slate-50 rounded-[3rem] mx-auto mb-6 flex items-center justify-center border-4 border-white shadow-xl overflow-hidden">
                    @if($karyawan->foto)
                        <img src="{{ asset('storage/'.$karyawan->foto) }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-6xl font-black text-cdi italic">{{ substr($karyawan->nama, 0, 1) }}</span>
                    @endif
                </div>

                <h3 class="text-2xl font-black text-cdi uppercase italic leading-tight tracking-tighter">{{ $karyawan->nama }}</h3>
                <p class="text-cdi-orange font-bold text-[11px] uppercase tracking-[0.2em] mt-2">{{ $karyawan->nip }}</p>
                
                <div class="mt-8 grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                        <p class="text-[8px] font-black text-slate-400 uppercase mb-1">Status</p>
                        <p class="text-[10px] font-black text-cdi uppercase italic">{{ $karyawan->status }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                        <p class="text-[8px] font-black text-slate-400 uppercase mb-1">Divisi</p>
                        <p class="text-[10px] font-black text-cdi uppercase italic">{{ $karyawan->divisi }}</p>
                    </div>
                </div>
            </div>

            {{-- QUICK ATTENDANCE SCORE --}}
            <div class="bg-cdi rounded-[2.5rem] p-8 text-white shadow-xl shadow-blue-900/20 relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-60">Rating Kehadiran</p>
                    <div class="flex items-end gap-2 mt-2">
                        <p class="text-4xl font-black italic">92%</p>
                        <p class="text-[10px] font-bold uppercase mb-1.5 text-green-400">Sangat Baik</p>
                    </div>
                    <div class="w-full bg-white/10 h-1.5 rounded-full mt-4 overflow-hidden">
                        <div class="bg-white h-full" style="width: 92%"></div>
                    </div>
                </div>
                <i class="fas fa-chart-line absolute -right-4 -bottom-4 text-7xl opacity-10"></i>
            </div>
        </div>

        {{-- RIGHT COLUMN: DETAILED INFO --}}
        <div class="lg:col-span-8 space-y-8">
            {{-- DATA BIODATA --}}
            <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm">
                <div class="flex items-center gap-4 mb-10 border-b border-slate-50 pb-6">
                    <div class="w-10 h-10 bg-cdi-orange/10 text-cdi-orange rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-tag text-sm"></i>
                    </div>
                    <h4 class="text-xs font-black text-cdi uppercase tracking-[0.2em] italic">Informasi Personal & Akademik</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-12">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Asal Instansi / Kampus</p>
                        <p class="font-bold text-cdi italic uppercase text-sm">{{ $karyawan->instansi ?? 'Umum / Profesional' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tanggal Bergabung</p>
                        <p class="font-bold text-cdi italic uppercase text-sm">{{ $karyawan->created_at->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Email Perusahaan</p>
                        <p class="font-bold text-cdi lowercase text-sm">{{ Str::slug($karyawan->nama) }}@perusahaan.com</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Lama Bekerja</p>
                        <p class="font-bold text-cdi italic uppercase text-sm">{{ $karyawan->created_at->diffForHumans(null, true) }}</p>
                    </div>
                </div>

                {{-- DYNAMIC PROGRESS MASA KONTRAK --}}
                <div class="mt-16 p-8 bg-slate-50 rounded-[2.5rem] border border-slate-100 relative">
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <p class="text-[10px] font-black text-cdi uppercase italic tracking-tighter">Sisa Masa Kontrak / Magang</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Hingga 31 Desember 2026</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-black text-cdi-orange italic">240 <span class="text-[10px]">HARI</span></p>
                        </div>
                    </div>
                    <div class="w-full bg-slate-200 h-4 rounded-full overflow-hidden p-1">
                        <div class="bg-cdi-orange h-full rounded-full shadow-sm shadow-orange-500/50" style="width: 65%"></div>
                    </div>
                    <p class="text-[8px] font-bold text-slate-400 uppercase mt-4 tracking-[0.1em] leading-relaxed flex items-center">
                        <i class="fas fa-info-circle mr-2 text-cdi-orange"></i>
                        Estimasi selesai dalam 8 bulan lagi. Disarankan melakukan review performa 1 bulan sebelum berakhir.
                    </p>
                </div>
            </div>

            {{-- RECENT ACTIVITY LOG (Gimmick/Statistik) --}}
            <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-history text-sm"></i>
                    </div>
                    <h4 class="text-xs font-black text-cdi uppercase tracking-[0.2em] italic">Aktivitas Terakhir</h4>
                </div>
                
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-2 mt-2 h-2 rounded-full bg-green-500"></div>
                        <div>
                            <p class="text-[11px] font-black text-cdi uppercase italic tracking-tight">Absensi Masuk - Hari Ini</p>
                            <p class="text-[9px] font-medium text-slate-400 uppercase mt-0.5">08:02 WIB • Melalui Aplikasi Mobile</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-2 mt-2 h-2 rounded-full bg-blue-500"></div>
                        <div>
                            <p class="text-[11px] font-black text-cdi uppercase italic tracking-tight">Update Data Profil</p>
                            <p class="text-[9px] font-medium text-slate-400 uppercase mt-0.5">Kemarin, 14:30 WIB • Oleh Admin</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection