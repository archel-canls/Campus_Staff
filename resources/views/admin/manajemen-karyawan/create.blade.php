@extends('layouts.app')

@section('title', 'Tambah Staf Baru')
@section('page_title', 'Registrasi Personel')

@section('content')
<div class="max-w-4xl mx-auto pb-12">
    {{-- BACK BUTTON --}}
    <a href="{{ route('manajemen-karyawan.index') }}" class="inline-flex items-center text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] hover:text-cdi mb-8 transition-colors group">
        <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Kembali ke Daftar
    </a>

    <div class="bg-white rounded-[3rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden" 
         x-data="{ 
            status: 'tetap',
            imagePreview: null,
            fileChosen(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => {
                    this.imagePreview = reader.result;
                };
            }
         }">
        
        {{-- HEADER FORM --}}
        <div class="p-10 bg-cdi text-white relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-2">
                    <span class="bg-cdi-orange text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">New Entry</span>
                </div>
                <h3 class="text-3xl font-black italic uppercase tracking-tighter leading-none">Pendaftaran <span class="text-cdi-orange">Personel</span></h3>
                <p class="text-[10px] font-bold opacity-60 uppercase tracking-[0.2em] mt-3">Lengkapi detail informasi untuk pembuatan ID Card sistem</p>
            </div>
            <i class="fas fa-user-plus absolute -right-6 -bottom-6 text-9xl opacity-10"></i>
        </div>

        <form action="{{ route('manajemen-karyawan.store') }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-10">
            @csrf
            
            {{-- PHOTO UPLOAD SECTION --}}
            <div class="flex flex-col md:flex-row items-center gap-8 p-8 bg-slate-50 rounded-[2.5rem] border-2 border-dashed border-slate-200">
                <div class="relative">
                    <template x-if="imagePreview">
                        <img :src="imagePreview" class="w-32 h-32 rounded-[2rem] object-cover border-4 border-white shadow-lg">
                    </template>
                    <template x-if="!imagePreview">
                        <div class="w-32 h-32 rounded-[2rem] bg-slate-200 flex items-center justify-center text-slate-400 border-4 border-white shadow-inner">
                            <i class="fas fa-camera text-3xl"></i>
                        </div>
                    </template>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h4 class="text-xs font-black text-cdi uppercase tracking-widest italic">Foto Profil Resmi</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase mt-1 mb-4">Format: JPG, PNG (Max. 2MB)</p>
                    <input type="file" name="foto" @change="fileChosen" class="hidden" id="foto-input" accept="image/*">
                    <label for="foto-input" class="cursor-pointer inline-flex items-center bg-white border border-slate-200 px-6 py-2.5 rounded-xl text-[10px] font-black uppercase italic tracking-widest hover:bg-cdi hover:text-white transition-all">
                        Pilih Berkas <i class="fas fa-upload ml-2 text-[8px]"></i>
                    </label>
                </div>
            </div>

            {{-- MAIN FORM FIELDS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                {{-- NAMA --}}
                <div class="group space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2 group-focus-within:text-cdi-orange transition-colors">Nama Lengkap Tanpa Gelar</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-xs"></i>
                        <input type="text" name="nama" required placeholder="Contoh: Archello Setya" 
                            class="w-full bg-slate-50 border-2 border-transparent p-4 pl-12 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all uppercase placeholder:text-slate-300">
                    </div>
                </div>

                {{-- NIP --}}
                <div class="group space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2 group-focus-within:text-cdi-orange transition-colors">Nomor Induk / ID Pegawai</label>
                    <div class="relative">
                        <i class="fas fa-id-badge absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-xs"></i>
                        <input type="text" name="nip" required placeholder="Contoh: CDI-2026-001" 
                            class="w-full bg-slate-50 border-2 border-transparent p-4 pl-12 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all uppercase">
                    </div>
                </div>

                {{-- DIVISI --}}
                <div class="group space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">Penempatan Divisi</label>
                    <div class="relative">
                        <i class="fas fa-briefcase absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-xs z-10"></i>
                        <select name="divisi" required class="w-full bg-slate-50 border-2 border-transparent p-4 pl-12 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all appearance-none relative z-0">
                            <option value="">Pilih Divisi</option>
                            <option value="IT Developer">IT Developer</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Human Resource">Human Resource</option>
                            <option value="Creative Design">Creative Design</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 text-[10px] pointer-events-none"></i>
                    </div>
                </div>

                {{-- STATUS --}}
                <div class="group space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">Kategori Kontrak</label>
                    <div class="relative">
                        <i class="fas fa-file-contract absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-xs z-10"></i>
                        <select name="status" x-model="status" required class="w-full bg-slate-50 border-2 border-transparent p-4 pl-12 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all appearance-none relative z-0">
                            <option value="tetap">Karyawan Tetap</option>
                            <option value="magang">Peserta Magang</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 text-[10px] pointer-events-none"></i>
                    </div>
                </div>
            </div>

            {{-- DYNAMIC INSTANSI FIELD (FOR INTERN) --}}
            <div x-show="status === 'magang'" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="p-8 bg-orange-50/50 rounded-[2rem] border border-orange-100 space-y-2" x-cloak>
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-cdi-orange ml-2 flex items-center gap-2">
                    <i class="fas fa-university"></i> Asal Kampus / Instansi Pendidikan
                </label>
                <input type="text" name="instansi" :required="status === 'magang'" placeholder="Contoh: Universitas Indonesia" 
                    class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi-orange outline-none transition-all uppercase">
            </div>

            {{-- SUBMIT SECTION --}}
            <div class="pt-8 flex items-center gap-4">
                <button type="submit" class="flex-1 bg-cdi text-white py-6 rounded-2xl font-black uppercase italic tracking-[0.2em] hover:bg-cdi-orange transition-all duration-300 shadow-xl shadow-blue-900/10 group">
                    Daftarkan Personel <i class="fas fa-paper-plane ml-2 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                </button>
                <button type="reset" class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center shadow-sm" title="Reset Form">
                    <i class="fas fa-undo"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection