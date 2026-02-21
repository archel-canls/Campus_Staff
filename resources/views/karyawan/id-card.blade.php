@extends('layouts.app')

@section('title', 'Smart Profile & ID Card')

@section('content')
<div class="min-h-screen bg-slate-50/50 pb-12">
    {{-- Header Section --}}
    <div class="bg-white border-b border-slate-200 pt-8 pb-16 px-6 mb-[-4rem] print:hidden">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
            <div>
                <h1 class="text-3xl font-black text-cdi italic uppercase tracking-tighter">Personel Profile</h1>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.3em]">Manajemen Identitas Digital CDI</p>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()" class="bg-cdi text-white px-6 py-3 rounded-xl font-black uppercase italic text-xs hover:bg-opacity-90 transition-all shadow-lg flex items-center">
                    <i class="fas fa-print mr-2"></i> Print ID Card Only
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- KOLOM KIRI: DETAIL PROFIL LENGKAP --}}
            <div class="lg:col-span-7 space-y-6 print:hidden">
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
                    {{-- Avatar & Nama Utama --}}
                    <div class="flex items-center gap-6 mb-10">
                        <div class="relative group">
                            <div class="w-24 h-24 rounded-[2rem] bg-slate-100 overflow-hidden border-4 border-white shadow-lg relative">
                                @if($karyawan->foto)
                                    <img id="preview-foto" src="{{ asset('storage/karyawan/foto/' . $karyawan->foto) }}" class="w-full h-full object-cover">
                                @else
                                    <img id="preview-foto" src="https://ui-avatars.com/api/?name={{ urlencode($karyawan->nama) }}&background=003366&color=fff" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <label for="foto-upload" class="absolute -bottom-2 -right-2 bg-cdi-orange text-white w-8 h-8 rounded-xl flex items-center justify-center cursor-pointer hover:scale-110 transition-transform shadow-lg border-2 border-white">
                                <i class="fas fa-camera text-xs"></i>
                                <input type="file" id="foto-upload" class="hidden" accept="image/*" onchange="previewImage(event)">
                            </label>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-cdi uppercase italic tracking-tight">{{ $karyawan->nama }}</h2>
                            <p class="text-cdi-orange font-bold text-[10px] uppercase tracking-widest">{{ $karyawan->jabatan }}</p>
                            <span class="mt-2 inline-block px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[9px] font-black uppercase tracking-tighter italic border border-slate-200">
                                NIP: {{ $karyawan->nip }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Identitas --}}
                        <div class="md:col-span-2 flex items-center gap-2 mb-2">
                            <span class="h-px flex-1 bg-slate-100"></span>
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Identitas & Kelahiran</span>
                            <span class="h-px flex-1 bg-slate-100"></span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">NIK (KTP)</label>
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 font-bold text-cdi text-sm flex justify-between items-center">
                                <span id="nik-display">{{ substr($karyawan->nik, 0, 4) . '********' . substr($karyawan->nik, -4) }}</span>
                                <button onclick="toggleNik('{{ $karyawan->nik }}')" class="text-slate-400 hover:text-cdi transition-colors"><i id="nik-icon" class="fas fa-eye"></i></button>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Tempat, Tgl Lahir</label>
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 font-bold text-cdi text-sm uppercase">
                                {{ $karyawan->tempat_lahir }}, {{ $karyawan->tanggal_lahir->format('d M Y') }} 
                                <span class="text-cdi-orange ml-1 text-xs italic">({{ $karyawan->age }} Thn)</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Jenis Kelamin / Gol. Darah</label>
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 font-bold text-cdi text-sm uppercase">
                                {{ $karyawan->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan' }} / <span class="text-red-600">{{ $karyawan->golongan_darah }}</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Pendidikan Terakhir</label>
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 font-bold text-cdi text-sm uppercase italic">
                                {{ $karyawan->pendidikan_terakhir }} <span class="text-slate-400 text-[10px]">({{ $karyawan->status_pendidikan }})</span>
                            </div>
                        </div>

                        {{-- Alamat --}}
                        <div class="md:col-span-2 flex items-center gap-2 mt-4">
                            <span class="h-px flex-1 bg-slate-100"></span>
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Domisili</span>
                            <span class="h-px flex-1 bg-slate-100"></span>
                        </div>

                        <div class="md:col-span-2 space-y-4">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 italic text-cdi-orange">Alamat KTP</label>
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 font-bold text-cdi text-sm leading-relaxed italic uppercase">
                                    {{ $karyawan->alamat_ktp }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 italic text-cdi-orange">Alamat Domisili</label>
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 font-bold text-cdi text-sm leading-relaxed italic uppercase">
                                    {{ $karyawan->alamat_domisili }}
                                </div>
                            </div>
                        </div>

                        {{-- Emergency --}}
                        <div class="md:col-span-2 flex items-center gap-2 mt-4">
                            <span class="h-px flex-1 bg-slate-100"></span>
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Emergency Contacts</span>
                            <span class="h-px flex-1 bg-slate-100"></span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Kontak Utama ({{ $karyawan->emergency_1_hubungan }})</label>
                            <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 font-bold text-cdi text-xs">
                                {{ $karyawan->emergency_1_nama }} <br>
                                <span class="text-blue-600">{{ $karyawan->emergency_1_telp }}</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 italic">Kontak Cadangan ({{ $karyawan->emergency_2_hubungan ?? 'N/A' }})</label>
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 font-bold text-cdi text-xs">
                                {{ $karyawan->emergency_2_nama ?? '-' }} <br>
                                <span class="text-slate-400">{{ $karyawan->emergency_2_telp ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Karir --}}
                        <div class="md:col-span-2 flex items-center gap-2 mt-4">
                            <span class="h-px flex-1 bg-slate-100"></span>
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Informasi Karir</span>
                            <span class="h-px flex-1 bg-slate-100"></span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Divisi & Status</label>
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 font-bold text-cdi text-sm uppercase italic">
                                {{ $karyawan->divisi->nama ?? 'N/A' }} / <span class="text-cdi-orange">{{ str_replace('_', ' ', $karyawan->status) }}</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Instansi / Tgl Masuk</label>
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 font-bold text-cdi text-sm uppercase">
                                {{ $karyawan->instansi ?? 'Campus Digital' }} / {{ $karyawan->tanggal_masuk ? $karyawan->tanggal_masuk->format('d M Y') : '-' }}
                            </div>
                        </div>
                    </div>
                    
                    <button id="save-btn" class="hidden w-full mt-8 bg-green-600 text-white py-4 rounded-2xl font-black uppercase italic tracking-widest text-xs shadow-lg hover:bg-green-700 transition-all animate-bounce">
                        <i class="fas fa-save mr-2"></i> Update Foto Profil
                    </button>
                </div>
            </div>

            {{-- KOLOM KANAN: ID CARD DESIGN --}}
            <div class="lg:col-span-5 flex flex-col items-center">
                <div id="printable-id-card" class="group perspective-1000 w-full max-w-[350px] aspect-[1/1.58] mb-8">
                    <div id="flip-card-inner" class="relative w-full h-full transition-transform duration-1000 preserve-3d cursor-pointer shadow-2xl rounded-[2.5rem]">
                        
                        {{-- FRONT SIDE --}}
                        <div class="absolute inset-0 backface-hidden bg-white rounded-[2.5rem] border-[8px] border-white overflow-hidden flex flex-col shadow-inner card-shadow z-20">
                            <div class="absolute top-0 left-0 w-full h-full bg-cdi opacity-10"></div>
                            <div class="absolute -top-20 -right-20 w-64 h-64 bg-cdi-orange/20 rounded-full blur-3xl"></div>
                            
                            {{-- Header Card --}}
                            <div class="absolute top-0 w-full h-44 {{ $karyawan->divisi ? $karyawan->divisi->bg_color_class : 'bg-cdi' }} rounded-b-[3rem] shadow-xl overflow-hidden">
                                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#fff 2px, transparent 2px); background-size: 20px 20px;"></div>
                                <div class="mt-8 w-full text-center relative z-10">
                                    <p class="text-white font-black italic tracking-tighter text-xl uppercase">CAMPUS<span class="text-cdi-orange">STAFF</span></p>
                                    <p class="text-[8px] text-white/60 font-bold tracking-[0.5em] -mt-1 uppercase">Digital Identity Card</p>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="relative mt-24 flex flex-col items-center z-20">
                                {{-- PERBAIKAN: Menghapus class 'rotate-3' agar foto lurus --}}
                                <div class="w-44 h-44 rounded-[2.5rem] bg-white p-2 shadow-2xl">
                                    <div class="w-full h-full rounded-[2rem] bg-slate-200 overflow-hidden border-2 border-slate-100 relative">
                                        <img id="card-foto" src="{{ $karyawan->foto ? asset('storage/karyawan/foto/' . $karyawan->foto) : 'https://ui-avatars.com/api/?name='.urlencode($karyawan->nama).'&background=003366&color=fff' }}" class="w-full h-full object-cover">
                                        <div class="absolute bottom-0 w-full bg-cdi/80 backdrop-blur-sm py-1.5 text-center">
                                            <p class="text-[8px] font-black text-white uppercase tracking-widest">{{ str_replace('_', ' ', $karyawan->status) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 text-center px-6">
                                    <h2 class="text-cdi font-black text-2xl uppercase italic tracking-tighter leading-none mb-1">{{ $karyawan->nama }}</h2>
                                    <p class="text-cdi-orange font-bold text-[11px] uppercase tracking-[0.3em] mb-4">{{ $karyawan->jabatan }}</p>
                                    
                                    <div class="inline-flex items-center gap-2 bg-slate-100 px-4 py-1.5 rounded-full border border-slate-200">
                                        <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                                        <p class="text-[10px] font-black text-cdi italic uppercase tracking-widest">{{ $karyawan->nip }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- QR Code --}}
                            <div class="mt-auto mb-8 w-full px-12 z-20">
                                <div class="bg-white p-3 rounded-[2rem] shadow-xl border-2 border-slate-100 flex flex-col items-center">
                                    <img src="https://bwipjs-api.metafloor.com/?bcid=qrcode&text={{ $karyawan->barcode_token }}&scale=3" class="w-20 h-20">
                                    <p class="text-[7px] font-black text-slate-300 tracking-[0.5em] uppercase mt-2">Verified Identity</p>
                                </div>
                            </div>
                            <div class="absolute bottom-0 left-0 w-full h-12 bg-cdi-orange opacity-10" style="clip-path: ellipse(70% 100% at 50% 100%);"></div>
                        </div>

                        {{-- BACK SIDE --}}
                        <div class="absolute inset-0 backface-hidden rotate-y-180 bg-cdi rounded-[2.5rem] border-[8px] border-white overflow-hidden flex flex-col p-8 text-white shadow-2xl z-10">
                             <div class="absolute top-0 left-0 w-full h-full opacity-10" style="background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');"></div>
                             
                             <div class="flex justify-between items-start relative z-10">
                                 <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-md border border-white/20">
                                     <i class="fas fa-shield-alt text-cdi-orange"></i>
                                 </div>
                                 <div class="text-right">
                                     <p class="text-[8px] font-black text-white/50 uppercase tracking-[0.2em]">Authenticity</p>
                                     <p class="text-xs font-black italic uppercase">Official Pass</p>
                                 </div>
                             </div>

                             <div class="mt-10 space-y-6 relative z-10">
                                 <div>
                                     <h4 class="text-[10px] font-black text-cdi-orange uppercase tracking-[0.3em] mb-3 flex items-center gap-2">
                                         <span class="w-8 h-[2px] bg-cdi-orange"></span> Syarat & Ketentuan
                                     </h4>
                                     <ul class="text-[9px] text-white/70 font-bold space-y-2 leading-relaxed italic">
                                         <li>1. Kartu ini milik CDI & wajib dipakai saat bertugas.</li>
                                         <li>2. Scan QR Code untuk akses & presensi harian.</li>
                                         <li>3. Jika menemukan kartu ini, harap kembalikan ke HRD CDI.</li>
                                     </ul>
                                 </div>
                                 <div class="bg-white/5 backdrop-blur-sm p-4 rounded-2xl border border-white/10 text-center">
                                     <p class="text-[8px] font-black text-cdi-orange uppercase tracking-widest mb-1 italic">Division Details</p>
                                     <p class="text-[10px] font-bold text-white uppercase">{{ $karyawan->divisi->nama ?? 'General' }} - {{ $karyawan->divisi->kode ?? 'CDI' }}</p>
                                 </div>
                             </div>

                             <div class="mt-auto flex items-center justify-between relative z-10 border-t border-white/10 pt-4">
                                 <div>
                                     <p class="text-[7px] font-bold text-white/40 uppercase tracking-[0.4em]">Powered By</p>
                                     <p class="text-sm font-black italic tracking-tighter uppercase">CDI<span class="text-cdi-orange">SYSTEMS</span></p>
                                 </div>
                                 <i class="fas fa-fingerprint text-3xl text-white/20"></i>
                             </div>
                        </div>

                    </div>
                </div>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.3em] animate-pulse print:hidden">
                    <i class="fas fa-sync-alt mr-2"></i> Hover card to view back side
                </p>
            </div>

        </div>
    </div>
</div>

<style>
    /* CSS untuk Tampilan Web */
    .perspective-1000 { perspective: 1000px; }
    .preserve-3d { transform-style: preserve-3d; position: relative; }
    .backface-hidden { 
        backface-visibility: hidden; 
        -webkit-backface-visibility: hidden; 
    }
    .rotate-y-180 { transform: rotateY(180deg); }
    .group:hover #flip-card-inner { transform: rotateY(180deg); }
    #flip-card-inner > div:first-child { z-index: 2; }

    /* CSS KHUSUS PRINT */
    @media print {
        * { 
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important; 
        }
        body * { visibility: hidden; }
        #printable-id-card, #printable-id-card * { visibility: visible; }
        #printable-id-card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: auto;
            box-shadow: none !important;
            transform: none !important;
        }
        #flip-card-inner { 
            transform: none !important; 
            display: block !important;
            box-shadow: none !important;
        }
        #flip-card-inner > div:first-child {
            position: relative !important;
            width: 350px; 
            height: 553px;
            margin: 0 auto;
            page-break-after: always; 
            z-index: 2;
        }
        .rotate-y-180 {
            display: flex !important; 
            position: relative !important;
            transform: none !important; 
            width: 350px;
            height: 553px;
            margin: 0 auto;
            visibility: visible !important;
        }
        @page { 
            size: portrait; 
            margin: 0.5cm; 
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let isNikVisible = false;
    function toggleNik(nikFull) {
        const display = document.getElementById('nik-display');
        const icon = document.getElementById('nik-icon');
        if (isNikVisible) {
            display.innerText = nikFull.substring(0, 4) + '********' + nikFull.substring(nikFull.length - 4);
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        } else {
            display.innerText = nikFull;
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        }
        isNikVisible = !isNikVisible;
    }

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            document.getElementById('preview-foto').src = reader.result;
            document.getElementById('card-foto').src = reader.result;
            document.getElementById('save-btn').classList.remove('hidden');
            window.selectedFile = event.target.files[0];
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    document.getElementById('save-btn').addEventListener('click', function() {
        if (!window.selectedFile) return;
        const formData = new FormData();
        formData.append('foto', window.selectedFile);
        formData.append('_token', '{{ csrf_token() }}');
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

        fetch('{{ route("karyawan.update-foto") }}', { 
            method: 'POST', 
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false });
                btn.classList.add('hidden');
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save mr-2"></i> Update Foto Profil';
        });
    });
</script>
@endsection