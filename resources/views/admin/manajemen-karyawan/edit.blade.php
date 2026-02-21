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
            jalur: '{{ in_array($karyawan->status, ['tetap', 'kontrak']) ? 'karyawan' : 'magang' }}',
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

        <form action="{{ route('manajemen-karyawan.update', $karyawan->id) }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-12">
            @csrf
            @method('PUT')
            
            {{-- SECTION 1: PHOTO & IDENTITY --}}
            <div class="space-y-6">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="bg-cdi text-white w-8 h-8 rounded-xl flex items-center justify-center text-xs font-black">01</span>
                    <h3 class="text-xs font-black text-cdi uppercase tracking-widest">Identitas & Foto</h3>
                </div>

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
                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1 mb-4">Format: JPG, PNG (Max. 2MB). Kosongkan jika tetap.</p>
                        <input type="file" name="foto" @change="fileChosen" class="hidden" id="foto-input" accept="image/*">
                        <label for="foto-input" class="cursor-pointer inline-flex items-center bg-white border border-slate-200 px-6 py-2.5 rounded-xl text-[10px] font-black uppercase italic tracking-widest hover:bg-cdi hover:text-white transition-all shadow-sm">
                            Pilih Berkas Baru <i class="fas fa-sync ml-2 text-[8px]"></i>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama', $karyawan->nama) }}" required class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all uppercase">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">NIK (16 Digit)</label>
                        <input type="text" name="nik" value="{{ old('nik', $karyawan->nik) }}" required maxlength="16" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $karyawan->tempat_lahir) }}" required class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all uppercase">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $karyawan->tanggal_lahir) }}" required class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Jenis Kelamin</label>
                        <select name="jenis_kelamin" required class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all">
                            <option value="L" {{ $karyawan->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="P" {{ $karyawan->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Golongan Darah</label>
                        <select name="golongan_darah" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all">
                            @foreach(['A', 'B', 'AB', 'O', '-'] as $goldar)
                                <option value="{{ $goldar }}" {{ $karyawan->golongan_darah == $goldar ? 'selected' : '' }}>{{ $goldar == '-' ? 'Tidak Tahu' : $goldar }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: ALAMAT & KONTAK --}}
            <div class="space-y-6">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="bg-cdi text-white w-8 h-8 rounded-xl flex items-center justify-center text-xs font-black">02</span>
                    <h3 class="text-xs font-black text-cdi uppercase tracking-widest">Lokasi & Kontak</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Alamat Sesuai KTP</label>
                        <textarea name="alamat_ktp" required rows="2" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all">{{ old('alamat_ktp', $karyawan->alamat_ktp) }}</textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Alamat Domisili</label>
                        <textarea name="alamat_domisili" required rows="2" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all">{{ old('alamat_domisili', $karyawan->alamat_domisili) }}</textarea>
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Nomor HP / WhatsApp</label>
                        <input type="text" name="telepon" value="{{ old('telepon', $karyawan->telepon) }}" required class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all">
                    </div>
                </div>
            </div>

            {{-- SECTION 3: PENEMPATAN & PENDIDIKAN --}}
            <div class="space-y-6">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="bg-cdi text-white w-8 h-8 rounded-xl flex items-center justify-center text-xs font-black">03</span>
                    <h3 class="text-xs font-black text-cdi uppercase tracking-widest">Penempatan & Pendidikan</h3>
                </div>
                <div class="p-8 bg-blue-50/50 rounded-[2.5rem] border border-blue-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Jalur Pendaftaran</label>
                        <select id="jalur_pendaftaran" x-model="jalur" class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm">
                            <option value="karyawan">Jalur Karyawan</option>
                            <option value="magang">Jalur Magang</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Status Spesifik</label>
                        <select name="status" x-model="status" class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm">
                            <template x-if="jalur === 'karyawan'">
                                <optgroup label="Karyawan">
                                    <option value="tetap" {{ $karyawan->status == 'tetap' ? 'selected' : '' }}>Karyawan Tetap</option>
                                    <option value="kontrak" {{ $karyawan->status == 'kontrak' ? 'selected' : '' }}>Karyawan Kontrak</option>
                                </optgroup>
                            </template>
                            <template x-if="jalur === 'magang'">
                                <optgroup label="Magang">
                                    <option value="magang_mbkm" {{ $karyawan->status == 'magang_mbkm' ? 'selected' : '' }}>Magang MBKM (Kampus Merdeka)</option>
                                    <option value="magang_ppl" {{ $karyawan->status == 'magang_ppl' ? 'selected' : '' }}>Magang PPL / PKL</option>
                                    <option value="magang_mandiri" {{ $karyawan->status == 'magang_mandiri' ? 'selected' : '' }}>Magang Mandiri (Paid)</option>
                                </optgroup>
                            </template>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Divisi</label>
                        <select name="divisi_id" required class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm">
                            @foreach(\App\Models\Divisi::all() as $div)
                                <option value="{{ $div->id }}" {{ $karyawan->divisi_id == $div->id ? 'selected' : '' }}>{{ $div->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan', $karyawan->jabatan) }}" required class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm uppercase">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[9px] font-black text-cdi-orange uppercase tracking-widest ml-4">Gaji Pokok / Uang Saku</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 font-black">RP</span>
                            <input type="number" name="gaji_pokok" value="{{ old('gaji_pokok', $karyawan->gaji_pokok) }}" required class="w-full bg-white border-2 border-transparent p-4 pl-12 rounded-2xl text-sm font-bold focus:border-cdi-orange outline-none transition-all shadow-sm">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Jenjang Pendidikan</label>
                        <select name="pendidikan_terakhir" required class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm">
                            @foreach(['SMA', 'D3', 'S1', 'S2'] as $pend)
                                <option value="{{ $pend }}" {{ $karyawan->pendidikan_terakhir == $pend ? 'selected' : '' }}>{{ $pend }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Status Studi</label>
                        <select name="status_pendidikan" required class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm">
                            <option value="Lulus" {{ $karyawan->status_pendidikan == 'Lulus' ? 'selected' : '' }}>Sudah Lulus</option>
                            <option value="Belum Lulus" {{ $karyawan->status_pendidikan == 'Belum Lulus' ? 'selected' : '' }}>Masih Aktif</option>
                        </select>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Nama Instansi Pendidikan / Kampus</label>
                        <input type="text" name="instansi" value="{{ old('instansi', $karyawan->instansi) }}" required class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm uppercase">
                    </div>
                </div>
            </div>

            {{-- SECTION 4: TANGGUNGAN & EMERGENCY --}}
            <div class="space-y-6">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="bg-cdi text-white w-8 h-8 rounded-xl flex items-center justify-center text-xs font-black">04</span>
                    <h3 class="text-xs font-black text-cdi uppercase tracking-widest">Tanggungan & Kontak Darurat</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Jumlah Tanggungan</label>
                        <input type="number" name="jumlah_tanggungan" value="{{ old('jumlah_tanggungan', $karyawan->jumlah_tanggungan ?? 0) }}" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Bukti Tanggungan (KK/PDF)</label>
                        <input type="file" name="bukti_tanggungan" class="w-full text-xs font-bold text-slate-400">
                        @if($karyawan->bukti_tanggungan)
                            <p class="text-[8px] text-cdi font-bold mt-1 uppercase italic"><i class="fas fa-file-alt mr-1"></i> File Sudah Tersedia</p>
                        @endif
                    </div>
                    
                    <div class="md:col-span-2 p-6 bg-red-50/50 rounded-3xl border border-red-100 space-y-4">
                        <p class="text-[9px] font-black text-red-500 uppercase tracking-widest">Kontak Darurat Utama</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input type="text" name="emergency_1_nama" value="{{ old('emergency_1_nama', $karyawan->emergency_1_nama) }}" placeholder="Nama Lengkap" class="bg-white p-3 rounded-xl text-xs font-bold border-2 border-transparent focus:border-red-200 outline-none">
                            <input type="text" name="emergency_1_hubungan" value="{{ old('emergency_1_hubungan', $karyawan->emergency_1_hubungan) }}" placeholder="Hubungan" class="bg-white p-3 rounded-xl text-xs font-bold border-2 border-transparent focus:border-red-200 outline-none">
                            <input type="text" name="emergency_1_telp" value="{{ old('emergency_1_telp', $karyawan->emergency_1_telp) }}" placeholder="No. Telepon" class="bg-white p-3 rounded-xl text-xs font-bold border-2 border-transparent focus:border-red-200 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 5: ACCOUNT SECURITY --}}
            <div class="bg-slate-900 p-8 rounded-[2.5rem] space-y-6 text-white shadow-2xl shadow-slate-900/40">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 bg-cdi-orange text-white rounded-lg flex items-center justify-center text-xs">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h4 class="text-[10px] font-black uppercase tracking-widest italic">Akses Akun Personel</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Username</label>
                        <input type="text" name="username" value="{{ old('username', $karyawan->user->username ?? '') }}" required class="w-full bg-slate-800 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi-orange outline-none transition-all lowercase">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $karyawan->user->email ?? '') }}" required class="w-full bg-slate-800 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi-orange outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Password Baru</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full bg-slate-800 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi-orange outline-none transition-all">
                    </div>
                </div>
                <p class="text-[8px] text-slate-500 italic uppercase">* ID Personel (NIP) tidak dapat diubah secara manual demi integritas database</p>
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