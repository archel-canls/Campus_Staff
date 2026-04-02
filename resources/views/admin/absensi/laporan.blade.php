@extends('layouts.app')

@section('title', 'Laporan Bulanan Lengkap')
@section('page_title', 'Rekapitulasi Kehadiran Personel')

@section('content')
<div class="space-y-8 pb-20" x-data="{ 
    showLiburModal: false,
    showAbsenManualModal: false,
    // State untuk Filter Client-side
    searchQuery: '',
    filterDivisi: '',
    filterJabatan: '',
    
    // Master data untuk filter dinamis
    @php
        $divisis = \App\Models\Divisi::all();
        $divisiJabatanMap = $divisis->mapWithKeys(function($d) {
            $jabatans = is_array($d->daftar_jabatan) ? array_keys($d->daftar_jabatan) : [];
            return [$d->nama => $jabatans];
        });
        $allUniqueJabatans = \App\Models\Karyawan::distinct()->pluck('jabatan')->filter()->toArray();
        sort($allUniqueJabatans);
    @endphp

    divisiMap: {{ json_encode($divisiJabatanMap) }},
    allJabatans: {{ json_encode($allUniqueJabatans) }},

    get filteredJabatanOptions() {
        if (this.filterDivisi === '') return this.allJabatans;
        return this.divisiMap[this.filterDivisi] || [];
    },

    updateDivisi() {
        this.filterJabatan = '';
        this.applyFilters();
    },

    applyFilters() {
        const searchTerm = this.searchQuery.toUpperCase();
        const selectedDivisi = this.filterDivisi;
        const selectedJabatan = this.filterJabatan;
        let visibleCount = 0;

        document.querySelectorAll('.person-row').forEach(row => {
            const nama = row.getAttribute('data-nama');
            const nip = row.getAttribute('data-nip');
            const divisi = row.getAttribute('data-divisi');
            const jabatan = row.getAttribute('data-jabatan');

            const matchesSearch = nama.includes(searchTerm) || nip.includes(searchTerm);
            const matchesDivisi = selectedDivisi === '' || divisi === selectedDivisi;
            const matchesJabatan = selectedJabatan === '' || jabatan === selectedJabatan;

            if (matchesSearch && matchesDivisi && matchesJabatan) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
                // Sembunyikan detail jika baris utama disembunyikan
                const detailId = row.getAttribute('data-detail-id');
                if (detailId) document.getElementById(detailId).classList.add('hidden');
            }
        });

        document.getElementById('noResultsRow').style.display = visibleCount === 0 ? '' : 'none';
    }
}">
    {{-- FILTER & ACTION CARD --}}
    <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100 flex flex-col gap-8 print:hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex flex-wrap items-center gap-4">
                {{-- Form Filter Bulan & Tahun (Otomatis Submit) --}}
                <form id="periodForm" action="{{ route('absensi.laporan') }}" method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center bg-slate-50 rounded-2xl px-4 py-1 border border-slate-100">
                        <i class="fas fa-calendar-alt text-cdi-orange mr-2"></i>
                        <select name="bulan" onchange="document.getElementById('periodForm').submit()"
                            class="bg-transparent border-none py-3 font-black text-[11px] text-cdi outline-none uppercase cursor-pointer">
                            @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('bulan', date('m')) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center bg-slate-50 rounded-2xl px-4 py-1 border border-slate-100">
                        <i class="fas fa-layer-group text-cdi-orange mr-2"></i>
                        <select name="tahun" onchange="document.getElementById('periodForm').submit()"
                            class="bg-transparent border-none py-3 font-black text-[11px] text-cdi outline-none uppercase cursor-pointer">
                            @php $yNow = date('Y'); @endphp
                            @for($y = $yNow; $y >= $yNow - 2; $y--)
                            <option value="{{ $y }}" {{ request('tahun', $yNow) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </form>

                {{-- TOMBOL ABSEN MANUAL --}}
                <button @click="showAbsenManualModal = true" class="bg-blue-50 text-blue-600 px-6 py-4 rounded-2xl font-black uppercase italic text-[10px] border border-blue-100 hover:bg-blue-600 hover:text-white transition-all">
                    <i class="fas fa-plus-circle mr-2"></i> Absen Manual
                </button>

                {{-- TOMBOL ATUR HARI LIBUR --}}
                <button @click="showLiburModal = true" class="bg-red-50 text-red-600 px-6 py-4 rounded-2xl font-black uppercase italic text-[10px] border border-red-100 hover:bg-red-600 hover:text-white transition-all">
                    <i class="fas fa-umbrella-beach mr-2"></i> Atur Hari Libur
                </button>
            </div>

            <button onclick="window.print()" class="text-cdi font-black text-[10px] uppercase italic hover:text-cdi-orange transition-colors">
                <i class="fas fa-file-pdf mr-2"></i> Cetak Semua (A4)
            </button>
        </div>

        {{-- LIVE FILTER SECTION (Client Side) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-slate-50 pt-6">
            <div class="relative group">
                <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-cdi-orange transition-colors"></i>
                <input type="text" x-model="searchQuery" @input="applyFilters()" placeholder="CARI NAMA ATAU NIP..."
                    class="w-full bg-slate-50 border border-slate-100 rounded-2xl pl-12 pr-4 py-4 text-[11px] font-bold uppercase tracking-widest focus:ring-2 focus:ring-cdi-orange/20 outline-none transition-all">
            </div>

            <div class="relative">
                <select x-model="filterDivisi" @change="updateDivisi()" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-[11px] font-bold uppercase tracking-widest outline-none appearance-none cursor-pointer">
                    <option value="">SEMUA DIVISI</option>
                    @foreach($divisis as $div)
                    <option value="{{ $div->nama }}">{{ $div->nama }}</option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none"></i>
            </div>

            <div class="relative">
                <select x-model="filterJabatan" @change="applyFilters()" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-[11px] font-bold uppercase tracking-widest outline-none appearance-none cursor-pointer">
                    <option value="">SEMUA JABATAN</option>
                    <template x-for="j in filteredJabatanOptions" :key="j">
                        <option :value="j" x-text="j.toUpperCase()"></option>
                    </template>
                </select>
                <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none"></i>
            </div>
        </div>
    </div>

    {{-- REKAPITULASI UTAMA --}}
    <div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="reportTable">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400">Karyawan</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Rekap Bulanan</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Skor Kehadiran</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($laporan as $row)
                    @php
                    $now = \Carbon\Carbon::now();
                    $targetBulan = (int) request('bulan', date('m'));
                    $targetTahun = (int) request('tahun', date('Y'));

                    $dt = \Carbon\Carbon::create($targetTahun, $targetBulan, 1);
                    $totalHari = $dt->daysInMonth;

                    $hadirCount = 0;
                    $izinCount = 0;
                    $alpaCount = 0;
                    $hariKerjaBerlalu = 0;

                    $liburBulanIni = \App\Models\HariLibur::whereYear('tanggal', $targetTahun)
                    ->whereMonth('tanggal', $targetBulan)
                    ->pluck('tanggal')
                    ->map(fn($t) => \Carbon\Carbon::parse($t)->format('Y-m-d'))
                    ->toArray();

                    for($d=1; $d<=$totalHari; $d++) {
                        $checkDate=\Carbon\Carbon::create($targetTahun, $targetBulan, $d)->startOfDay();
                        $dateString = $checkDate->format('Y-m-d');

                        if($checkDate->isSunday() || in_array($dateString, $liburBulanIni)) continue;

                        $absensi = $row->absensis->first(fn($a) => \Carbon\Carbon::parse($a->jam_masuk)->isSameDay($checkDate));
                        $izin = $row->perizinans->where('status', 'disetujui')->first(fn($i) => $checkDate->between(\Carbon\Carbon::parse($i->tanggal_mulai), \Carbon\Carbon::parse($i->tanggal_selesai)));

                        if($absensi) {
                        $hadirCount++;
                        $hariKerjaBerlalu++;
                        } elseif($izin) {
                        $izinCount++;
                        $hariKerjaBerlalu++;
                        } else {
                        $jamBatas = $checkDate->copy()->hour(17);
                        if($checkDate->isPast() && !$checkDate->isToday()) {
                        $alpaCount++;
                        $hariKerjaBerlalu++;
                        } elseif($checkDate->isToday() && $now->greaterThan($jamBatas)) {
                        $alpaCount++;
                        $hariKerjaBerlalu++;
                        }
                        }
                        }

                        $skor = ($hariKerjaBerlalu > 0) ? round(($hadirCount / $hariKerjaBerlalu) * 100) : 100;
                        $color = $skor >= 80 ? 'green' : ($skor >= 50 ? 'orange' : 'red');
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-all group person-row"
                            data-nama="{{ strtoupper($row->nama) }}"
                            data-nip="{{ $row->nip }}"
                            data-divisi="{{ $row->divisi->nama ?? '' }}"
                            data-jabatan="{{ $row->jabatan ?? '' }}"
                            data-detail-id="detail-{{ $row->id }}">
                            <td class="px-8 py-6">
                                <p class="font-black text-cdi uppercase italic text-sm leading-none">{{ $row->nama }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">
                                    {{ $row->nip }} • {{ $row->divisi->nama ?? 'Tanpa Divisi' }}
                                </p>
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
                                                <th class="pb-3">Data Masuk & Lokasi</th>
                                                <th class="pb-3">Data Pulang & Lokasi</th>
                                                <th class="pb-3 text-right">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            @for($dia = 1; $dia <= $totalHari; $dia++)
                                                @php
                                                $currentDate=\Carbon\Carbon::create($targetTahun, $targetBulan, $dia);
                                                $dateStr=$currentDate->format('Y-m-d');
                                                $isSunday = $currentDate->isSunday();
                                                $isLibur = in_array($dateStr, $liburBulanIni);

                                                $absensiHariIni = $row->absensis->first(fn($a) => \Carbon\Carbon::parse($a->jam_masuk)->isSameDay($currentDate));
                                                $izinHariIni = $row->perizinans->where('status', 'disetujui')->first(fn($i) => $currentDate->between(\Carbon\Carbon::parse($i->tanggal_mulai), \Carbon\Carbon::parse($i->tanggal_selesai)));
                                                @endphp
                                                <tr class="{{ ($isSunday || $isLibur) ? 'bg-red-50/30' : '' }}">
                                                    <td class="py-4 font-bold {{ ($isSunday || $isLibur) ? 'text-red-500' : 'text-cdi' }}">
                                                        {{ $currentDate->translatedFormat('d M Y') }}
                                                        <span class="block text-[7px] font-medium text-slate-400 uppercase leading-none">{{ $currentDate->translatedFormat('l') }}</span>
                                                    </td>
                                                    <td class="py-4">
                                                        @if($absensiHariIni)
                                                        <div class="font-black text-slate-600 italic">{{ \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') }}</div>
                                                        @if($absensiHariIni->latitude && $absensiHariIni->longitude)
                                                        <div class="text-[7px] text-slate-400 font-bold uppercase mt-1 address-loader"
                                                            data-lat="{{ $absensiHariIni->latitude }}"
                                                            data-lng="{{ $absensiHariIni->longitude }}">
                                                            <i class="fas fa-spinner fa-spin mr-1"></i> Mencari Lokasi...
                                                        </div>
                                                        @endif
                                                        @else
                                                        <span class="text-slate-300">--:--</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-4">
                                                        @if($absensiHariIni && $absensiHariIni->jam_keluar)
                                                        <div class="font-black text-slate-600 italic">{{ \Carbon\Carbon::parse($absensiHariIni->jam_keluar)->format('H:i') }}</div>
                                                        @if($absensiHariIni->lat_pulang && $absensiHariIni->lng_pulang)
                                                        <div class="text-[7px] text-slate-400 font-bold uppercase mt-1 address-loader"
                                                            data-lat="{{ $absensiHariIni->lat_pulang }}"
                                                            data-lng="{{ $absensiHariIni->lng_pulang }}">
                                                            <i class="fas fa-spinner fa-spin mr-1"></i> Mencari Lokasi...
                                                        </div>
                                                        @elseif($absensiHariIni->latitude && $absensiHariIni->longitude)
                                                        <div class="text-[7px] text-slate-400 font-bold uppercase mt-1 address-loader"
                                                            data-lat="{{ $absensiHariIni->latitude }}"
                                                            data-lng="{{ $absensiHariIni->longitude }}">
                                                            <i class="fas fa-spinner fa-spin mr-1"></i> Mencari Lokasi...
                                                        </div>
                                                        @endif
                                                        @else
                                                        <span class="text-slate-300">--:--</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-4 text-right">
                                                        @if($absensiHariIni)
                                                        <span class="px-2 py-0.5 rounded bg-green-100 text-green-600 font-black uppercase italic text-[8px]">Hadir</span>
                                                        @elseif($izinHariIni)
                                                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-600 font-black uppercase italic text-[8px]">Izin</span>
                                                        @elseif($isSunday || $isLibur)
                                                        <span class="px-2 py-0.5 rounded bg-red-100 text-red-600 font-black uppercase italic text-[8px]">Libur</span>
                                                        @elseif($currentDate->isFuture() || ($currentDate->isToday() && $now->hour < 17))
                                                            <span class="px-2 py-0.5 rounded bg-slate-50 text-slate-300 font-black uppercase italic text-[8px]">Mendatang</span>
                                                            @else
                                                            <span class="px-2 py-0.5 rounded bg-red-100 text-red-600 font-black uppercase italic text-[8px]">Absen</span>
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

                        <tr id="noResultsRow" class="hidden">
                            <td colspan="4" class="px-8 py-20 text-center">
                                <i class="fas fa-user-slash text-4xl text-slate-200 mb-4 block"></i>
                                <p class="text-slate-400 font-black uppercase italic text-[11px] tracking-widest">Tidak ada personel yang cocok dengan pencarian Anda</p>
                            </td>
                        </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ATUR HARI LIBUR --}}
    <div x-show="showLiburModal"
        class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-cloak
        x-transition.opacity>
        <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl p-8" @click.away="showLiburModal = false">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-black text-cdi uppercase italic text-lg tracking-tighter">Atur Hari Libur</h3>
                <button @click="showLiburModal = false" class="text-slate-400 hover:text-red-500"><i class="fas fa-times"></i></button>
            </div>

            <form action="{{ route('libur.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Pilih Tanggal</label>
                    <input type="date" name="tanggal" required class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-cdi outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Keterangan</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Tanggal Merah / Cuti Bersama" required class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-cdi outline-none">
                </div>
                <button type="submit" class="w-full bg-cdi text-white py-4 rounded-xl font-black uppercase italic text-[11px] tracking-widest hover:bg-cdi-orange transition-all shadow-lg">
                    SIMPAN HARI LIBUR
                </button>
            </form>

            <div class="mt-8 border-t border-slate-50 pt-6">
                <p class="text-[10px] font-black text-slate-400 uppercase mb-4">Daftar Libur Bulan Ini</p>
                <div class="space-y-2 max-h-40 overflow-y-auto pr-2">
                    @forelse(\App\Models\HariLibur::whereMonth('tanggal', request('bulan', date('m')))->get() as $hl)
                    <div class="flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <div>
                            <p class="text-[10px] font-black text-cdi uppercase leading-none">{{ \Carbon\Carbon::parse($hl->tanggal)->format('d M Y') }}</p>
                            <p class="text-[9px] text-slate-400 font-bold mt-1">{{ $hl->keterangan }}</p>
                        </div>
                        <form action="{{ route('libur.destroy', $hl->id) }}" method="POST" onsubmit="return confirm('Hapus hari libur ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-xs"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                    @empty
                    <p class="text-[9px] text-slate-300 italic">Belum ada hari libur khusus bulan ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    {{-- REVISI MODAL ABSEN MANUAL (LENGKAP DENGAN KETERANGAN IZIN & ABSEN) --}}
    <div x-show="showAbsenManualModal"
        class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-cloak
        x-transition.opacity
        x-data="{ 
        manualSearch: '',
        manualDivisi: '',
        manualJabatan: '',
        selectedKaryawans: [],
        statusUtama: 'hadir', 
        jumlahHari: '1', 
        startDate: '',
        endDate: '',
        alasanIzin: '',
        isSubmitting: false,
        
        // Data karyawan diambil dari variable $laporan/karyawan yang dikirim controller
        karyawanList: [
            @foreach($laporan as $k)
            { 
                id: '{{ $k->id }}', 
                nama: '{{ strtoupper($k->nama) }}', 
                nip: '{{ $k->nip }}', 
                divisi: '{{ $k->divisi->nama ?? '' }}', 
                jabatan: '{{ $k->jabatan ?? '' }}' 
            },
            @endforeach
        ],

        get filteredKaryawans() {
            return this.karyawanList.filter(k => {
                const matchSearch = k.nama.includes(this.manualSearch.toUpperCase()) || k.nip.includes(this.manualSearch);
                const matchDivisi = this.manualDivisi === '' || k.divisi === this.manualDivisi;
                const matchJabatan = this.manualJabatan === '' || k.jabatan === this.manualJabatan;
                return matchSearch && matchDivisi && matchJabatan;
            });
        },

        toggleSelectAll() {
            let visibleIds = this.filteredKaryawans.map(k => k.id);
            if (this.selectedKaryawans.length === visibleIds.length && visibleIds.length > 0) {
                this.selectedKaryawans = [];
            } else {
                this.selectedKaryawans = visibleIds;
            }
        }
     }">

        <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl p-8 max-h-[90vh] overflow-y-auto" @click.away="showAbsenManualModal = false">
            {{-- HEADER --}}
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-black text-cdi uppercase italic text-lg tracking-tighter leading-none">Input Absensi Manual</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Kelola Kehadiran, Perizinan & Alpa Massal</p>
                </div>
                <button @click="showAbsenManualModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('absensi.storeBulkManual') }}" method="POST" class="space-y-6" @submit="isSubmitting = true">
                @csrf

                {{-- STEP 1: PILIH KARYAWAN --}}
                <div class="space-y-3">
                    <label class="block text-[10px] font-black text-slate-400 uppercase italic">1. Pilih Personel</label>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <input type="text" x-model="manualSearch" placeholder="CARI NAMA/NIP..."
                            class="bg-slate-50 border border-slate-100 rounded-xl px-4 py-2 text-[10px] font-bold outline-none focus:ring-2 focus:ring-cdi">

                        <select x-model="manualDivisi" class="bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-[10px] font-bold outline-none">
                            <option value="">SEMUA DIVISI</option>
                            @foreach($divisis as $div)
                            <option value="{{ $div->nama }}">{{ $div->nama }}</option>
                            @endforeach
                        </select>

                        <select x-model="manualJabatan" class="bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-[10px] font-bold outline-none">
                            <option value="">SEMUA JABATAN</option>
                            @foreach($allUniqueJabatans as $j)
                            <option value="{{ $j }}">{{ strtoupper($j) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-between items-center px-1">
                        <span class="text-[9px] font-black text-cdi-orange uppercase italic" x-text="selectedKaryawans.length + ' Orang dipilih'"></span>
                        <button type="button" @click="toggleSelectAll()" class="text-[9px] font-black text-blue-600 uppercase hover:underline">
                            <span x-text="selectedKaryawans.length === filteredKaryawans.length && filteredKaryawans.length > 0 ? 'BATAL SEMUA' : 'PILIH SEMUA HASIL FILTER'"></span>
                        </button>
                    </div>

                    <div class="border border-slate-100 rounded-2xl h-32 overflow-y-auto bg-slate-50/50 p-2 space-y-1">
                        <template x-for="k in filteredKaryawans" :key="k.id">
                            <label class="flex items-center p-3 rounded-xl hover:bg-white hover:shadow-sm cursor-pointer transition-all border border-transparent hover:border-slate-100">
                                <input type="checkbox" name="karyawan_ids[]" :value="k.id" x-model="selectedKaryawans"
                                    class="rounded border-slate-300 text-cdi-orange focus:ring-cdi-orange mr-4">
                                <div>
                                    <p class="text-[11px] font-black text-cdi leading-none" x-text="k.nama"></p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase mt-1" x-text="k.nip + ' • ' + k.divisi"></p>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- STEP 2: STATUS --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase italic">2. Status Kehadiran</label>
                        <select name="status_utama" x-model="statusUtama" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-[11px] font-bold outline-none focus:ring-2 focus:ring-cdi">
                            <option value="hadir">HADIR (TEPAT WAKTU)</option>
                            <option value="izin">IZIN / SAKIT / CUTI</option>
                            <option value="absen">ABSEN (ALPA/BOLOS)</option>
                        </select>
                    </div>
                    <div class="space-y-2" x-show="statusUtama === 'izin'">
                        <label class="block text-[10px] font-black text-slate-400 uppercase italic">Jenis Perizinan</label>
                        <select name="keterangan_izin" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-[11px] font-bold outline-none focus:ring-2 focus:ring-cdi-orange">
                            <option value="Izin">IZIN UMUM</option>
                            <option value="Sakit">SAKIT</option>
                            <option value="Cuti">CUTI TAHUNAN</option>
                            <option value="Tugas Luar">TUGAS LUAR</option>
                        </select>
                    </div>
                </div>

                {{-- ALASAN --}}
                <div class="space-y-2" x-show="statusUtama !== 'hadir'" x-transition>
                    <label class="block text-[10px] font-black text-slate-400 uppercase italic">Alasan / Catatan</label>
                    <textarea name="alasan" x-model="alasanIzin" rows="2"
                        :placeholder="statusUtama === 'absen' ? 'Catatan alpa (opsional)...' : 'Wajib isi alasan izin...'"
                        class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-[11px] font-bold outline-none focus:ring-2 focus:ring-cdi-orange"
                        :required="statusUtama === 'izin'"></textarea>
                </div>

                {{-- STEP 3: DURASI & TANGGAL --}}
                <div class="space-y-4 border-t border-slate-50 pt-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-black text-slate-400 uppercase italic">3. Rentang Waktu</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="jumlahHari" value="1" name="tipe_durasi" class="text-cdi focus:ring-cdi">
                                <span class="text-[10px] font-bold text-slate-600">1 Hari</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="jumlahHari" value="lebih" name="tipe_durasi" class="text-cdi focus:ring-cdi">
                                <span class="text-[10px] font-bold text-slate-600">Rentang Tanggal</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 mb-1" x-text="jumlahHari === '1' ? 'TANGGAL' : 'DARI TANGGAL'"></label>
                            <input type="date" name="tanggal_mulai" x-model="startDate" required
                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-[11px] font-bold outline-none focus:ring-2 focus:ring-cdi">
                        </div>
                        <div x-show="jumlahHari === 'lebih'">
                            <label class="block text-[9px] font-bold text-slate-500 mb-1">SAMPAI TANGGAL</label>
                            <input type="date" name="tanggal_selesai" x-model="endDate" :required="jumlahHari === 'lebih'"
                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-[11px] font-bold outline-none focus:ring-2 focus:ring-cdi">
                        </div>
                    </div>

                    {{-- JAM (KHUSUS HADIR) --}}
                    <div class="grid grid-cols-2 gap-4" x-show="statusUtama === 'hadir'" x-transition>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 mb-1">JAM MASUK</label>
                            <input type="time" name="jam_masuk" value="08:00"
                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-[11px] font-bold outline-none focus:ring-2 focus:ring-cdi">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 mb-1">JAM PULANG</label>
                            <input type="time" name="jam_keluar" value="16:00"
                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-[11px] font-bold outline-none focus:ring-2 focus:ring-cdi">
                        </div>
                    </div>
                </div>

                {{-- SUBMIT --}}
                <button type="submit"
                    :disabled="selectedKaryawans.length === 0 || !startDate || (statusUtama === 'izin' && !alasanIzin) || isSubmitting"
                    class="w-full bg-cdi text-white py-5 rounded-2xl font-black uppercase italic text-[12px] tracking-widest hover:bg-cdi-orange transition-all shadow-lg shadow-cdi/20 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!isSubmitting">PROSES ABSENSI MANUAL</span>
                    <span x-show="isSubmitting" class="flex items-center justify-center gap-2">
                        <i class="fas fa-circle-notch fa-spin"></i> SEDANG MEMPROSES...
                    </span>
                </button>
            </form>
        </div>
    </div>

    {{-- SCRIPT NOTIFIKASI (SWEETALERT2) --}}
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'BERHASIL!',
            text: "{{ session('success') }}",
            confirmButtonColor: '#003366',
            customClass: {
                title: 'font-black uppercase italic',
                popup: 'rounded-[2rem]'
            }
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'GAGAL!',
            text: "{{ session('error') }}",
            confirmButtonColor: '#dc2626',
            customClass: {
                title: 'font-black uppercase italic',
                popup: 'rounded-[2rem]'
            }
        });
    </script>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Address Loading
        loadAllAddresses();
    });

    async function loadAllAddresses() {
        const loaders = document.querySelectorAll('.address-loader');
        for (const el of loaders) {
            const lat = el.dataset.lat;
            const lng = el.dataset.lng;
            if (!lat || !lng) continue;

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                    headers: {
                        'Accept-Language': 'id'
                    }
                });
                const data = await response.json();
                if (data.display_name) {
                    const addr = data.address;
                    const cleanLabel = [
                        addr.road || addr.suburb || addr.pedestrian || '',
                        addr.village || addr.neighbourhood || addr.hamlet || '',
                        addr.city_district || addr.municipality || '',
                        addr.city || addr.regency || '',
                        addr.country || ''
                    ].filter(Boolean).join(', ');
                    el.innerHTML = `<i class="fas fa-location-dot text-blue-500 mr-1"></i> ${cleanLabel.toUpperCase()}`;
                } else {
                    el.innerHTML = `<i class="fas fa-map-marker-alt text-slate-300 mr-1"></i> LOKASI TIDAK TERDETEKSI`;
                }
            } catch (error) {
                el.innerHTML = `<i class="fas fa-exclamation-triangle text-orange-400 mr-1"></i> GAGAL MEMUAT LOKASI`;
            }
            await new Promise(r => setTimeout(r, 1200));
        }
    }

    function toggleDetail(id) {
        const row = document.getElementById(id);
        if (row) row.classList.toggle('hidden');
    }

    function printKaryawan(id, name) {
        const content = document.getElementById(id).innerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Cetak Laporan - ' + name + '</title>');
        printWindow.document.write('<style>body{font-family:sans-serif;padding:20px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #eee;padding:10px;text-align:left;} .print\\:hidden{display:none;} .address-loader i { color: #3b82f6; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h2 style="text-align:center">LAPORAN KEHADIRAN: ' + name + '</h2>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
        }, 800);
    }
</script>

<style>
    [x-cloak] {
        display: none !important;
    }

    .text-green-600 {
        color: #16a34a;
    }

    .bg-green-500 {
        background-color: #22c55e;
    }

    .text-orange-600 {
        color: #ea580c;
    }

    .bg-orange-500 {
        background-color: #f97316;
    }

    .text-red-600 {
        color: #dc2626;
    }

    .bg-red-500 {
        background-color: #ef4444;
    }

    @media print {
        .detail-row {
            display: table-row !important;
        }

        header,
        aside,
        .print\:hidden,
        nav,
        .fixed {
            display: none !important;
        }

        .bg-white {
            border: none !important;
            box-shadow: none !important;
        }

        body {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .rounded-\[3rem\],
        .rounded-\[2rem\] {
            border-radius: 0.5rem !important;
            border: 1px solid #eee !important;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .space-y-8 {
            margin: 0 !important;
        }
    }
</style>
@endsection