@extends('layouts.app')

@section('title', 'Manajemen Payroll')
@section('page_title', 'Sistem Penggajian Bulanan')

@section('content')
<div class="space-y-8 pb-12" x-data="{ 
    openConfig: false, 
    openJabatan: false,
    openFilter: false,
    search: '{{ request('search') }}',
    divisi: '{{ request('divisi_id') }}',
    jabatan: '{{ request('jabatan') }}'
}">
    
    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h3 class="text-4xl font-black italic uppercase tracking-tighter text-cdi leading-none">
                Payroll <span class="text-cdi-orange">Dashboard</span>
            </h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-3 flex items-center">
                <span class="w-12 h-[3px] bg-cdi-orange mr-3"></span>
                Periode Aktif: {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button @click="openJabatan = true" class="bg-cdi text-white px-6 py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:bg-cdi-orange transition-all shadow-lg group">
                <i class="fas fa-money-bill-wave mr-2 group-hover:animate-bounce"></i> Atur Gaji Jabatan
            </button>
            <button @click="openConfig = true" class="bg-white border-2 border-slate-100 text-cdi px-6 py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:bg-slate-50 transition-all flex items-center shadow-sm">
                <i class="fas fa-cog mr-2 text-cdi-orange"></i> Config Payroll
            </button>
        </div>
    </div>

    {{-- 2. ADVANCED FILTER & SEARCH --}}
    <div class="bg-white p-4 md:p-6 rounded-[2.5rem] border border-slate-100 shadow-sm transition-all">
        <form action="{{ route('payroll.index') }}" method="GET" id="payrollFilterForm" class="space-y-4">
            <div class="flex flex-col lg:flex-row gap-4">
                {{-- Search Box --}}
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" x-model="search" placeholder="Cari Nama Karyawan atau NIP..." 
                        class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-12 pr-4 text-[11px] font-bold text-cdi focus:ring-2 focus:ring-cdi-orange/30 outline-none uppercase placeholder:text-slate-300">
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Filter Divisi --}}
                    <div class="relative min-w-[160px]">
                        <select name="divisi_id" x-model="divisi" @change="$el.form.submit()"
                            class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-[10px] font-black text-cdi outline-none appearance-none cursor-pointer uppercase tracking-wider">
                            <option value="">Semua Divisi</option>
                            @foreach($divisis as $div)
                                <option value="{{ $div->id }}" {{ request('divisi_id') == $div->id ? 'selected' : '' }}>
                                    {{ $div->nama }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 text-[10px] pointer-events-none"></i>
                    </div>

                    {{-- Filter Jabatan (Manual String dari Database) --}}
                    <div class="relative min-w-[160px]">
                        <select name="jabatan" x-model="jabatan" @change="$el.form.submit()"
                            class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-[10px] font-black text-cdi outline-none appearance-none cursor-pointer uppercase tracking-wider">
                            <option value="">Semua Jabatan</option>
                            @php
                                $allJabatan = \App\Models\Karyawan::whereNotNull('jabatan')->distinct()->pluck('jabatan');
                            @endphp
                            @foreach($allJabatan as $j)
                                <option value="{{ $j }}" {{ request('jabatan') == $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 text-[10px] pointer-events-none"></i>
                    </div>

                    {{-- Dropdown Periode (Bulan & Tahun Gabung) --}}
                    <div class="relative" x-data="{ openDate: false }">
                        <button type="button" @click="openDate = !openDate" 
                            class="bg-slate-900 text-white px-6 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-cdi-orange transition-all flex items-center gap-3 shadow-lg shadow-slate-200">
                            <i class="fas fa-calendar-alt"></i>
                            {{ \Carbon\Carbon::create(2024, $bulan, 1)->translatedFormat('F') }} {{ $tahun }}
                            <i class="fas fa-chevron-down transition-transform" :class="openDate ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="openDate" @click.away="openDate = false" x-cloak
                            class="absolute right-0 mt-3 w-72 bg-white rounded-[2rem] shadow-2xl border border-slate-100 z-50 p-6 space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="col-span-2">
                                    <label class="text-[9px] font-black uppercase text-slate-400 mb-2 block">Pilih Bulan</label>
                                    <select name="bulan" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-[11px] font-bold text-cdi outline-none">
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create(2024, $m, 1)->translatedFormat('F') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="text-[9px] font-black uppercase text-slate-400 mb-2 block">Pilih Tahun</label>
                                    <select name="tahun" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-[11px] font-bold text-cdi outline-none">
                                        @foreach(range(now()->year - 2, now()->year) as $y)
                                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-cdi text-white py-3 rounded-xl font-black uppercase text-[10px] hover:bg-cdi-orange transition-all">
                                Terapkan Periode
                            </button>
                        </div>
                    </div>

                    {{-- Reset Filter --}}
                    @if(request('search') || request('divisi_id') || request('jabatan'))
                        <a href="{{ route('payroll.index') }}" class="w-12 h-12 flex items-center justify-center bg-red-50 text-red-500 rounded-2xl hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Reset Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- 3. PAYROLL TABLE --}}
    <div class="bg-white rounded-[3.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 uppercase text-[9px] font-black tracking-widest text-slate-400">
                        <th class="px-10 py-8 text-center w-20">Identity</th>
                        <th class="px-6 py-8">Karyawan / Divisi</th>
                        <th class="px-6 py-8">Gaji Pokok</th>
                        <th class="px-6 py-8">Bonus Absensi</th>
                        <th class="px-6 py-8">Tunj. Keluarga</th>
                        <th class="px-6 py-8 bg-slate-100/50">Take Home Pay</th>
                        <th class="px-10 py-8 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($karyawans as $k)
                    @php
                        $hourlyRate = session('payroll_config.hourly_rate', 25000); 
                        $tunjanganPerTanggungan = session('payroll_config.tunjangan_tanggungan', 100000);
                        
                        $gajiPokok = $k->divisi ? $k->divisi->getGajiJabatan($k->jabatan) : 0;

                        $totalMenit = 0;
                        foreach($k->absensis as $abs) {
                            if($abs->jam_masuk && $abs->jam_keluar) {
                                $totalMenit += \Carbon\Carbon::parse($abs->jam_masuk)->diffInMinutes(\Carbon\Carbon::parse($abs->jam_keluar));
                            }
                        }
                        $totalJam = floor($totalMenit / 60);
                        $bonusAbsensi = $totalJam * $hourlyRate;

                        $totalTunjangan = ($k->jumlah_tanggungan ?? 0) * $tunjanganPerTanggungan;
                        $grandTotal = $gajiPokok + $bonusAbsensi + $totalTunjangan;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-all group">
                        <td class="px-10 py-7">
                            <div class="w-14 h-14 rounded-[1.5rem] bg-slate-100 p-1 group-hover:bg-cdi-orange/10 transition-colors">
                                @if($k->foto)
                                    <img src="{{ asset('storage/'.$k->foto) }}" class="w-full h-full object-cover rounded-[1.2rem] shadow-sm">
                                @else
                                    <div class="w-full h-full rounded-[1.2rem] bg-cdi text-white flex items-center justify-center font-black italic text-xs">
                                        {{ $k->initials }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <div>
                                <p class="font-black text-cdi uppercase italic text-[12px] leading-none group-hover:text-cdi-orange transition-colors">{{ $k->nama }}</p>
                                <p class="text-[8px] font-bold text-slate-400 uppercase mt-2 tracking-widest flex items-center">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-500 mr-2">{{ $k->nip }}</span>
                                    {{ $k->jabatan }} • {{ $k->divisi->nama ?? 'N/A' }}
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <span class="text-[11px] font-black text-cdi">Rp {{ number_format($gajiPokok, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-7">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-blue-600">Rp {{ number_format($bonusAbsensi, 0, ',', '.') }}</span>
                                <span class="text-[8px] font-bold text-blue-400 uppercase italic tracking-tighter">{{ $totalJam }} Jam Kerja</span>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-green-600">+Rp {{ number_format($totalTunjangan, 0, ',', '.') }}</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase italic tracking-tighter">{{ $k->jumlah_tanggungan }} Tanggungan</span>
                            </div>
                        </td>
                        <td class="px-6 py-7 bg-slate-50/30 group-hover:bg-slate-100/50 transition-colors">
                            <p class="text-[15px] font-black text-cdi italic tracking-tighter">
                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                            </p>
                        </td>
                        <td class="px-10 py-7 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('payroll.lock', $k->id) }}" method="POST" onsubmit="return confirm('Kunci gaji periode ini?')">
                                    @csrf
                                    <button type="submit" class="bg-cdi text-white px-5 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-cdi-orange transition-all shadow-md active:scale-95">
                                        <i class="fas fa-lock mr-2"></i> Lock Gaji
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-10 py-32 text-center">
                            <div class="relative inline-block">
                                <i class="fas fa-search text-slate-100 text-8xl"></i>
                                <i class="fas fa-user-slash absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-slate-200 text-2xl"></i>
                            </div>
                            <p class="text-slate-400 font-black uppercase text-[11px] tracking-[0.3em] mt-6">Data tidak ditemukan</p>
                            <p class="text-slate-300 text-[9px] font-bold uppercase mt-2">Coba sesuaikan filter atau kata kunci pencarian Anda</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($karyawans instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="p-8 bg-slate-50/50 border-t border-slate-100">
            {{ $karyawans->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

{{-- MODAL 1: ATUR GAJI PER JABATAN --}}
<div x-show="openJabatan" 
     class="fixed inset-0 bg-cdi/90 backdrop-blur-xl z-[999] flex items-center justify-center p-4" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     x-cloak>
    <div class="bg-white rounded-[3rem] w-full max-w-lg overflow-hidden shadow-2xl" @click.away="openJabatan = false">
        <div class="p-8 bg-cdi text-white flex justify-between items-center relative overflow-hidden">
            <div class="relative z-10">
                <h4 class="font-black italic uppercase text-xl">Set <span class="text-cdi-orange">Gaji Pokok</span></h4>
                <p class="text-[9px] font-bold text-white/50 uppercase tracking-widest mt-1">Master Data Jabatan</p>
            </div>
            <button @click="openJabatan = false" class="relative z-10 text-white/50 hover:text-white transition-all text-xl"><i class="fas fa-times-circle"></i></button>
            <i class="fas fa-money-check-alt absolute -right-4 -bottom-4 text-white/5 text-8xl"></i>
        </div>
        <form action="{{ route('payroll.update_gaji_jabatan') }}" method="POST" class="p-8 space-y-6">
            @csrf
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-2">Pilih Divisi & Jabatan</label>
                <select name="divisi_jabatan" required class="w-full bg-slate-50 border-2 border-slate-100 p-5 rounded-2xl text-sm font-bold mt-2 outline-none focus:border-cdi focus:ring-4 focus:ring-cdi/5 transition-all">
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
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-2">Nominal Baru (Per Bulan)</label>
                <div class="relative mt-2">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-slate-300 text-xs">RP</span>
                    <input type="number" name="nominal" required placeholder="Contoh: 5000000" 
                        class="w-full bg-slate-50 border-2 border-slate-100 p-5 pl-14 rounded-2xl text-sm font-black text-cdi outline-none focus:border-cdi focus:ring-4 focus:ring-cdi/5 transition-all">
                </div>
            </div>
            <button type="submit" class="w-full bg-cdi text-white py-6 rounded-2xl font-black uppercase italic tracking-[0.2em] hover:bg-cdi-orange transition-all shadow-xl shadow-blue-900/10 active:scale-[0.98]">
                Update Gaji Jabatan
            </button>
        </form>
    </div>
</div>

{{-- MODAL 2: CONFIG GLOBAL --}}
<div x-show="openConfig" 
     class="fixed inset-0 bg-cdi/90 backdrop-blur-xl z-[999] flex items-center justify-center p-4" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     x-cloak>
    <div class="bg-white rounded-[3rem] w-full max-w-md overflow-hidden shadow-2xl" @click.away="openConfig = false">
        <div class="p-8 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h4 class="font-black italic uppercase text-xl text-cdi">Payroll <span class="text-cdi-orange">Config</span></h4>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Global Parameter Perusahaan</p>
            </div>
            <button @click="openConfig = false" class="text-slate-300 hover:text-red-500 transition-all text-xl"><i class="fas fa-times-circle"></i></button>
        </div>
        <form action="{{ route('payroll.config') }}" method="POST" class="p-8 space-y-6">
            @csrf
            <div class="space-y-4">
                <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                    <label class="text-[10px] font-black uppercase text-blue-400 tracking-widest mb-2 block">Bonus Per Jam (Presensi)</label>
                    <div class="relative">
                         <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-blue-200 text-xs">RP</span>
                         <input type="number" name="hourly_rate" value="{{ session('payroll_config.hourly_rate', 25000) }}" 
                            class="w-full bg-white border-none rounded-xl p-4 pl-12 text-sm font-black text-cdi outline-none ring-2 ring-blue-100 focus:ring-blue-400 transition-all">
                    </div>
                </div>

                <div class="p-4 bg-green-50 rounded-2xl border border-green-100">
                    <label class="text-[10px] font-black uppercase text-green-400 tracking-widest mb-2 block">Tunjangan Per Tanggungan</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-green-200 text-xs">RP</span>
                        <input type="number" name="tunjangan_tanggungan" value="{{ session('payroll_config.tunjangan_tanggungan', 100000) }}" 
                            class="w-full bg-white border-none rounded-xl p-4 pl-12 text-sm font-black text-cdi outline-none ring-2 ring-green-100 focus:ring-green-400 transition-all">
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full bg-cdi-orange text-white py-6 rounded-2xl font-black uppercase italic tracking-[0.2em] transition-all shadow-xl shadow-orange-500/20 hover:bg-cdi active:scale-[0.98]">
                Simpan Konfigurasi
            </button>
        </form>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    /* Custom Scrollbar for Table */
    .overflow-x-auto::-webkit-scrollbar { height: 6px; }
    .overflow-x-auto::-webkit-scrollbar-track { background: transparent; }
    .overflow-x-auto::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
@endsection