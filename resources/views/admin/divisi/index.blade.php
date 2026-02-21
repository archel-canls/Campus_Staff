@extends('layouts.app')

@section('title', 'Manajemen Divisi')
@section('page_title', 'Struktur Organisasi')

@section('content')
<div class="space-y-8 pb-12" x-data="{ 
    showAddModal: false, 
    showEditModal: false,
    editData: { id: '', nama: '', kode: '', deskripsi: '', foto: '' }
}">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h3 class="text-5xl font-black italic uppercase tracking-tighter text-cdi leading-none">
                Struktur <span class="text-cdi-orange">Divisi</span>
            </h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-3 flex items-center">
                <span class="w-12 h-[3px] bg-cdi mr-3"></span>
                Total: {{ $divisis->count() }} Departemen Terdaftar
            </p>
        </div>
        <button @click="showAddModal = true" class="bg-cdi text-white px-8 py-5 rounded-2xl font-black uppercase italic text-[11px] tracking-widest hover:bg-cdi-orange transition-all shadow-2xl shadow-blue-900/20 active:scale-95">
            <i class="fas fa-plus-circle mr-2"></i> Tambah Divisi Baru
        </button>
    </div>

    {{-- Grid Divisi --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($divisis as $d)
        <div class="group relative h-[450px] rounded-[3.5rem] overflow-hidden border-4 border-white shadow-2xl transition-all duration-500 hover:-translate-y-2">
            
            {{-- Background Image (Default jika kosong) --}}
            <div class="absolute inset-0 z-0">
                <img src="{{ $d->foto ? asset('storage/'.$d->foto) : 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80' }}" 
                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                     alt="{{ $d->nama }}">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
            </div>

            {{-- Content Overlay --}}
            <div class="absolute inset-0 z-10 p-10 flex flex-col justify-end">
                <div class="translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                    <span class="bg-cdi-orange text-white text-[9px] font-black px-4 py-1 rounded-full uppercase tracking-widest mb-4 inline-block shadow-lg">
                        {{ $d->kode }}
                    </span>
                    <h4 class="text-3xl font-black text-white uppercase italic leading-none mb-3 drop-shadow-md">
                        {{ $d->nama }}
                    </h4>
                    <p class="text-white/70 text-[11px] font-medium uppercase leading-relaxed italic line-clamp-2 mb-8 group-hover:text-white transition-colors">
                        {{ $d->deskripsi ?? 'Optimalisasi operasional dan manajemen strategis departemen.' }}
                    </p>

                    <div class="flex items-center justify-between border-t border-white/20 pt-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20">
                                <i class="fas fa-users text-white text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[14px] font-black text-white leading-none">{{ $d->karyawans_count ?? 0 }}</p>
                                <p class="text-[7px] font-black text-white/50 uppercase tracking-tighter">Personel</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button @click="
                                editData = { 
                                    id: '{{ $d->id }}', 
                                    nama: '{{ $d->nama }}', 
                                    kode: '{{ $d->kode }}', 
                                    deskripsi: '{{ $d->deskripsi }}'
                                }; 
                                showEditModal = true" 
                                class="w-12 h-12 bg-white/10 backdrop-blur-md text-white rounded-2xl hover:bg-white hover:text-cdi transition-all flex items-center justify-center border border-white/20">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <a href="{{ route('divisi.show', $d->id) }}" class="w-12 h-12 bg-white text-cdi rounded-2xl flex items-center justify-center hover:bg-cdi-orange hover:text-white transition-all shadow-xl font-black">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delete Button --}}
            <form action="{{ route('divisi.destroy', $d->id) }}" method="POST" class="absolute top-8 right-8 z-20 opacity-0 group-hover:opacity-100 transition-all duration-300">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Hapus Divisi?')" class="w-10 h-10 bg-red-500/20 backdrop-blur-md text-red-500 rounded-full hover:bg-red-500 hover:text-white transition-all border border-red-500/50">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </form>
        </div>
        @endforeach
    </div>

    {{-- MODAL ADD --}}
    <div x-show="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md" x-cloak x-transition>
        <div class="bg-white w-full max-w-2xl rounded-[4rem] shadow-2xl p-14 overflow-hidden relative">
            <div class="absolute top-0 right-0 p-10 opacity-5">
                <i class="fas fa-building text-9xl"></i>
            </div>

            <h3 class="text-4xl font-black text-cdi uppercase italic mb-2">Registrasi <span class="text-cdi-orange">Divisi</span></h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mb-10">Penambahan entitas struktur organisasi baru</p>
            
            <form action="{{ route('divisi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Nama Departemen</label>
                        <input type="text" name="nama" required placeholder="CONTOH: DIGITAL MARKETING" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:border-cdi-orange outline-none transition-all uppercase italic">
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Kode Identitas (3-4 Huruf)</label>
                        <input type="text" name="kode" required placeholder="DGM" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:border-cdi-orange outline-none transition-all uppercase">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Foto / Background Divisi (Opsional)</label>
                    <input type="file" name="foto" class="w-full text-xs font-bold text-slate-400 file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-cdi file:text-white hover:file:bg-cdi-orange file:transition-all">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Deskripsi Operasional</label>
                    <textarea name="deskripsi" rows="3" placeholder="Jelaskan peran divisi ini dalam perusahaan..." class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:border-cdi-orange outline-none transition-all italic"></textarea>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 bg-cdi text-white py-5 rounded-[1.5rem] font-black uppercase italic text-xs tracking-widest hover:bg-cdi-orange transition-all shadow-xl shadow-blue-900/20">
                        Finalisasi Data Divisi
                    </button>
                    <button type="button" @click="showAddModal = false" class="px-10 bg-slate-100 text-slate-400 rounded-[1.5rem] font-black uppercase text-[10px] hover:bg-slate-200 transition-all">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div x-show="showEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md" x-cloak x-transition>
        <div class="bg-white w-full max-w-2xl rounded-[4rem] shadow-2xl p-14">
            <h3 class="text-4xl font-black text-cdi uppercase italic mb-10 text-center">Update <span class="text-cdi-orange">Informasi</span></h3>
            
            <form :action="'{{ url('admin/divisi') }}/' + editData.id" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PATCH')
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Nama Divisi</label>
                        <input type="text" name="nama" x-model="editData.nama" required class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold outline-none focus:border-cdi uppercase italic">
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Kode</label>
                        <input type="text" name="kode" x-model="editData.kode" required class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold outline-none focus:border-cdi uppercase">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Ubah Background (Biarkan kosong jika tidak diubah)</label>
                    <input type="file" name="foto" class="w-full text-xs font-bold text-slate-400 file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-cdi file:text-white">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Deskripsi</label>
                    <textarea name="deskripsi" x-model="editData.deskripsi" rows="3" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold outline-none focus:border-cdi italic"></textarea>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-5 rounded-[1.5rem] font-black uppercase italic text-xs tracking-widest hover:bg-blue-700 transition-all shadow-xl">
                        Update Perubahan
                    </button>
                    <button type="button" @click="showEditModal = false" class="px-10 bg-slate-100 text-slate-400 rounded-[1.5rem] font-black uppercase text-[10px]">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection