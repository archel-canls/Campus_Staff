@extends('layouts.app')

@section('title', 'Karyawan Dashboard')
@section('page_title', 'Dashboard Personel')

@section('content')
<div class="space-y-8">
    {{-- BARIS 1: WELCOME BANNER & QUICK ACTIONS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Welcome Banner --}}
        <div class="lg:col-span-2 bg-cdi rounded-[3rem] p-10 relative overflow-hidden shadow-2xl shadow-blue-900/20">
            <div class="relative z-10">
                <p class="text-cdi-orange font-black uppercase tracking-[0.4em] text-[10px] mb-2">Selamat Datang Kembali,</p>
                <h1 class="text-4xl font-black text-white italic uppercase tracking-tighter leading-none mb-6">
                    {{ Auth::user()->karyawan->nama ?? Auth::user()->name }}
                </h1>
                <div class="flex flex-wrap gap-4">
                    <div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl border border-white/10">
                        <p class="text-[8px] font-black text-white/50 uppercase tracking-widest">Divisi</p>
                        {{-- PERBAIKAN: Mengambil properti 'nama' dari objek divisi agar tidak muncul format JSON --}}
                        <p class="text-xs font-bold text-white uppercase italic">
                            {{ Auth::user()->karyawan->divisi->nama ?? '-' }}
                        </p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl border border-white/10">
                        <p class="text-[8px] font-black text-white/50 uppercase tracking-widest">Status</p>
                        <p class="text-xs font-bold text-white uppercase italic">{{ Auth::user()->karyawan->status ?? '-' }}</p>
                    </div>
                </div>
            </div>
            {{-- Ornamen --}}
            <i class="fas fa-rocket absolute -right-10 -bottom-10 text-[15rem] text-white/5 -rotate-12"></i>
        </div>

        {{-- Quick Navigation --}}
        <div class="bg-white rounded-[3rem] p-8 border border-slate-100 shadow-sm">
            <h4 class="text-[10px] font-black text-cdi uppercase tracking-widest mb-6 italic">Navigasi Cepat</h4>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('karyawan.id-card') }}" class="group p-4 bg-slate-50 rounded-3xl border border-transparent hover:border-cdi-orange hover:bg-white transition-all text-center">
                    <div class="w-10 h-10 bg-white shadow-sm rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-cdi-orange group-hover:text-white transition-all">
                        <i class="fas fa-id-card text-sm"></i>
                    </div>
                    <p class="text-[9px] font-black text-cdi uppercase tracking-tighter">Smart Card</p>
                </a>
                <a href="{{ route('karyawan.absensi') }}" class="group p-4 bg-slate-50 rounded-3xl border border-transparent hover:border-cdi-orange hover:bg-white transition-all text-center">
                    <div class="w-10 h-10 bg-white shadow-sm rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-cdi-orange group-hover:text-white transition-all">
                        <i class="fas fa-fingerprint text-sm"></i>
                    </div>
                    <p class="text-[9px] font-black text-cdi uppercase tracking-tighter">Absensi</p>
                </a>
                <a href="{{ route('karyawan.perizinan') }}" class="group p-4 bg-slate-50 rounded-3xl border border-transparent hover:border-cdi-orange hover:bg-white transition-all text-center">
                    <div class="w-10 h-10 bg-white shadow-sm rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-cdi-orange group-hover:text-white transition-all">
                        <i class="fas fa-envelope-open-text text-sm"></i>
                    </div>
                    <p class="text-[9px] font-black text-cdi uppercase tracking-tighter">Izin/Cuti</p>
                </a>
                <a href="{{ route('karyawan.slip-gaji') }}" class="group p-4 bg-slate-50 rounded-3xl border border-transparent hover:border-cdi-orange hover:bg-white transition-all text-center">
                    <div class="w-10 h-10 bg-white shadow-sm rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-cdi-orange group-hover:text-white transition-all">
                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                    </div>
                    <p class="text-[9px] font-black text-cdi uppercase tracking-tighter">Slip Gaji</p>
                </a>
            </div>
        </div>
    </div>

    {{-- BARIS 2: STATS & SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Summary Kehadiran --}}
        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm relative overflow-hidden group">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Kehadiran Bulan Ini</p>
            <h4 class="text-4xl font-black text-cdi mt-2 italic">
                {{ $totalHadir ?? 0 }} <span class="text-xs not-italic text-slate-400">Hari</span>
            </h4>
            <div class="mt-4 flex items-center text-[9px] font-bold text-green-500 bg-green-50 w-fit px-2 py-1 rounded-lg">
                <i class="fas fa-arrow-up mr-1"></i> Stabil
            </div>
        </div>

        {{-- Status Absen Hari Ini --}}
        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm relative overflow-hidden group">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Status Hari Ini</p>
            @if($absenHariIni)
                <div class="flex items-center mt-3 text-green-600">
                    <i class="fas fa-check-circle text-2xl mr-3"></i>
                    <span class="text-xl font-black uppercase italic">Sudah Absen</span>
                </div>
            @else
                <div class="flex items-center mt-3 text-red-500">
                    <i class="fas fa-clock text-2xl mr-3 animate-pulse"></i>
                    <span class="text-xl font-black uppercase italic">Belum Absen</span>
                </div>
            @endif
        </div>

        {{-- Sisa Cuti --}}
        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm relative overflow-hidden group">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sisa Kuota Cuti</p>
            <h4 class="text-4xl font-black text-cdi mt-2 italic">
                {{ $sisaCuti ?? 12 }} <span class="text-xs not-italic text-slate-400">Hari</span>
            </h4>
        </div>

        {{-- Estimasi Gaji --}}
        <div class="bg-gradient-to-br from-cdi-orange to-orange-600 rounded-[2.5rem] p-8 text-white shadow-lg shadow-orange-500/20">
            <p class="text-[9px] font-black uppercase tracking-widest opacity-80">Take Home Pay (Est)</p>
            <h4 class="text-2xl font-black italic mt-2">Rp {{ number_format($estimasiGaji ?? 0, 0, ',', '.') }}</h4>
            <p class="text-[8px] font-bold mt-2 text-white/60">*Berdasarkan kehadiran saat ini</p>
        </div>
    </div>

    {{-- BARIS 3: RIWAYAT & INFO --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Riwayat Absensi --}}
        <div class="lg:col-span-2 bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h4 class="text-xs font-black text-cdi uppercase tracking-[0.2em] italic leading-none">Log Aktivitas Terbaru</h4>
                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-2">Menampilkan 5 aktivitas absensi terakhir</p>
                </div>
                <a href="{{ route('karyawan.absensi') }}" class="text-[9px] font-black text-cdi-orange uppercase tracking-widest border-b-2 border-cdi-orange/20 hover:border-cdi-orange pb-1 transition-all">Lihat Semua</a>
            </div>

            <div class="space-y-4">
                @forelse($riwayatAbsensi ?? [] as $absen)
                <div class="flex items-center justify-between p-5 bg-slate-50 rounded-[2rem] border border-white hover:bg-white hover:shadow-xl hover:shadow-slate-200/50 transition-all group">
                    <div class="flex items-center space-x-5">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-cdi shadow-sm group-hover:bg-cdi group-hover:text-white transition-all">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <div>
                            <p class="text-xs font-black text-cdi uppercase italic">Absensi Masuk</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase">{{ \Carbon\Carbon::parse($absen->jam_masuk)->translatedFormat('l, d F Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-black text-cdi uppercase italic leading-none">{{ \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') }} <span class="text-[9px] text-slate-400 font-bold ml-1">WIB</span></p>
                        @if(\Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') > '08:00')
                            <span class="text-[8px] font-black text-red-500 uppercase italic">Terlambat</span>
                        @else
                            <span class="text-[8px] font-black text-green-500 uppercase italic">Tepat Waktu</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-10 text-center opacity-30">
                    <i class="fas fa-folder-open text-4xl mb-3"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest">Belum ada aktivitas</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Info Box --}}
        <div class="space-y-6">
            <div class="bg-blue-600 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-xl shadow-blue-600/20">
                <h4 class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-4">Pengumuman Internal</h4>
                <p class="text-sm font-bold italic leading-relaxed">Jangan lupa melakukan update data domisili pada menu profil jika terdapat perubahan alamat.</p>
                <i class="fas fa-bullhorn absolute -right-4 -bottom-4 text-6xl text-white/10 -rotate-12"></i>
            </div>

            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm">
                <h4 class="text-[10px] font-black text-cdi uppercase tracking-widest mb-6 italic">Jadwal Kerja</h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b border-slate-50 pb-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Senin - Jumat</span>
                        <span class="text-xs font-black text-cdi italic">08:00 - 17:00</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-50 pb-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Sabtu</span>
                        <span class="text-xs font-black text-cdi-orange italic">08:00 - 12:00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Minggu</span>
                        <span class="text-xs font-black text-red-500 italic uppercase text-[10px]">Libur</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection