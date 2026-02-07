@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Overview Sistem')

@section('content')
<div class="space-y-8 pb-10">
    {{-- HERO SECTION --}}
    <div class="relative bg-cdi rounded-[3.5rem] p-8 md:p-14 overflow-hidden shadow-2xl shadow-blue-900/30">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="text-center md:text-left">
                <div class="inline-flex items-center bg-white/10 px-4 py-2 rounded-full mb-4 backdrop-blur-md">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse mr-2"></span>
                    <span class="text-[9px] font-black text-white uppercase tracking-[0.2em]">Sistem Online & Stabil</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-black text-white italic uppercase tracking-tighter leading-none">
                    Hello, <span class="text-cdi-orange">Administrator</span>
                </h2>
                <p class="text-white/50 font-bold uppercase tracking-[0.4em] text-[10px] mt-4 flex items-center justify-center md:justify-start">
                    <i class="fas fa-calendar-alt mr-2"></i> {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('absensi.scan') }}" class="bg-cdi-orange text-white px-8 py-5 rounded-2xl font-black uppercase italic text-xs hover:scale-105 transition-all shadow-xl shadow-orange-500/20">
                    <i class="fas fa-qrcode mr-2"></i> Buka Scanner
                </a>
                <a href="{{ route('payroll.export') }}" class="bg-white/10 text-white border border-white/20 px-8 py-5 rounded-2xl font-black uppercase italic text-xs hover:bg-white hover:text-cdi transition-all">
                    <i class="fas fa-download mr-2"></i> Laporan Cepat
                </a>
            </div>
        </div>
        {{-- Background Decorations --}}
        <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-cdi-orange/10 rounded-full blur-[100px]"></div>
        <div class="absolute -left-20 -top-20 w-72 h-72 bg-blue-500/10 rounded-full blur-[80px]"></div>
        <i class="fas fa-chart-pie absolute right-10 top-1/2 -translate-y-1/2 text-white/5 text-[15rem] rotate-12"></i>
    </div>

    {{-- STATS GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
        @php
            $totalStaf = \App\Models\Karyawan::count();
            $hariIni = \Carbon\Carbon::today();

            // Hitung Kehadiran
            $hadirHariIni = \App\Models\Absensi::whereDate('jam_masuk', $hariIni)->count();
            
            $terlambatHariIni = \App\Models\Absensi::whereDate('jam_masuk', $hariIni)
                                ->where('keterangan', 'LIKE', '%Terlambat%')
                                ->count();

            // Hitung Izin/Sakit Approved
            $izinHariIni = \App\Models\Perizinan::where('status', 'disetujui')
                            ->whereDate('tanggal_mulai', '<=', $hariIni)
                            ->whereDate('tanggal_selesai', '>=', $hariIni)
                            ->count();

            $mangkir = $totalStaf - ($hadirHariIni + $izinHariIni);
        @endphp
        
        {{-- Total --}}
        <div class="bg-white p-7 rounded-[2.5rem] border border-slate-100 shadow-sm group hover:border-cdi transition-all">
            <div class="w-12 h-12 bg-slate-50 text-cdi rounded-2xl flex items-center justify-center mb-5 group-hover:bg-cdi group-hover:text-white transition-all">
                <i class="fas fa-users text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Personel</p>
            <p class="text-4xl font-black text-cdi mt-1 italic">{{ $totalStaf }}</p>
        </div>

        {{-- Hadir --}}
        <div class="bg-white p-7 rounded-[2.5rem] border border-slate-100 shadow-sm group hover:border-green-500 transition-all">
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-green-500 group-hover:text-white transition-all">
                <i class="fas fa-check-double text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Hadir</p>
            <p class="text-4xl font-black text-green-600 mt-1 italic">{{ $hadirHariIni }}</p>
        </div>

        {{-- Terlambat --}}
        <div class="bg-white p-7 rounded-[2.5rem] border border-slate-100 shadow-sm group hover:border-orange-500 transition-all">
            <div class="w-12 h-12 bg-orange-50 text-cdi-orange rounded-2xl flex items-center justify-center mb-5 group-hover:bg-cdi-orange group-hover:text-white transition-all">
                <i class="fas fa-stopwatch text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Terlambat</p>
            <p class="text-4xl font-black text-cdi-orange mt-1 italic">{{ $terlambatHariIni }}</p>
        </div>

        {{-- Izin/Sakit --}}
        <div class="bg-white p-7 rounded-[2.5rem] border border-slate-100 shadow-sm group hover:border-blue-500 transition-all">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-blue-500 group-hover:text-white transition-all">
                <i class="fas fa-envelope-open-text text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Izin / Sakit</p>
            <p class="text-4xl font-black text-blue-600 mt-1 italic">{{ $izinHariIni }}</p>
        </div>

        {{-- Tanpa Keterangan --}}
        <div class="bg-white p-7 rounded-[2.5rem] border border-slate-100 shadow-sm group hover:border-red-500 transition-all">
            <div class="w-12 h-12 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-red-500 group-hover:text-white transition-all">
                <i class="fas fa-user-slash text-xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Absen</p>
            <p class="text-4xl font-black text-red-500 mt-1 italic">{{ $mangkir > 0 ? $mangkir : 0 }}</p>
        </div>
    </div>

    {{-- ANALYTICS & LOGS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Chart --}}
        <div class="lg:col-span-2 bg-white p-10 rounded-[3.5rem] border border-slate-100 shadow-sm flex flex-col">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                    <h4 class="text-lg font-black text-cdi uppercase italic tracking-tighter">Tren Kehadiran Mingguan</h4>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Perbandingan kehadiran 7 hari terakhir</p>
                </div>
            </div>
            <div class="flex-1 min-h-[350px]">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        {{-- List Section --}}
        <div class="space-y-8 flex flex-col">
            {{-- PERIZINAN PENDING --}}
            <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm">
                <h4 class="text-sm font-black text-cdi uppercase italic tracking-tighter mb-6">Persetujuan Izin</h4>
                <div class="space-y-4">
                    @forelse(\App\Models\Perizinan::where('status', 'pending')->with('karyawan')->take(3)->get() as $p)
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl group hover:bg-white hover:shadow-md transition-all">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-orange-500 text-white rounded-lg flex items-center justify-center text-[10px]">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-cdi uppercase leading-none">{{ $p->karyawan->nama }}</p>
                                <p class="text-[8px] font-bold text-slate-400 uppercase mt-1">{{ $p->jenis_izin }}</p>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <form action="{{ route('admin.perizinan.konfirmasi', [$p->id, 'disetujui']) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-7 h-7 bg-green-500 text-white rounded-lg flex items-center justify-center hover:scale-110 transition-transform">
                                    <i class="fas fa-check text-[8px]"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <p class="text-[9px] font-bold text-slate-400 uppercase italic">Tidak ada antrian izin</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- LOG ABSENSI TERAKHIR --}}
            <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm flex-1">
                <h4 class="text-sm font-black text-cdi uppercase italic tracking-tighter mb-6">Absensi Terakhir</h4>
                <div class="space-y-6">
                    @forelse(\App\Models\Absensi::with('karyawan')->latest()->take(5)->get() as $abs)
                    <div class="flex items-center justify-between group">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center font-black text-cdi text-xs border-2 border-white shadow-sm overflow-hidden">
                                @if($abs->karyawan->foto)
                                    <img src="{{ asset('storage/'.$abs->karyawan->foto) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="uppercase">{{ substr($abs->karyawan->nama, 0, 1) }}</span>
                                @endif
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-cdi uppercase leading-none">{{ $abs->karyawan->nama }}</p>
                                <p class="text-[8px] font-bold text-slate-400 uppercase mt-1 italic">{{ $abs->jam_masuk->format('H:i') }} • {{ $abs->keterangan }}</p>
                            </div>
                        </div>
                        <span class="text-[8px] font-black text-cdi/20 uppercase italic">{{ $abs->jam_masuk->diffForHumans() }}</span>
                    </div>
                    @empty
                    <p class="text-[10px] text-slate-400 text-center uppercase font-bold italic py-10">Belum ada data</p>
                    @endforelse
                </div>
                <a href="{{ route('absensi.riwayat') }}" class="block text-center mt-8 text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-cdi transition-colors">
                    Lihat Selengkapnya <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(30, 41, 59, 0.2)');
    gradient.addColorStop(1, 'rgba(30, 41, 59, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            datasets: [{
                label: 'Hadir',
                data: [45, 52, 48, 61, 55, 20, 10],
                borderColor: '#1e293b',
                backgroundColor: gradient,
                borderWidth: 4,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#f97316',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 10, weight: 'bold' } } },
                x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } }
            }
        }
    });
</script>
@endpush
@endsection