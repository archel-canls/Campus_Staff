@extends('layouts.app')

@section('title', 'Manajemen Payroll')
@section('page_title', 'Sistem Penggajian Bulanan')

@section('content')
{{-- 
    State Global menggunakan Alpine.js 
    - init(): Mengatur watcher agar saat bulan/tahun/divisi berubah, form otomatis submit.
--}}
<div class="space-y-8 pb-12" x-data="{ 
    openGaji: false,
    activeTab: 'jabatan',
    selectedDivisi: '{{ request('divisi_id') }}',
    search: '{{ request('search') }}',
    globalBulan: '{{ $bulan }}',
    globalTahun: '{{ $tahun }}',
    divisiData: [
        @foreach($divisis as $div)
        {
            id: '{{ $div->id }}',
            nama: '{{ $div->nama }}',
            jabatans: [
                @if($div->daftar_jabatan)
                    @foreach(array_keys($div->daftar_jabatan) as $jab)
                        '{{ $jab }}',
                    @endforeach
                @endif
            ]
        },
        @endforeach
    ],
    init() {
        // Watcher untuk otomatis submit saat filter berubah
        $watch('globalBulan', value => $refs.filterForm.submit());
        $watch('globalTahun', value => $refs.filterForm.submit());
        $watch('selectedDivisi', value => $refs.filterForm.submit());
    }
}">

    {{-- 0. NOTIFICATION SYSTEM --}}
    @if(session('success'))
    <div class="fixed top-10 right-10 z-[9999] animate-bounce">
        <div class="bg-green-500 text-white px-8 py-4 rounded-2xl shadow-2xl flex items-center gap-4">
            <i class="fas fa-check-circle text-2xl"></i>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest opacity-80">Berhasil</p>
                <p class="font-bold text-sm">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if(session('error') || $errors->any())
    <div class="fixed top-10 right-10 z-[9999]">
        <div class="bg-red-500 text-white px-8 py-4 rounded-2xl shadow-2xl flex items-center gap-4 border-4 border-white">
            <i class="fas fa-exclamation-triangle text-2xl"></i>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest opacity-80">Gagal</p>
                <p class="font-bold text-sm">{{ session('error') ?? 'Periksa kembali inputan Anda' }}</p>
            </div>
        </div>
    </div>
    @endif
    
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
            {{-- Tombol Konfigurasi --}}
            <button @click="openGaji = true" type="button" class="bg-cdi text-white px-8 py-4 rounded-2xl font-black uppercase italic text-[11px] tracking-widest hover:bg-cdi-orange transition-all shadow-lg group">
                <i class="fas fa-cog mr-2 group-hover:rotate-90 transition-transform"></i> Konfigurasi
            </button>

            {{-- TOMBOL SIMPAN (BARU): Untuk memicu syncSnapshot/Save data periode ini --}}
            <form action="{{ route('payroll.lock_all') }}" method="POST" onsubmit="return confirm('Simpan dan kunci seluruh data payroll untuk periode ini?')">
                @csrf
                <input type="hidden" name="bulan" :value="globalBulan">
                <input type="hidden" name="tahun" :value="globalTahun">
                <button type="submit" class="bg-green-600 text-white px-8 py-4 rounded-2xl font-black uppercase italic text-[11px] tracking-widest hover:bg-green-700 transition-all shadow-lg flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan & Kunci Data
                </button>
            </form>
        </div>
    </div>

    {{-- 2. AUTOMATIC FILTER & SEARCH --}}
    <div class="bg-white p-4 md:p-6 rounded-[2.5rem] border border-slate-100 shadow-sm">
        <form action="{{ route('admin.payroll') }}" method="GET" x-ref="filterForm" class="space-y-4">
            <div class="flex flex-col lg:flex-row gap-4">
                {{-- Pencarian --}}
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" x-model="search" @keyup.enter="$refs.filterForm.submit()" placeholder="Cari Nama atau NIP... (Enter)" 
                        class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-12 pr-4 text-[11px] font-bold text-cdi focus:ring-2 focus:ring-cdi-orange/30 outline-none uppercase placeholder:text-slate-300">
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Filter Divisi --}}
                    <div class="relative min-w-[160px]">
                        <select name="divisi_id" x-model="selectedDivisi"
                            class="w-full bg-slate-100 border-none rounded-2xl py-4 px-6 text-[10px] font-black text-cdi outline-none appearance-none cursor-pointer uppercase tracking-wider focus:bg-white focus:ring-2 focus:ring-cdi transition-all">
                            <option value="">-- Semua Divisi --</option>
                            @foreach($divisis as $div)
                                <option value="{{ $div->id }}">{{ $div->nama }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                    </div>

                    {{-- Filter Bulan --}}
                    <div class="relative min-w-[140px]">
                        <select name="bulan" x-model="globalBulan"
                            class="w-full bg-slate-100 border-none rounded-2xl py-4 px-6 text-[10px] font-black text-cdi outline-none appearance-none cursor-pointer uppercase tracking-wider focus:bg-white focus:ring-2 focus:ring-cdi transition-all">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create(2024, $m, 1)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-calendar-alt absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                    </div>

                    {{-- Filter Tahun --}}
                    <div class="relative min-w-[100px]">
                        <select name="tahun" x-model="globalTahun"
                            class="w-full bg-slate-100 border-none rounded-2xl py-4 px-6 text-[10px] font-black text-cdi outline-none appearance-none cursor-pointer uppercase tracking-wider focus:bg-white focus:ring-2 focus:ring-cdi transition-all">
                            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-clock absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                    </div>
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
                        <th class="px-6 py-8">Gaji Pokok ({{ \Carbon\Carbon::create(null, $bulan)->format('M') }})</th>
                        <th class="px-6 py-8">Gaji Divisi</th>
                        <th class="px-6 py-8">Bonus Absensi</th>
                        <th class="px-6 py-8">Tunj. Keluarga</th>
                        <th class="px-10 py-8 bg-slate-100/50 text-right">Take Home Pay</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($karyawans as $k)
                    <tr class="hover:bg-slate-50/50 transition-all group">
                        <td class="px-10 py-7">
                            <div class="w-14 h-14 rounded-[1.5rem] bg-slate-100 p-1 group-hover:bg-cdi-orange/10 transition-colors mx-auto">
                                @if($k->foto)
                                    <img src="{{ asset('storage/'.$k->foto) }}" class="w-full h-full object-cover rounded-[1.2rem] shadow-sm">
                                @else
                                    <div class="w-full h-full rounded-[1.2rem] bg-cdi text-white flex items-center justify-center font-black italic text-xs">
                                        {{ $k->generated_initials }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <div>
                                <p class="font-black text-cdi uppercase italic text-[12px] leading-none group-hover:text-cdi-orange transition-colors">{{ $k->nama }}</p>
                                <p class="text-[8px] font-bold text-slate-400 uppercase mt-2 tracking-widest">
                                    {{ $k->jabatan }} • {{ $k->nama_divisi }}
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-slate-600">Rp {{ number_format($k->gaji_pokok_final, 0, ',', '.') }}</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase italic tracking-tighter">Fix Individu</span>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-cdi">Rp {{ number_format($k->gaji_jabatan_final, 0, ',', '.') }}</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase italic tracking-tighter">Snap Divisi</span>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-blue-600">Rp {{ number_format($k->total_jam_kerja * $k->rate_absensi_final, 0, ',', '.') }}</span>
                                <span class="text-[8px] font-bold text-blue-400 uppercase italic tracking-tighter">{{ $k->total_jam_kerja }} Jam x {{ number_format($k->rate_absensi_final/1000, 0) }}k</span>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-black text-green-600">+Rp {{ number_format($k->tanggungan_final * $k->tunjangan_final, 0, ',', '.') }}</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase italic tracking-tighter">{{ $k->tanggungan_final }} Jiwa</span>
                            </div>
                        </td>
                        <td class="px-10 py-7 bg-slate-100/30 group-hover:bg-slate-100/80 transition-colors text-right">
                            <p class="text-[16px] font-black text-cdi italic tracking-tighter">
                                Rp {{ number_format($k->total_gaji, 0, ',', '.') }}
                            </p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-10 py-32 text-center">
                            <i class="fas fa-receipt text-slate-100 text-8xl"></i>
                            <p class="text-slate-400 font-black uppercase text-[11px] tracking-[0.3em] mt-6">Belum ada data penggajian periode ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL UTAMA: ATUR GAJI & PARAMETER --}}
    <div x-show="openGaji" 
         class="fixed inset-0 bg-cdi/80 z-[999] flex items-center justify-center p-4 backdrop-blur-sm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-cloak>
        
        <div class="bg-white rounded-[3.5rem] w-full max-w-5xl max-h-[95vh] overflow-hidden shadow-2xl flex flex-col md:flex-row" @click.away="openGaji = false">
            
            {{-- Sidebar Modal --}}
            <div class="w-full md:w-72 bg-slate-900 p-8 flex flex-col justify-between">
                <div>
                    <h4 class="text-white font-black italic uppercase text-2xl tracking-tighter leading-none mb-8">
                        Atur <br><span class="text-cdi-orange">Keuangan</span>
                    </h4>
                    
                    <nav class="space-y-2">
                        <button @click="activeTab = 'jabatan'" :class="activeTab === 'jabatan' ? 'bg-cdi-orange text-white' : 'text-slate-400 hover:text-white'" 
                            class="w-full flex items-center gap-4 px-5 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all text-left">
                            <i class="fas fa-sitemap w-5 text-center"></i> Gaji Per Jabatan
                        </button>
                        <button @click="activeTab = 'pokok'" :class="activeTab === 'pokok' ? 'bg-cdi-orange text-white' : 'text-slate-400 hover:text-white'" 
                            class="w-full flex items-center gap-4 px-5 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all text-left">
                            <i class="fas fa-user-tie w-5 text-center"></i> Gaji Pokok Individu
                        </button>
                        <button @click="activeTab = 'absensi'" :class="activeTab === 'absensi' ? 'bg-cdi-orange text-white' : 'text-slate-400 hover:text-white'" 
                            class="w-full flex items-center gap-4 px-5 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all text-left">
                            <i class="fas fa-bolt w-5 text-center"></i> Rate Per Jam
                        </button>
                        <button @click="activeTab = 'tunjangan'" :class="activeTab === 'tunjangan' ? 'bg-cdi-orange text-white' : 'text-slate-400 hover:text-white'" 
                            class="w-full flex items-center gap-4 px-5 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all text-left">
                            <i class="fas fa-heart w-5 text-center"></i> Tunjangan Keluarga
                        </button>
                    </nav>
                </div>
                
                <button @click="openGaji = false" class="text-slate-500 hover:text-white text-[10px] font-bold uppercase tracking-widest text-left mt-8">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
                </button>
            </div>

            {{-- Content Area Modal --}}
            <div class="flex-1 p-10 overflow-y-auto bg-slate-50/50">
                
                {{-- TAB 1: MASTER JABATAN --}}
                <div x-show="activeTab === 'jabatan'" x-transition>
                    <div class="mb-8">
                        <h5 class="text-cdi font-black uppercase text-lg italic leading-none">Standardisasi Gaji Jabatan</h5>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-2">Mengubah nilai gaji dasar untuk kategori jabatan tertentu (Snapshot Bulanan)</p>
                    </div>

                    <form action="{{ route('payroll.update_gaji_jabatan') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="bulan" :value="globalBulan">
                        <input type="hidden" name="tahun" :value="globalTahun">

                        <div x-data="{ localDivisi: '' }" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[9px] font-black text-slate-400 uppercase ml-2">Pilih Divisi</label>
                                    <select x-model="localDivisi" name="divisi_id" required class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase transition-all">
                                        <option value="">-- Divisi --</option>
                                        @foreach($divisis as $div)
                                            <option value="{{ $div->id }}">{{ $div->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-[9px] font-black text-slate-400 uppercase ml-2">Pilih Jabatan</label>
                                    <select name="jabatan" required
                                        class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase transition-all">
                                        <option value="">-- Jabatan --</option>
                                        <template x-for="div in divisiData.filter(d => d.id == localDivisi)" :key="div.id">
                                            <template x-for="jab in div.jabatans" :key="jab">
                                                <option :value="jab" x-text="jab"></option>
                                            </template>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-slate-300 text-[10px]">RP</span>
                                <input type="number" name="nominal" required placeholder="NOMINAL GAJI DIVISI UNTUK PERIODE INI" 
                                    class="w-full bg-white border-2 border-slate-100 p-5 pl-12 rounded-2xl text-xs font-black text-cdi outline-none focus:border-cdi">
                            </div>

                            <button type="submit" class="w-full bg-cdi text-white py-5 rounded-2xl font-black uppercase italic tracking-widest hover:bg-cdi-orange shadow-lg transition-all">
                                Update Gaji Divisi (Snapshot)
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TAB 2: GAJI INDIVIDU --}}
                <div x-show="activeTab === 'pokok'" x-transition>
                    <div class="mb-8">
                        <h5 class="text-cdi font-black uppercase text-lg italic leading-none">Custom Gaji Pokok Individu</h5>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-2">Gaji pokok spesifik karyawan untuk periode pilihan</p>
                    </div>

                    <form action="{{ route('payroll.update_gaji_pokok') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="bulan" :value="globalBulan">
                        <input type="hidden" name="tahun" :value="globalTahun">

                        <div class="space-y-4">
                            <select name="karyawan_id" required class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($karyawans as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                @endforeach
                            </select>

                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-slate-300 text-[10px]">RP</span>
                                <input type="number" name="gaji_pokok" required placeholder="NOMINAL GAJI POKOK PERIODE INI" 
                                    class="w-full bg-white border-2 border-slate-100 p-5 pl-12 rounded-2xl text-xs font-black text-cdi outline-none focus:border-cdi">
                            </div>

                            <button type="submit" class="w-full bg-cdi text-white py-5 rounded-2xl font-black uppercase italic tracking-widest hover:bg-blue-700 shadow-lg transition-all">
                                Simpan Gaji Individu (Snapshot)
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TAB 3: RATE PER JAM --}}
                <div x-show="activeTab === 'absensi'" x-transition>
                    <div class="mb-8">
                        <h5 class="text-cdi font-black uppercase text-lg italic leading-none">Rate Bonus Jam Kerja</h5>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-2">Bayaran per jam untuk periode terpilih (Semua atau Individu)</p>
                    </div>

                    <form action="{{ route('payroll.update_hourly_rate') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="bulan" :value="globalBulan">
                        <input type="hidden" name="tahun" :value="globalTahun">

                        <div class="space-y-4">
                            <select name="karyawan_id" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase">
                                <option value="">-- Terapkan ke Semua Karyawan --</option>
                                @foreach($karyawans as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                @endforeach
                            </select>
                            
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-slate-300 text-[10px]">RP</span>
                                <input type="number" name="rate_per_jam" required placeholder="NOMINAL PER JAM (MISAL: 30000)" 
                                    class="w-full bg-white border-2 border-slate-100 p-5 pl-12 rounded-2xl text-xs font-black text-cdi outline-none focus:border-cdi">
                            </div>

                            <button type="submit" class="w-full bg-cdi text-white py-5 rounded-2xl font-black uppercase italic tracking-widest hover:bg-green-700 shadow-lg transition-all">
                                Update Rate Absensi (Snapshot)
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TAB 4: TUNJANGAN --}}
                <div x-show="activeTab === 'tunjangan'" x-transition>
                    <div class="mb-8">
                        <h5 class="text-cdi font-black uppercase text-lg italic leading-none">Parameter Tunjangan Keluarga</h5>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-2">Nilai per tanggungan untuk periode terpilih</p>
                    </div>

                    <form action="{{ route('payroll.config') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="bulan" :value="globalBulan">
                        <input type="hidden" name="tahun" :value="globalTahun">

                        <div class="p-8 bg-green-50 rounded-[2.5rem] border-2 border-green-100">
                            <label class="text-[10px] font-black uppercase text-green-600 tracking-widest mb-4 block text-center">Rupiah per 1 Jiwa</label>
                            <div class="relative max-w-sm mx-auto">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 font-black text-green-300 text-lg">RP</span>
                                <input type="number" name="tunjangan_tanggungan" 
                                    value="{{ $karyawans->first()->tunjangan_final ?? 100000 }}" required 
                                    class="w-full bg-white border-none rounded-3xl p-6 pl-16 text-xl font-black text-cdi outline-none ring-4 ring-green-100 focus:ring-green-400 transition-all text-center">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-cdi-orange text-white py-6 rounded-3xl font-black uppercase italic tracking-widest hover:bg-cdi shadow-lg transition-all">
                            Simpan Tunjangan (Snapshot)
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>

<style>
    [x-cloak] { display: none !important; }
    .overflow-x-auto::-webkit-scrollbar { height: 6px; }
    .overflow-x-auto::-webkit-scrollbar-track { background: transparent; }
    .overflow-x-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    input[type=number] {
      -moz-appearance: textfield;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .fixed {
        animation: slideIn 0.3s ease-out forwards;
    }
</style>
@endsection