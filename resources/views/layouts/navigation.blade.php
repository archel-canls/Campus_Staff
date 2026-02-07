<nav class="space-y-2">
    @if(Auth::check() && Auth::user()->role === 'admin')
        {{-- SECTION: ADMIN --}}
        <div class="px-4 py-4" x-show="sidebarOpen">
            <span class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em] italic">Menu Utama Admin</span>
        </div>

        {{-- Dashboard Admin --}}
        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('admin.dashboard') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-th-large w-6 text-center text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest">Overview</span>
        </a>

        {{-- Manajemen Karyawan --}}
        <a href="{{ route('manajemen-karyawan.index') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('manajemen-karyawan.*') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-users w-6 text-center text-sm {{ request()->routeIs('manajemen-karyawan.*') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest">Data Personel</span>
        </a>

        {{-- NEW: Manajemen Divisi --}}
        <a href="{{ route('divisi.index') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('divisi.*') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-sitemap w-6 text-center text-sm {{ request()->routeIs('divisi.*') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest">Struktur Divisi</span>
        </a>

        <div class="px-4 py-4 mt-4" x-show="sidebarOpen">
            <span class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em] italic">Sistem Absensi</span>
        </div>

        {{-- Scanner Barcode --}}
        <a href="{{ route('absensi.scan') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('absensi.scan') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <div class="relative">
                <i class="fas fa-qrcode w-6 text-center text-sm {{ request()->routeIs('absensi.scan') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
                @if(request()->routeIs('absensi.scan'))
                    <span class="absolute -top-1 -right-1 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                    </span>
                @endif
            </div>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest text-nowrap">Scanner Barcode</span>
        </a>

        {{-- Log Kehadiran --}}
        <a href="{{ route('absensi.riwayat') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('absensi.riwayat') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-history w-6 text-center text-sm {{ request()->routeIs('absensi.riwayat') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest text-nowrap">Log Kehadiran</span>
        </a>

        {{-- Rekap Laporan --}}
        <a href="{{ route('absensi.laporan') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('absensi.laporan') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-file-invoice w-6 text-center text-sm {{ request()->routeIs('absensi.laporan') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest text-nowrap">Rekap Laporan</span>
        </a>

        <div class="px-4 py-4 mt-4" x-show="sidebarOpen">
            <span class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em] italic">Administrasi</span>
        </div>

        {{-- Payroll Gaji --}}
        <a href="{{ route('payroll.index') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('payroll.*') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-wallet w-6 text-center text-sm {{ request()->routeIs('payroll.*') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest text-nowrap">Payroll Gaji</span>
        </a>

    @elseif(Auth::check() && Auth::user()->role === 'karyawan')
        {{-- SECTION: KARYAWAN --}}
        <div class="px-4 py-4" x-show="sidebarOpen">
            <span class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em] italic">Menu Personel</span>
        </div>

        {{-- Profil Karyawan --}}
        <a href="{{ route('karyawan.dashboard') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('karyawan.dashboard') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-user-circle w-6 text-center text-sm {{ request()->routeIs('karyawan.dashboard') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest text-nowrap">Dashboard</span>
        </a>

        {{-- ID Card --}}
        <a href="{{ route('karyawan.id-card') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('karyawan.id-card') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-id-badge w-6 text-center text-sm {{ request()->routeIs('karyawan.id-card') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest text-nowrap">Profil dan ID Card</span>
        </a>

        <div class="px-4 py-4 mt-4" x-show="sidebarOpen">
            <span class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em] italic">Kehadiran & Status</span>
        </div>

        {{-- Absensi Saya --}}
        <a href="{{ route('karyawan.absensi') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('karyawan.absensi') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-calendar-check w-6 text-center text-sm {{ request()->routeIs('karyawan.absensi') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest text-nowrap">Absensi Saya</span>
        </a>

        {{-- Perizinan --}}
        <a href="{{ route('karyawan.perizinan') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('karyawan.perizinan') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-envelope-open-text w-6 text-center text-sm {{ request()->routeIs('karyawan.perizinan') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest text-nowrap">Perizinan</span>
        </a>

        {{-- Info Jabatan --}}
        <a href="{{ route('karyawan.jabatan') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('karyawan.jabatan') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-briefcase w-6 text-center text-sm {{ request()->routeIs('karyawan.jabatan') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest text-nowrap">Info Jabatan</span>
        </a>

        {{-- Slip Gaji --}}
        <a href="{{ route('karyawan.slip-gaji') }}" 
           class="flex items-center space-x-4 p-4 rounded-[1.5rem] transition-all duration-300 group {{ request()->routeIs('karyawan.slip-gaji') ? 'bg-cdi-orange text-white shadow-lg shadow-orange-500/20' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-wallet w-6 text-center text-sm {{ request()->routeIs('karyawan.slip-gaji') ? 'text-white' : 'group-hover:text-cdi-orange' }}"></i>
            <span x-show="sidebarOpen" class="font-black uppercase italic text-[10px] tracking-widest text-nowrap">Slip Gaji</span>
        </a>
    @endif
</nav>