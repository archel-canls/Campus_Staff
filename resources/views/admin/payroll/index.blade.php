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
            <button @click="openGaji = true" type="button" class="bg-cdi text-white px-8 py-4 rounded-2xl font-black uppercase italic text-[11px] tracking-widest hover:bg-cdi-orange transition-all shadow-lg group">
                <i class="fas fa-cog mr-2 group-hover:rotate-90 transition-transform"></i> Konfigurasi Gaji
            </button>

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
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="search" x-model="search" @keyup.enter="$refs.filterForm.submit()" placeholder="Cari Nama atau NIP... (Enter)" 
                        class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-12 pr-4 text-[11px] font-bold text-cdi focus:ring-2 focus:ring-cdi-orange/30 outline-none uppercase placeholder:text-slate-300">
                </div>

                <div class="flex flex-wrap items-center gap-3">
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

                    <div class="relative min-w-[140px]">
                        <select name="bulan" x-model="globalBulan"
                            class="w-full bg-slate-100 border-none rounded-2xl py-4 px-6 text-[10px] font-black text-cdi outline-none appearance-none cursor-pointer uppercase tracking-wider focus:bg-white focus:ring-2 focus:ring-cdi transition-all">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create(2024, $m, 1)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-calendar-alt absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                    </div>

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
                        <th class="px-6 py-8 text-center w-20">Foto</th>
                        <th class="px-6 py-8">Karyawan</th>
                        <th class="px-4 py-8">Gaji Pokok</th>
                        <th class="px-4 py-8">Gaji Divisi</th>
                        <th class="px-4 py-8 text-blue-600">Bonus Absensi</th>
                        <th class="px-4 py-8 text-green-600">Bonus Lainnya</th>
                        <th class="px-4 py-8">Tunjangan</th>
                        <th class="px-4 py-8 text-red-600">Potongan</th>
                        <th class="px-10 py-8 bg-slate-100/50 text-right">Total (THP)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($karyawans as $k)
                    <tr class="hover:bg-slate-50/50 transition-all group text-[11px] font-black uppercase">
                        <td class="px-6 py-7">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 p-1 group-hover:bg-cdi-orange/10 transition-colors mx-auto overflow-hidden">
                                @if($k->foto)
                                    <img src="{{ asset('storage/'.$k->foto) }}" class="w-full h-full object-cover rounded-xl">
                                @else
                                    <div class="w-full h-full bg-cdi text-white flex items-center justify-center italic text-[10px]">
                                        {{ $k->generated_initials }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <p class="text-cdi italic leading-none group-hover:text-cdi-orange transition-colors">{{ $k->nama }}</p>
                            <p class="text-[8px] font-bold text-slate-400 mt-2 tracking-widest">{{ $k->jabatan }}</p>
                        </td>
                        <td class="px-4 py-7 text-slate-500">
                            Rp {{ number_format($k->gaji_pokok_final, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-7 text-cdi">
                            Rp {{ number_format($k->gaji_jabatan_final, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-7 text-blue-600">
                            Rp {{ number_format($k->total_jam_kerja * $k->rate_absensi_final, 0, ',', '.') }}
                            <div class="text-[7px] text-blue-400 lowercase">{{ $k->total_jam_kerja }} jam</div>
                        </td>
                        <td class="px-4 py-7 text-green-600">
                            Rp {{ number_format($k->bonus_tambahan ?? 0, 0, ',', '.') }}
                            <div class="text-[7px] text-green-400 normal-case italic">{{ $k->keterangan_bonus ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-7 text-slate-600">
                            Rp {{ number_format($k->tanggungan_final * $k->tunjangan_final, 0, ',', '.') }}
                            <div class="text-[7px] text-slate-400 italic">{{ $k->tanggungan_final }} Jiwa</div>
                        </td>
                        <td class="px-4 py-7 text-red-600">
                            -Rp {{ number_format($k->potongan_gaji ?? 0, 0, ',', '.') }}
                            <div class="text-[7px] text-red-400 normal-case italic">{{ $k->keterangan_potongan ?? '-' }}</div>
                        </td>
                        <td class="px-10 py-7 bg-slate-100/30 group-hover:bg-slate-100/80 text-right">
                            <p class="text-[15px] text-cdi italic tracking-tighter">
                                Rp {{ number_format($k->total_gaji, 0, ',', '.') }}
                            </p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-10 py-32 text-center">
                            <i class="fas fa-receipt text-slate-100 text-8xl"></i>
                            <p class="text-slate-400 font-black uppercase text-[11px] tracking-[0.3em] mt-6">Belum ada data penggajian periode ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL UTAMA: KONFIGURASI --}}
    <div x-show="openGaji" 
         class="fixed inset-0 bg-cdi/80 z-[999] flex items-center justify-center p-4 backdrop-blur-sm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-cloak>
        
        <div class="bg-white rounded-[3.5rem] w-full max-w-6xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col md:flex-row" @click.away="openGaji = false">
            
            {{-- Sidebar Modal --}}
            <div class="w-full md:w-72 bg-slate-900 p-8 flex flex-col justify-between overflow-y-auto">
                <div>
                    <h4 class="text-white font-black italic uppercase text-2xl tracking-tighter leading-tight mb-8">
                        Konfigurasi <br><span class="text-cdi-orange">Keuangan</span>
                    </h4>
                    
                    <nav class="space-y-1">
                        @php
                            $tabs = [
                                ['id' => 'jabatan', 'icon' => 'fa-sitemap', 'label' => 'Gaji Jabatan'],
                                ['id' => 'pokok', 'icon' => 'fa-user-tie', 'label' => 'Gaji Individu'],
                                ['id' => 'bonus', 'icon' => 'fa-plus-circle', 'label' => 'Bonus Tambahan'],
                                ['id' => 'potongan', 'icon' => 'fa-minus-circle', 'label' => 'Potongan Gaji'],
                                ['id' => 'absensi', 'icon' => 'fa-bolt', 'label' => 'Rate Absensi'],
                                ['id' => 'tunjangan', 'icon' => 'fa-heart', 'label' => 'Tunjangan'],
                            ];
                        @endphp

                        @foreach($tabs as $tab)
                        <button @click="activeTab = '{{ $tab['id'] }}'" 
                            :class="activeTab === '{{ $tab['id'] }}' ? 'bg-cdi-orange text-white shadow-lg' : 'text-slate-400 hover:text-white'" 
                            class="w-full flex items-center gap-4 px-5 py-4 rounded-2xl text-[9px] font-black uppercase tracking-widest transition-all text-left">
                            <i class="fas {{ $tab['icon'] }} w-5 text-center"></i> {{ $tab['label'] }}
                        </button>
                        @endforeach
                    </nav>
                </div>
                <button @click="openGaji = false" class="text-slate-500 hover:text-white text-[9px] font-bold uppercase tracking-widest text-left mt-8">
                    <i class="fas fa-arrow-left mr-2"></i> Tutup Pengaturan
                </button>
            </div>

            {{-- Content Area Modal --}}
            <div class="flex-1 p-10 overflow-y-auto bg-slate-50/50">
                
                {{-- TAB: GAJI JABATAN --}}
                <div x-show="activeTab === 'jabatan'" x-transition>
                    <div class="mb-8">
                        <h5 class="text-cdi font-black uppercase text-lg italic leading-none">Standardisasi Gaji Jabatan</h5>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-2">Setting Gaji per-Divisi & Jabatan</p>
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
                                    <select name="jabatan" required class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase transition-all">
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
                                <input type="number" name="nominal" required placeholder="NOMINAL GAJI DIVISI" class="w-full bg-white border-2 border-slate-100 p-5 pl-12 rounded-2xl text-xs font-black text-cdi outline-none focus:border-cdi shadow-sm">
                            </div>
                            <button type="submit" class="w-full bg-cdi text-white py-5 rounded-2xl font-black uppercase italic tracking-widest hover:bg-cdi-orange shadow-lg transition-all">Simpan Snapshot Divisi</button>
                        </div>
                    </form>
                </div>

                {{-- TAB: GAJI INDIVIDU --}}
                <div x-show="activeTab === 'pokok'" x-transition>
                    <div class="mb-8">
                        <h5 class="text-cdi font-black uppercase text-lg italic leading-none">Custom Gaji Pokok Individu</h5>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-2">Ubah Gaji Pokok karyawan tertentu secara spesifik</p>
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
                                <input type="number" name="gaji_pokok" required placeholder="NOMINAL GAJI POKOK INDIVIDU" class="w-full bg-white border-2 border-slate-100 p-5 pl-12 rounded-2xl text-xs font-black text-cdi outline-none focus:border-cdi">
                            </div>
                            <button type="submit" class="w-full bg-cdi text-white py-5 rounded-2xl font-black uppercase italic tracking-widest hover:bg-cdi-orange shadow-lg transition-all">Simpan Gaji Individu</button>
                        </div>
                    </form>
                </div>

                {{-- TAB: BONUS TAMBAHAN (MULTI-INPUT) --}}
                <div x-show="activeTab === 'bonus'" x-transition 
                     x-data="{ 
                        bonusTarget: 'karyawan', 
                        bonusDivisi: '',
                        items: [{ nominal: '', keterangan: '' }],
                        addItem() { this.items.push({ nominal: '', keterangan: '' }) },
                        removeItem(index) { if(this.items.length > 1) this.items.splice(index, 1) }
                     }">
                    <div class="mb-8">
                        <h5 class="text-cdi font-black uppercase text-lg italic leading-none text-green-600">Input Bonus Tambahan</h5>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-2">Dapat menginput lebih dari satu bonus sekaligus</p>
                    </div>
                    
                    <div class="flex gap-2 mb-6 bg-slate-100 p-1 rounded-2xl">
                        <button @click="bonusTarget = 'karyawan'" :class="bonusTarget === 'karyawan' ? 'bg-white text-cdi shadow-sm' : 'text-slate-400'" class="flex-1 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">Per Karyawan</button>
                        <button @click="bonusTarget = 'divisi'" :class="bonusTarget === 'divisi' ? 'bg-white text-cdi shadow-sm' : 'text-slate-400'" class="flex-1 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">Per Divisi</button>
                        <button @click="bonusTarget = 'jabatan'" :class="bonusTarget === 'jabatan' ? 'bg-white text-cdi shadow-sm' : 'text-slate-400'" class="flex-1 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">Per Jabatan</button>
                    </div>

                    <form action="{{ route('payroll.update_bonus') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="bulan" :value="globalBulan">
                        <input type="hidden" name="tahun" :value="globalTahun">
                        <input type="hidden" name="target_type" :value="bonusTarget">

                        <div class="space-y-4">
                            <div x-show="bonusTarget === 'karyawan'">
                                <select name="karyawan_id" :required="bonusTarget === 'karyawan'" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase">
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach($karyawans as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="bonusTarget === 'divisi'">
                                <select name="divisi_id" :required="bonusTarget === 'divisi'" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase">
                                    <option value="">-- Pilih Divisi --</option>
                                    @foreach($divisis as $div)
                                        <option value="{{ $div->id }}">{{ $div->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="bonusTarget === 'jabatan'" class="grid grid-cols-2 gap-4">
                                <select x-model="bonusDivisi" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase">
                                    <option value="">-- Divisi --</option>
                                    @foreach($divisis as $div)
                                        <option value="{{ $div->id }}">{{ $div->nama }}</option>
                                    @endforeach
                                </select>
                                <select name="jabatan" :required="bonusTarget === 'jabatan'" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase">
                                    <option value="">-- Jabatan --</option>
                                    <template x-for="div in divisiData.filter(d => d.id == bonusDivisi)" :key="div.id">
                                        <template x-for="jab in div.jabatans" :key="jab">
                                            <option :value="jab" x-text="jab"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>

                            {{-- Bonus Items List --}}
                            <div class="space-y-3 bg-white/50 p-4 rounded-[2rem] border border-slate-100">
                                <label class="text-[9px] font-black text-slate-400 uppercase ml-2 mb-2 block">Daftar Item Bonus</label>
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="flex gap-3 items-center animate-fadeIn">
                                        <div class="relative flex-1">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-300 text-[10px]">RP</span>
                                            <input type="number" name="bonus_nominal[]" x-model="item.nominal" required placeholder="NOMINAL" class="w-full bg-white border-2 border-slate-100 p-4 pl-10 rounded-2xl text-xs font-black text-cdi outline-none focus:border-cdi">
                                        </div>
                                        <div class="flex-[1.5]">
                                            <input type="text" name="bonus_keterangan[]" x-model="item.keterangan" required placeholder="KETERANGAN BONUS (CONTOH: LEMBUR RAYA)" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[10px] font-bold text-cdi outline-none focus:border-cdi uppercase">
                                        </div>
                                        <button type="button" @click="removeItem(index)" class="w-10 h-10 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </template>
                                
                                <button type="button" @click="addItem()" class="w-full py-3 border-2 border-dashed border-slate-200 rounded-2xl text-[9px] font-black text-slate-400 uppercase hover:border-green-400 hover:text-green-500 transition-all mt-2">
                                    <i class="fas fa-plus mr-2"></i> Tambah Baris Bonus
                                </button>
                            </div>

                            <button type="submit" class="w-full bg-green-600 text-white py-5 rounded-2xl font-black uppercase italic tracking-widest hover:bg-green-700 shadow-lg transition-all">Terapkan Semua Bonus</button>
                        </div>
                    </form>
                </div>

                {{-- TAB: POTONGAN GAJI (MULTI-INPUT) --}}
                <div x-show="activeTab === 'potongan'" x-transition 
                     x-data="{ 
                        potTarget: 'karyawan', 
                        potDivisi: '',
                        items: [{ nominal: '', keterangan: '' }],
                        addItem() { this.items.push({ nominal: '', keterangan: '' }) },
                        removeItem(index) { if(this.items.length > 1) this.items.splice(index, 1) }
                     }">
                    <div class="mb-8">
                        <h5 class="text-cdi font-black uppercase text-lg italic leading-none text-red-600">Input Potongan Gaji</h5>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-2">Denda atau cicilan per individu, divisi atau jabatan</p>
                    </div>

                    <div class="flex gap-2 mb-6 bg-slate-100 p-1 rounded-2xl">
                        <button @click="potTarget = 'karyawan'" :class="potTarget === 'karyawan' ? 'bg-white text-cdi shadow-sm' : 'text-slate-400'" class="flex-1 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">Per Karyawan</button>
                        <button @click="potTarget = 'divisi'" :class="potTarget === 'divisi' ? 'bg-white text-cdi shadow-sm' : 'text-slate-400'" class="flex-1 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">Per Divisi</button>
                        <button @click="potTarget = 'jabatan'" :class="potTarget === 'jabatan' ? 'bg-white text-cdi shadow-sm' : 'text-slate-400'" class="flex-1 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">Per Jabatan</button>
                    </div>

                    <form action="{{ route('payroll.update_potongan') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="bulan" :value="globalBulan">
                        <input type="hidden" name="tahun" :value="globalTahun">
                        <input type="hidden" name="target_type" :value="potTarget">

                        <div class="space-y-4">
                            <div x-show="potTarget === 'karyawan'">
                                <select name="karyawan_id" :required="potTarget === 'karyawan'" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase">
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach($karyawans as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="potTarget === 'divisi'">
                                <select name="divisi_id" :required="potTarget === 'divisi'" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase">
                                    <option value="">-- Pilih Divisi --</option>
                                    @foreach($divisis as $div)
                                        <option value="{{ $div->id }}">{{ $div->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="potTarget === 'jabatan'" class="grid grid-cols-2 gap-4">
                                <select x-model="potDivisi" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase">
                                    <option value="">-- Divisi --</option>
                                    @foreach($divisis as $div)
                                        <option value="{{ $div->id }}">{{ $div->nama }}</option>
                                    @endforeach
                                </select>
                                <select name="jabatan" :required="potTarget === 'jabatan'" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[11px] font-black text-cdi outline-none focus:border-cdi uppercase">
                                    <option value="">-- Jabatan --</option>
                                    <template x-for="div in divisiData.filter(d => d.id == potDivisi)" :key="div.id">
                                        <template x-for="jab in div.jabatans" :key="jab">
                                            <option :value="jab" x-text="jab"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>

                            {{-- Potongan Items List --}}
                            <div class="space-y-3 bg-white/50 p-4 rounded-[2rem] border border-slate-100">
                                <label class="text-[9px] font-black text-slate-400 uppercase ml-2 mb-2 block">Daftar Item Potongan</label>
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="flex gap-3 items-center animate-fadeIn">
                                        <div class="relative flex-1">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-300 text-[10px]">RP</span>
                                            <input type="number" name="potongan_nominal[]" x-model="item.nominal" required placeholder="NOMINAL" class="w-full bg-white border-2 border-slate-100 p-4 pl-10 rounded-2xl text-xs font-black text-cdi outline-none focus:border-cdi">
                                        </div>
                                        <div class="flex-[1.5]">
                                            <input type="text" name="potongan_keterangan[]" x-model="item.keterangan" required placeholder="KETERANGAN POTONGAN (CONTOH: KASBON)" class="w-full bg-white border-2 border-slate-100 p-4 rounded-2xl text-[10px] font-bold text-cdi outline-none focus:border-cdi uppercase">
                                        </div>
                                        <button type="button" @click="removeItem(index)" class="w-10 h-10 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </template>
                                
                                <button type="button" @click="addItem()" class="w-full py-3 border-2 border-dashed border-slate-200 rounded-2xl text-[9px] font-black text-slate-400 uppercase hover:border-red-400 hover:text-red-500 transition-all mt-2">
                                    <i class="fas fa-plus mr-2"></i> Tambah Baris Potongan
                                </button>
                            </div>

                            <button type="submit" class="w-full bg-red-600 text-white py-5 rounded-2xl font-black uppercase italic tracking-widest hover:bg-red-700 shadow-lg transition-all">Terapkan Semua Potongan</button>
                        </div>
                    </form>
                </div>

                {{-- TAB: RATE ABSENSI --}}
                <div x-show="activeTab === 'absensi'" x-transition>
                    <div class="mb-8">
                        <h5 class="text-cdi font-black uppercase text-lg italic leading-none text-blue-600">Konfigurasi Rate Absensi</h5>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-2">Global Snapshot: Nilai Rupiah per 1 jam kerja</p>
                    </div>
                    <form action="{{ route('payroll.update_rate_absensi') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="bulan" :value="globalBulan">
                        <input type="hidden" name="tahun" :value="globalTahun">
                        <div class="space-y-4">
                            <div class="relative">
                                <span class="absolute left-10 top-1/2 -translate-y-1/2 font-black text-blue-300 text-lg">RP</span>
                                <input type="number" name="rate_absensi" 
                                    value="{{ $karyawans->first()->rate_absensi_final ?? 10000 }}" required 
                                    class="w-full bg-white border-none rounded-3xl p-6 pl-20 text-xl font-black text-cdi outline-none ring-4 ring-blue-50 focus:ring-blue-400 transition-all text-center">
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-6 rounded-3xl font-black uppercase italic tracking-widest hover:bg-cdi shadow-lg transition-all">Simpan Rate Absensi</button>
                        </div>
                    </form>
                </div>

                {{-- TAB: TUNJANGAN --}}
                <div x-show="activeTab === 'tunjangan'" x-transition>
                    <div class="mb-8">
                        <h5 class="text-cdi font-black uppercase text-lg italic leading-none text-green-600">Parameter Tunjangan Keluarga</h5>
                        <p class="text-slate-400 text-[10px] font-bold uppercase mt-2">Global Snapshot: Nilai Rupiah per 1 jiwa tanggungan</p>
                    </div>
                    <form action="{{ route('payroll.update_tunjangan') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="bulan" :value="globalBulan">
                        <input type="hidden" name="tahun" :value="globalTahun">
                        <div class="space-y-4">
                            <div class="relative">
                                <span class="absolute left-10 top-1/2 -translate-y-1/2 font-black text-green-300 text-lg">RP</span>
                                <input type="number" name="tunjangan_tanggungan" 
                                    value="{{ $karyawans->first()->tunjangan_final ?? 100000 }}" required 
                                    class="w-full bg-white border-none rounded-3xl p-6 pl-20 text-xl font-black text-cdi outline-none ring-4 ring-green-50 focus:ring-green-400 transition-all text-center">
                            </div>
                            <button type="submit" class="w-full bg-cdi-orange text-white py-6 rounded-3xl font-black uppercase italic tracking-widest hover:bg-cdi shadow-lg transition-all">Simpan Tunjangan</button>
                        </div>
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

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>
@endsection