@extends('layouts.app')

@section('title', 'Smart Scanner Presensi')
@section('page_title', 'Scanner Kehadiran Personel')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-6xl mx-auto space-y-8 pb-20">
    {{-- Selector Mode --}}
    <div class="flex justify-center relative z-[100]">
        <div class="bg-white/80 backdrop-blur-md p-1.5 rounded-[2.5rem] shadow-2xl border border-white/50 flex space-x-1">
            <button onclick="setMode('masuk')" id="btn-mode-masuk" 
                class="px-10 py-4 rounded-full font-black uppercase italic text-[10px] tracking-widest transition-all duration-500 bg-cdi text-white shadow-xl flex items-center">
                <i class="fas fa-sign-in-alt mr-2 text-sm"></i> Mode Masuk
            </button>
            <button onclick="setMode('keluar')" id="btn-mode-keluar" 
                class="px-10 py-4 rounded-full font-black uppercase italic text-[10px] tracking-widest transition-all duration-500 text-slate-400 hover:bg-slate-50 flex items-center">
                <i class="fas fa-sign-out-alt mr-2 text-sm"></i> Mode Keluar
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <div class="bg-white rounded-[3.5rem] shadow-2xl border border-slate-100 overflow-hidden">
                <div id="scanner-header" class="p-10 bg-cdi text-white relative transition-all duration-700">
                    <div class="relative z-10 flex justify-between items-center">
                        <div>
                            <h3 class="text-3xl font-black italic uppercase tracking-tighter" id="mode-text">SCANNER MASUK</h3>
                            <p class="text-[10px] opacity-60 font-bold uppercase tracking-widest" id="debug-status">Status: Menyiapkan Kamera...</p>
                        </div>
                        <div class="flex items-center space-x-3 bg-black/20 px-5 py-2.5 rounded-2xl">
                            <div id="camera-dot" class="w-2.5 h-2.5 bg-red-500 rounded-full"></div>
                            <span id="camera-text" class="text-[11px] font-black uppercase tracking-widest">OFFLINE</span>
                        </div>
                    </div>
                </div>

                <div class="p-10">
                    {{-- Container Kamera --}}
                    <div class="relative bg-black rounded-[3rem] overflow-hidden border-[6px] border-slate-50 shadow-inner" style="min-height: 400px;">
                        <div id="reader" class="w-full"></div>
                        
                        {{-- Overlay Scanner dengan Petunjuk Arah --}}
                        <div class="absolute inset-0 pointer-events-none z-50 flex items-center justify-center">
                            <div id="scan-frame" class="w-72 h-72 border-2 border-white/30 rounded-[2.5rem] relative transition-all duration-300">
                                {{-- Sudut Dinamis --}}
                                <div class="corner-guide absolute -top-1 -left-1 w-14 h-14 border-t-[8px] border-l-[8px] border-white rounded-tl-[1.8rem] transition-colors duration-300"></div>
                                <div class="corner-guide absolute -top-1 -right-1 w-14 h-14 border-t-[8px] border-r-[8px] border-white rounded-tr-[1.8rem] transition-colors duration-300"></div>
                                <div class="corner-guide absolute -bottom-1 -left-1 w-14 h-14 border-b-[8px] border-l-[8px] border-white rounded-bl-[1.8rem] transition-colors duration-300"></div>
                                <div class="corner-guide absolute -bottom-1 -right-1 w-14 h-14 border-b-[8px] border-r-[8px] border-white rounded-br-[1.8rem] transition-colors duration-300"></div>
                                
                                {{-- Petunjuk Arah (Arrows) --}}
                                <i class="fas fa-chevron-up absolute -top-8 left-1/2 -translate-x-1/2 text-white/50 animate-bounce"></i>
                                <i class="fas fa-chevron-down absolute -bottom-8 left-1/2 -translate-x-1/2 text-white/50 animate-bounce"></i>
                                <i class="fas fa-chevron-left absolute top-1/2 -left-8 -translate-y-1/2 text-white/50 animate-ping"></i>
                                <i class="fas fa-chevron-right absolute top-1/2 -right-8 -translate-y-1/2 text-white/50 animate-ping"></i>

                                <div class="scanner-line" id="scan-line"></div>
                            </div>
                        </div>
                        
                        {{-- Tip Text --}}
                        <div class="absolute bottom-6 left-0 w-full text-center z-50">
                            <span class="bg-black/60 backdrop-blur px-4 py-2 rounded-full text-[9px] font-black text-white uppercase tracking-widest">Posisikan Kode QR di Tengah Kotak</span>
                        </div>
                    </div>

                    <div class="mt-8 flex space-x-2">
                        <input type="text" id="manual-nip" placeholder="Ketik NIP secara manual..." 
                            class="flex-1 bg-slate-100 border-none p-4 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-cdi-orange">
                        <button onclick="submitManual()" 
                            class="bg-cdi text-white px-8 rounded-2xl font-black uppercase text-xs hover:scale-105 transition-all">Check</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel Kanan --}}
        <div class="lg:col-span-4 space-y-6">
            <div id="last-scan-card" class="bg-white p-8 rounded-[3.5rem] shadow-xl border border-slate-100 text-center hidden relative overflow-hidden">
                <div id="res-bg-accent" class="absolute top-0 left-0 w-full h-2 bg-green-500"></div>
                <div id="res-icon-bg" class="w-20 h-20 bg-green-50 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <i id="res-icon" class="fas fa-check text-3xl text-green-500"></i>
                </div>
                <h4 id="res-nama" class="text-xl font-black text-cdi uppercase italic leading-none mb-1">-</h4>
                <p id="res-nip" class="text-[10px] font-black text-cdi-orange tracking-[0.2em] mb-4">-</p>
                <div class="flex justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="text-left">
                        <p class="text-[8px] font-bold text-slate-400 uppercase">Waktu</p>
                        <p id="res-waktu" class="text-xs font-black">00:00</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[8px] font-bold text-slate-400 uppercase">Mode</p>
                        <p id="res-tipe" class="text-xs font-black text-green-600 uppercase">MASUK</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[3.5rem] shadow-xl border border-slate-100 min-h-[300px]">
                <h4 class="text-[10px] font-black text-cdi uppercase tracking-[0.2em] mb-6">Aktivitas Terkini</h4>
                <div id="recent-logs" class="space-y-4">
                    <p class="text-center text-slate-300 py-10 font-bold italic text-[10px] uppercase">Belum ada scan hari ini</p>
                </div>
            </div>
        </div>
    </div>
</div>

<audio id="beep-success" src="https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3" preload="auto"></audio>
<audio id="beep-error" src="https://assets.mixkit.co/active_storage/sfx/2571/2571-preview.mp3" preload="auto"></audio>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let currentMode = 'masuk';
    let isProcessing = false;
    let html5QrCode = null;

    function setMode(mode) {
        currentMode = mode;
        isProcessing = false;
        const h = document.getElementById('scanner-header');
        const tm = document.getElementById('mode-text');
        const bm = document.getElementById('btn-mode-masuk');
        const bk = document.getElementById('btn-mode-keluar');
        const sl = document.getElementById('scan-line');

        if (mode === 'masuk') {
            h.style.backgroundColor = '#1e293b'; 
            tm.innerText = 'SCANNER MASUK';
            bm.className = 'px-10 py-4 rounded-full font-black uppercase italic text-[10px] bg-cdi text-white shadow-xl flex items-center';
            bk.className = 'px-10 py-4 rounded-full font-black uppercase italic text-[10px] text-slate-400 hover:bg-slate-50 flex items-center';
            sl.style.background = '#22c55e';
            sl.style.boxShadow = '0 0 15px #22c55e';
        } else {
            h.style.backgroundColor = '#f97316'; 
            tm.innerText = 'SCANNER KELUAR';
            bk.className = 'px-10 py-4 rounded-full font-black uppercase italic text-[10px] bg-cdi-orange text-white shadow-xl flex items-center';
            bm.className = 'px-10 py-4 rounded-full font-black uppercase italic text-[10px] text-slate-400 hover:bg-slate-50 flex items-center';
            sl.style.background = '#f97316';
            sl.style.boxShadow = '0 0 15px #f97316';
        }
    }

    async function startCamera() {
        const config = { 
            fps: 25, 
            qrbox: { width: 280, height: 280 },
            aspectRatio: 1.0,
            rememberLastUsedCamera: true
        };

        try {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }
            
            await html5QrCode.start(
                { facingMode: "environment" }, 
                config, 
                (decodedText) => {
                    // KONDISI 3: HIJAU (Berhasil Baca)
                    updateUIStatus('success');

                    if (!isProcessing) {
                        isProcessing = true;
                        document.getElementById('reader').style.filter = 'brightness(1.5)';
                        setTimeout(() => document.getElementById('reader').style.filter = 'brightness(1)', 150);
                        submitAbsensi(decodedText, currentMode);
                    }
                },
                (errorMessage) => {
                    // KONDISI 2: MERAH (QR Terlihat tapi belum terbaca)
                    // Library mengirim errorMessage terus menerus jika QR ada di frame tapi belum "terdecode"
                    if(errorMessage.includes("No MultiFormat Readers")) {
                        updateUIStatus('standby'); // KONDISI 1: PUTIH (Tidak ada QR)
                    } else {
                        updateUIStatus('detected'); // KONDISI 2: MERAH
                    }
                }
            );
            
            document.getElementById('camera-dot').className = "w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse";
            document.getElementById('camera-text').innerText = "ONLINE";
            document.getElementById('debug-status').innerText = "Status: Siap Scan Barcode/QR";
        } catch (err) {
            document.getElementById('debug-status').innerText = "Status: Akses Kamera Ditolak";
        }
    }

    /**
     * Logic Warna Frame:
     * 'standby' -> Putih (Default)
     * 'detected' -> Merah (Ada QR tapi posisi belum pas)
     * 'success' -> Hijau (Berhasil scan)
     */
    function updateUIStatus(status) {
        const frame = document.getElementById('scan-frame');
        const corners = document.querySelectorAll('.corner-guide');
        
        if(status === 'success') {
            frame.style.borderColor = '#22c55e';
            corners.forEach(c => c.style.borderColor = '#22c55e');
        } else if (status === 'detected') {
            frame.style.borderColor = '#ef4444';
            corners.forEach(c => c.style.borderColor = '#ef4444');
        } else {
            frame.style.borderColor = 'rgba(255,255,255,0.3)';
            corners.forEach(c => c.style.borderColor = '#ffffff');
        }
    }

    function submitAbsensi(nip, mode) {
        document.getElementById('debug-status').innerText = "Status: Memproses " + nip + "...";

        fetch("{{ route('absensi.submit') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "X-CSRF-TOKEN": "{{ csrf_token() }}" 
            },
            body: JSON.stringify({ nip: nip, tipe: mode })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('beep-success').play();
                showResult(data.data, mode);
                addToLog(data.data, mode);
                document.getElementById('manual-nip').value = '';
                setTimeout(() => { isProcessing = false; }, 3000);
            } else {
                document.getElementById('beep-error').play();
                Swal.fire({ 
                    icon: data.status === 'warning' ? 'warning' : 'error', 
                    title: 'Oops!', 
                    text: data.message 
                }).then(() => { isProcessing = false; });
            }
        })
        .catch(e => {
            Swal.fire('Error', 'Gagal terhubung ke server', 'error');
            isProcessing = false;
        })
        .finally(() => {
            document.getElementById('debug-status').innerText = "Status: Siap Scan Kembali";
        });
    }

    function submitManual() {
        const nipVal = document.getElementById('manual-nip').value;
        if(!nipVal) return;
        submitAbsensi(nipVal, currentMode);
    }

    function showResult(data, mode) {
        const c = document.getElementById('last-scan-card');
        c.classList.remove('hidden');
        document.getElementById('res-nama').innerText = data.nama;
        document.getElementById('res-nip').innerText = data.nip;
        document.getElementById('res-waktu').innerText = data.waktu;
        document.getElementById('res-tipe').innerText = mode.toUpperCase();
        
        if(mode === 'keluar') {
            document.getElementById('res-bg-accent').className = "absolute top-0 left-0 w-full h-2 bg-orange-500";
            document.getElementById('res-icon-bg').className = "w-20 h-20 bg-orange-50 rounded-3xl flex items-center justify-center mx-auto mb-4";
            document.getElementById('res-icon').className = "fas fa-door-open text-3xl text-orange-500";
            document.getElementById('res-tipe').className = "text-xs font-black text-orange-600 uppercase";
        } else {
            document.getElementById('res-bg-accent').className = "absolute top-0 left-0 w-full h-2 bg-green-500";
            document.getElementById('res-icon-bg').className = "w-20 h-20 bg-green-50 rounded-3xl flex items-center justify-center mx-auto mb-4";
            document.getElementById('res-icon').className = "fas fa-check text-3xl text-green-500";
            document.getElementById('res-tipe').className = "text-xs font-black text-green-600 uppercase";
        }
    }

    function addToLog(data, mode) {
        const container = document.getElementById('recent-logs');
        if (container.innerHTML.includes('Belum ada')) container.innerHTML = '';
        
        const html = `
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-[1.5rem] border border-slate-100 animate-slide-in">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white shadow-sm rounded-xl flex items-center justify-center text-xs font-black text-cdi">${data.nama.charAt(0)}</div>
                    <div>
                        <p class="text-[10px] font-black text-cdi uppercase leading-none mb-1">${data.nama}</p>
                        <p class="text-[8px] font-bold text-slate-400 uppercase">${data.waktu}</p>
                    </div>
                </div>
                <span class="text-[8px] font-black px-2 py-1 rounded-lg ${mode === 'masuk' ? 'bg-green-100 text-green-600' : 'bg-orange-100 text-orange-600'} uppercase">${mode}</span>
            </div>`;
        container.insertAdjacentHTML('afterbegin', html);
    }

    document.addEventListener("DOMContentLoaded", startCamera);
</script>

<style>
    #reader video {
        width: 100% !important; height: 100% !important;
        object-fit: cover !important; border-radius: 2.5rem !important;
    }
    #reader { border: none !important; }
    
    .scanner-line {
        position: absolute; width: 100%; height: 4px;
        background: #22c55e; box-shadow: 0 0 20px #22c55e;
        top: 0; animation: scan 2.5s ease-in-out infinite;
        z-index: 60;
    }

    #scan-frame { animation: pulse-frame 2s infinite; }

    @keyframes pulse-frame {
        0% { transform: scale(1); opacity: 0.8; }
        50% { transform: scale(1.02); opacity: 1; }
        100% { transform: scale(1); opacity: 0.8; }
    }

    @keyframes scan { 0% { top: 0%; } 50% { top: 100%; } 100% { top: 0%; } }
    .animate-slide-in { animation: slideIn 0.5s ease-out forwards; }
    @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush
@endsection