@extends('layouts.app')

@section('title', 'Laporan Kehadiran Saya')
@section('page_title', 'My Attendance Report')

@section('content')
<div class="space-y-8 pb-20">
    {{-- FILTER CARD --}}
    <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-6 print:hidden">
        <form action="{{ route('karyawan.absensi') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center bg-slate-50 rounded-2xl px-4 py-1 border border-slate-100">
                <i class="fas fa-calendar-alt text-cdi-orange mr-2"></i>
                <select name="bulan" class="bg-transparent border-none py-3 font-black text-[11px] text-cdi outline-none uppercase cursor-pointer">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ (int)$bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center bg-slate-50 rounded-2xl px-4 py-1 border border-slate-100">
                <i class="fas fa-layer-group text-cdi-orange mr-2"></i>
                <select name="tahun" class="bg-transparent border-none py-3 font-black text-[11px] text-cdi outline-none uppercase cursor-pointer">
                    @php $yNow = date('Y'); @endphp
                    @for($y = $yNow; $y >= $yNow - 2; $y--)
                        <option value="{{ $y }}" {{ (int)$tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="bg-cdi text-white px-8 py-4 rounded-2xl font-black uppercase italic text-[10px] hover:bg-cdi-orange transition-all shadow-lg shadow-blue-900/10">
                Tampilkan Data
            </button>
        </form>
        <button onclick="window.print()" class="text-cdi font-black text-[10px] uppercase italic hover:text-cdi-orange transition-colors">
            <i class="fas fa-file-pdf mr-2"></i> Cetak Laporan
        </button>
    </div>

    {{-- REKAPITULASI SCORE --}}
    @php
        $totalHari = \Carbon\Carbon::create((int)$tahun, (int)$bulan)->daysInMonth;
        $hadirCount = $absensis->count();
        $izinCount = $perizinans->sum('lama_hari');
        
        $minggu = 0;
        $absenCount = 0;
        $now = \Carbon\Carbon::now();
        $jamPulangKantor = 16; // Asumsi jam 16:00

        for($d=1; $d<=$totalHari; $d++) {
            $checkDate = \Carbon\Carbon::create((int)$tahun, (int)$bulan, $d);
            if($checkDate->isSunday()) {
                $minggu++;
            } else {
                // Hitung Absen (Tidak Hadir) hanya jika hari sudah lewat, 
                // atau hari ini sudah lewat jam pulang kantor
                $isPast = $checkDate->isPast() && !$checkDate->isToday();
                $isTodayOver = $checkDate->isToday() && $now->hour >= $jamPulangKantor;

                if($isPast || $isTodayOver) {
                    $adaAbsen = $absensis->first(fn($a) => \Carbon\Carbon::parse($a->jam_masuk)->isSameDay($checkDate));
                    $adaIzin = $perizinans->first(fn($i) => $checkDate->between($i->tanggal_mulai, $i->tanggal_selesai));
                    
                    if(!$adaAbsen && !$adaIzin) $absenCount++;
                }
            }
        }
        
        $hariKerjaEfektif = $totalHari - $minggu;
        $skor = ($hariKerjaEfektif > 0) ? round(($hadirCount / $hariKerjaEfektif) * 100) : 0;
        $color = $skor >= 80 ? 'green' : ($skor >= 50 ? 'orange' : 'red');
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm text-center">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Hadir</p>
            <h3 class="text-3xl font-black text-green-600 italic">{{ $hadirCount }}</h3>
        </div>
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm text-center">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Izin/Cuti</p>
            <h3 class="text-3xl font-black text-blue-600 italic">{{ $izinCount }}</h3>
        </div>
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm text-center">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Absen</p>
            <h3 class="text-3xl font-black text-red-600 italic">{{ $absenCount }}</h3>
        </div>
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm text-center">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Rating Kehadiran</p>
            <h3 class="text-3xl font-black text-{{ $color }}-600 italic">{{ $skor }}%</h3>
        </div>
    </div>

    {{-- DETAIL TABLE --}}
    <div class="bg-white rounded-[3.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
            <h4 class="font-black text-cdi uppercase italic text-xs tracking-widest">
                Detail Log Harian: {{ \Carbon\Carbon::create((int)$tahun, (int)$bulan, 1)->translatedFormat('F Y') }}
            </h4>
            <div class="text-[10px] font-bold text-slate-400 uppercase italic">
                {{ $hariKerjaEfektif }} Hari Kerja Efektif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 border-b border-slate-100">Tanggal</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 border-b border-slate-100">Jam Masuk</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 border-b border-slate-100">Jam Pulang</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 text-right border-b border-slate-100">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @for($dia = 1; $dia <= $totalHari; $dia++)
                        @php
                            $currentDate = \Carbon\Carbon::create((int)$tahun, (int)$bulan, $dia);
                            $isSunday = $currentDate->isSunday();
                            $isPast = $currentDate->isPast() && !$currentDate->isToday();
                            $isToday = $currentDate->isToday();
                            $isFuture = $currentDate->isFuture();
                            
                            $absensiHariIni = $absensis->first(function($a) use ($currentDate) {
                                return \Carbon\Carbon::parse($a->jam_masuk)->isSameDay($currentDate);
                            });

                            $izinHariIni = $perizinans->first(function($i) use ($currentDate) {
                                return $currentDate->between(
                                    \Carbon\Carbon::parse($i->tanggal_mulai)->startOfDay(), 
                                    \Carbon\Carbon::parse($i->tanggal_selesai)->endOfDay()
                                );
                            });
                        @endphp
                        <tr class="{{ $isSunday ? 'bg-red-50/20' : '' }} hover:bg-slate-50 transition-colors">
                            <td class="px-8 py-4">
                                <span class="text-xs font-black {{ $isSunday ? 'text-red-500' : 'text-cdi' }} uppercase italic">
                                    {{ $currentDate->translatedFormat('d M Y') }}
                                </span>
                                <span class="block text-[8px] font-bold text-slate-400 uppercase leading-none mt-1">{{ $currentDate->translatedFormat('l') }}</span>
                            </td>
                            <td class="px-8 py-4 font-black text-slate-600 text-xs italic">
                                @if($absensiHariIni)
                                    {{ \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') }}
                                @else
                                    <span class="text-slate-200">--:--</span>
                                @endif
                            </td>
                            <td class="px-8 py-4 font-black text-slate-600 text-xs italic">
                                @if($absensiHariIni && $absensiHariIni->jam_keluar)
                                    {{ \Carbon\Carbon::parse($absensiHariIni->jam_keluar)->format('H:i') }}
                                @else
                                    <span class="text-slate-200">--:--</span>
                                @endif
                            </td>
                            <td class="px-8 py-4 text-right">
                                @if($absensiHariIni)
                                    <span class="px-3 py-1 rounded-lg bg-green-100 text-green-600 font-black uppercase italic text-[9px]">Hadir</span>
                                @elseif($izinHariIni)
                                    <span class="px-3 py-1 rounded-lg bg-blue-100 text-blue-600 font-black uppercase italic text-[9px]">{{ $izinHariIni->jenis_izin }}</span>
                                @elseif($isSunday)
                                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-400 font-black uppercase italic text-[9px]">OFF</span>
                                @elseif($isFuture)
                                    <span class="px-3 py-1 rounded-lg bg-slate-50 text-slate-300 font-black uppercase italic text-[9px]">-</span>
                                @elseif($isToday)
                                    @if($now->hour < $jamPulangKantor)
                                        <span class="px-3 py-1 rounded-lg bg-orange-100 text-orange-500 font-black uppercase italic text-[9px]">Belum Absen</span>
                                    @else
                                        <span class="px-3 py-1 rounded-lg bg-red-100 text-red-500 font-black uppercase italic text-[9px]">Absen</span>
                                    @endif
                                @elseif($isPast)
                                    <span class="px-3 py-1 rounded-lg bg-red-100 text-red-500 font-black uppercase italic text-[9px]">Absen</span>
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        header, aside, nav, .print\:hidden { display: none !important; }
        .bg-white { border: none !important; box-shadow: none !important; }
        body { background: white !important; margin: 0; padding: 0; }
        .rounded-\[3rem\], .rounded-\[3\.5rem\], .rounded-\[2\.5rem\] { 
            border-radius: 10px !important; 
            border: 1px solid #eee !important; 
        }
        .space-y-8 { margin-top: 0 !important; }
        table { width: 100% !important; border: 1px solid #eee !important; }
        th, td { border-bottom: 1px solid #eee !important; }
    }
    
    .text-green-600 { color: #16a34a; }
    .text-blue-600 { color: #2563eb; }
    .text-orange-600 { color: #ea580c; }
    .text-red-600 { color: #dc2626; }
    .text-cdi-orange { color: #f97316; }
</style>
@endsection