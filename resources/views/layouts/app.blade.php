<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDI-Staff - @yield('title')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,700;1,800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }

        [x-cloak] {
            display: none !important;
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
    </style>
</head>

<body class="bg-slate-50 text-slate-900" x-data="{ sidebarOpen: true }">

    <div class="flex min-h-screen">
        <aside
            :class="sidebarOpen ? 'w-72' : 'w-20'"
            class="bg-cdi text-white transition-all duration-300 flex flex-col shadow-xl z-50 fixed inset-y-0 left-0 lg:relative">

            <div class="p-6 flex items-center justify-between border-b border-white/10 overflow-hidden">
                <div class="flex items-center space-x-3" x-show="sidebarOpen">
                    <div class="w-8 h-8 bg-cdi-orange rounded-lg flex items-center justify-center shadow-lg shadow-orange-500/20">
                        <i class="fas fa-id-badge text-white"></i>
                    </div>
                    <span class="font-black text-xl italic tracking-tighter uppercase">Campus<span class="text-cdi-orange">-STAFF</span></span>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="hover:text-cdi-orange transition-colors mx-auto lg:mx-0">
                    <i class="fas" :class="sidebarOpen ? 'fa-chevron-left' : 'fa-bars'"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto pt-4 px-4 space-y-2">
                @include('layouts.navigation')
            </div>

            <div class="p-4 border-t border-white/10">
                <div x-show="sidebarOpen" class="mb-4 px-2">
                    <p class="text-[9px] text-white/30 font-bold uppercase tracking-[0.3em] italic">Versi Sistem 1.0.4</p>
                </div>

                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="flex items-center space-x-4 p-4 text-white/60 hover:text-white hover:bg-red-500/20 rounded-2xl transition-all group">
                    <i class="fas fa-sign-out-alt group-hover:text-red-400"></i>
                    <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest">Keluar Sistem</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 bg-white">
            <header class="h-20 bg-white border-b flex items-center justify-between px-8 shadow-sm">
                <div class="flex items-center space-x-4">
                    <h2 class="font-black text-slate-700 uppercase italic tracking-tighter text-lg">
                        @yield('page_title', 'Dashboard')
                    </h2>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-black text-cdi italic uppercase leading-none">{{ Auth::user()->name }}</p>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ Auth::user()->role }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-50 border-2 border-slate-100 flex items-center justify-center text-cdi shadow-inner">
                        <i class="fas fa-user-shield text-sm"></i>
                    </div>
                </div>
            </header>

            <main class="p-8 flex-1 overflow-y-auto bg-slate-50/50">
                @if(session('success'))
                <div class="mb-6 p-4 bg-green-500 text-white rounded-2xl font-bold text-xs uppercase italic tracking-widest shadow-lg shadow-green-500/20">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>