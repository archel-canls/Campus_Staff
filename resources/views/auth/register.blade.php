<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CDI Staff Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,700;1,800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
        .bg-cdi { background-color: #003366; }
        .text-cdi { color: #003366; }
        .bg-cdi-orange { background-color: #FF8C00; }
        .text-cdi-orange { color: #FF8C00; }
        input::placeholder { font-style: italic; opacity: 0.5; }
        
        /* Custom Scrollbar */
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f8fafc; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #FF8C00; }
    </style>
</head>
<body class="bg-slate-50">

<div class="min-h-screen flex items-center justify-center p-4 md:p-6 lg:p-10">
    <div class="max-w-[1200px] w-full bg-white rounded-[3rem] shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[85vh]">
        
        <div class="lg:col-span-4 bg-cdi p-12 hidden lg:flex flex-col justify-between relative overflow-hidden">
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-cdi-orange/20 rounded-full -ml-20 -mb-20 blur-3xl"></div>
            
            <div class="relative z-10">
                <div class="flex items-center space-x-2 bg-white/10 w-fit px-4 py-2 rounded-full border border-white/20">
                    <span class="text-white font-black italic tracking-tighter text-sm uppercase">CDI<span class="text-white/50">-STAFF</span></span>
                </div>
            </div>

            <div class="relative z-10">
                <h1 class="text-5xl font-black text-white italic uppercase tracking-tighter leading-none">
                    Join Our <br> <span class="text-cdi-orange text-6xl">Network.</span>
                </h1>
                <p class="text-white/50 font-bold uppercase tracking-[0.2em] text-[10px] mt-6 leading-relaxed">
                    Sistem manajemen personel terintegrasi. Pastikan data diri lengkap untuk keperluan administrasi, payroll, dan keamanan kerja.
                </p>
            </div>

            <div class="relative z-10 flex items-center space-x-4 text-white/40 text-xs font-bold uppercase tracking-widest">
                <span>Enterprise Security</span>
                <span class="w-1 h-1 bg-white/20 rounded-full"></span>
                <span>v2.6.0</span>
            </div>
        </div>

        <div class="lg:col-span-8 p-6 md:p-10 lg:px-14 flex flex-col bg-white overflow-y-auto custom-scroll" style="max-height: 90vh;">
            <div class="mb-10 text-center lg:text-left">
                <h2 class="text-3xl font-black text-cdi uppercase italic tracking-tighter">Registrasi <span class="text-cdi-orange">Personel Baru.</span></h2>
                <p class="text-slate-400 font-bold text-[9px] uppercase tracking-[0.2em] mt-2">Lengkapi formulir di bawah ini dengan data yang valid</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-2xl text-[11px] font-bold text-red-600 uppercase italic">
                    <ul class="list-disc ml-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="space-y-10" id="regForm">
                @csrf
                
                {{-- NIP Hidden Field --}}
                <input type="hidden" name="nip" id="nip_auto">

                {{-- STEP 01: Identitas --}}
                <div class="space-y-4">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="bg-cdi text-white w-8 h-8 rounded-xl flex items-center justify-center text-xs shadow-lg shadow-blue-900/20 font-black">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <div>
                            <span class="text-[9px] font-black text-cdi-orange uppercase tracking-widest block leading-none mb-1">Step 01</span>
                            <h3 class="text-xs font-black text-cdi uppercase tracking-widest">Identitas Utama</h3>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Sesuai KTP" 
                                   class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">NIK (16 Digit)</label>
                            <input type="text" name="nik" value="{{ old('nik') }}" required maxlength="16" placeholder="330xxxxxxxxxxxxx" 
                                   class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required placeholder="Kota Kelahiran" 
                                   class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required 
                                   class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="jenis_kelamin" required class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi outline-none focus:border-cdi-orange transition-all">
                                <option value="L">Laki-Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Golongan Darah</label>
                            <select name="golongan_darah" class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi outline-none focus:border-cdi-orange transition-all">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="AB">AB</option>
                                <option value="O">O</option>
                                <option value="-">Tidak Tahu</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- STEP 02: Lokasi --}}
                <div class="space-y-4">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="bg-cdi text-white w-8 h-8 rounded-xl flex items-center justify-center text-xs shadow-lg shadow-blue-900/20 font-black">
                            <i class="fas fa-map-marked-alt"></i>
                        </span>
                        <div>
                            <span class="text-[9px] font-black text-cdi-orange uppercase tracking-widest block leading-none mb-1">Step 02</span>
                            <h3 class="text-xs font-black text-cdi uppercase tracking-widest">Informasi Lokasi</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Alamat Sesuai KTP</label>
                            <textarea name="alamat_ktp" required placeholder="Alamat lengkap sesuai dokumen negara" rows="3"
                                      class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">{{ old('alamat_ktp') }}</textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Alamat Domisili</label>
                            <textarea name="alamat_domisili" required placeholder="Tempat tinggal saat ini" rows="3"
                                      class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">{{ old('alamat_domisili') }}</textarea>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Nomor HP Aktif (WhatsApp)</label>
                            <input type="text" name="telepon" value="{{ old('telepon') }}" required placeholder="08xxxxxxxxxx" 
                                   class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                        </div>
                    </div>
                </div>

                {{-- STEP 03: Jalur Pendaftaran & Pendidikan --}}
                <div class="space-y-4">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="bg-cdi text-white w-8 h-8 rounded-xl flex items-center justify-center text-xs shadow-lg shadow-blue-900/20 font-black">
                            <i class="fas fa-briefcase"></i>
                        </span>
                        <div>
                            <span class="text-[9px] font-black text-cdi-orange uppercase tracking-widest block leading-none mb-1">Step 03</span>
                            <h3 class="text-xs font-black text-cdi uppercase tracking-widest">Jalur Pendaftaran & Pendidikan</h3>
                        </div>
                    </div>
                    
                    <div class="p-8 bg-blue-50/50 rounded-[2.5rem] border border-blue-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-4">Pilih Jalur</label>
                                <select id="jalur_pendaftaran" name="jalur_pendaftaran" class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:border-cdi-orange outline-none transition-all">
                                    <option value="karyawan">Jalur Karyawan</option>
                                    <option value="magang">Jalur Magang</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-4">Status Spesifik</label>
                                <select name="status" id="status_select" class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:border-cdi-orange outline-none transition-all">
                                    <option value="tetap">Karyawan Tetap</option>
                                    <option value="kontrak">Karyawan Kontrak</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-4" id="label_pendidikan">Jenjang Pendidikan Terakhir</label>
                                <select name="pendidikan_terakhir" id="pendidikan_select" required class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi outline-none focus:border-cdi-orange transition-all">
                                    <option value="">-- Pilih Jenjang --</option>
                                    <option value="SD">SD / Sederajat</option>
                                    <option value="SMP">SMP / Sederajat</option>
                                    <option value="SMA">SMA / Sederajat</option>
                                    <option value="SMK">SMK / Sederajat</option>
                                    <option value="D3">Diploma 3 (D3)</option>
                                    <option value="S1">Sarjana (S1)</option>
                                    <option value="S2">Magister (S2)</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-4" id="label_status_didik">Status Pendidikan</label>
                                <select name="status_pendidikan" id="status_pendidikan_select" required class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi outline-none focus:border-cdi-orange transition-all">
                                    <option value="Lulus">Sudah Lulus / Berijazah</option>
                                    <option value="Belum Lulus">Masih Menempuh Pendidikan</option>
                                </select>
                            </div>

                            <div class="space-y-1 md:col-span-2">
                                <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-4" id="label_instansi">Nama Asal Pendidikan Terakhir</label>
                                <input type="text" name="instansi" id="instansi_input" required placeholder="Contoh: Universitas Gadjah Mada" 
                                       class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:border-cdi-orange outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 04: Tanggungan & Dokumen --}}
                <div class="space-y-4">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="bg-cdi-orange text-white w-8 h-8 rounded-xl flex items-center justify-center text-xs shadow-lg shadow-orange-500/20 font-black">
                            <i class="fas fa-users"></i>
                        </span>
                        <div>
                            <span class="text-[9px] font-black text-cdi-orange uppercase tracking-widest block leading-none mb-1">Step 04</span>
                            <h3 class="text-xs font-black text-cdi uppercase tracking-widest">Tanggungan & Dokumen</h3>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Jumlah Tanggungan (Opsional)</label>
                            <input type="number" name="jumlah_tanggungan" value="{{ old('jumlah_tanggungan', 0) }}" min="0" 
                                   class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Bukti Tanggungan (KK/Surat)</label>
                            <div class="relative">
                                <input type="file" name="bukti_tanggungan" accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full bg-slate-50 border-2 border-transparent py-2.5 px-6 rounded-2xl font-bold text-xs text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-cdi file:text-white hover:file:bg-cdi-orange">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EMERGENCY CONTACTS --}}
                <div class="space-y-4">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="bg-red-500 text-white w-8 h-8 rounded-xl flex items-center justify-center text-xs shadow-lg shadow-red-500/20 font-black">
                            <i class="fas fa-phone-alt"></i>
                        </span>
                        <div>
                            <span class="text-[9px] font-black text-red-500 uppercase tracking-widest block leading-none mb-1">Emergency</span>
                            <h3 class="text-xs font-black text-cdi uppercase tracking-widest">Kontak Darurat</h3>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-slate-50 rounded-3xl space-y-4 border border-slate-100">
                        <p class="text-[9px] font-black text-cdi-orange uppercase tracking-[0.2em] ml-4">Kontak Utama (Opsional)</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Nama Lengkap</label>
                                <input type="text" name="emergency_1_nama" placeholder="Nama orang terdekat" 
                                       class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:border-cdi-orange outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Hubungan</label>
                                <select name="emergency_1_hubungan" class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:border-cdi-orange outline-none transition-all">
                                    <option value="">-- Pilih Hubungan --</option>
                                    <option value="Ayah">Ayah</option>
                                    <option value="Ibu">Ibu</option>
                                    <option value="Saudara / Kerabat">Saudara / Kerabat</option>
                                    <option value="Teman">Teman</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">No. Telepon</label>
                                <input type="text" name="emergency_1_telp" placeholder="08xxxxxxxxxx" 
                                       class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:border-cdi-orange outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50/50 rounded-3xl space-y-4 border border-dashed border-slate-200">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-4">Kontak Cadangan (Opsional)</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Nama Lengkap</label>
                                <input type="text" name="emergency_2_nama" placeholder="Nama orang terdekat kedua" 
                                       class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:border-cdi-orange outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Hubungan</label>
                                <select name="emergency_2_hubungan" class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:border-cdi-orange outline-none transition-all">
                                    <option value="">-- Pilih Hubungan --</option>
                                    <option value="Ayah">Ayah</option>
                                    <option value="Ibu">Ibu</option>
                                    <option value="Saudara / Kerabat">Saudara / Kerabat</option>
                                    <option value="Teman">Teman</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">No. Telepon</label>
                                <input type="text" name="emergency_2_telp" placeholder="08xxxxxxxxxx" 
                                       class="w-full bg-white border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:border-cdi-orange outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECURITY --}}
                <div class="space-y-4 border-t border-slate-100 pt-10">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="bg-slate-800 text-white w-8 h-8 rounded-xl flex items-center justify-center text-xs shadow-lg shadow-slate-800/20 font-black">
                            <i class="fas fa-key"></i>
                        </span>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block leading-none mb-1">Security</span>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Akun & Keamanan</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Username Login</label>
                            <input type="text" name="username" value="{{ old('username') }}" required placeholder="username123" 
                                   class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all lowercase">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Email Aktif</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@domain.com" 
                                   class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                        </div>
                        
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Password</label>
                            <div class="relative group">
                                <input type="password" name="password" id="password" required placeholder="••••••••" 
                                       class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 pr-12 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                                <button type="button" onclick="togglePassword('password', 'eye-icon-1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-cdi-orange transition-colors">
                                    <i id="eye-icon-1" class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-4">Konfirmasi Password</label>
                            <div class="relative group">
                                <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="••••••••" 
                                       class="w-full bg-slate-50 border-2 border-transparent py-3 px-6 pr-12 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                                <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-cdi-orange transition-colors">
                                    <i id="eye-icon-2" class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-cdi text-white py-5 rounded-[2rem] font-black uppercase italic tracking-widest text-xs shadow-xl shadow-blue-900/20 hover:bg-cdi-orange hover:-translate-y-1 transition-all">
                        Finalisasi Pendaftaran <i class="fas fa-check-double ml-2"></i>
                    </button>
                </div>
            </form>

            <div class="mt-12 mb-6 text-center">
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">
                    Sudah memiliki akun? <a href="{{ route('login') }}" class="text-cdi font-black hover:text-cdi-orange transition-colors border-b-2 border-cdi/10 hover:border-cdi-orange ml-1">Log In Di Sini</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // --- FUNGSI TOGGLE PASSWORD ---
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }

    // --- LOGIKA JALUR PENDAFTARAN ---
    const jalurSelect = document.getElementById('jalur_pendaftaran');
    const statusSelect = document.getElementById('status_select');
    const instansiInput = document.getElementById('instansi_input');
    const labelInstansi = document.getElementById('label_instansi');
    const labelPendidikan = document.getElementById('label_pendidikan');
    const statusDidikSelect = document.getElementById('status_pendidikan_select');

    jalurSelect.addEventListener('change', function() {
        statusSelect.innerHTML = '';
        if (this.value === 'karyawan') {
            labelInstansi.innerText = 'Nama Asal Pendidikan Terakhir';
            labelPendidikan.innerText = 'Jenjang Pendidikan Terakhir';
            instansiInput.placeholder = 'Contoh: Universitas Gadjah Mada / SMK Negeri 1';
            statusDidikSelect.innerHTML = `<option value="Lulus" selected>Sudah Lulus / Berijazah</option>`;
            statusSelect.innerHTML = `
                <option value="tetap">Karyawan Tetap</option>
                <option value="kontrak">Karyawan Kontrak</option>
            `;
        } else {
            labelInstansi.innerText = 'Nama Instansi (Sekolah/Universitas Asal)';
            labelPendidikan.innerText = 'Jenjang Pendidikan Saat Ini';
            instansiInput.placeholder = 'Contoh: SMK Negeri 7 Semarang / Binus University';
            statusDidikSelect.innerHTML = `
                <option value="Belum Lulus" selected>Masih Menempuh Pendidikan (Aktif)</option>
                <option value="Lulus">Sudah Lulus (Fresh Graduate)</option>
            `;
            statusSelect.innerHTML = `
                <option value="magang_mbkm">Magang MBKM (Kampus Merdeka)</option>
                <option value="magang_ppl">Magang PPL / PKL</option>
                <option value="magang_mandiri">Magang Mandiri (Paid)</option>
            `;
        }
    });

    // --- LOGIKA AUTO-CREATE NIP (12 DIGIT) ---
    // Struktur: YYMM (Masuk) + G (Gender) + YYMM (Lahir) + RRR (Random/Urut)
    function generateNIP() {
        const tglLahirVal = document.getElementById('tanggal_lahir').value; 
        const jkVal = document.getElementById('jenis_kelamin').value; 
        
        if(!tglLahirVal) return "";

        // 1. Ambil Waktu Masuk (Sekarang) - 4 Digit
        const now = new Date();
        const yearNow = now.getFullYear().toString().slice(-2);
        const monthNow = (now.getMonth() + 1).toString().padStart(2, '0');
        const part1 = yearNow + monthNow; // Misal: 2602

        // 2. Ambil Kode Gender - 1 Digit
        const part2 = (jkVal === 'L') ? "1" : "2"; // 1 atau 2

        // 3. Ambil Waktu Lahir (YYMM dari Input) - 4 Digit
        const dob = new Date(tglLahirVal);
        const dobYear = dob.getFullYear().toString().slice(-2); // YY Lahir
        const dobMonth = (dob.getMonth() + 1).toString().padStart(2, '0'); // MM Lahir
        const part3 = dobYear + dobMonth; // Misal: 9505

        // 4. Nomor Urut / Random (Sementara di Client) - 3 Digit
        // Pada produksi, ini idealnya di-generate di Backend agar benar-benar berurutan (001, 002, dst)
        const part4 = Math.floor(Math.random() * 900 + 100).toString(); 

        return part1 + part2 + part3 + part4;
    }

    // --- SUBMIT HANDLER ---
    document.getElementById('regForm').addEventListener('submit', function(e) {
        const pass = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirmation').value;
        const tglLahir = document.getElementById('tanggal_lahir').value;

        if(!tglLahir) {
            e.preventDefault();
            alert('Silahkan isi tanggal lahir terlebih dahulu untuk membuat ID Personel!');
            return;
        }

        if(pass !== confirm) {
            e.preventDefault();
            alert('Konfirmasi password tidak cocok!');
            return;
        }

        // Set NIP otomatis sesaat sebelum submit
        const generatedNip = generateNIP();
        document.getElementById('nip_auto').value = generatedNip;
    });
</script>

</body> 
</html>