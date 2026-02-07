@extends('layouts.app')
@section('title', 'Perizinan')
@section('page_title', 'Pengajuan Izin / Cuti')

@section('content')
{{-- Library Notifikasi --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-4xl space-y-8 pb-20" x-data="perizinanHandler()">
    
    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#1e293b'
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#1e293b'
            });
        </script>
    @endif

    {{-- Error List dari Controller --}}
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-2xl mb-6 shadow-sm">
            <div class="flex items-center mb-2">
                <i class="fas fa-times-circle text-red-500 mr-2"></i>
                <span class="text-red-800 font-black uppercase text-[10px]">Terdapat Kesalahan Input:</span>
            </div>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li class="text-red-700 text-[10px] font-bold uppercase tracking-tight">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm relative overflow-hidden">
        {{-- Dekorasi Latar --}}
        <i class="fas fa-file-signature absolute -right-10 -bottom-10 text-[15rem] text-slate-50 opacity-50 pointer-events-none"></i>

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 relative z-10 gap-4">
            <h3 class="text-2xl font-black text-cdi uppercase italic tracking-tighter">
                Form <span class="text-cdi-orange">Perizinan</span>
            </h3>
            <div class="bg-slate-100 px-4 py-2 rounded-full">
                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Sisa Cuti: <span class="text-cdi font-black">{{ Auth::user()->karyawan->sisa_cuti ?? 0 }} Hari</span></span>
            </div>
        </div>
        
        <form action="{{ route('karyawan.perizinan.store') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="space-y-6 relative z-10"
              @submit="isSubmitting = true">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Jenis Izin --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 italic">Jenis Izin / Cuti</label>
                    <select name="jenis_izin" required 
                            class="w-full bg-slate-50 border-2 border-transparent rounded-2xl p-4 font-bold text-cdi outline-none focus:border-cdi-orange/20 focus:ring-4 focus:ring-cdi-orange/5 transition-all">
                        <option value="" disabled selected>Pilih Jenis...</option>
                        <option value="Sakit" {{ old('jenis_izin') == 'Sakit' ? 'selected' : '' }}>Sakit (Dengan Surat Dokter)</option>
                        <option value="Keperluan Mendesak" {{ old('jenis_izin') == 'Keperluan Mendesak' ? 'selected' : '' }}>Keperluan Mendesak</option>
                        <option value="Cuti" {{ old('jenis_izin') == 'Cuti' ? 'selected' : '' }}>Cuti Tahunan</option>
                    </select>
                </div>

                {{-- Durasi --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 text-nowrap italic">Estimasi Durasi</label>
                    <div class="bg-slate-50 rounded-2xl p-4 flex items-center border-2 border-transparent transition-all" 
                         :class="calculateDays() > 0 ? 'border-cdi-orange/10 bg-orange-50/30' : ''">
                         <span class="font-black text-cdi-orange italic mr-2 text-2xl" x-text="calculateDays()">0</span>
                         <span class="text-[10px] font-black text-slate-400 uppercase">Hari Kalender</span>
                    </div>
                </div>

                {{-- Tanggal Mulai --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 italic">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" x-model="startDate" required 
                           min="{{ date('Y-m-d') }}"
                           class="w-full bg-slate-50 border-2 border-transparent rounded-2xl p-4 font-bold text-cdi outline-none focus:border-cdi-orange/20 focus:ring-4 focus:ring-cdi-orange/5">
                </div>

                {{-- Tanggal Selesai --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 italic">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" x-model="endDate" required 
                           :min="startDate"
                           class="w-full bg-slate-50 border-2 border-transparent rounded-2xl p-4 font-bold text-cdi outline-none focus:border-cdi-orange/20 focus:ring-4 focus:ring-cdi-orange/5">
                </div>
            </div>

            {{-- Alasan --}}
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4 italic">Alasan Singkat & Jelas</label>
                <textarea name="alasan" rows="3" required 
                          class="w-full bg-slate-50 border-2 border-transparent rounded-2xl p-4 font-bold text-cdi outline-none focus:border-cdi-orange/20 focus:ring-4 focus:ring-cdi-orange/5" 
                          placeholder="Jelaskan keperluan Anda secara mendetail namun singkat...">{{ old('alasan') }}</textarea>
            </div>

            {{-- File Upload Logic --}}
            <div class="space-y-4">
                {{-- Warning jika lebih dari 3 hari --}}
                <div x-show="calculateDays() > 3" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="p-4 bg-orange-50 rounded-2xl border border-orange-200 flex items-start space-x-3 shadow-sm">
                    <div class="bg-orange-500 p-2 rounded-lg text-white">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <p class="text-[10px] font-bold text-orange-700 uppercase leading-relaxed pt-1">
                        Wajib melampirkan dokumen PDF karena durasi izin Anda melebihi 3 hari kerja.
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 italic">Lampiran Pendukung (PDF)</label>
                    <div class="relative group">
                        <input type="file" name="lampiran_pdf" :required="calculateDays() > 3" accept=".pdf" 
                               class="w-full bg-slate-50 text-slate-500 border-2 border-dashed border-slate-200 rounded-2xl p-4 font-bold outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-cdi file:text-white hover:file:bg-cdi-orange transition-all cursor-pointer group-hover:border-cdi-orange/30">
                    </div>
                    <div class="flex justify-between items-center px-4">
                        <p class="text-[8px] font-bold text-slate-400 uppercase italic">*Format: PDF, Maks 2MB</p>
                        <p x-show="calculateDays() > 3" class="text-[8px] font-black text-red-500 uppercase italic">Wajib Dilampirkan!</p>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="pt-6 flex items-center space-x-4">
                <button type="submit" 
                        :disabled="isSubmitting || calculateDays() <= 0"
                        class="relative overflow-hidden group disabled:opacity-50 disabled:cursor-not-allowed bg-cdi text-white px-10 py-5 rounded-2xl font-black uppercase italic text-xs transition-all shadow-xl shadow-slate-200 flex items-center space-x-3">
                    <span x-show="!isSubmitting" class="flex items-center">
                        <i class="fas fa-paper-plane mr-2 group-hover:translate-x-1 transition-transform"></i>
                        Kirim Permohonan
                    </span>
                    <span x-show="isSubmitting" class="flex items-center">
                        <svg class="animate-spin h-4 w-4 mr-3 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memverifikasi...
                    </span>
                </button>
                <a href="{{ route('karyawan.absensi') }}" class="text-[10px] font-black text-slate-400 uppercase border-b-2 border-transparent hover:border-slate-300 transition-all">
                    Batal
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
                
                // Reset jam ke 00:00 agar perhitungan murni tanggal
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
    /* Styling khusus input date agar seragam */
    input[type="date"]::-webkit-calendar-picker-indicator {
        cursor: pointer;
        filter: invert(0.2);
        opacity: 0.5;
        transition: 0.3s;
    }
    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }
    .text-cdi-orange { color: #f97316; }
    .bg-cdi { background-color: #1e293b; }
    .text-cdi { color: #1e293b; }
</style>
@endsection