@extends('layouts.app')

@section('title', 'Admin Dashboard - Central Data Intelligence')
@section('page_title', 'Analytics Overview')

@section('content')
<div class="space-y-8 pb-10">
    {{-- DATA BRIDGE & LOGIC --}}
    @php
    $totalStaf = \App\Models\Karyawan::count();
    $hariIni = \Carbon\Carbon::today();

    // Ambil input periode dari request, default ke bulan & tahun sekarang
    $selectedMonth = request('bulan', date('m'));
    $selectedYear = request('tahun', date('Y'));
    $targetDate = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1);

    // Data Hari Ini (Statistik Atas tetap realtime hari ini)
    $hadirHariIni = \App\Models\Absensi::whereDate('jam_masuk', $hariIni)->count();
    $terlambatHariIni = \App\Models\Absensi::whereDate('jam_masuk', $hariIni)
    ->where('keterangan', 'LIKE', '%Terlambat%')
    ->count();
    $izinHariIni = \App\Models\Perizinan::where('status', 'disetujui')
    ->whereDate('tanggal_mulai', '<=', $hariIni)
        ->whereDate('tanggal_selesai', '>=', $hariIni)
        ->count();
        $persentaseHadirHariIni = $totalStaf > 0 ? round(($hadirHariIni / $totalStaf) * 100) : 0;

        // LOGIKA GRAFIK DINAMIS (Berdasarkan Periode Terpilih)
        $labels = [];
        $chartData = [];
        $startOfMonth = $targetDate->copy()->startOfMonth();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        // Ambil hari libur pada periode terpilih
        $liburPeriodeIni = \App\Models\HariLibur::whereYear('tanggal', $selectedYear)
        ->whereMonth('tanggal', $selectedMonth)
        ->pluck('tanggal')
        ->map(fn($t) => \Carbon\Carbon::parse($t)->format('Y-m-d'))
        ->toArray();

        for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
        $dateString = $date->format('Y-m-d');

        // Cek Minggu atau Hari Libur Nasional
        if ($date->isSunday() || in_array($dateString, $liburPeriodeIni)) {
        continue;
        }

        $labels[] = $date->format('d/m');
        $absensiCount = \App\Models\Absensi::whereDate('jam_masuk', $date)->count();

        $persentase = $totalStaf > 0 ? round(($absensiCount / $totalStaf) * 100) : 0;
        $chartData[] = $persentase;
        }
        @endphp

        {{-- Hidden data for Chart.js --}}
        <input type="hidden" id="chartLabelsData" value="{{ json_encode($labels) }}">
        <input type="hidden" id="chartValuesData" value="{{ json_encode($chartData) }}">

        {{-- TOP BAR --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">
                    Selamat Datang, <span class="text-blue-600">{{ Auth::user()->name }}</span>
                </h2>
                <p class="text-slate-500 font-medium mt-1">Pantau performa dan kehadiran tim Anda hari ini.</p>
            </div>
            <div class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                <div class="bg-blue-50 text-blue-600 p-3 rounded-xl">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="pr-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Hari Ini</p>
                    <p class="text-sm font-bold text-slate-700">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
        </div>

        {{-- STATS GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <p class="text-sm font-bold text-slate-500">Total Personel</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $totalStaf }}</h3>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <p class="text-sm font-bold text-slate-500">Rasio Kehadiran Hari Ini</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $persentaseHadirHariIni }}%</h3>

                <div class="w-full bg-slate-100 h-1.5 rounded-full mt-4 overflow-hidden">
                    @if($persentaseHadirHariIni > 0)
                    <div class="bg-green-500 h-1.5 rounded-full transition-all duration-700"
                        @style(['width'=> $persentaseHadirHariIni . '%'])>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <p class="text-sm font-bold text-slate-500">Terlambat</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $terlambatHariIni }}</h3>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fas fa-envelope-open-text text-xl"></i>
                </div>
                <p class="text-sm font-bold text-slate-500">Izin / Sakit</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $izinHariIni }}</h3>
            </div>
        </div>

        {{-- MAIN CONTENT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- AREA CHART BULANAN --}}
            <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                    <div>
                        <h4 class="text-lg font-black text-slate-800 uppercase tracking-tight">Grafik Kehadiran</h4>
                        <p class="text-xs text-slate-400 font-medium">Periode {{ $targetDate->translatedFormat('F Y') }}</p>
                    </div>

                    {{-- FORM FILTER PERIODE (AUTO SUBMIT) --}}
                    <form action="{{ url()->current() }}" method="GET" id="filterForm" class="flex items-center gap-2">
                        <select name="bulan" onchange="this.form.submit()" class="text-[10px] font-bold border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-slate-50 cursor-pointer">
                            @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                            @endforeach
                        </select>
                        <select name="tahun" onchange="this.form.submit()" class="text-[10px] font-bold border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-slate-50 cursor-pointer">
                            @for($y = date('Y'); $y >= date('Y')-2; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <a href="{{ route('payroll.export') }}" class="text-xs font-bold bg-slate-50 hover:bg-slate-100 text-slate-600 px-4 py-2 rounded-xl transition-all border border-slate-200">
                            <i class="fas fa-file-export mr-1"></i> Export
                        </a>
                    </form>
                </div>

                <div class="h-[350px]">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="space-y-6">
                {{-- PENDING APPROVAL --}}
                <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-white font-bold text-sm tracking-widest uppercase">Persetujuan Izin</h4>
                        @php $pendingCount = \App\Models\Perizinan::where('status', 'pending')->count(); @endphp
                        @if($pendingCount > 0)
                        <span class="bg-orange-500 text-white text-[10px] px-2 py-1 rounded-lg font-black">
                            {{ $pendingCount }} BARU
                        </span>
                        @endif
                    </div>
                    <div class="space-y-4">
                        @forelse(\App\Models\Perizinan::where('status', 'pending')->with('karyawan')->take(3)->get() as $p)
                        <div class="bg-white/5 border border-white/10 p-4 rounded-2xl flex items-center justify-between group hover:bg-white/10 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-white">{{ Str::words($p->karyawan->nama, 2) }}</p>
                                    <p class="text-[9px] text-white/50 font-medium">{{ $p->jenis_izin }} • {{ $p->lama_hari }} Hari</p>
                                </div>
                            </div>
                            <form action="{{ route('admin.perizinan.konfirmasi', [$p->id, 'disetujui']) }}" method="POST">
                                @csrf
                                <button class="w-8 h-8 rounded-full bg-green-500/20 text-green-400 hover:bg-green-500 hover:text-white transition-all flex items-center justify-center">
                                    <i class="fas fa-check text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                        @empty
                        <div class="text-center py-6">
                            <p class="text-[10px] text-white/30 font-bold uppercase tracking-widest italic">Tidak ada antrian</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- RECENT LOGS (HARIAN DASHBOARD) --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                    <h4 class="text-slate-800 font-bold text-sm tracking-tight uppercase mb-6">Aktivitas Terkini (Hari Ini)</h4>
                    <div class="space-y-6">
                        @forelse(\App\Models\Absensi::with('karyawan')->whereDate('jam_masuk', $hariIni)->latest()->take(5)->get() as $abs)
                        <div class="flex items-start gap-4">
                            <div class="relative">
                                <div class="w-10 h-10 rounded-full border border-slate-100 overflow-hidden bg-slate-100">
                                    @if($abs->karyawan->foto)
                                    <img src="{{ asset('storage/'.$abs->karyawan->foto) }}" class="w-full h-full object-cover">
                                    @else
                                    <div class="w-full h-full flex items-center justify-center text-xs font-black text-slate-400">
                                        {{ substr($abs->karyawan->nama, 0, 1) }}
                                    </div>
                                    @endif
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 {{ str_contains($abs->keterangan, 'Terlambat') ? 'bg-orange-500' : 'bg-green-500' }} rounded-full border-2 border-white"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-black text-slate-800 truncate">{{ $abs->karyawan->nama }}</p>
                                <p class="text-[10px] text-slate-400 font-medium mt-0.5">{{ $abs->jam_masuk->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-black text-slate-700">{{ $abs->jam_masuk->format('H:i') }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-center text-slate-400 py-4">Belum ada aktivitas hari ini.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('absensi.riwayat') }}" class="block mt-8 text-center text-[10px] font-black text-blue-600 uppercase tracking-widest hover:underline">
                        Lihat Seluruh Log <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartLabels = JSON.parse(document.getElementById('chartLabelsData').value);
    const chartDataValues = JSON.parse(document.getElementById('chartValuesData').value);
    const ctx = document.getElementById('attendanceChart').getContext('2d');

    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Persentase Kehadiran (%)',
                data: chartDataValues,
                borderColor: '#3b82f6',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                backgroundColor: gradient,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + '% Kehadiran';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 20,
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.5)',
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection