@extends('layouts.app')
@section('title', 'Slip Gaji')
@section('page_title', 'E-Payroll')

@section('content')
<div class="space-y-6 pb-20">
    {{-- TOTAL EARNINGS CARD --}}
    <div class="bg-cdi rounded-[3rem] p-10 text-white shadow-2xl shadow-blue-900/20 relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-60">Total Penghasilan (THP) - {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</p>
            
            {{-- Efek Sensor Gaji --}}
            <div id="salary-display" class="cursor-pointer group mt-2" onclick="toggleSalary()">
                <h2 id="salary-hidden" class="text-5xl font-black italic uppercase tracking-tighter">
                    Rp **********
                </h2>
                <h2 id="salary-shown" class="text-5xl font-black italic uppercase tracking-tighter hidden">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </h2>
                
                <div class="mt-6 flex items-center space-x-2 bg-white/10 w-fit px-4 py-2 rounded-full backdrop-blur-md hover:bg-white/20 transition-all">
                    <i id="salary-icon" class="fas fa-lock text-[10px]"></i>
                    <span id="salary-text" class="text-[9px] font-black uppercase tracking-widest">Klik untuk buka kunci</span>
                </div>
            </div>
        </div>
        <i class="fas fa-wallet absolute -right-10 -bottom-10 text-[15rem] text-white/5"></i>
    </div>

    {{-- SUMMARY MINI CARDS --}}
    <div class="grid grid-cols-2 gap-4 print:hidden">
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Kehadiran</p>
            <h4 class="text-xl font-black text-cdi italic">{{ $totalHadir }} <span class="text-[10px] text-slate-400">Hari</span></h4>
        </div>
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Terlambat</p>
            <h4 class="text-xl font-black text-red-500 italic">{{ $totalTelat }} <span class="text-[10px] text-slate-400">Kali</span></h4>
        </div>
    </div>

    {{-- ARSIP & DETAIL SECTION --}}
    <div class="bg-white rounded-[3rem] p-8 border border-slate-100">
        <div class="flex justify-between items-center mb-6 ml-2">
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Detail & Arsip Slip Gaji</h4>
            
            {{-- Tombol Cetak --}}
            <button onclick="window.print()" class="text-cdi font-black text-[9px] uppercase italic hover:text-cdi-orange flex items-center">
                <i class="fas fa-print mr-2"></i> Print Slip
            </button>
        </div>

        <div class="space-y-3">
            {{-- Baris Detail Gaji Sekarang (Bisa dikembangkan menjadi list bulan-bulan sebelumnya) --}}
            <div class="flex items-center justify-between p-5 bg-slate-50 rounded-2xl border border-white hover:border-cdi-orange transition-all cursor-pointer group" onclick="toggleDetail()">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-cdi shadow-sm group-hover:bg-cdi-orange group-hover:text-white transition-all">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <span class="font-black uppercase italic text-cdi text-xs block leading-none">Slip Gaji {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</span>
                        <span class="text-[8px] font-bold text-slate-400 uppercase italic">Klik untuk lihat rincian</span>
                    </div>
                </div>
                <i class="fas fa-chevron-down text-slate-300 group-hover:text-cdi-orange transition-transform" id="chevron-detail"></i>
            </div>

            {{-- RINCIAN TERSEMBUNYI (Dinamis) --}}
            <div id="detail-payroll" class="hidden p-6 bg-slate-50/50 rounded-3xl border border-slate-100 mt-2 space-y-4 animate-in fade-in duration-300">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <p class="text-[9px] font-black text-green-600 uppercase border-b pb-1">Penghasilan</p>
                        <div class="flex justify-between text-xs font-bold text-cdi">
                            <span class="text-slate-500 italic">Gaji Pokok</span>
                            <span>Rp {{ number_format($gajiPokok, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-cdi">
                            <span class="text-slate-500 italic">Tunjangan & Insentif</span>
                            <span>Rp {{ number_format($totalInsentif + $totalUangMakan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <p class="text-[9px] font-black text-red-600 uppercase border-b pb-1">Potongan</p>
                        <div class="flex justify-between text-xs font-bold text-cdi">
                            <span class="text-slate-500 italic">Keterlambatan</span>
                            <span class="text-red-500">- Rp {{ number_format($totalPotonganTelat, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-cdi">
                            <span class="text-slate-500 italic">BPJS</span>
                            <span class="text-red-500">- Rp {{ number_format($potonganBPJS, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Placeholder Arsip (Bulan Sebelumnya) --}}
            @php
                $bulanLalu = \Carbon\Carbon::create($tahun, $bulan, 1)->subMonth();
            @endphp
            <div class="flex items-center justify-between p-5 bg-slate-50 rounded-2xl border border-white opacity-60 grayscale cursor-not-available group">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-300 shadow-sm">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <span class="font-black uppercase italic text-slate-400 text-xs">{{ $bulanLalu->translatedFormat('F Y') }}</span>
                </div>
                <i class="fas fa-lock text-slate-200"></i>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSalary() {
        const hidden = document.getElementById('salary-hidden');
        const shown = document.getElementById('salary-shown');
        const icon = document.getElementById('salary-icon');
        const text = document.getElementById('salary-text');

        if (shown.classList.contains('hidden')) {
            shown.classList.remove('hidden');
            hidden.classList.add('hidden');
            icon.classList.replace('fa-lock', 'fa-unlock');
            text.innerText = 'Klik untuk sembunyikan';
        } else {
            shown.classList.add('hidden');
            hidden.classList.remove('hidden');
            icon.classList.replace('fa-unlock', 'fa-lock');
            text.innerText = 'Klik untuk buka kunci';
        }
    }

    function toggleDetail() {
        const detail = document.getElementById('detail-payroll');
        const chevron = document.getElementById('chevron-detail');
        
        detail.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }
</script>

<style>
    .text-cdi-orange { color: #f97316; }

    @media print {
        header, aside, nav, .print\:hidden, #salary-icon, #salary-text, #salary-hidden { display: none !important; }
        #salary-shown { display: block !important; color: black !important; }
        #detail-payroll { display: block !important; border: none !important; background: white !important; }
        .bg-cdi { background: #f8fafc !important; color: black !important; border: 1px solid #eee !important; }
        .text-white { color: black !important; }
        .rounded-\[3rem\] { border-radius: 1rem !important; }
    }
</style>
@endsection