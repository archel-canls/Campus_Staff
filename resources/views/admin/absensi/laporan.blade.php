@extends('layouts.app')

@section('title', 'Laporan Bulanan Lengkap')
@section('page_title', 'Rekapitulasi Kehadiran Personel')

@section('content')
<div class="space-y-8 pb-20">
    {{-- FILTER & ACTION CARD --}}
    <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-6 print:hidden">
        <form action="{{ route('absensi.laporan') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center bg-slate-50 rounded-2xl px-4 py-1 border border-slate-100">
                <i class="fas fa-calendar-alt text-cdi-orange mr-2"></i>
                <select name="bulan" class="bg-transparent border-none py-3 font-black text-[11px] text-cdi outline-none uppercase cursor-pointer">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('bulan', date('m')) == $m ? 'selected' : '' }}>
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
                        <option value="{{ $y }}" {{ request('tahun', $yNow) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="bg-cdi text-white px-8 py-4 rounded-2xl font-black uppercase italic text-[10px] hover:bg-cdi-orange transition-all shadow-lg shadow-blue-900/10">
                TAMPILKAN LAPORAN
            </button>
        </form>
        <button onclick="window.print()" class="text-cdi font-black text-[10px] uppercase italic hover:text-cdi-orange transition-colors">
            <i class="fas fa-file-pdf mr-2"></i> Cetak Semua (A4)
        </button>
    </div>

    {{-- REKAPITULASI UTAMA --}}
    <div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Data Personel</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Rekap Bulanan</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Skor Kehadiran</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($laporan as $row)
                    @php
                        $now = \Carbon\Carbon::now();
                        // KONVERSI KE INTEGER UNTUK MENGHINDARI TYPE ERROR
                        $targetBulan = (int) request('bulan', date('m'));
                        $targetTahun = (int) request('tahun', date('Y'));
                        
                        $dt = \Carbon\Carbon::create($targetTahun, $targetBulan, 1);
                        $totalHari = $dt->daysInMonth;
                        
                        $hadirCount = 0;
                        $izinCount = 0;
                        $alpaCount = 0;
                        $hariKerjaBerlalu = 0;

                        for($d=1; $d<=$totalHari; $d++) {
                            $checkDate = \Carbon\Carbon::create($targetTahun, $targetBulan, $d)->startOfDay();
                            
                            if($checkDate->isSunday()) continue;

                            $absensi = $row->absensis->first(fn($a) => \Carbon\Carbon::parse($a->jam_masuk)->isSameDay($checkDate));
                            $izin = $row->perizinans->where('status', 'disetujui')->first(fn($i) => $checkDate->between(\Carbon\Carbon::parse($i->tanggal_mulai), \Carbon\Carbon::parse($i->tanggal_selesai)));

                            if($absensi) {
                                $hadirCount++;
                                $hariKerjaBerlalu++;
                            } elseif($izin) {
                                $izinCount++;
                                $hariKerjaBerlalu++;
                            } else {
                                // Jam Batas Akhir Kerja (17:00)
                                $jamBatas = $checkDate->copy()->hour(17);
                                
                                if($checkDate->isPast() && !$checkDate->isToday()) {
                                    $alpaCount++;
                                    $hariKerjaBerlalu++;
                                } elseif($checkDate->isToday() && $now->greaterThan($jamBatas)) {
                                    $alpaCount++;
                                    $hariKerjaBerlalu++;
                                }
                                // Jika hari ini belum jam 17:00 atau masa depan, tidak dihitung alpa & tidak masuk pembagi skor
                            }
                        }

                        $skor = ($hariKerjaBerlalu > 0) ? round(($hadirCount / $hariKerjaBerlalu) * 100) : 100;
                        $color = $skor >= 80 ? 'green' : ($skor >= 50 ? 'orange' : 'red');
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-all group">
                        <td class="px-8 py-6">
                            <p class="font-black text-cdi uppercase italic text-sm leading-none">{{ $row->nama }}</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">{{ $row->nip }} • {{ $row->divisi }}</p>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex justify-center gap-3">
                                <div class="text-center px-3">
                                    <p class="text-[8px] font-black text-slate-300 uppercase">Hadir</p>
                                    <p class="font-black text-green-600 text-base italic">{{ $hadirCount }}</p>
                                </div>
                                <div class="text-center px-3 border-l border-slate-100">
                                    <p class="text-[8px] font-black text-slate-300 uppercase">Izin</p>
                                    <p class="font-black text-blue-600 text-base italic">{{ $izinCount }}</p>
                                </div>
                                <div class="text-center px-3 border-l border-slate-100">
                                    <p class="text-[8px] font-black text-slate-300 uppercase">Absen</p>
                                    <p class="font-black text-red-600 text-base italic">{{ $alpaCount }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col items-center gap-1.5">
                                <span class="text-[10px] font-black uppercase italic text-{{ $color }}-600">{{ $skor }}% Rating</span>
                                <div class="w-24 bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                   <div class="bg-{{ $color }}-500 h-full" style="width: {{ $skor }}%;"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <button onclick="toggleDetail('detail-{{ $row->id }}')" class="bg-slate-50 text-slate-400 hover:text-cdi w-10 h-10 rounded-xl transition-all border border-slate-100">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- DETAIL HARIAN --}}
                    <tr id="detail-{{ $row->id }}" class="hidden detail-row bg-slate-50/30">
                        <td colspan="4" class="px-8 py-8">
                            <div class="bg-white rounded-[2rem] border border-slate-100 shadow-inner overflow-hidden p-6">
                                <div class="flex justify-between items-center mb-6 border-b border-slate-50 pb-4">
                                    <h5 class="font-black text-cdi uppercase italic text-[11px] tracking-widest">
                                        Log: {{ $row->nama }} ({{ \Carbon\Carbon::create()->month($targetBulan)->translatedFormat('F') }} {{ $targetTahun }})
                                    </h5>
                                    <button onclick="printKaryawan('detail-{{ $row->id }}', '{{ $row->nama }}')" class="text-[9px] font-black text-blue-600 uppercase border border-blue-100 px-3 py-1 rounded-lg print:hidden hover:bg-blue-50">Cetak Personal</button>
                                </div>
                                <table class="w-full text-[10px]">
                                    <thead>
                                        <tr class="text-slate-400 font-black uppercase tracking-tighter text-left">
                                            <th class="pb-3 w-32">Tanggal</th>
                                            <th class="pb-3">Masuk</th>
                                            <th class="pb-3">Pulang</th>
                                            <th class="pb-3 text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @for($dia = 1; $dia <= $totalHari; $dia++)
                                            @php
                                                $currentDate = \Carbon\Carbon::create($targetTahun, $targetBulan, $dia);
                                                $isSunday = $currentDate->isSunday();
                                                $absensiHariIni = $row->absensis->first(fn($a) => \Carbon\Carbon::parse($a->jam_masuk)->isSameDay($currentDate));
                                                $izinHariIni = $row->perizinans->where('status', 'disetujui')->first(fn($i) => $currentDate->between(\Carbon\Carbon::parse($i->tanggal_mulai), \Carbon\Carbon::parse($i->tanggal_selesai)));
                                            @endphp
                                            <tr class="{{ $isSunday ? 'bg-red-50/30' : '' }}">
                                                <td class="py-3 font-bold {{ $isSunday ? 'text-red-500' : 'text-cdi' }}">
                                                    {{ $currentDate->translatedFormat('d M Y') }}
                                                    <span class="block text-[7px] font-medium text-slate-400 uppercase leading-none">{{ $currentDate->translatedFormat('l') }}</span>
                                                </td>
                                                <td class="py-3 font-black text-slate-600 italic">
                                                    {{ $absensiHariIni ? \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') : '--:--' }}
                                                </td>
                                                <td class="py-3 font-black text-slate-600 italic">
                                                    {{ ($absensiHariIni && $absensiHariIni->jam_keluar) ? \Carbon\Carbon::parse($absensiHariIni->jam_keluar)->format('H:i') : '--:--' }}
                                                </td>
                                                <td class="py-3 text-right">
                                                    @if($absensiHariIni)
                                                        <span class="px-2 py-0.5 rounded bg-green-100 text-green-600 font-black uppercase italic text-[8px]">Hadir</span>
                                                    @elseif($izinHariIni)
                                                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-600 font-black uppercase italic text-[8px]">Izin</span>
                                                    @elseif($isSunday)
                                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-400 font-black uppercase italic text-[8px]">Libur</span>
                                                    @elseif($currentDate->isFuture() || ($currentDate->isToday() && $now->hour < 17))
                                                        <span class="px-2 py-0.5 rounded bg-slate-50 text-slate-300 font-black uppercase italic text-[8px]">Mendatang</span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded bg-red-100 text-red-600 font-black uppercase italic text-[8px]">Alpa</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>



<script>
    function toggleDetail(id) {
        const row = document.getElementById(id);
        if (row) row.classList.toggle('hidden');
    }

    function printKaryawan(id, name) {
        const content = document.getElementById(id).innerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Cetak Laporan - ' + name + '</title>');
        printWindow.document.write('<style>body{font-family:sans-serif;padding:20px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #eee;padding:10px;text-align:left;} .print\\:hidden{display:none;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h2 style="text-align:center">LAPORAN KEHADIRAN: ' + name + '</h2>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
</script>

<style>
    .text-green-600 { color: #16a34a; } .bg-green-500 { background-color: #22c55e; }
    .text-orange-600 { color: #ea580c; } .bg-orange-500 { background-color: #f97316; }
    .text-red-600 { color: #dc2626; } .bg-red-500 { background-color: #ef4444; }

    @media print {
        .detail-row { display: table-row !important; }
        header, aside, .print\:hidden, nav { display: none !important; }
        .bg-white { border: none !important; box-shadow: none !important; }
        body { background: white !important; }
        .rounded-\[3rem\], .rounded-\[2rem\] { border-radius: 0.5rem !important; border: 1px solid #eee !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>
@endsection