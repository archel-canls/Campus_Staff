@extends('layouts.app')
@section('title', 'E-Payroll Saya')

@section('content')
<div class="space-y-8 pb-20" x-data="{ showSalary: false }">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-cdi uppercase italic tracking-tighter">E-Payroll <span class="text-cdi-orange">CDI</span></h1>
            <p class="text-slate-500 font-medium">Transparansi gaji dan riwayat pendapatan Anda.</p>
        </div>
        
        {{-- Form Filter dengan Auto-Submit --}}
        <form id="filterForm" action="{{ route('karyawan.payroll') }}" method="GET" class="flex items-center gap-2 bg-white p-2 rounded-3xl shadow-sm border border-slate-100">
            <select name="bulan" onchange="document.getElementById('filterForm').submit()" class="bg-transparent border-none font-bold text-cdi focus:ring-0 cursor-pointer">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2025, $m, 1)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <select name="tahun" onchange="document.getElementById('filterForm').submit()" class="bg-transparent border-none font-bold text-cdi focus:ring-0 cursor-pointer">
                @foreach(range(now()->year, now()->year - 2) as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            {{-- Tombol Cari dihapus untuk otomasi --}}
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-cdi rounded-[3rem] p-10 text-white shadow-2xl relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-64 h-64 bg-white/5 rounded-full blur-3xl group-hover:scale-125 transition-all duration-700"></div>
            
            <div class="relative z-10 space-y-6">
                <div class="flex justify-between items-start">
                    <span class="bg-white/20 backdrop-blur-md px-4 py-1 rounded-full text-xs font-black uppercase tracking-widest">
                        Periode {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}
                    </span>
                    <button @click="showSalary = !showSalary" class="text-white/60 hover:text-white transition-colors">
                        <i class="fas" :class="showSalary ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>

                <div>
                    <p class="text-white/70 font-medium italic">Take Home Pay (THP)</p>
                    <h2 class="text-6xl font-black italic tracking-tighter transition-all" 
                        x-text="showSalary ? 'Rp {{ number_format($grandTotal, 0, ',', '.') }}' : 'Rp •••••••••'">
                    </h2>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-white/10">
                    <div>
                        <p class="text-xs text-white/50 uppercase font-black">Jam Kerja</p>
                        <p class="text-xl font-bold italic">{{ number_format($totalJamKerja, 1) }} <span class="text-sm opacity-50">Hrs</span></p>
                    </div>
                    <div>
                        <p class="text-xs text-white/50 uppercase font-black">Tunjangan</p>
                        <p class="text-xl font-bold italic" x-text="showSalary ? 'Rp {{ number_format($history->total_tunjangan_keluarga ?? 0, 0, ',', '.') }}' : 'Rp •••'">
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-white/50 uppercase font-black">Bonus</p>
                        <p class="text-xl font-bold italic text-green-300" x-text="showSalary ? 'Rp {{ number_format($history->bonus_tambahan ?? 0, 0, ',', '.') }}' : 'Rp •••'">
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-white/50 uppercase font-black">Potongan</p>
                        <p class="text-xl font-bold italic text-red-300" x-text="showSalary ? 'Rp {{ number_format($history->potongan_gaji ?? 0, 0, ',', '.') }}' : 'Rp •••'">
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[3rem] p-8 shadow-xl border border-slate-100 flex flex-col items-center text-center space-y-4">
            <div class="relative">
                <div class="w-32 h-32 rounded-3xl bg-slate-100 overflow-hidden border-4 border-cdi-orange shadow-lg">
                    @if($karyawan->foto)
                        <img src="{{ asset('storage/'.$karyawan->foto) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl font-black text-slate-300 bg-slate-100">
                            {{ $karyawan->initials }}
                        </div>
                    @endif
                </div>
            </div>
            <div>
                <h3 class="text-xl font-black text-cdi uppercase italic">{{ $karyawan->nama }}</h3>
                <p class="text-cdi-orange font-bold text-sm tracking-widest">{{ $karyawan->jabatan }}</p>
                <p class="text-slate-400 text-xs font-medium mt-1">{{ $karyawan->divisi->nama ?? '-' }} • {{ $karyawan->nip }}</p>
            </div>
            <button onclick="window.print()" class="w-full py-4 bg-slate-100 hover:bg-cdi hover:text-white rounded-2xl font-black transition-all text-cdi text-sm flex items-center justify-center gap-2">
                <i class="fas fa-print"></i> CETAK SLIP GAJI
            </button>
        </div>
    </div>

    <div class="bg-white rounded-[3rem] shadow-xl overflow-hidden border border-slate-100">
        <div class="p-8 border-b border-slate-50 flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center shadow-inner">
                <i class="fas fa-file-invoice-dollar text-xl"></i>
            </div>
            <h3 class="text-xl font-black text-cdi uppercase italic tracking-tighter">Rincian Penghasilan</h3>
        </div>
        <div class="p-8">
            <div class="space-y-4">
                <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl">
                    <span class="font-bold text-slate-600">Gaji Pokok Individu</span>
                    <span class="font-black text-cdi" x-text="showSalary ? 'Rp {{ number_format($history->gaji_pokok_nominal ?? 0, 0, ',', '.') }}' : 'Rp ••••••'"></span>
                </div>
                <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl">
                    <span class="font-bold text-slate-600">Gaji Jabatan ({{ $karyawan->jabatan }})</span>
                    <span class="font-black text-cdi" x-text="showSalary ? 'Rp {{ number_format($history->gaji_divisi_snapshot ?? 0, 0, ',', '.') }}' : 'Rp ••••••'"></span>
                </div>
                <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl">
                    <div>
                        <span class="font-bold text-slate-600">Insentif Kehadiran</span>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ number_format($totalJamKerja, 1) }} jam x Rp {{ number_format($history->rate_absensi_per_jam ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <span class="font-black text-green-600" x-text="showSalary ? 'Rp {{ number_format($totalJamKerja * ($history->rate_absensi_per_jam ?? 0), 0, ',', '.') }}' : 'Rp ••••••'"></span>
                </div>
                <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl">
                    <div>
                        <span class="font-bold text-slate-600">Tunjangan Keluarga</span>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $history->jumlah_tanggungan_snapshot ?? 0 }} Jiwa x Rp {{ number_format($history->tunjangan_per_tanggungan ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <span class="font-black text-cdi" x-text="showSalary ? 'Rp {{ number_format($history->total_tunjangan_keluarga ?? 0, 0, ',', '.') }}' : 'Rp ••••••'"></span>
                </div>
                <div class="flex justify-between items-center p-4 bg-red-50 rounded-2xl">
                    <span class="font-bold text-red-600">Total Potongan</span>
                    <span class="font-black text-red-600" x-text="showSalary ? '- Rp {{ number_format($history->potongan_gaji ?? 0, 0, ',', '.') }}' : 'Rp ••••••'"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-xl font-black text-cdi uppercase italic tracking-tighter flex items-center gap-2">
            <i class="fas fa-history text-cdi-orange"></i> Riwayat Penggajian
        </h3>
        <div class="bg-white rounded-[3rem] shadow-xl overflow-hidden border border-slate-100">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="p-6 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Periode</th>
                            <th class="p-6 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Jabatan Saat Itu</th>
                            <th class="p-6 text-center text-xs font-black text-slate-400 uppercase tracking-widest">Jam Kerja</th>
                            <th class="p-6 text-right text-xs font-black text-slate-400 uppercase tracking-widest">Total Diterima</th>
                            <th class="p-6 text-center text-xs font-black text-slate-400 uppercase tracking-widest">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($allHistories as $h)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="p-6">
                                <p class="font-black text-cdi italic">{{ \Carbon\Carbon::create($h->tahun, $h->bulan, 1)->translatedFormat('F Y') }}</p>
                                <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-bold">Snapshot Locked</span>
                            </td>
                            <td class="p-6">
                                <p class="text-slate-600 font-bold">{{ $karyawan->jabatan }}</p>
                            </td>
                            <td class="p-6 text-center">
                                <span class="font-black text-slate-400 italic">{{ number_format($h->total_jam_kerja ?? 0, 1) }} Jam</span>
                            </td>
                            <td class="p-6 text-right">
                                <p class="font-black text-cdi text-lg italic group-hover:text-cdi-orange transition-colors">
                                    Rp {{ number_format($h->hitungTotalGaji($h->total_jam_kerja ?? 0), 0, ',', '.') }}
                                </p>
                            </td>
                            <td class="p-6 text-center">
                                <a href="{{ route('karyawan.payroll', ['bulan' => $h->bulan, 'tahun' => $h->tahun]) }}" 
                                   class="inline-flex items-center justify-center w-10 h-10 bg-slate-100 text-slate-400 rounded-xl hover:bg-cdi-orange hover:text-white transition-all shadow-sm">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-20 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-folder-open text-5xl text-slate-200 mb-4"></i>
                                    <p class="text-slate-400 font-bold italic uppercase tracking-widest">Belum ada riwayat penggajian</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body { background: white !important; }
        aside, nav, form, button, .print\:hidden, .fa-history, .history-section { display: none !important; }
        .bg-cdi { background: #1e293b !important; -webkit-print-color-adjust: exact; color: white !important; border-radius: 2rem !important; }
        .bg-slate-50 { background: #f8fafc !important; }
        .text-cdi-orange { color: #f97316 !important; }
        h2 { color: white !important; }
        [x-text] { content: attr(x-text) !important; }
    }
</style>
@endsection