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
            <a href="{{ route('manajemen-karyawan.edit', $karyawan->id) }}" class="bg-slate-100 text-slate-600 px-6 py-3 rounded-xl font-black uppercase italic text-[10px] hover:bg-slate-200 transition-all border border-slate-200">
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
                {{-- STATUS INDICATOR --}}
                <div class="absolute top-0 right-0 p-6">
                    <span class="flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                </div>

                {{-- PROFILE PICTURE --}}
                <div class="w-40 h-40 bg-gradient-to-tr from-slate-100 to-slate-50 rounded-[3rem] mx-auto mb-6 flex items-center justify-center border-4 border-white shadow-xl overflow-hidden">
                    @if($karyawan->foto)
                        <img src="{{ asset('storage/'.$karyawan->foto) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-cdi text-white text-6xl font-black italic">
                            {{ substr($karyawan->nama, 0, 1) }}
                        </div>
                    @endif
                </div>

                <h3 class="text-2xl font-black text-cdi uppercase italic leading-tight tracking-tighter">{{ $karyawan->nama }}</h3>
                <p class="text-cdi-orange font-bold text-[11px] uppercase tracking-[0.2em] mt-2">{{ $karyawan->nip }}</p>
                
                {{-- TOP SUMMARY BENTO --}}
                <div class="mt-8 grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                        <p class="text-[8px] font-black text-slate-400 uppercase mb-1">Status</p>
                        <p class="text-[10px] font-black text-cdi uppercase italic">
                            {{ str_replace(['magang_', '_'], [' ', ' '], $karyawan->status) }}
                        </p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                        <p class="text-[8px] font-black text-slate-400 uppercase mb-1">Divisi</p>
                        <p class="text-[10px] font-black text-cdi uppercase italic">{{ $karyawan->divisi->nama ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            {{-- EMERGENCY CONTACT (REAL DATA) --}}
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm relative overflow-hidden">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 bg-red-50 text-red-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-ambulance text-xs"></i>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-cdi">Kontak Darurat Utama</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase">Nama Kontak</p>
                        <p class="text-[11px] font-bold text-cdi uppercase italic">{{ $karyawan->emergency_1_nama ?? '-' }}</p>
                    </div>
                    <div class="flex justify-between">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase">Hubungan</p>
                            <p class="text-[11px] font-bold text-cdi uppercase italic">{{ $karyawan->emergency_1_hubungan ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-black text-slate-400 uppercase">Telepon</p>
                            <p class="text-[11px] font-bold text-cdi italic">{{ $karyawan->emergency_1_telp ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CONTACT QUICK INFO --}}
            <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-slate-200">
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <i class="fas fa-envelope text-cdi-orange text-xs"></i>
                        <div class="overflow-hidden">
                            <p class="text-[8px] font-black text-slate-500 uppercase">Email Sistem</p>
                            <p class="text-[10px] font-bold truncate">{{ $karyawan->user->email ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <i class="fas fa-phone text-cdi-orange text-xs"></i>
                        <div>
                            <p class="text-[8px] font-black text-slate-500 uppercase">WhatsApp / Telp</p>
                            <p class="text-[10px] font-bold">{{ $karyawan->telepon }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: DETAILED INFO --}}
        <div class="lg:col-span-8 space-y-8">
            {{-- DATA PERSONAL & PEKERJAAN --}}
            <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm">
                <div class="flex items-center gap-4 mb-10 border-b border-slate-50 pb-6">
                    <div class="w-10 h-10 bg-cdi-orange/10 text-cdi-orange rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-tag text-sm"></i>
                    </div>
                    <h4 class="text-xs font-black text-cdi uppercase tracking-[0.2em] italic">Informasi Personal & Pekerjaan</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-12">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">NIK (Sesuai KTP)</p>
                        <p class="font-bold text-cdi italic text-sm">{{ $karyawan->nik }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tempat, Tanggal Lahir</p>
                        <p class="font-bold text-cdi italic uppercase text-sm">
                            {{ $karyawan->tempat_lahir }}, {{ \Carbon\Carbon::parse($karyawan->tanggal_lahir)->translatedFormat('d F Y') }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Jenis Kelamin / Gol. Darah</p>
                        <p class="font-bold text-cdi italic uppercase text-sm">
                            {{ $karyawan->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan' }} / {{ $karyawan->golongan_darah }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Jabatan / Role</p>
                        <p class="font-bold text-cdi italic uppercase text-sm">{{ $karyawan->jabatan }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Gaji Pokok / Uang Saku</p>
                        <p class="font-bold text-cdi italic uppercase text-sm">Rp {{ number_format($karyawan->gaji_pokok, 0, ',', '.') }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Jumlah Tanggungan</p>
                        <p class="font-bold text-cdi italic uppercase text-sm">{{ $karyawan->jumlah_tanggungan ?? 0 }} Orang</p>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Alamat KTP</p>
                        <p class="font-bold text-cdi uppercase text-xs leading-relaxed">{{ $karyawan->alamat_ktp }}</p>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Alamat Domisili</p>
                        <p class="font-bold text-cdi-orange uppercase text-xs leading-relaxed">{{ $karyawan->alamat_domisili }}</p>
                    </div>
                </div>
            </div>

            {{-- DATA PENDIDIKAN & INSTANSI --}}
            <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm">
                <div class="flex items-center gap-4 mb-10 border-b border-slate-50 pb-6">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-university text-sm"></i>
                    </div>
                    <h4 class="text-xs font-black text-cdi uppercase tracking-[0.2em] italic">Riwayat Pendidikan & Instansi</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-12">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Asal Kampus / Sekolah</p>
                        <p class="font-bold text-cdi italic uppercase text-sm">{{ $karyawan->instansi }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Jenjang / Status Studi</p>
                        <p class="font-bold text-cdi italic uppercase text-sm">{{ $karyawan->pendidikan_terakhir }} ({{ $karyawan->status_pendidikan }})</p>
                    </div>
                    @if($karyawan->bukti_tanggungan)
                    <div class="space-y-1 md:col-span-2">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Dokumen Penunjang (KK/Lainnya)</p>
                        <a href="{{ asset('storage/'.$karyawan->bukti_tanggungan) }}" target="_blank" class="inline-flex items-center gap-2 mt-2 px-4 py-2 bg-slate-100 rounded-lg text-[10px] font-black text-cdi uppercase hover:bg-cdi hover:text-white transition-all">
                            <i class="fas fa-file-pdf"></i> Lihat Dokumen Terlampir
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- MASA KERJA INDICATOR --}}
            <div class="p-10 bg-slate-50 rounded-[3rem] border border-slate-100 relative overflow-hidden group">
                <i class="fas fa-calendar-alt absolute -right-4 -bottom-4 text-8xl text-slate-200/50 group-hover:scale-110 transition-transform duration-700"></i>
                <div class="relative z-10">
                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <p class="text-[10px] font-black text-cdi uppercase italic tracking-tighter">Durasi Bergabung</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Join Date: {{ \Carbon\Carbon::parse($karyawan->created_at)->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-black text-cdi italic tracking-tighter">
                                {{ \Carbon\Carbon::parse($karyawan->created_at)->diffInDays(now()) }} 
                                <span class="text-[10px] text-slate-400 not-italic">HARI</span>
                            </p>
                        </div>
                    </div>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div class="bg-cdi h-full rounded-full" style="width: 100%"></div>
                    </div>
                    <p class="text-[8px] font-bold text-slate-400 uppercase mt-6 tracking-widest flex items-center gap-2">
                        <i class="fas fa-info-circle text-cdi-orange"></i>
                        Data ini digunakan sebagai basis perhitungan masa kerja dan loyalitas personel.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection