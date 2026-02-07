@extends('layouts.app')

@section('title', 'Edit Personel: ' . $karyawan->nama)
@section('page_title', 'Update Data Staf')

@section('content')
<div class="max-w-4xl mx-auto pb-12">
    {{-- BACK BUTTON --}}
    <a href="{{ route('manajemen-karyawan.index') }}" class="inline-flex items-center text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] hover:text-cdi mb-8 transition-colors group">
        <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Batal & Kembali
    </a>

    <div class="bg-white rounded-[3rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden" 
         x-data="{ 
            status: '{{ $karyawan->status }}',
            imagePreview: '{{ $karyawan->foto ? asset('storage/'.$karyawan->foto) : null }}',
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
                    <span class="bg-cdi-orange text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">Update Mode</span>
                </div>
                <h3 class="text-3xl font-black italic uppercase tracking-tighter leading-none">Edit Data <span class="text-cdi-orange">Personel</span></h3>
                <p class="text-[10px] font-bold opacity-60 uppercase tracking-[0.2em] mt-3">Mengubah informasi untuk ID: {{ $karyawan->nip }}</p>
            </div>
            <i class="fas fa-user-edit absolute -right-6 -bottom-6 text-9xl opacity-10"></i>
        </div>

        <form action="{{ route('manajemen-karyawan.update', $karyawan->id) }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-10">
            @csrf
            @method('PUT')
            
            {{-- PHOTO UPLOAD SECTION --}}
            <div class="flex flex-col md:flex-row items-center gap-8 p-8 bg-slate-50 rounded-[2.5rem] border-2 border-dashed border-slate-200">
                <div class="relative">
                    <template x-if="imagePreview">
                        <img :src="imagePreview" class="w-32 h-32 rounded-[2rem] object-cover border-4 border-white shadow-lg">
                    </template>
                    <template x-if="!imagePreview">
                        <div class="w-32 h-32 rounded-[2rem] bg-slate-200 flex items-center justify-center text-slate-400 border-4 border-white shadow-inner font-black text-4xl italic">
                            {{ substr($karyawan->nama, 0, 1) }}
                        </div>
                    </template>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h4 class="text-xs font-black text-cdi uppercase tracking-widest italic">Ganti Foto Profil</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase mt-1 mb-4">Kosongkan jika tidak ingin mengubah foto</p>
                    <input type="file" name="foto" @change="fileChosen" class="hidden" id="foto-input" accept="image/*">
                    <label for="foto-input" class="cursor-pointer inline-flex items-center bg-white border border-slate-200 px-6 py-2.5 rounded-xl text-[10px] font-black uppercase italic tracking-widest hover:bg-cdi hover:text-white transition-all">
                        Unggah Foto Baru <i class="fas fa-sync ml-2 text-[8px]"></i>
                    </label>
                </div>
            </div>

            {{-- MAIN FORM FIELDS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                {{-- NAMA --}}
                <div class="group space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">Nama Lengkap</label>
                    <input type="text" name="nama" value="{{ old('nama', $karyawan->nama) }}" required 
                        class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all uppercase">
                </div>

                {{-- NIP --}}
                <div class="group space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">NIP / ID Number</label>
                    <input type="text" name="nip" value="{{ old('nip', $karyawan->nip) }}" required 
                        class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all uppercase">
                </div>

                {{-- DIVISI --}}
                <div class="group space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">Divisi</label>
                    <select name="divisi" required class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all appearance-none">
                        @foreach(['IT Developer', 'Marketing', 'Human Resource', 'Creative Design'] as $div)
                            <option value="{{ $div }}" {{ $karyawan->divisi == $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- STATUS --}}
                <div class="group space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-2">Status Pekerjaan</label>
                    <select name="status" x-model="status" required class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all appearance-none">
                        <option value="tetap">Karyawan Tetap</option>
                        <option value="magang">Peserta Magang</option>
                    </select>
                </div>
            </div>

            {{-- DYNAMIC INSTANSI FIELD --}}
            <div x-show="status === 'magang'" 
                 x-transition 
                 class="p-8 bg-orange-50/50 rounded-[2rem] border border-orange-100 space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-cdi-orange ml-2">Asal Kampus / Instansi</label>
                <input type="text" name="instansi" value="{{ old('instansi', $karyawan->instansi) }}" :required="status === 'magang'" 
                    class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi-orange outline-none transition-all uppercase">
            </div>

            {{-- SUBMIT SECTION --}}
            <div class="pt-8 flex items-center gap-4">
                <button type="submit" class="flex-1 bg-cdi text-white py-6 rounded-2xl font-black uppercase italic tracking-[0.2em] hover:bg-cdi-orange transition-all duration-300 shadow-xl shadow-blue-900/10 group">
                    Simpan Perubahan <i class="fas fa-save ml-2 group-hover:scale-110 transition-transform"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection