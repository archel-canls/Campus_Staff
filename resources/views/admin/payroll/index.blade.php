@extends('layouts.app')

@section('title', 'Manajemen Payroll')
@section('page_title', 'Sistem Penggajian Bulanan')

@section('content')
<div class="space-y-8 pb-12" x-data="{ openConfig: false, openJabatan: false }">
    
    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h3 class="text-4xl font-black italic uppercase tracking-tighter text-cdi leading-none">
                Payroll <span class="text-cdi-orange">Dashboard</span>
            </h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-3 flex items-center">
                <span class="w-12 h-[3px] bg-cdi-orange mr-3"></span>
                Periode: {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button @click="openJabatan = true" class="bg-cdi text-white px-6 py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:bg-cdi-orange transition-all shadow-lg">
                <i class="fas fa-money-bill-wave mr-2"></i> Atur Gaji Jabatan
            </button>
            <button @click="openConfig = true" class="bg-white border-2 border-slate-100 text-cdi px-6 py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:bg-slate-50 transition-all flex items-center">
                <i class="fas fa-cog mr-2 text-cdi-orange"></i> Bonus Absensi
            </button>
        </div>
    </div>

    {{-- 2. FILTER & SEARCH --}}
    <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-wrap items-center gap-4">
        <form action="{{ route('payroll.index') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full">
            <div class="flex-1 min-w-[200px] relative">
                <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Karyawan..." 
                    class="w-full bg-slate-50 border-none rounded-xl py-4 pl-12 pr-4 text-[11px] font-bold text-cdi focus:ring-2 focus:ring-cdi-orange/30 outline-none uppercase">
            </div>
            <div class="flex gap-2">
                <select name="bulan" class="bg-slate-50 border-none rounded-xl py-4 px-6 text-[11px] font-black text-cdi outline-none">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(2024, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
                <select name="tahun" class="bg-slate-50 border-none rounded-xl py-4 px-6 text-[11px] font-black text-cdi outline-none">
                    @foreach(range(now()->year - 2, now()->year) as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-cdi text-white px-6 py-4 rounded-xl font-black uppercase text-[10px] hover:bg-cdi-orange transition-all">
                    Filter Data
                </button>
            </div>
        </form>
    </div>

    {{-- 3. PAYROLL TABLE --}}
    <div class="bg-white rounded-[3.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 uppercase text-[9px] font-black tracking-widest text-slate-400">
                        <th class="px-10 py-8">Karyawan / Jabatan</th>
                        <th class="px-6 py-8">Gaji Pokok (Bulanan)</th>
                        <th class="px-6 py-8">Bonus Absensi (Jam)</th>
                        <th class="px-6 py-8">Tunjangan Keluarga</th>
                        <th class="px-6 py-8 bg-slate-100/50">Take Home Pay</th>
                        <th class="px-10 py-8 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($karyawans as $k)
                    @php
                        // Ambil config dari session atau default
                        $hourlyRate = session('payroll_config.hourly_rate', 25000); 
                        $tunjanganPerTanggungan = session('payroll_config.tunjangan_tanggungan', 100000);
                        
                        // 1. Gaji Pokok Bulanan diambil dari model Divisi via helper yang kita buat sebelumnya
                        $gajiPokok = $k->divisi ? $k->divisi->getGajiJabatan($k->jabatan) : 0;

                        // 2. Kalkulasi Bonus Absensi (Gaji per Jam)
                        $totalMenit = 0;
                        foreach($k->absensis as $abs) {
                            if($abs->jam_masuk && $abs->jam_keluar) {
                                $totalMenit += $abs->jam_masuk->diffInMinutes($abs->jam_keluar);
                            }
                        }
                        $totalJam = floor($totalMenit / 60);
                        $bonusAbsensi = $totalJam * $hourlyRate;

                        // 3. Tunjangan Keluarga
                        $totalTunjangan = ($k->jumlah_tanggungan ?? 0) * $tunjanganPerTanggungan;
                        
                        // Grand Total (Take Home Pay)
                        $grandTotal = $gajiPokok + $bonusAbsensi + $totalTunjangan;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-all group">
                        <td class="px-10 py-7">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-2xl bg-cdi text-white flex items-center justify-center font-black text-sm italic shadow-lg">
                                    {{ $k->initials }}
                                </div>
                                <div>
                                    <p class="font-black text-cdi uppercase italic text-[12px] leading-none">{{ $k->nama }}</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">
                                        {{ $k->jabatan }} • {{ $k->divisi->nama ?? 'Tanpa Divisi' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <span class="text-[11px] font-black text-cdi">Rp {{ number_format($gajiPokok, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-7">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-blue-600">Rp {{ number_format($bonusAbsensi, 0, ',', '.') }}</span>
                                <span class="text-[8px] font-bold text-blue-400 uppercase italic">{{ $totalJam }} Jam Kerja Terdeteksi</span>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-green-600">+Rp {{ number_format($totalTunjangan, 0, ',', '.') }}</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase italic">{{ $k->jumlah_tanggungan }} Tanggungan</span>
                            </div>
                        </td>
                        <td class="px-6 py-7 bg-slate-50/50">
                            <p class="text-[14px] font-black text-cdi italic tracking-tighter">
                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                            </p>
                        </td>
                        <td class="px-10 py-7 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('payroll.lock', $k->id) }}" method="POST" onsubmit="return confirm('Kunci gaji periode ini?')">
                                    @csrf
                                    <button type="submit" class="bg-cdi text-white px-5 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-cdi-orange transition-all shadow-md">
                                        <i class="fas fa-lock mr-1"></i> Lock Gaji
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @if($karyawans->isEmpty())
                    <tr>
                        <td colspan="6" class="px-10 py-20 text-center">
                            <i class="fas fa-folder-open text-slate-200 text-5xl mb-4"></i>
                            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">Tidak ada data karyawan ditemukan</p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL 1: ATUR GAJI PER JABATAN --}}
<div x-show="openJabatan" 
     class="fixed inset-0 bg-cdi/90 backdrop-blur-xl z-[999] flex items-center justify-center p-4" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     x-cloak>
    <div class="bg-white rounded-[3rem] w-full max-w-lg overflow-hidden shadow-2xl">
        <div class="p-8 bg-cdi text-white flex justify-between items-center">
            <h4 class="font-black italic uppercase text-xl">Set <span class="text-cdi-orange">Gaji Bulanan</span></h4>
            <button @click="openJabatan = false" class="text-white/50 hover:text-white transition-all"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('payroll.update_gaji_jabatan') }}" method="POST" class="p-8 space-y-6">
            @csrf
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Pilih Divisi & Jabatan</label>
                <select name="divisi_jabatan" required class="w-full bg-slate-50 border-2 border-slate-100 p-4 rounded-2xl text-sm font-bold mt-2 outline-none focus:border-cdi">
                    <option value="">-- Pilih Jabatan --</option>
                    @foreach($divisis as $div)
                        <optgroup label="DIVISI: {{ $div->nama }}">
                            @if($div->daftar_jabatan)
                                @foreach($div->daftar_jabatan as $jabatan => $gaji)
                                    <option value="{{ $div->id }}|{{ $jabatan }}">
                                        {{ $jabatan }} (Saat ini: Rp {{ number_format(is_array($gaji) ? ($gaji['gaji'] ?? 0) : $gaji, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            @endif
                        </optgroup>
                    @endforeach
                </select>
                <p class="text-[8px] font-bold text-slate-400 mt-2 uppercase italic">*Gaji ini adalah gaji pokok statis per bulan sesuai jabatan</p>
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Nominal Gaji Pokok Baru (Bulan)</label>
                <div class="relative mt-2">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-300 text-xs">RP</span>
                    <input type="number" name="nominal" required placeholder="Contoh: 5000000" class="w-full bg-slate-50 border-2 border-slate-100 p-4 pl-12 rounded-2xl text-sm font-black text-cdi outline-none focus:border-cdi">
                </div>
            </div>
            <button type="submit" class="w-full bg-cdi text-white py-5 rounded-2xl font-black uppercase italic tracking-widest hover:bg-cdi-orange transition-all shadow-xl">
                Update Aturan Gaji
            </button>
        </form>
    </div>
</div>

{{-- MODAL 2: CONFIG BONUS ABSENSI (HOURLY RATE) --}}
<div x-show="openConfig" 
     class="fixed inset-0 bg-cdi/90 backdrop-blur-xl z-[999] flex items-center justify-center p-4" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     x-cloak>
    <div class="bg-white rounded-[3rem] w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-8 bg-slate-100 text-cdi relative">
            <h4 class="font-black italic uppercase text-xl text-cdi">Global <span class="text-cdi-orange">Config</span></h4>
            <button @click="openConfig = false" class="absolute top-8 right-8 text-slate-400 hover:text-cdi transition-all"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('payroll.config') }}" method="POST" class="p-8 space-y-6">
            @csrf
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Upah Per Jam (Bonus Kehadiran)</label>
                <div class="relative mt-2">
                     <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-300 text-xs">RP</span>
                     <input type="number" name="hourly_rate" value="{{ session('payroll_config.hourly_rate', 25000) }}" class="w-full bg-slate-50 border-2 border-slate-100 p-4 pl-12 rounded-2xl text-sm font-black text-cdi outline-none focus:border-cdi">
                </div>
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Tunjangan Tanggungan (Per Anak/Istri)</label>
                <div class="relative mt-2">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-300 text-xs">RP</span>
                    <input type="number" name="tunjangan_tanggungan" value="{{ session('payroll_config.tunjangan_tanggungan', 100000) }}" class="w-full bg-slate-50 border-2 border-slate-100 p-4 pl-12 rounded-2xl text-sm font-black text-cdi outline-none focus:border-cdi">
                </div>
            </div>
            <button type="submit" class="w-full bg-cdi-orange text-white py-5 rounded-2xl font-black uppercase italic tracking-widest transition-all shadow-xl hover:bg-cdi">
                Simpan Konfigurasi
            </button>
        </form>
    </div>
</div>
@endsection