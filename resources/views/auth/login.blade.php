<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CDI Staff Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,700;1,800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }

        .bg-cdi {
            background-color: #003366;
        }

        .text-cdi {
            color: #003366;
        }

        .bg-cdi-orange {
            background-color: #FF8C00;
        }

        .text-cdi-orange {
            color: #FF8C00;
        }

        input::placeholder {
            font-style: italic;
            opacity: 0.5;
        }

        /* Animasi Modal */
        .modal-enter {
            animation: modalFadeIn 0.3s ease-out forwards;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>

<body class="bg-slate-50">

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-[1000px] w-full bg-white rounded-[3.5rem] shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">

            <div class="bg-cdi p-12 hidden md:flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-cdi-orange/20 rounded-full -mr-20 -mt-20 blur-3xl"></div>

                <div class="relative z-10">
                    <div class="flex items-center space-x-2 bg-white/10 w-fit px-4 py-2 rounded-full border border-white/20">
                        <span class="text-white font-black italic tracking-tighter text-sm uppercase">Campus<span class="text-white/50">-STAFF</span></span>
                    </div>
                </div>

                <div class="relative z-10">
                    <h1 class="text-5xl font-black text-white italic uppercase tracking-tighter leading-none">
                        Identity <br> <span class="text-cdi-orange text-6xl">System.</span>
                    </h1>
                    <p class="text-white/50 font-bold uppercase tracking-[0.3em] text-[10px] mt-6 leading-relaxed">
                        Manajemen kehadiran dan data karyawan <br> PT Campus Digital Indonesia.
                    </p>
                </div>

                <div class="relative z-10 flex items-center space-x-4 text-white/40 text-xs font-bold uppercase tracking-widest">
                    <span>v2.0.1</span>
                    <span class="w-1 h-1 bg-white/20 rounded-full"></span>
                    <span>Secure Encryption</span>
                </div>
            </div>

            <div class="p-12 md:p-20 flex flex-col justify-center">
                <div class="mb-10 text-center md:text-left">
                    <h2 class="text-3xl font-black text-cdi uppercase italic tracking-tighter">Selamat <span class="text-cdi-orange">Datang.</span></h2>
                    <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2">Gunakan kredensial akun Anda untuk masuk</p>
                </div>

                @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                    @foreach ($errors->all() as $error)
                    <p class="text-red-600 text-[11px] font-bold uppercase italic">{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl text-green-600 text-[11px] font-bold uppercase italic">
                    {{ session('success') }}
                </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Username Personel</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-6 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="text" name="username" value="{{ old('username') }}" required placeholder="Masukkan username Anda"
                                class="w-full bg-slate-50 border-2 border-transparent py-4 pl-14 pr-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Security Password</label>
                            <button type="button" onclick="openForgotModal()" class="text-[9px] font-black text-cdi-orange uppercase tracking-widest hover:underline outline-none">Lupa?</button>
                        </div>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-6 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="password" name="password" id="password" required placeholder="••••••••"
                                class="w-full bg-slate-50 border-2 border-transparent py-4 pl-14 pr-14 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi-orange outline-none transition-all">
                            <button type="button" onclick="togglePassword()" class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 hover:text-cdi">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center px-4">
                        <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-slate-300 text-cdi focus:ring-cdi">
                        <label for="remember" class="ml-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest cursor-pointer">Ingat Perangkat Ini</label>
                    </div>

                    <button type="submit" class="w-full bg-cdi text-white py-5 rounded-[1.5rem] font-black uppercase italic tracking-widest text-sm shadow-xl shadow-blue-900/20 hover:bg-cdi-orange hover:-translate-y-1 transition-all">
                        Masuk Sekarang <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <div class="mt-12 text-center">
                    <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">
                        Belum punya akun? <a href="{{ route('register') }}" class="text-cdi font-black hover:text-cdi-orange transition-colors">Daftar Personel Baru</a>
                    </p>
                </div>
            </div>

        </div>
    </div>

    <div id="forgotModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl relative modal-enter overflow-hidden">
            <button onclick="closeForgotModal()" class="absolute top-6 right-6 text-slate-300 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>

            <div id="stepChoice">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-black text-cdi uppercase italic tracking-tighter">Pemulihan <span class="text-cdi-orange">Akun.</span></h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pilih data yang ingin Anda pulihkan</p>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <button onclick="setForgotType('username')" class="group flex items-center p-5 border-2 border-slate-50 rounded-2xl hover:border-cdi transition-all text-left bg-slate-50 hover:bg-white">
                        <div class="w-12 h-12 bg-white group-hover:bg-cdi flex items-center justify-center rounded-xl shadow-sm transition-all">
                            <i class="fas fa-id-badge text-cdi group-hover:text-white transition-all"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-black text-cdi uppercase italic">Lupa Username</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Kirim username ke email Anda</p>
                        </div>
                    </button>
                    <button onclick="setForgotType('password')" class="group flex items-center p-5 border-2 border-slate-50 rounded-2xl hover:border-cdi-orange transition-all text-left bg-slate-50 hover:bg-white">
                        <div class="w-12 h-12 bg-white group-hover:bg-cdi-orange flex items-center justify-center rounded-xl shadow-sm transition-all">
                            <i class="fas fa-key text-cdi-orange group-hover:text-white transition-all"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-black text-cdi-orange uppercase italic">Lupa Password</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Atur ulang password dengan OTP</p>
                        </div>
                    </button>
                </div>
            </div>

            <div id="stepEmail" class="hidden">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-black text-cdi uppercase italic tracking-tighter">Verifikasi <span class="text-cdi-orange">Email.</span></h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Masukkan email terdaftar Anda</p>
                </div>
                <div class="space-y-4">
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-6 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        <input type="email" id="forgotEmail" placeholder="Masukkan email terdaftar"
                            class="w-full bg-slate-50 border-2 border-transparent py-4 pl-14 pr-6 rounded-2xl font-bold text-sm text-cdi focus:bg-white focus:border-cdi outline-none transition-all">
                    </div>
                    <button onclick="submitForgotEmail()" id="btnSendEmail" class="w-full bg-cdi text-white py-4 rounded-2xl font-black uppercase italic tracking-widest text-xs shadow-lg hover:bg-cdi-orange transition-all">
                        Lanjutkan Verifikasi
                    </button>
                </div>
            </div>

            <div id="stepOtp" class="hidden">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-black text-cdi uppercase italic tracking-tighter">Input <span class="text-cdi-orange">OTP.</span></h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Cek kotak masuk email Anda</p>
                </div>
                <div class="space-y-4">
                    <input type="text" id="resetOtp" maxlength="6" placeholder="000000"
                        class="w-full text-center text-3xl font-black tracking-[0.5em] py-5 bg-slate-50 border-2 border-transparent rounded-2xl focus:border-cdi-orange outline-none text-cdi">
                    <button onclick="verifyOtp()" id="btnVerifyOtp" class="w-full bg-cdi-orange text-white py-4 rounded-2xl font-black uppercase italic tracking-widest text-xs shadow-lg transition-all">
                        Verifikasi Kode
                    </button>
                </div>
            </div>

            <div id="stepNewPass" class="hidden">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-black text-cdi uppercase italic tracking-tighter">Password <span class="text-cdi-orange">Baru.</span></h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Amankan akun Anda kembali</p>
                </div>
                <div class="space-y-3">
                    <input type="password" id="newPass" placeholder="Password Baru" class="w-full bg-slate-50 py-4 px-6 rounded-2xl font-bold text-sm outline-none border-2 border-transparent focus:border-cdi">
                    <input type="password" id="confirmPass" placeholder="Konfirmasi Password Baru" class="w-full bg-slate-50 py-4 px-6 rounded-2xl font-bold text-sm outline-none border-2 border-transparent focus:border-cdi">
                    <button onclick="finalizeReset()" class="w-full bg-cdi text-white py-4 rounded-2xl font-black uppercase italic tracking-widest text-xs shadow-lg hover:bg-cdi-orange transition-all mt-4">
                        Perbarui Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- FITUR LOGIN ASLI ---
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // --- FITUR LUPA USERNAME/PASSWORD (MODAL) ---
        let currentType = '';
        let currentEmail = '';

        function openForgotModal() {
            document.getElementById('forgotModal').classList.remove('hidden');
        }

        function closeForgotModal() {
            document.getElementById('forgotModal').classList.add('hidden');
            // Reset modal state
            document.getElementById('stepChoice').classList.remove('hidden');
            document.getElementById('stepEmail').classList.add('hidden');
            document.getElementById('stepOtp').classList.add('hidden');
            document.getElementById('stepNewPass').classList.add('hidden');
        }

        function setForgotType(type) {
            currentType = type;
            document.getElementById('stepChoice').classList.add('hidden');
            document.getElementById('stepEmail').classList.remove('hidden');
        }

        async function submitForgotEmail() {
            currentEmail = document.getElementById('forgotEmail').value;
            if (!currentEmail) return alert('Email wajib diisi!');

            const btn = document.getElementById('btnSendEmail');
            btn.disabled = true;
            btn.innerText = 'Memproses...';

            try {
                const response = await fetch("{{ route('forgot.fetch') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email: currentEmail,
                        type: currentType
                    })
                });
                const data = await response.json();

                if (data.success) {
                    if (currentType === 'username') {
                        alert('Username telah dikirim ke email Anda. Silakan cek inbox.');
                        closeForgotModal();
                    } else {
                        document.getElementById('stepEmail').classList.add('hidden');
                        document.getElementById('stepOtp').classList.remove('hidden');
                    }
                } else {
                    alert(data.message);
                }
            } catch (err) {
                alert('Terjadi kesalahan sistem.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Lanjutkan Verifikasi';
            }
        }

        async function verifyOtp() {
            const otp = document.getElementById('resetOtp').value;
            if (otp.length < 6) return alert('Masukkan 6 digit kode!');

            const response = await fetch("{{ route('forgot.verify.otp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    email: currentEmail,
                    otp: otp
                })
            });
            const data = await response.json();

            if (data.success) {
                document.getElementById('stepOtp').classList.add('hidden');
                document.getElementById('stepNewPass').classList.remove('hidden');
            } else {
                alert(data.message);
            }
        }

        async function finalizeReset() {
            const pass = document.getElementById('newPass').value;
            const confirm = document.getElementById('confirmPass').value;

            if (pass !== confirm) return alert('Konfirmasi password tidak cocok!');

            const response = await fetch("{{ route('forgot.reset.final') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    email: currentEmail,
                    password: pass,
                    password_confirmation: confirm
                })
            });
            const data = await response.json();

            if (data.success) {
                alert('Password berhasil diubah. Silakan login.');
                location.reload();
            } else {
                alert('Gagal: Password minimal 8 karakter.');
            }
        }
    </script>

</body>

</html>