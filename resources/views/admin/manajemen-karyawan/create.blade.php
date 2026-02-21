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
            jalur: 'karyawan',
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
                    <span class="bg-cdi-orange text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">Administrator Entry</span>
                </div>
                <h3 class="text-3xl font-black italic uppercase tracking-tighter leading-none">Pendaftaran <span class="text-cdi-orange">Personel</span></h3>
                <p class="text-[10px] font-bold opacity-60 uppercase tracking-[0.2em] mt-3">Input data lengkap untuk pembuatan ID Card dan sistem payroll</p>
            </div>
            <i class="fas fa-user-plus absolute -right-6 -bottom-6 text-9xl opacity-10"></i>
        </div>

        <form action="{{ route('manajemen-karyawan.store') }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-12" id="regForm">
            @csrf
            
            {{-- NIP Hidden Field (Auto Generated) --}}
            <input type="hidden" name="nip" id="nip_auto">

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
                            <div class="w-32 h-32 rounded-[2rem] bg-slate-200 flex items-center justify-center text-slate-400 border-4 border-white shadow-inner">
                                <i class="fas fa-camera text-3xl"></i>
                            </div>
                        </template>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h4 class="text-xs font-black text-cdi uppercase tracking-widest italic">Foto Profil (Opsional)</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1 mb-4">Format: JPG, PNG (Max. 2MB)</p>
                        <input type="file" name="foto" @change="fileChosen" class="hidden" id="foto-input" accept="image/*">
                        <label for="foto-input" class="cursor-pointer inline-flex items-center bg-white border border-slate-200 px-6 py-2.5 rounded-xl text-[10px] font-black uppercase italic tracking-widest hover:bg-cdi hover:text-white transition-all shadow-sm">
                            Pilih Berkas <i class="fas fa-upload ml-2 text-[8px]"></i>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="Nama Sesuai KTP" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all uppercase">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">NIK (16 Digit)</label>
                        <input type="text" name="nik" required maxlength="16" placeholder="330xxxxxxxxxxxxx" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" required placeholder="Kota Kelahiran" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all uppercase">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" required class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="jenis_kelamin" required class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all">
                            <option value="L">Laki-Laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Golongan Darah</label>
                        <select name="golongan_darah" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all">
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O">O</option>
                            <option value="-">Tidak Tahu</option>
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
                        <textarea name="alamat_ktp" required rows="2" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Alamat Domisili</label>
                        <textarea name="alamat_domisili" required rows="2" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all"></textarea>
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Nomor HP / WhatsApp</label>
                        <input type="text" name="telepon" required placeholder="08xxxxxxxxxx" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi focus:bg-white outline-none transition-all">
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
                        <select id="jalur_pendaftaran" name="jalur_pendaftaran" x-model="jalur" class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm">
                            <option value="karyawan">Jalur Karyawan</option>
                            <option value="magang">Jalur Magang</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Status Spesifik</label>
                        <select name="status" x-model="status" class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm">
                            <template x-if="jalur === 'karyawan'">
                                <optgroup label="Karyawan">
                                    <option value="tetap">Karyawan Tetap</option>
                                    <option value="kontrak">Karyawan Kontrak</option>
                                </optgroup>
                            </template>
                            <template x-if="jalur === 'magang'">
                                <optgroup label="Magang">
                                    <option value="magang_mbkm">Magang MBKM (Kampus Merdeka)</option>
                                    <option value="magang_ppl">Magang PPL / PKL</option>
                                    <option value="magang_mandiri">Magang Mandiri (Paid)</option>
                                </optgroup>
                            </template>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Divisi</label>
                        <select name="divisi_id" required class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach(\App\Models\Divisi::all() as $div)
                                <option value="{{ $div->id }}">{{ $div->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Jabatan</label>
                        <input type="text" name="jabatan" required placeholder="Contoh: UI/UX Designer" class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm uppercase">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Jenjang Pendidikan</label>
                        <select name="pendidikan_terakhir" required class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm">
                            <option value="SMA">SMA/SMK</option>
                            <option value="D3">Diploma (D3)</option>
                            <option value="S1">Sarjana (S1)</option>
                            <option value="S2">Magister (S2)</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Status Studi</label>
                        <select name="status_pendidikan" required class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm">
                            <option value="Lulus">Sudah Lulus</option>
                            <option value="Belum Lulus">Masih Aktif</option>
                        </select>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[9px] font-black text-cdi uppercase tracking-widest ml-4">Nama Instansi Pendidikan / Kampus</label>
                        <input type="text" name="instansi" required placeholder="Contoh: Universitas Diponegoro" class="w-full bg-white border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all shadow-sm uppercase">
                    </div>
                    
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[9px] font-black text-cdi-orange uppercase tracking-widest ml-4">Gaji Pokok / Uang Saku</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 font-black">RP</span>
                            <input type="number" name="gaji_pokok" required placeholder="0" class="w-full bg-white border-2 border-transparent p-4 pl-12 rounded-2xl text-sm font-bold focus:border-cdi-orange outline-none transition-all shadow-sm">
                        </div>
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
                        <input type="number" name="jumlah_tanggungan" value="0" class="w-full bg-slate-50 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Bukti Tanggungan (KK/PDF)</label>
                        <input type="file" name="bukti_tanggungan" class="w-full text-xs font-bold text-slate-400">
                    </div>
                    
                    <div class="md:col-span-2 p-6 bg-red-50/50 rounded-3xl border border-red-100 space-y-4">
                        <p class="text-[9px] font-black text-red-500 uppercase tracking-widest">Kontak Darurat Utama</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input type="text" name="emergency_1_nama" placeholder="Nama Lengkap" class="bg-white p-3 rounded-xl text-xs font-bold border-2 border-transparent focus:border-red-200 outline-none">
                            <input type="text" name="emergency_1_hubungan" placeholder="Hubungan (Misal: Ibu)" class="bg-white p-3 rounded-xl text-xs font-bold border-2 border-transparent focus:border-red-200 outline-none">
                            <input type="text" name="emergency_1_telp" placeholder="No. Telepon" class="bg-white p-3 rounded-xl text-xs font-bold border-2 border-transparent focus:border-red-200 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 5: SECURITY --}}
            <div class="bg-slate-900 p-8 rounded-[2.5rem] space-y-6 text-white shadow-2xl shadow-slate-900/40">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 bg-cdi-orange text-white rounded-lg flex items-center justify-center text-xs">
                        <i class="fas fa-key"></i>
                    </div>
                    <h4 class="text-[10px] font-black uppercase tracking-widest italic">Akses Login Sistem</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Username</label>
                        <input type="text" name="username" required placeholder="username123" class="w-full bg-slate-800 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi-orange outline-none transition-all lowercase">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Email</label>
                        <input type="email" name="email" required placeholder="email@domain.com" class="w-full bg-slate-800 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi-orange outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Password Default</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-800 border-2 border-transparent p-4 rounded-2xl text-sm font-bold focus:border-cdi-orange outline-none transition-all">
                    </div>
                </div>
            </div>

            {{-- SUBMIT SECTION --}}
            <div class="pt-8 flex items-center gap-4">
                <button type="submit" class="flex-1 bg-cdi text-white py-6 rounded-2xl font-black uppercase italic tracking-[0.2em] hover:bg-cdi-orange transition-all duration-300 shadow-xl shadow-blue-900/10 group">
                    Daftarkan Personel <i class="fas fa-check-double ml-2 group-hover:scale-125 transition-transform"></i>
                </button>
                <button type="reset" class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center shadow-sm" title="Reset Form">
                    <i class="fas fa-undo"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- LOGIKA AUTO-CREATE NIP (12 DIGIT) ---
    // Struktur: YYMM (Masuk) + G (Gender) + YYMM (Lahir) + RRR (Random)
    function generateNIP() {
        const tglLahirVal = document.getElementById('tanggal_lahir').value; 
        const jkVal = document.getElementById('jenis_kelamin').value; 
        
        if(!tglLahirVal) return "";

        // 1. Ambil Waktu Masuk (Sekarang) - 4 Digit
        const now = new Date();
        const yearNow = now.getFullYear().toString().slice(-2);
        const monthNow = (now.getMonth() + 1).toString().padStart(2, '0');
        const part1 = yearNow + monthNow;

        // 2. Kode Gender - 1 Digit
        const part2 = (jkVal === 'L') ? "1" : "2";

        // 3. Waktu Lahir (YYMM dari Input) - 4 Digit
        const dob = new Date(tglLahirVal);
        const dobYear = dob.getFullYear().toString().slice(-2);
        const dobMonth = (dob.getMonth() + 1).toString().padStart(2, '0');
        const part3 = dobYear + dobMonth;

        // 4. Random 3 Digit
        const part4 = Math.floor(Math.random() * 900 + 100).toString(); 

        return part1 + part2 + part3 + part4;
    }

    // --- SUBMIT HANDLER ---
    document.getElementById('regForm').addEventListener('submit', function(e) {
        const tglLahir = document.getElementById('tanggal_lahir').value;

        if(!tglLahir) {
            e.preventDefault();
            alert('Silahkan isi tanggal lahir terlebih dahulu untuk men-generate NIP Otomatis!');
            return;
        }

        // Set NIP otomatis sebelum submit
        const generatedNip = generateNIP();
        document.getElementById('nip_auto').value = generatedNip;
    });
</script>
@endsection