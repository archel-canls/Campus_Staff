@extends('layouts.app')

@section('title', 'Permohonan Akun')
@section('page_title', 'Verifikasi Pendaftar')

@section('content')
<div class="space-y-8 pb-10">
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h3 class="text-4xl font-black italic uppercase tracking-tighter text-cdi leading-none">
                Permohonan <span class="text-cdi-orange">Pendaftaran.</span>
            </h3>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-3 flex items-center">
                <span class="w-10 h-[2px] bg-cdi-orange mr-3"></span>
                Validasi calon staf dan peserta magang baru
            </p>
        </div>
        <a href="{{ route('manajemen-karyawan.index') }}" class="text-[10px] font-black uppercase text-slate-400 hover:text-cdi transition-all">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Database
        </a>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 flex flex-wrap gap-4 items-center justify-between">
        <div class="relative w-full md:w-96">
            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" id="searchPermohonan" placeholder="CARI NAMA / NIP / INSTANSI..."
                class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-12 pr-6 text-[11px] font-bold tracking-widest focus:ring-2 focus:ring-cdi-orange transition-all">
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-2xl">
            <span class="text-[10px] font-black text-slate-400 uppercase">Total Antrean:</span>
            <span class="text-sm font-black text-cdi">{{ $permohonans->count() }}</span>
        </div>
    </div>

    {{-- TABLE LIST --}}
    <div class="bg-white rounded-[3rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full border-collapse" id="tablePermohonan">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Calon Personel</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Posisi & Divisi</th>
                        <th class="px-8 py-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Instansi</th>
                        <th class="px-8 py-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($permohonans as $user)
                    @php
                    $k = $user->karyawan;
                    $userData = $user->load('karyawan.divisi');
                    $encodedData = base64_encode(json_encode($userData));
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors group row-permohonan">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 overflow-hidden border-2 border-white shadow-sm flex-shrink-0">
                                    <img src="{{ $user->profile_photo }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="text-xs font-black uppercase text-cdi tracking-tight name-tag">{{ $user->name }}</div>
                                    <div class="text-[9px] font-bold text-slate-400 tracking-widest mt-1 nip-tag">{{ $k->nip }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-3 py-1.5 rounded-lg bg-cdi/5 text-cdi text-[9px] font-black uppercase tracking-tighter">
                                {{ $k->jabatan ?? 'Belum Ditentukan' }}
                            </span>
                            <div class="text-[9px] font-bold text-slate-400 mt-2 italic">{{ $k->divisi->nama ?? 'Menunggu Verifikasi' }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-[10px] font-bold text-slate-600 instansi-tag uppercase tracking-tighter">{{ $k->instansi }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="showDetailPermohonan('{{ $encodedData }}')"
                                    class="p-3 bg-slate-100 text-slate-400 rounded-xl hover:bg-cdi hover:text-white transition-all shadow-sm" title="Lihat Detail & Verifikasi">
                                    <i class="fas fa-user-check text-xs"></i>
                                </button>
                                <form action="{{ route('manajemen-karyawan.destroy', $k->id) }}" method="POST" class="inline" onsubmit="return confirm('Tolak dan hapus data pendaftaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all shadow-md shadow-red-200" title="Tolak">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-user-clock text-slate-200 text-3xl"></i>
                                </div>
                                <h4 class="text-sm font-black text-slate-300 uppercase italic tracking-widest">Tidak ada antrean permohonan</h4>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DETAIL LENGKAP --}}
<div id="modalDetail" class="fixed inset-0 z-[99] hidden">
    <div class="absolute inset-0 bg-cdi/60 backdrop-blur-sm shadow-2xl"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl p-6">
        <div class="bg-white rounded-[3rem] overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xl font-black italic uppercase text-cdi">Verifikasi <span class="text-cdi-orange">Pendaftar.</span></h3>
                <button onclick="closeModal()" class="w-10 h-10 rounded-2xl bg-white shadow-sm flex items-center justify-center text-slate-400 hover:text-red-500 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-8 overflow-y-auto custom-scroll flex-1">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-[11px]">
                    {{-- Sisi Kiri: Foto & NIP --}}
                    <div class="space-y-6">
                        <div class="aspect-square rounded-[2.5rem] bg-slate-100 overflow-hidden border-4 border-slate-50 shadow-inner">
                            <img id="detFoto" src="" class="w-full h-full object-cover">
                        </div>
                        <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 text-center">
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">ID Personel (NIP)</label>
                            <div id="detNip" class="text-sm font-black text-cdi tracking-tighter italic"></div>
                        </div>

                        {{-- TANGGUNGAN BOX --}}
                        <div class="bg-cdi-orange/5 p-6 rounded-3xl border border-cdi-orange/10">
                            <label class="block text-[9px] font-black text-cdi-orange uppercase tracking-widest mb-3">Data Tanggungan</label>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-slate-400 font-bold">Jumlah:</span>
                                <span id="detJmlTanggungan" class="text-sm font-black text-cdi">0</span>
                            </div>
                            <div id="wrapperBukti">
                                <a id="detBuktiLink" href="#" target="_blank" class="flex items-center justify-center gap-2 w-full py-3 bg-white border border-cdi-orange/20 rounded-xl text-cdi-orange font-black uppercase text-[9px] hover:bg-cdi-orange hover:text-white transition-all">
                                    <i class="fas fa-file-download"></i> Lihat Bukti Dokumen
                                </a>
                            </div>
                            <p id="noBuktiText" class="text-[9px] text-slate-400 italic text-center hidden">Tidak ada dokumen diunggah</p>
                        </div>
                    </div>

                    {{-- Sisi Kanan: Detail Data --}}
                    <div class="md:col-span-2 space-y-8">
                        {{-- Identitas --}}
                        <div>
                            <h4 class="text-[10px] font-black text-cdi-orange uppercase tracking-[0.2em] mb-4 flex items-center">
                                <i class="fas fa-user-circle mr-2"></i> Informasi Identitas
                            </h4>
                            <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                                <div>
                                    <label class="text-slate-400 font-bold uppercase text-[9px]">Nama Lengkap</label>
                                    <p id="detNama" class="font-black text-slate-700 uppercase"></p>
                                </div>
                                <div>
                                    <label class="text-slate-400 font-bold uppercase text-[9px]">NIK</label>
                                    <p id="detNik" class="font-bold text-slate-700"></p>
                                </div>
                                <div>
                                    <label class="text-slate-400 font-bold uppercase text-[9px]">Tempat, Tgl Lahir</label>
                                    <p id="detTtl" class="font-bold text-slate-700"></p>
                                </div>
                                <div>
                                    <label class="text-slate-400 font-bold uppercase text-[9px]">Jenis Kelamin / Goldar</label>
                                    <p id="detJk" class="font-bold text-slate-700"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Pekerjaan (Asal) --}}
                        <div>
                            <h4 class="text-[10px] font-black text-cdi-orange uppercase tracking-[0.2em] mb-4 flex items-center">
                                <i class="fas fa-briefcase mr-2"></i> Status Pendaftaran
                            </h4>
                            <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                                <div>
                                    <label class="text-slate-400 font-bold uppercase text-[9px]">Instansi Asal</label>
                                    <p id="detInstansi" class="font-bold text-slate-700 uppercase"></p>
                                </div>
                                <div>
                                    <label class="text-slate-400 font-bold uppercase text-[9px]">Status Staf</label>
                                    <p id="detStatus" class="font-bold text-slate-700 uppercase"></p>
                                </div>
                                <div class="col-span-2">
                                    <label class="text-slate-400 font-bold uppercase text-[9px]">Pendidikan</label>
                                    <p id="detPendidikan" class="font-bold text-slate-700"></p>
                                </div>
                            </div>
                        </div>

                        {{-- FORM PENENTUAN JABATAN SESUAI STRUKTUR DIVISI --}}
                        <div class="p-6 bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200">
                            <h4 class="text-[10px] font-black text-cdi uppercase tracking-[0.2em] mb-4">
                                <i class="fas fa-id-badge mr-2"></i> Tentukan Penempatan Staf
                            </h4>
                            <form id="formApprove" method="POST" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[9px] font-black text-slate-400 uppercase ml-2 mb-1 block">Pilih Divisi</label>
                                        <select name="divisi_id" id="selectDivisi" required
                                            class="w-full bg-white border-none rounded-xl py-3 px-4 text-[11px] font-bold focus:ring-2 focus:ring-cdi-orange transition-all">
                                            <option value="">-- PILIH DIVISI --</option>
                                            @foreach($divisis as $divisi)
                                            @php
                                            $jabatanData = [];
                                            foreach($divisi->daftar_jabatan ?? [] as $namaJabatan => $target) {
                                            $jabatanData[$namaJabatan] = [
                                            'nama' => $namaJabatan,
                                            'sisa' => $divisi->getSisaKuota($namaJabatan)
                                            ];
                                            }
                                            @endphp
                                            <option value="{{ $divisi->id }}" data-jabatan="{{ json_encode($jabatanData) }}">
                                                {{ $divisi->nama }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-black text-slate-400 uppercase ml-2 mb-1 block">Pilih Jabatan</label>
                                        <select name="jabatan" id="selectJabatan" required disabled
                                            class="w-full bg-white border-none rounded-xl py-3 px-4 text-[11px] font-bold focus:ring-2 focus:ring-cdi-orange transition-all disabled:opacity-50">
                                            <option value="">-- PILIH JABATAN --</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="w-full py-4 bg-green-500 text-white rounded-2xl font-black uppercase italic tracking-widest hover:bg-green-600 transition-all shadow-lg shadow-green-200 mt-4">
                                    <i class="fas fa-check-circle mr-2"></i> Konfirmasi & Aktifkan Akun
                                </button>
                            </form>
                        </div>

                        {{-- Domisili & Emergency --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h4 class="text-[10px] font-black text-cdi-orange uppercase tracking-[0.2em] mb-4 flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2"></i> Kontak Personel
                                </h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-slate-400 font-bold uppercase text-[9px]">Alamat Domisili</label>
                                        <p id="detAlamat" class="font-bold text-slate-700 leading-relaxed"></p>
                                    </div>
                                    <div>
                                        <label class="text-slate-400 font-bold uppercase text-[9px]">Telepon / Email</label>
                                        <p id="detTelpEmail" class="font-bold text-slate-700"></p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-[10px] font-black text-red-500 uppercase tracking-[0.2em] mb-4 flex items-center">
                                    <i class="fas fa-phone-alt mr-2"></i> Kontak Darurat
                                </h4>
                                <div class="p-4 bg-red-50 rounded-2xl border border-red-100">
                                    <div class="mb-2">
                                        <p id="detEmergency1" class="font-black text-slate-700 text-[10px]"></p>
                                        <p id="detEmergency1Hub" class="text-[9px] text-slate-400 font-bold"></p>
                                    </div>
                                    <div class="pt-2 border-t border-red-100">
                                        <p id="detEmergency2" class="font-black text-slate-700 text-[10px]"></p>
                                        <p id="detEmergency2Hub" class="text-[9px] text-slate-400 font-bold"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectDivisi = document.getElementById('selectDivisi');
        const selectJabatan = document.getElementById('selectJabatan');

        // Fitur Update Jabatan Dinamis berdasarkan Struktur Divisi & Sisa Kuota
        selectDivisi.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.value) {
                selectJabatan.innerHTML = '<option value="">-- PILIH JABATAN --</option>';
                selectJabatan.disabled = true;
                return;
            }

            try {
                const jabatans = JSON.parse(selectedOption.getAttribute('data-jabatan') || '{}');
                selectJabatan.innerHTML = '<option value="">-- PILIH JABATAN --</option>';

                const keys = Object.keys(jabatans);
                if (keys.length > 0) {
                    selectJabatan.disabled = false;
                    keys.forEach(key => {
                        const jab = jabatans[key];
                        const opt = document.createElement('option');
                        opt.value = jab.nama;

                        // Cek Sisa Kuota
                        if (jab.sisa <= 0) {
                            opt.disabled = true;
                            opt.textContent = `${jab.nama.toUpperCase()} (PENUH)`;
                            opt.classList.add('text-red-400');
                        } else {
                            opt.textContent = `${jab.nama.toUpperCase()} (SISA: ${jab.sisa})`;
                        }

                        selectJabatan.appendChild(opt);
                    });
                } else {
                    selectJabatan.disabled = true;
                    const opt = document.createElement('option');
                    opt.textContent = "TIDAK ADA JABATAN TERSEDIA";
                    selectJabatan.appendChild(opt);
                }
            } catch (e) {
                console.error("Error parsing jabatan data", e);
            }
        });

        // Fitur Search Table (Nama, NIP, Instansi)
        const searchInput = document.getElementById('searchPermohonan');
        const rows = document.querySelectorAll('.row-permohonan');

        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            rows.forEach(row => {
                const nameTag = row.querySelector('.name-tag');
                const nipTag = row.querySelector('.nip-tag');
                const instansiTag = row.querySelector('.instansi-tag');

                const name = nameTag ? nameTag.innerText.toLowerCase() : '';
                const nip = nipTag ? nipTag.innerText.toLowerCase() : '';
                const instansi = instansiTag ? instansiTag.innerText.toLowerCase() : '';

                if (name.includes(val) || nip.includes(val) || instansi.includes(val)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    function showDetailPermohonan(encodedData) {
        const user = JSON.parse(atob(encodedData));
        const k = user.karyawan;

        // Identitas & Foto
        document.getElementById('detFoto').src = user.profile_photo || '';
        document.getElementById('detNama').innerText = user.name || '-';
        document.getElementById('detNip').innerText = k.nip || '-';
        document.getElementById('detNik').innerText = k.nik || '-';
        document.getElementById('detTtl').innerText = (k.tempat_lahir || '-') + ', ' + (k.tanggal_lahir || '-');

        const jkText = k.jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan';
        document.getElementById('detJk').innerText = jkText + ' / Goldar: ' + (k.golongan_darah || '-');

        // Status Pendaftaran
        document.getElementById('detInstansi').innerText = k.instansi || '-';
        document.getElementById('detStatus').innerText = k.status ? k.status.replace(/_/g, ' ') : '-';
        document.getElementById('detPendidikan').innerText = (k.pendidikan_terakhir || '-') + ' (' + (k.status_pendidikan || 'Lulus') + ')';

        // Kontak
        document.getElementById('detAlamat').innerText = k.alamat_domisili || '-';
        document.getElementById('detTelpEmail').innerText = (k.telepon || '-') + ' / ' + (user.email || '-');

        // Tanggungan & Bukti File
        document.getElementById('detJmlTanggungan').innerText = k.jumlah_tanggungan || '0';
        const linkBukti = document.getElementById('detBuktiLink');
        const textNoBukti = document.getElementById('noBuktiText');

        if (k.bukti_tanggungan) {
            linkBukti.classList.remove('hidden');
            textNoBukti.classList.add('hidden');
            linkBukti.href = `/storage/karyawan/bukti_tanggungan/${k.bukti_tanggungan}`;
        } else {
            linkBukti.classList.add('hidden');
            textNoBukti.classList.remove('hidden');
        }

        // Kontak Darurat
        document.getElementById('detEmergency1').innerText = k.emergency_1_nama || 'Tidak ada data';
        document.getElementById('detEmergency1Hub').innerText = (k.emergency_1_hubungan || '') + ' (' + (k.emergency_1_telp || '') + ')';
        document.getElementById('detEmergency2').innerText = k.emergency_2_nama || '-';
        document.getElementById('detEmergency2Hub').innerText = (k.emergency_2_hubungan || '') + ' (' + (k.emergency_2_telp || '') + ')';

        // Set Action Form Approve
        document.getElementById('formApprove').action = `/admin/manajemen-karyawan/approve/${user.id}`;

        // Reset Penempatan Form saat buka modal baru
        document.getElementById('selectDivisi').value = "";
        const selectJabatan = document.getElementById('selectJabatan');
        selectJabatan.innerHTML = '<option value="">-- PILIH JABATAN --</option>';
        selectJabatan.disabled = true;

        // Tampilkan Modal
        document.getElementById('modalDetail').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modalDetail').classList.add('hidden');
    }
</script>
@endsection