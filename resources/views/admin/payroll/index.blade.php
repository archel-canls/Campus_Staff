@extends('layouts.app')

@section('title', 'Manajemen Payroll')
@section('page_title', 'Sistem Penggajian')

@section('content')
<div class="space-y-8 pb-12" x-data="{ openConfig: false }">
    
    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h3 class="text-4xl font-black italic uppercase tracking-tighter text-cdi leading-none">
                Payroll <span class="text-cdi-orange">Dashboard</span>
            </h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-3 flex items-center">
                <span class="w-12 h-[3px] bg-cdi-orange mr-3"></span>
                Periode: {{ now()->translatedFormat('F Y') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button @click="openConfig = true" class="bg-white border-2 border-slate-100 text-cdi px-6 py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:bg-slate-50 transition-all flex items-center">
                <i class="fas fa-cog mr-2 text-cdi-orange"></i> Atur Biaya Harian
            </button>
            <button onclick="window.print()" class="bg-cdi text-white px-8 py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:bg-cdi-orange transition-all shadow-xl shadow-blue-900/20 flex items-center">
                <i class="fas fa-print mr-2"></i> Cetak Laporan
            </button>
        </div>
    </div>

    {{-- 2. STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-cdi p-8 rounded-[3rem] shadow-sm relative overflow-hidden text-white">
            <p class="text-[10px] font-black opacity-50 uppercase tracking-widest">Estimasi Total Kas Keluar</p>
            <p class="text-3xl font-black mt-2 italic">Rp {{ number_format($karyawans->sum(fn($k) => $k->gaji_pokok + ($k->absensis_count * 25000)), 0, ',', '.') }}</p>
            <i class="fas fa-money-bill-wave absolute -right-4 -top-4 opacity-10 text-8xl"></i>
        </div>
        <div class="bg-white p-8 rounded-[3rem] border-2 border-slate-50 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Karyawan</p>
            <p class="text-3xl font-black text-cdi mt-2 italic">{{ $karyawans->count() }} <span class="text-sm not-italic opacity-30">Orang</span></p>
        </div>
        <div class="bg-white p-8 rounded-[3rem] border-2 border-slate-50 shadow-sm">
            <p class="text-[10px] font-black text-green-500 uppercase tracking-widest">Sudah Terbayar</p>
            <p class="text-3xl font-black text-cdi mt-2 italic">0 <span class="text-sm not-italic opacity-30">Orang</span></p>
        </div>
    </div>

    {{-- 3. SEARCH --}}
    <div class="bg-white p-4 rounded-[2.5rem] border border-slate-100 shadow-sm">
        <form action="" method="GET" class="relative">
            <i class="fas fa-search absolute left-6 top-1/2 -translate-y-1/2 text-slate-300"></i>
            <input type="text" name="search" placeholder="Cari nama karyawan untuk mengatur gaji..." 
                class="w-full bg-slate-50 border-none rounded-2xl py-5 pl-14 pr-6 text-[11px] font-bold text-cdi outline-none focus:ring-2 focus:ring-cdi-orange/30 transition-all uppercase tracking-wider">
        </form>
    </div>

    {{-- 4. PAYROLL TABLE --}}
    <div class="bg-white rounded-[3.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 uppercase text-[9px] font-black tracking-widest text-slate-400">
                        <th class="px-10 py-8">Karyawan</th>
                        <th class="px-6 py-8">Gaji Pokok (Input)</th>
                        <th class="px-6 py-8 text-center">Kehadiran (Auto)</th>
                        <th class="px-6 py-8">Bonus/Lembur</th>
                        <th class="px-6 py-8">Total Terima</th>
                        <th class="px-10 py-8 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($karyawans as $k)
                    <form action="{{ route('payroll.update', $k->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <tr class="hover:bg-slate-50/50 transition-all group">
                            <td class="px-10 py-7">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-xl bg-cdi text-white flex items-center justify-center font-black text-xs italic shadow-md">
                                        {{ substr($k->nama, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-black text-cdi uppercase italic text-[12px] leading-none">{{ $k->nama }}</p>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">{{ $k->jabatan ?? 'Staff' }} • {{ $k->nip }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-7">
                                <div class="relative max-w-[150px]">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[8px] font-black text-slate-300">RP</span>
                                    <input type="number" name="gaji_pokok" value="{{ $k->gaji_pokok ?? 0 }}" 
                                        class="w-full bg-slate-50 border-2 border-transparent rounded-xl py-3 pl-8 pr-3 text-[11px] font-black text-cdi focus:border-cdi-orange focus:bg-white outline-none transition-all">
                                </div>
                            </td>

                            <td class="px-6 py-7 text-center">
                                <span class="text-[11px] font-black text-green-600 bg-green-50 px-3 py-1 rounded-lg">
                                    +{{ number_format($k->absensis_count * 25000, 0, ',', '.') }}
                                </span>
                                <p class="text-[8px] font-bold text-slate-300 uppercase mt-1">({{ $k->absensis_count }} Hari Hadir)</p>
                            </td>

                            <td class="px-6 py-7">
                                <input type="number" name="bonus" placeholder="0" 
                                    class="w-full max-w-[120px] bg-slate-50 border-2 border-transparent rounded-xl py-3 px-3 text-[11px] font-black text-cdi-orange focus:border-cdi-orange outline-none transition-all">
                            </td>

                            <td class="px-6 py-7">
                                <p class="text-[13px] font-black text-cdi italic tracking-tighter">
                                    Rp {{ number_format(($k->gaji_pokok ?? 0) + ($k->absensis_count * 25000), 0, ',', '.') }}
                                </p>
                            </td>

                            <td class="px-10 py-7">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="submit" class="bg-cdi text-white px-4 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-cdi-orange transition-all shadow-md">
                                        Simpan
                                    </button>
                                    <a href="#" class="bg-slate-100 text-slate-400 w-10 h-10 flex items-center justify-center rounded-xl hover:text-cdi transition-all">
                                        <i class="fas fa-file-invoice text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </form>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL GLOBAL CONFIG --}}
<div x-show="openConfig" class="fixed inset-0 bg-cdi/80 backdrop-blur-md z-[999] flex items-center justify-center p-4" x-cloak>
    <div class="bg-white rounded-[3rem] w-full max-w-md overflow-hidden">
        <div class="p-8 bg-cdi text-white flex justify-between items-center">
            <h4 class="font-black italic uppercase text-xl">Payroll <span class="text-cdi-orange">Rules</span></h4>
            <button @click="openConfig = false"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-8 space-y-6">
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Insentif Kehadiran / Hari</label>
                <input type="number" value="25000" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-black text-cdi mt-2 focus:border-cdi outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Uang Makan / Hari</label>
                <input type="number" value="15000" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-black text-cdi mt-2 focus:border-cdi outline-none">
            </div>
            <button @click="openConfig = false" class="w-full bg-cdi-orange text-white py-4 rounded-2xl font-black uppercase italic tracking-[0.2em] shadow-lg">
                Update Aturan Global
            </button>
        </div>
    </div>
</div>
@endsection