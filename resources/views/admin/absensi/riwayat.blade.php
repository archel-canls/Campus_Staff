@extends('layouts.app')

@section('title', 'Riwayat & Perizinan')
@section('page_title', 'Laporan Kehadiran Harian')

@section('content')
<div x-data="{ tab: 'absensi' }" class="space-y-8 pb-20">
    {{-- HEADER SECTION --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
        <div>
            <h3 class="text-3xl font-black italic uppercase tracking-tighter text-cdi">Log <span class="text-cdi-orange">Kehadiran</span></h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">
                <i class="fas fa-calendar-alt mr-1 text-cdi-orange"></i>
                Data hari ini: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            </p>
        </div>
        
        {{-- TAB NAVIGATION --}}
        <div class="flex bg-slate-100 p-1.5 rounded-2xl">
            <button @click="tab = 'absensi'" 
                :class="tab === 'absensi' ? 'bg-white shadow-sm text-cdi' : 'text-slate-500'" 
                class="px-6 py-2 rounded-xl font-black uppercase italic text-[10px] transition-all">
                Daftar Hadir
            </button>
            <button @click="tab = 'perizinan'" 
                :class="tab === 'perizinan' ? 'bg-white shadow-sm text-cdi' : 'text-slate-500'" 
                class="px-6 py-2 rounded-xl font-black uppercase italic text-[10px] transition-all relative">
                Persetujuan Izin
                @php 
                    $pendingCount = \App\Models\Perizinan::where('status', 'pending')->count(); 
                @endphp
                @if($pendingCount > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[8px] flex items-center justify-center rounded-full animate-bounce border-2 border-white">
                        {{ $pendingCount }}
                    </span>
                @endif
            </button>
        </div>

        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-slate-100 text-slate-600 px-6 py-4 rounded-2xl font-black uppercase italic text-xs hover:bg-cdi hover:text-white transition-all shadow-sm">
                <i class="fas fa-print mr-2"></i> Print / PDF
            </button>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-100 text-green-600 px-6 py-4 rounded-2xl text-[10px] font-black uppercase italic animate-pulse">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- TAB 1: DAFTAR HADIR (SEMUA KARYAWAN) --}}
    <div x-show="tab === 'absensi'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Identitas Staf</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Waktu Masuk</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Status Kehadiran</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Waktu Keluar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php
                            $today = \Carbon\Carbon::today();
                            $allKaryawan = \App\Models\Karyawan::with(['absensis' => function($q) use ($today) {
                                $q->whereDate('jam_masuk', $today);
                            }, 'perizinans' => function($q) use ($today) {
                                $q->where('status', 'disetujui')
                                  ->whereDate('tanggal_mulai', '<=', $today)
                                  ->whereDate('tanggal_selesai', '>=', $today);
                            }])->get();
                        @endphp

                        @forelse($allKaryawan as $karyawan)
                        @php
                            $abs = $karyawan->absensis->first();
                            $izin = $karyawan->perizinans->first();
                            $txtTerlambat = null;
                            
                            if($abs && !$izin) {
                                // Batas jam masuk 08:00
                                $jamMasukKantor = \Carbon\Carbon::parse($abs->jam_masuk)->startOfDay()->setHour(8);
                                if ($abs->jam_masuk->gt($jamMasukKantor)) {
                                    $diff = $abs->jam_masuk->diff($jamMasukKantor);
                                    $txtTerlambat = $diff->format('%h Jam %i Menit');
                                }
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center font-black text-cdi group-hover:bg-cdi group-hover:text-white transition-all">
                                        {{ substr($karyawan->nama, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-black text-cdi uppercase italic text-sm leading-none tracking-tight">{{ $karyawan->nama }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-widest">{{ $karyawan->nip }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                @if($abs)
                                    <span class="font-black text-cdi text-lg tracking-tighter">{{ $abs->jam_masuk->format('H:i') }}</span>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase italic">Terverifikasi</p>
                                @elseif($izin)
                                    <span class="px-3 py-1 rounded-lg bg-blue-50 text-blue-600 text-[8px] font-black uppercase border border-blue-100 italic">Disetujui Admin</span>
                                @else
                                    <span class="font-black text-slate-200 text-lg tracking-tighter">--:--</span>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-center">
                                @if($izin)
                                    <div class="inline-flex flex-col">
                                        <span class="px-3 py-1 rounded-lg bg-blue-600 text-white text-[8px] font-black uppercase shadow-sm shadow-blue-200">Izin / Cuti</span>
                                        <span class="text-[8px] font-bold text-blue-400 mt-1 uppercase italic">{{ $izin->jenis_izin }}</span>
                                    </div>
                                @elseif($abs)
                                    @if($txtTerlambat)
                                        <div class="inline-flex flex-col">
                                            <span class="px-3 py-1 rounded-lg bg-orange-50 text-cdi-orange text-[8px] font-black uppercase border border-orange-100">Terlambat</span>
                                            <span class="text-[8px] font-bold text-cdi-orange mt-1 italic">{{ $txtTerlambat }}</span>
                                        </div>
                                    @else
                                        <span class="px-3 py-1 rounded-lg bg-green-50 text-green-600 text-[8px] font-black uppercase border border-green-100">Tepat Waktu</span>
                                    @endif
                                @else
                                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-400 text-[8px] font-black uppercase border border-slate-200">Belum Absen</span>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-right">
                                @if($abs && $abs->jam_keluar)
                                    <span class="font-black text-cdi-orange text-lg tracking-tighter">{{ \Carbon\Carbon::parse($abs->jam_keluar)->format('H:i') }}</span>
                                @else
                                    <span class="font-black text-slate-200 text-lg tracking-tighter">--:--</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center text-slate-300 font-black uppercase italic tracking-widest text-xs">
                                Tidak ada data karyawan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TAB 2: PERIZINAN --}}
    <div x-show="tab === 'perizinan'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;" class="space-y-8">
        {{-- BAGIAN 1: MENUNGGU PERSETUJUAN --}}
        <div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                <h4 class="text-[10px] font-black text-cdi-orange uppercase tracking-widest italic">Menunggu Persetujuan</h4>
                <span class="px-3 py-1 bg-orange-100 text-cdi-orange rounded-full text-[8px] font-bold uppercase">{{ $pendingCount }} Pengajuan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Nama & Alasan</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Dokumen</th>
                            <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Keputusan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php
                            $perizinanPending = \App\Models\Perizinan::where('status', 'pending')->with('karyawan')->latest()->get();
                        @endphp
                        @forelse($perizinanPending as $izin)
                        <tr class="hover:bg-slate-50/30 transition-colors">
                            <td class="px-8 py-6">
                                <p class="font-black text-cdi uppercase italic text-sm leading-none">{{ $izin->karyawan->nama }}</p>
                                <p class="text-[10px] font-bold text-slate-400 mt-2 italic">"{{ $izin->alasan }}"</p>
                                <span class="inline-block mt-2 px-2 py-0.5 bg-blue-50 text-blue-600 text-[8px] font-bold rounded uppercase tracking-wider border border-blue-100">{{ $izin->jenis_izin }}</span>
                            </td>
                            <td class="px-8 py-6 text-xs font-black text-cdi tracking-tighter">
                                {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d/m') }} - {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d/m/y') }}
                            </td>
                            <td class="px-8 py-6 text-center">
                                @if($izin->lampiran_pdf)
                                    <a href="{{ asset('uploads/perizinan/'.$izin->lampiran_pdf) }}" target="_blank" class="text-red-500 hover:text-red-700 transition-colors inline-block hover:scale-110 transform">
                                        <i class="fas fa-file-pdf text-xl"></i>
                                    </a>
                                @else
                                    <span class="text-slate-200 text-[10px] font-bold uppercase tracking-widest italic">No PDF</span>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2">
                                    <form action="{{ route('admin.perizinan.konfirmasi', [$izin->id, 'ditolak']) }}" method="POST" onsubmit="return confirm('Tolak perizinan ini?')">
                                        @csrf
                                        <button class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 hover:bg-red-500 hover:text-white transition-all group">
                                            <i class="fas fa-times text-xs group-hover:scale-125 transition-transform"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.perizinan.konfirmasi', [$izin->id, 'disetujui']) }}" method="POST">
                                        @csrf
                                        <button class="px-6 py-2.5 bg-cdi text-white rounded-xl font-black uppercase italic text-[9px] hover:bg-cdi-orange transition-all shadow-lg shadow-blue-900/10">
                                            Setujui
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="opacity-20 mb-4 text-4xl text-cdi"><i class="fas fa-check-circle"></i></div>
                                <p class="text-slate-300 font-black uppercase italic text-xs tracking-widest">Semua pengajuan telah diproses</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- BAGIAN 2: RIWAYAT PERIZINAN (SUDAH DIKONFIRMASI) --}}
        <div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden opacity-80 hover:opacity-100 transition-opacity">
            <div class="px-8 py-6 border-b border-slate-50 bg-slate-100/30">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Riwayat Konfirmasi Terakhir (10 Data)</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/20">
                            <th class="px-8 py-4 text-[9px] font-black uppercase text-slate-400">Karyawan</th>
                            <th class="px-8 py-4 text-[9px] font-black uppercase text-slate-400">Jenis</th>
                            <th class="px-8 py-4 text-[9px] font-black uppercase text-slate-400 text-center">Status</th>
                            <th class="px-8 py-4 text-[9px] font-black uppercase text-slate-400 text-right">Waktu Proses</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php
                            $historyPerizinan = \App\Models\Perizinan::where('status', '!=', 'pending')->with('karyawan')->latest()->take(10)->get();
                        @endphp
                        @foreach($historyPerizinan as $h)
                        <tr>
                            <td class="px-8 py-4">
                                <span class="font-bold text-cdi uppercase text-xs italic leading-none">{{ $h->karyawan->nama }}</span>
                            </td>
                            <td class="px-8 py-4">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">{{ $h->jenis_izin }}</span>
                            </td>
                            <td class="px-8 py-4 text-center">
                                @if($h->status == 'disetujui')
                                    <span class="px-2 py-0.5 rounded-md bg-green-100 text-green-600 text-[8px] font-black uppercase italic border border-green-200">Disetujui</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-red-100 text-red-600 text-[8px] font-black uppercase italic border border-red-200">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-8 py-4 text-right">
                                <span class="text-[9px] font-bold text-slate-400 uppercase italic">{{ $h->updated_at->diffForHumans() }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        header, aside, nav, .print\:hidden, .text-right, button, .flex.bg-slate-100, .opacity-80, .shadow-lg { 
            display: none !important; 
        }
        body { 
            background: white !important; 
            padding: 0 !important; 
        }
        .rounded-\[3rem\], .rounded-\[2\.5rem\] { 
            border-radius: 0.5rem !important; 
            border: 1px solid #ddd !important; 
            box-shadow: none !important; 
        }
        * { 
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important; 
        }
        .px-8 { 
            padding-left: 1rem !important; 
            padding-right: 1rem !important; 
        }
        table { 
            width: 100% !important; 
            border: 1px solid #eee !important;
        }
    }
</style>
@endsection