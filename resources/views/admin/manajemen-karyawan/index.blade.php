@extends('layouts.app')

@section('title', 'Manajemen Staf')
@section('page_title', 'Database Personel')

@section('content')
<div class="space-y-8 pb-10">
    {{-- HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h3 class="text-4xl font-black italic uppercase tracking-tighter text-cdi leading-none">
                Database <span class="text-cdi-orange">Personel.</span>
            </h3>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-3 flex items-center">
                <span class="w-10 h-[2px] bg-cdi-orange mr-3"></span>
                Manajemen data pusat staf tetap dan peserta magang
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('manajemen-karyawan.permohonan') }}" class="inline-flex items-center justify-center bg-white border-2 border-slate-100 text-cdi px-6 py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:border-cdi-orange transition-all shadow-sm group">
                <i class="fas fa-user-clock mr-2 text-cdi-orange group-hover:animate-pulse"></i> Permohonan Akun
            </a>
            <a href="{{ route('manajemen-karyawan.create') }}" class="inline-flex items-center justify-center bg-cdi text-white px-8 py-4 rounded-2xl font-black uppercase italic text-[10px] tracking-widest hover:bg-cdi-orange transition-all shadow-xl shadow-blue-900/10 group">
                <i class="fas fa-plus-circle mr-2 group-hover:rotate-90 transition-transform"></i> Tambah Anggota
            </a>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-[0.03] text-7xl group-hover:scale-110 transition-transform text-cdi"><i class="fas fa-users"></i></div>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.15em]">Total Personel</p>
            <p class="text-3xl font-black text-cdi mt-1 italic">{{ $karyawans->count() }} <span class="text-xs not-italic font-bold text-slate-300">Jiwa</span></p>
        </div>
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm group">
            <p class="text-[9px] font-black text-blue-400 uppercase tracking-[0.15em]">Staf Tetap</p>
            <p class="text-3xl font-black text-cdi mt-1 italic">{{ $karyawans->where('status', 'tetap')->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm group">
            <p class="text-[9px] font-black text-cdi-orange uppercase tracking-[0.15em]">Peserta Magang</p>
            <p class="text-3xl font-black text-cdi mt-1 italic">{{ $karyawans->whereIn('status', ['magang_kampus', 'magang_mandiri'])->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm group">
            <p class="text-[9px] font-black text-green-400 uppercase tracking-[0.15em]">Departemen</p>
            <p class="text-3xl font-black text-cdi mt-1 italic">{{ \App\Models\Divisi::count() }} <span class="text-[10px] text-slate-300 italic font-normal">Divisi</span></p>
        </div>
    </div>

    {{-- SEARCH & FILTER BAR --}}
    <div class="bg-white p-4 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-wrap gap-4 items-center">
        {{-- Search Input --}}
        <div class="relative flex-1 min-w-[300px]">
            <i class="fas fa-search absolute left-6 top-1/2 -translate-y-1/2 text-slate-300"></i>
            <input type="text" id="searchInput" placeholder="CARI NAMA, NIP, ATAU NIK PERSONEL..." 
                class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-14 pr-6 text-[11px] font-bold text-cdi outline-none focus:ring-2 focus:ring-cdi-orange/20 transition-all uppercase placeholder:text-slate-300">
        </div>

        {{-- Filter Divisi --}}
        <div class="relative min-w-[200px]">
            <select id="filterDivisi" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-[11px] font-bold text-cdi outline-none cursor-pointer uppercase appearance-none focus:ring-2 focus:ring-cdi-orange/20">
                <option value="">Semua Divisi</option>
                @foreach(\App\Models\Divisi::orderBy('nama')->get() as $div)
                    <option value="{{ $div->nama }}">{{ $div->nama }}</option>
                @endforeach
            </select>
            <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-[10px]"></i>
        </div>

        {{-- Filter Status --}}
        <div class="relative min-w-[200px]">
            <select id="filterStatus" class="w-full bg-slate-50 border-none rounded-2xl py-4 px-6 text-[11px] font-bold text-cdi outline-none cursor-pointer uppercase appearance-none focus:ring-2 focus:ring-cdi-orange/20">
                <option value="">Semua Status</option>
                <option value="tetap">Staf Tetap</option>
                <option value="magang">Peserta Magang</option>
            </select>
            <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-[10px]"></i>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-600 text-white px-8 py-4 rounded-2xl flex items-center shadow-lg shadow-green-900/20 animate-bounce-short" role="alert">
        <i class="fas fa-check-circle mr-3"></i>
        <p class="font-black text-[10px] uppercase italic tracking-widest">{{ session('success') }}</p>
    </div>
    @endif

    {{-- MAIN TABLE --}}
    <div class="bg-white rounded-[3rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-left border-collapse" id="employeeTable">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-10 py-7 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Info Personel</th>
                        <th class="px-10 py-7 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Divisi & Peran</th>
                        <th class="px-10 py-7 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Keterangan Kontrak</th>
                        <th class="px-10 py-7 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50" id="tableBody">
                    @forelse($karyawans as $k)
                    <tr class="hover:bg-slate-50/80 transition-all group table-row-item" 
                        data-divisi="{{ $k->divisi->nama ?? 'N/A' }}"
                        data-status-type="{{ Str::contains($k->status, 'magang') ? 'magang' : 'tetap' }}">
                        <td class="px-10 py-6">
                            <div class="flex items-center space-x-5">
                                <div class="relative">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center font-black text-cdi border-4 border-white shadow-md overflow-hidden uppercase italic text-xl">
                                        @if($k->foto)
                                            <img src="{{ asset('storage/karyawan/foto/'.$k->foto) }}" alt="Foto {{ $k->nama }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        @else
                                            {{ substr($k->nama, 0, 1) }}
                                        @endif
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-4 border-white rounded-full shadow-sm"></div>
                                </div>
                                <div>
                                    <p class="font-black text-cdi uppercase italic text-sm tracking-tight group-hover:text-cdi-orange transition-colors search-target">{{ $k->nama }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-1 search-target">NIP: {{ $k->nip }}</p>
                                    <p class="text-[9px] font-bold text-slate-300 uppercase tracking-widest search-target">NIK: {{ $k->nik }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-6">
                            <div class="flex flex-col">
                                <span class="text-[12px] font-black text-cdi uppercase italic">{{ $k->divisi->nama ?? 'NON-DIVISI' }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 flex items-center">
                                    <i class="fas fa-briefcase mr-1.5 text-cdi-orange/50"></i> {{ $k->jabatan ?? 'Staf' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-10 py-6">
                            @if($k->status == 'tetap')
                                <div class="flex flex-col gap-1.5">
                                    <span class="bg-blue-50 text-blue-600 px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest w-fit border border-blue-100 shadow-sm shadow-blue-100">
                                        <i class="fas fa-shield-alt mr-1.5"></i> Karyawan Tetap
                                    </span>
                                    <span class="text-[8px] font-bold text-slate-300 uppercase italic ml-1">Terdaftar: {{ \Carbon\Carbon::parse($k->tanggal_masuk)->translatedFormat('d M Y') }}</span>
                                </div>
                            @else
                                <div class="flex flex-col gap-2">
                                    <span class="bg-orange-50 text-cdi-orange px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest w-fit border border-orange-100 shadow-sm shadow-orange-100">
                                        <i class="fas fa-user-graduate mr-1.5"></i> 
                                        {{ $k->status == 'magang_kampus' ? 'Magang Kampus' : 'Magang Mandiri' }}
                                    </span>
                                    <div class="flex items-center text-[8px] font-bold text-slate-400 uppercase italic ml-1">
                                        <i class="fas fa-university mr-1.5"></i> 
                                        <span class="search-target">{{ $k->instansi ?? 'Umum' }}</span>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="px-10 py-6 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('manajemen-karyawan.show', $k->id) }}" class="w-11 h-11 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center hover:bg-cdi hover:text-white transition-all shadow-sm group/btn" title="Detail Profil">
                                    <i class="fas fa-id-badge text-xs"></i>
                                </a>
                                <a href="{{ route('manajemen-karyawan.edit', $k->id) }}" class="w-11 h-11 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center hover:bg-cdi-orange hover:text-white transition-all shadow-sm" title="Edit Data">
                                    <i class="fas fa-pen-nib text-xs"></i>
                                </a>
                                <form action="{{ route('manajemen-karyawan.destroy', $k->id) }}" method="POST" class="inline" onsubmit="return confirm('PERINGATAN! Menghapus personel akan menghapus seluruh data login & history kerja. Lanjutkan?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-11 h-11 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Hapus Permanen">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyState">
                        <td colspan="4" class="px-8 py-40 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-28 h-28 bg-slate-50 rounded-[3rem] flex items-center justify-center text-slate-100 text-5xl mb-8 border border-slate-100 shadow-inner">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <h4 class="font-black text-cdi italic uppercase tracking-tighter text-2xl leading-none">Database Kosong.</h4>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em] mt-4">Belum ada data personel aktif dalam sistem</p>
                                <a href="{{ route('manajemen-karyawan.create') }}" class="mt-8 text-cdi-orange font-black uppercase text-[9px] tracking-widest border-b-2 border-cdi-orange pb-1 hover:text-cdi hover:border-cdi transition-all">Daftarkan Personel Baru</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    
                    {{-- JavaScript Search Results Placeholders --}}
                    <tr id="noResults" class="hidden">
                        <td colspan="4" class="px-8 py-40 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center text-cdi-orange text-3xl mb-6 animate-pulse">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h4 class="font-black text-cdi italic uppercase tracking-tighter text-xl">Tidak Menemukan Apapun.</h4>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-2">Cek kembali filter atau kata kunci pencarian Anda</p>
                                <button onclick="window.location.reload()" class="mt-6 px-6 py-3 bg-slate-100 rounded-xl text-[9px] font-black uppercase tracking-widest text-cdi hover:bg-slate-200 transition-all">Reset Filter</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const filterDivisi = document.getElementById('filterDivisi');
        const filterStatus = document.getElementById('filterStatus');
        const rows = document.querySelectorAll('.table-row-item');
        const noResults = document.getElementById('noResults');

        function performFilter() {
            const searchText = searchInput.value.toLowerCase().trim();
            const selectedDivisi = filterDivisi.value.toLowerCase();
            const selectedStatusType = filterStatus.value.toLowerCase();
            let visibleCount = 0;

            rows.forEach(row => {
                // Info targets (Nama, NIP, NIK, Instansi)
                const searchableText = Array.from(row.querySelectorAll('.search-target'))
                                            .map(el => el.textContent.toLowerCase())
                                            .join(' ');
                
                const rowDivisi = row.getAttribute('data-divisi').toLowerCase();
                const rowStatusType = row.getAttribute('data-status-type').toLowerCase();
                
                // Matches logic
                const matchesSearch = searchableText.includes(searchText);
                const matchesDivisi = selectedDivisi === "" || rowDivisi === selectedDivisi;
                const matchesStatus = selectedStatusType === "" || rowStatusType === selectedStatusType;

                if (matchesSearch && matchesDivisi && matchesStatus) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            // Empty state for search results
            if (visibleCount === 0 && rows.length > 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        // Fast listeners
        searchInput.addEventListener('input', performFilter);
        filterDivisi.addEventListener('change', performFilter);
        filterStatus.addEventListener('change', performFilter);
    });
</script>

<style>
    /* Styling khusus Scrollbar Table */
    .custom-scroll::-webkit-scrollbar {
        height: 8px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
        border: 2px solid #f8fafc;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }

    @keyframes bounce-short {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    .animate-bounce-short {
        animation: bounce-short 2s ease-in-out infinite;
    }
</style>
@endsection