<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDI - Terminal Presensi Digital v2.5</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,700;1,800&display=swap" rel="stylesheet">

    <style>
        body { 
            font-family: 'Instrument Sans', sans-serif; 
            background-color: #003366; 
            min-height: 100vh;
            background-image: radial-gradient(circle at 2px 2px, rgba(255, 255, 255, 0.05) 1px, transparent 0);
            background-size: 30px 30px;
            color: white;
            overflow-x: hidden;
        }
        
        .bg-cdi { background-color: #003366; }
        .text-cdi { color: #003366; }
        .bg-cdi-orange { background-color: #FF8C00; }
        .text-cdi-orange { color: #FF8C00; }

        .id-card-pattern {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0.05;
            z-index: 0;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 86c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm66-3c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm-46-43c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm0 20c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm40-3c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm0 20c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z' fill='%23ffffff' fill-opacity='1' fill-rule='evenodd'/%3E%3C/svg%3E");
        }

        .glass-panel {
            background: rgba(255, 255, 255, 1);
            border-radius: 3.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 2rem;
        }

        .scanner-line {
            position: absolute; width: 100%; height: 4px;
            background: #FF8C00; box-shadow: 0 0 15px #FF8C00;
            top: 0; animation: scan 3s ease-in-out infinite;
            z-index: 60;
        }

        @keyframes scan { 0% { top: 5%; } 50% { top: 95%; } 100% { top: 5%; } }
        .animate-pop { animation: pop 0.3s ease-out; }
        @keyframes pop { 0% { transform: scale(0.9); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        ::-webkit-scrollbar { width: 0px; }
    </style>
</head>
<body class="flex flex-col items-center py-12 px-6">

    <div class="id-card-pattern"></div>

    {{-- 1. Header Section --}}
    <div class="text-center mb-8 relative z-10">
        <div class="inline-block bg-cdi-orange px-6 py-1.5 rounded-full mb-6 shadow-lg">
            <span class="text-white font-black italic tracking-[0.3em] text-[9px] uppercase">CDI Management System v2.5</span>
        </div>
        <h1 class="text-6xl font-black italic uppercase tracking-tighter leading-none mb-2">
            TERMINAL <span class="text-cdi-orange">PRESENSI</span>
        </h1>
        <div class="flex flex-col items-center">
            <p id="current-clock" class="text-4xl font-black tracking-[0.2em]"></p>
            <p id="current-date" class="text-[11px] font-bold opacity-60 uppercase tracking-[0.4em] mt-1 italic"></p>
        </div>
    </div>

    {{-- 2. Mode Switcher --}}
    <div class="mb-10 relative z-10">
        <div class="bg-black/20 backdrop-blur-xl p-1.5 rounded-full flex space-x-1 border border-white/10">
            <button onclick="setMode('masuk')" id="btn-mode-masuk" 
                class="px-10 py-3 rounded-full font-black uppercase italic text-[11px] tracking-widest transition-all duration-300 bg-white text-cdi shadow-xl">
                <i class="fas fa-sign-in-alt mr-2"></i> Masuk
            </button>
            <button onclick="setMode('keluar')" id="btn-mode-keluar" 
                class="px-10 py-3 rounded-full font-black uppercase italic text-[11px] tracking-widest transition-all duration-300 text-white/50 hover:text-white">
                <i class="fas fa-sign-out-alt mr-2"></i> Keluar
            </button>
        </div>
    </div>

    <div class="w-full max-w-xl space-y-8 relative z-10">
        
        {{-- 3. Main Scanner Panel --}}
        <div class="glass-panel overflow-hidden">
            <div id="status-bar" class="bg-slate-800 text-white px-8 py-4 flex justify-between items-center transition-colors duration-500">
                <div>
                    <h3 id="status-title" class="text-sm font-black italic uppercase tracking-widest">Scanner Masuk</h3>
                    <p class="text-[9px] opacity-70 font-bold uppercase tracking-tighter">Status: Terminal Ready</p>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2 bg-black/30 px-4 py-1.5 rounded-full border border-white/10">
                        <div id="camera-dot" class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-[9px] font-black tracking-widest uppercase">Online</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center transition-all shadow-lg active:scale-90">
                            <i class="fas fa-power-off text-[10px]"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-8 flex flex-col items-center">
                <div class="w-full aspect-square max-w-[340px] bg-slate-900 rounded-[2.5rem] p-2 shadow-2xl relative overflow-hidden mb-8 border-4 border-slate-100">
                    <div id="reader" class="w-full h-full"></div>
                    
                    <div class="absolute inset-0 pointer-events-none z-50 flex items-center justify-center p-8">
                        <div class="w-full h-full relative border-2 border-white/10 rounded-[1.5rem]">
                            <div class="absolute -top-1 -left-1 w-12 h-12 border-t-[6px] border-l-[6px] border-cdi-orange rounded-tl-2xl"></div>
                            <div class="absolute -top-1 -right-1 w-12 h-12 border-t-[6px] border-r-[6px] border-cdi-orange rounded-tr-2xl"></div>
                            <div class="absolute -bottom-1 -left-1 w-12 h-12 border-b-[6px] border-l-[6px] border-cdi-orange rounded-bl-2xl"></div>
                            <div class="absolute -bottom-1 -right-1 w-12 h-12 border-b-[6px] border-r-[6px] border-cdi-orange rounded-br-2xl"></div>
                            <div class="scanner-line"></div>
                        </div>
                    </div>
                    <div class="absolute bottom-4 w-full text-center z-50">
                        <span class="bg-black/50 backdrop-blur px-3 py-1 rounded-full text-[8px] font-bold text-white uppercase tracking-widest">Posisikan Kode QR di Tengah</span>
                    </div>
                </div>

                <div class="w-full flex space-x-2">
                    <input type="text" id="manual-nip" placeholder="ENTER NIP / BARCODE MANUALLY" 
                        class="flex-1 bg-slate-100 border-2 border-slate-100 rounded-2xl px-6 py-4 text-center text-xs font-black text-cdi outline-none focus:border-cdi-orange transition-all uppercase tracking-widest placeholder:text-slate-400">
                    <button onclick="submitManual()" class="bg-cdi-orange hover:bg-orange-600 text-white px-8 rounded-2xl font-black uppercase italic text-[10px] tracking-widest transition-all shadow-lg active:scale-95">
                        Verify
                    </button>
                </div>
            </div>
        </div>

        {{-- 4. Scan Result (Floating Card) --}}
        <div id="last-scan-card" class="hidden animate-pop">
            <div class="bg-white rounded-[2.5rem] p-6 shadow-2xl border-2 border-white flex items-center justify-between">
                <div class="flex items-center space-x-5">
                    <div id="res-icon-bg" class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-inner text-white text-2xl transition-colors duration-500">
                        <i id="res-icon" class="fas fa-check"></i>
                    </div>
                    <div class="text-left">
                        <h4 id="res-nama" class="text-base font-black text-cdi uppercase italic leading-none">Memproses...</h4>
                        <p id="res-nip" class="text-[10px] font-bold text-cdi-orange tracking-[0.2em] mt-1.5 uppercase">-</p>
                        
                        {{-- Lokasi detail sesuai format permintaan --}}
                        <p id="res-lokasi" class="text-[8px] font-bold text-slate-500 uppercase mt-1 leading-tight max-w-[250px]">
                             Menjemput Lokasi...
                        </p>

                        <div class="flex items-center mt-2 space-x-3">
                            <span class="text-[9px] font-black text-slate-400 uppercase"><i class="far fa-clock mr-1"></i> <span id="res-waktu">00:00</span></span>
                            <span id="res-tipe-badge" class="text-[8px] font-black px-2 py-0.5 rounded uppercase">STATUS</span>
                        </div>
                    </div>
                </div>
                <div class="pr-2">
                    <i class="fas fa-id-badge text-slate-100 text-4xl"></i>
                </div>
            </div>
        </div>

        {{-- 5. Live Activity Feed --}}
        <div class="text-center">
            <div class="flex items-center justify-center space-x-4 mb-6">
                <div class="h-[1px] flex-1 bg-white/10"></div>
                <h4 class="text-[10px] font-black text-white/40 uppercase tracking-[0.4em]">Live Activity Feed</h4>
                <div class="h-[1px] flex-1 bg-white/10"></div>
            </div>
            
            <div id="recent-logs" class="space-y-3">
                <p class="text-[10px] font-bold italic text-white/20 uppercase tracking-[0.2em] py-4">Waiting for scan signal...</p>
            </div>
        </div>
    </div>

    <audio id="beep-success" src="https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3" preload="auto"></audio>
    <audio id="beep-error" src="https://assets.mixkit.co/active_storage/sfx/2571/2571-preview.mp3" preload="auto"></audio>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let currentMode = 'masuk';
        let isProcessing = false;
        let html5QrCode = null;

        function updateDateTime() {
            const now = new Date();
            const days = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
            const months = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
            
            document.getElementById('current-date').innerText = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]}`;
            document.getElementById('current-clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        function setMode(mode) {
            currentMode = mode;
            const statusBar = document.getElementById('status-bar');
            const statusTitle = document.getElementById('status-title');
            const btnMasuk = document.getElementById('btn-mode-masuk');
            const btnKeluar = document.getElementById('btn-mode-keluar');

            if (mode === 'masuk') {
                statusBar.style.backgroundColor = '#1e293b'; 
                statusTitle.innerText = 'Scanner Masuk';
                btnMasuk.className = 'px-10 py-3 rounded-full font-black uppercase italic text-[11px] tracking-widest transition-all duration-300 bg-white text-cdi shadow-xl';
                btnKeluar.className = 'px-10 py-3 rounded-full font-black uppercase italic text-[11px] tracking-widest transition-all duration-300 text-white/50 hover:text-white';
            } else {
                statusBar.style.backgroundColor = '#FF8C00'; 
                statusTitle.innerText = 'Scanner Keluar';
                btnKeluar.className = 'px-10 py-3 rounded-full font-black uppercase italic text-[11px] tracking-widest transition-all duration-300 bg-cdi-orange text-white shadow-xl';
                btnMasuk.className = 'px-10 py-3 rounded-full font-black uppercase italic text-[11px] tracking-widest transition-all duration-300 text-white/50 hover:text-white';
            }
        }

        async function startCamera() {
            const config = { fps: 30, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };
            try {
                html5QrCode = new Html5Qrcode("reader");
                await html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
                    if (!isProcessing) {
                        isProcessing = true;
                        submitAbsensi(decodedText, currentMode);
                    }
                });
            } catch (err) {
                console.error("Camera error:", err);
            }
        }

        /**
         * REVERSE GEOCODING TERPERINCI
         * Mengambil Nama Negara sampai ke level Desa/Jalan (Sesuai riwayat blade)
         */
        async function getAddressDetail(lat, lng) {
            if (!lat || !lng) return "Lokasi tidak terdeteksi";
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
                const data = await response.json();
                const addr = data.address;
                
                // Menentukan komponen alamat secara spesifik
                const road = addr.road || addr.suburb || ""; // Jalan atau Area
                const village = addr.village || addr.neighbourhood || addr.hamlet || ""; // Desa/Kelurahan
                const district = addr.city_district || addr.county || ""; // Kecamatan
                const city = addr.city || addr.town || addr.state || ""; // Kota
                const country = addr.country || ""; // Negara

                // Format sesuai permintaan: , Mangunharjo,Tamansari hils, Tembalang, Semarang, Indonesia
                // Kita gabung yang tersedia saja
                const parts = [road, village, district, city, country].filter(part => part !== "");
                return parts.join(", ").toUpperCase();

            } catch (error) {
                return "KOORDINAT: " + lat.toFixed(4) + ", " + lng.toFixed(4);
            }
        }

        function submitAbsensi(nip, mode) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const locationName = await getAddressDetail(lat, lng);
                        sendData(nip, mode, lat, lng, locationName);
                    },
                    (error) => {
                        console.warn("Geolocation failed.");
                        sendData(nip, mode, null, null, "LOKASI OFFLINE/DITOLAK");
                    },
                    { enableHighAccuracy: true, timeout: 5000 }
                );
            } else {
                sendData(nip, mode, null, null, "GEOLOCATION NOT SUPPORTED");
            }
        }

        function sendData(nip, mode, lat, lng, locationName) {
            fetch("{{ route('absensi.submit') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ 
                    nip: nip, 
                    tipe: mode,
                    lat: lat,
                    lng: lng
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('beep-success').play();
                    
                    const resultData = {
                        nama: data.data.nama || 'User',
                        nip: data.data.nip || nip,
                        waktu: data.data.waktu || new Date().toLocaleTimeString('id-ID'),
                        lokasi: locationName
                    };

                    showResult(resultData, mode);
                    addToLog(resultData, mode);
                    document.getElementById('manual-nip').value = '';
                } else {
                    document.getElementById('beep-error').play();
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'VERIFIKASI GAGAL', 
                        text: data.message || 'Data tidak ditemukan', 
                        toast: true, position: 'top', timer: 3000, showConfirmButton: false 
                    });
                }
            })
            .catch(err => {
                console.error("Fetch Error:", err);
                document.getElementById('beep-error').play();
            })
            .finally(() => {
                setTimeout(() => { isProcessing = false; }, 3000);
            });
        }

        function showResult(data, mode) {
            const card = document.getElementById('last-scan-card');
            card.classList.remove('hidden');
            
            document.getElementById('res-nama').innerText = data.nama || 'UNKNOWN';
            document.getElementById('res-nip').innerText = data.nip || '000000';
            document.getElementById('res-waktu').innerText = data.waktu || '--:--';
            document.getElementById('res-lokasi').innerHTML = `<i class="fas fa-location-arrow text-cdi-orange mr-1"></i> ${data.lokasi}`;
            
            const badge = document.getElementById('res-tipe-badge');
            const iconBg = document.getElementById('res-icon-bg');
            const icon = document.getElementById('res-icon');

            if(mode === 'masuk') {
                iconBg.style.backgroundColor = '#22c55e';
                icon.className = 'fas fa-check';
                badge.innerText = 'MASUK';
                badge.className = 'text-[8px] font-black px-2 py-0.5 rounded bg-green-100 text-green-600 uppercase';
            } else {
                iconBg.style.backgroundColor = '#FF8C00';
                icon.className = 'fas fa-sign-out-alt';
                badge.innerText = 'KELUAR';
                badge.className = 'text-[8px] font-black px-2 py-0.5 rounded bg-orange-100 text-orange-600 uppercase';
            }
        }

        function addToLog(data, mode) {
            const container = document.getElementById('recent-logs');
            if (container.innerText.includes('Waiting for')) container.innerHTML = '';
            
            const firstChar = data.nama ? data.nama.charAt(0) : '?';
            const logNama = data.nama || 'Unknown User';
            const logWaktu = data.waktu || '--:--';
            const logLokasi = data.lokasi || '';

            const html = `
                <div class="bg-white/5 border border-white/10 p-4 rounded-2xl flex items-center justify-between animate-pop">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center text-[12px] font-black text-white uppercase">${firstChar}</div>
                        <div class="text-left">
                            <p class="text-[10px] font-black uppercase leading-none text-white">${logNama}</p>
                            <p class="text-[7px] font-bold text-white/40 uppercase mt-1">
                                <i class="fas fa-clock mr-1"></i>${logWaktu} • <i class="fas fa-location-arrow mr-1"></i>${logLokasi}
                            </p>
                        </div>
                    </div>
                    <span class="text-[7px] font-black px-2 py-1 rounded border ${mode === 'masuk' ? 'border-green-500/50 text-green-400' : 'border-orange-500/50 text-orange-400'} uppercase">${mode}</span>
                </div>`;
            container.insertAdjacentHTML('afterbegin', html);
            
            if (container.children.length > 5) {
                container.removeChild(container.lastChild);
            }
        }

        function submitManual() {
            const val = document.getElementById('manual-nip').value;
            if(val) submitAbsensi(val, currentMode);
        }

        document.addEventListener("DOMContentLoaded", startCamera);
    </script>
</body>
</html> 