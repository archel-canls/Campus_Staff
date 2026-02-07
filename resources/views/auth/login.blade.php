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
        body { font-family: 'Instrument Sans', sans-serif; }
        .bg-cdi { background-color: #003366; }
        .text-cdi { color: #003366; }
        .bg-cdi-orange { background-color: #FF8C00; }
        .text-cdi-orange { color: #FF8C00; }
        input::placeholder { font-style: italic; opacity: 0.5; }
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
                    Manajemen kehadiran dan data personel <br> PT Campus Digital Indonesia.
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
                        <a href="#" class="text-[9px] font-black text-cdi-orange uppercase tracking-widest hover:underline">Lupa?</a>
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

<script>
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
</script>

</body>
</html>