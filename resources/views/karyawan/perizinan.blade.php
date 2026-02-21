@extends('layouts.app')
@section('title', 'Perizinan')
@section('page_title', 'Pengajuan Izin / Cuti')

@section('content')
{{-- Library Notifikasi --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-4xl space-y-8 pb-20 relative" x-data="perizinanHandler()">
    
    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'BERHASIL!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#003366',
                background: '#ffffff',
                customClass: {
                    title: 'font-black uppercase italic italic',
                    confirmButton: 'rounded-full px-10 py-3 font-black uppercase italic text-[10px]'
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
                confirmButtonColor: '#003366'
            });
        </script>
    @endif

    {{-- Error List dari Controller --}}
    @if ($errors->any())
        <div class="bg-red-500/10 border-l-4 border-red-500 p-6 rounded-3xl mb-8 backdrop-blur-md">
            <div class="flex items-center mb-3">
                <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                <span class="text-red-500 font-black uppercase text-[10px] tracking-widest">Input Error Detected:</span>
            </div>
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="text-red-600/80 text-[10px] font-bold uppercase tracking-tight ml-8 list-disc">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Container with Glassmorphism --}}
    <div class="glass-panel-custom rounded-[3rem] p-10 border-2 border-white/50 shadow-2xl relative overflow-hidden bg-white/90 backdrop-blur-xl">
        {{-- Dekorasi Latar --}}
        <i class="fas fa-file-signature absolute -right-12 -bottom-12 text-[18rem] text-cdi opacity-[0.03] pointer-events-none rotate-12"></i>

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 relative z-10 gap-4 border-b border-slate-100 pb-8">
            <div>
                <h3 class="text-3xl font-black text-cdi uppercase italic tracking-tighter leading-none">
                    Form <span class="text-cdi-orange">Perizinan</span>
                </h3>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-2 italic">CDI Personal Request System</p>
            </div>
            <div class="bg-cdi px-6 py-3 rounded-2xl shadow-lg shadow-blue-900/20">
                <span class="text-[9px] font-black text-white/40 uppercase tracking-widest block mb-1">Quota Sisa Cuti</span>
                <span class="text-white font-black text-xl italic leading-none">{{ Auth::user()->karyawan->sisa_cuti ?? 0 }} <small class="text-[10px] opacity-50 not-italic">HARI</small></span>
            </div>
        </div>
        
        <form action="{{ route('karyawan.perizinan.store') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="space-y-8 relative z-10"
              @submit="isSubmitting = true">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Jenis Izin --}}
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase text-cdi/40 ml-4 italic tracking-widest">Kategori Perizinan</label>
                    <div class="relative group">
                        <select name="jenis_izin" required 
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-5 font-bold text-cdi outline-none focus:border-cdi-orange/30 focus:ring-4 focus:ring-cdi-orange/5 transition-all appearance-none cursor-pointer">
                            <option value="" disabled selected>Pilih Kategori...</option>
                            <option value="Sakit" {{ old('jenis_izin') == 'Sakit' ? 'selected' : '' }}>Sakit (Dengan Surat Dokter)</option>
                            <option value="Keperluan Mendesak" {{ old('jenis_izin') == 'Keperluan Mendesak' ? 'selected' : '' }}>Keperluan Mendesak</option>
                            <option value="Cuti" {{ old('jenis_izin') == 'Cuti' ? 'selected' : '' }}>Cuti Tahunan</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-cdi/20 pointer-events-none group-hover:text-cdi-orange transition-colors"></i>
                    </div>
                </div>

                {{-- Durasi --}}
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase text-cdi/40 ml-4 text-nowrap italic tracking-widest">Kalkulasi Durasi</label>
                    <div class="bg-cdi rounded-2xl p-4 flex items-center border-2 border-white/10 shadow-inner transition-all" 
                         :class="calculateDays() > 0 ? 'ring-4 ring-cdi-orange/10' : ''">
                         <div class="bg-cdi-orange text-white w-12 h-12 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                             <span class="font-black italic text-xl" x-text="calculateDays()">0</span>
                         </div>
                         <div>
                            <span class="text-[10px] font-black text-white uppercase block leading-none">Hari Kerja</span>
                            <span class="text-[8px] font-bold text-white/40 uppercase tracking-widest italic">Otomatis Terhitung</span>
                         </div>
                    </div>
                </div>

                {{-- Tanggal Mulai --}}
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase text-cdi/40 ml-4 italic tracking-widest">Periode Mulai</label>
                    <div class="relative group">
                        <input type="date" name="tanggal_mulai" x-model="startDate" required 
                               min="{{ date('Y-m-d') }}"
                               class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-5 font-bold text-cdi outline-none focus:border-cdi-orange/30 focus:ring-4 focus:ring-cdi-orange/5 transition-all">
                    </div>
                </div>

                {{-- Tanggal Selesai --}}
                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase text-cdi/40 ml-4 italic tracking-widest">Periode Selesai</label>
                    <div class="relative group">
                        <input type="date" name="tanggal_selesai" x-model="endDate" required 
                               :min="startDate"
                               class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-5 font-bold text-cdi outline-none focus:border-cdi-orange/30 focus:ring-4 focus:ring-cdi-orange/5 transition-all">
                    </div>
                </div>
            </div>

            {{-- Alasan --}}
            <div class="space-y-3">
                <label class="text-[10px] font-black uppercase text-cdi/40 ml-4 italic tracking-widest">Justifikasi & Alasan</label>
                <textarea name="alasan" rows="4" required 
                          class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-6 font-bold text-cdi outline-none focus:border-cdi-orange/30 focus:ring-4 focus:ring-cdi-orange/5 transition-all placeholder:text-slate-300" 
                          placeholder="Berikan alasan yang detail namun efisien agar mempercepat proses approval...">{{ old('alasan') }}</textarea>
            </div>

            {{-- File Upload Logic --}}
            <div class="space-y-4">
                {{-- Warning jika lebih dari 3 hari --}}
                <div x-show="calculateDays() > 3" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="p-5 bg-orange-500 rounded-3xl border-2 border-orange-400 flex items-center space-x-4 shadow-xl shadow-orange-500/20">
                    <div class="bg-white/20 p-3 rounded-xl text-white">
                        <i class="fas fa-file-shield text-xl"></i>
                    </div>
                    <p class="text-[10px] font-black text-white uppercase italic leading-tight tracking-widest">
                        Security Protocol: Lampiran Dokumen PDF Wajib disertakan untuk durasi > 3 hari.
                    </p>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black uppercase text-cdi/40 ml-4 italic tracking-widest">Upload Lampiran Pendukung</label>
                    <div class="relative group">
                        <div class="absolute left-6 top-1/2 -translate-y-1/2 text-cdi/30 group-hover:text-cdi-orange transition-colors">
                            <i class="fas fa-cloud-arrow-up text-lg"></i>
                        </div>
                        <input type="file" name="lampiran_pdf" :required="calculateDays() > 3" accept=".pdf" 
                               class="w-full bg-slate-50 text-slate-400 border-2 border-dashed border-slate-200 rounded-2xl py-5 pl-16 pr-6 font-bold outline-none file:mr-6 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-[9px] file:font-black file:bg-cdi file:text-white hover:file:bg-cdi-orange transition-all cursor-pointer group-hover:border-cdi-orange/50">
                    </div>
                    <div class="flex justify-between items-center px-4">
                        <p class="text-[8px] font-bold text-slate-400 uppercase italic tracking-[0.2em]">*Encrypted PDF max 2MB allowed</p>
                        <p x-show="calculateDays() > 3" class="text-[8px] font-black text-red-500 uppercase italic animate-pulse">Required Attachment!</p>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="pt-10 flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6 border-t border-slate-100">
                <button type="submit" 
                        :disabled="isSubmitting || calculateDays() <= 0"
                        class="w-full md:w-auto relative overflow-hidden group disabled:opacity-50 disabled:cursor-not-allowed bg-cdi text-white px-12 py-5 rounded-2xl font-black uppercase italic text-xs transition-all shadow-2xl shadow-blue-950/20 flex items-center justify-center space-x-4">
                    <span x-show="!isSubmitting" class="flex items-center tracking-widest">
                        <i class="fas fa-paper-plane mr-3 group-hover:translate-x-2 group-hover:-translate-y-1 transition-transform"></i>
                        Transmit Application
                    </span>
                    <span x-show="isSubmitting" class="flex items-center">
                        <svg class="animate-spin h-4 w-4 mr-3 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Encrypting Data...
                    </span>
                </button>
                <a href="{{ route('karyawan.absensi') }}" class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b-2 border-transparent hover:border-cdi-orange hover:text-cdi transition-all py-2">
                    Abort Request
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function perizinanHandler() {
        return {
            startDate: "{{ old('tanggal_mulai') }}",
            endDate: "{{ old('tanggal_selesai') }}",
            isSubmitting: false,
            calculateDays() {
                if (!this.startDate || !this.endDate) return 0;
                
                const start = new Date(this.startDate);
                const end = new Date(this.endDate);
                
                start.setHours(0, 0, 0, 0);
                end.setHours(0, 0, 0, 0);

                if (end < start) return 0;

                const diffTime = end - start;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                return diffDays > 0 ? diffDays : 0;
            }
        }
    }
</script>

<style>
    /* Styling khusus agar sesuai tema CDI */
    .bg-cdi { background-color: #003366; }
    .text-cdi { color: #003366; }
    .text-cdi-orange { color: #FF8C00; }
    
    input[type="date"]::-webkit-calendar-picker-indicator {
        cursor: pointer;
        filter: invert(15%) sepia(95%) border-src(45deg);
        opacity: 0.3;
        transition: 0.3s;
    }
    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }

    .glass-panel-custom {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
    }
</style>
@endsection