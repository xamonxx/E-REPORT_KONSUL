{{-- Sidebar Component --}}
@php
    $currentRoute = Route::currentRouteName();
    $user = auth()->user();
@endphp

<aside class="glass-sidebar h-screen flex flex-col py-6 w-64 shrink-0 bg-slate-100 lg:bg-slate-100/80 backdrop-blur-md border-r border-slate-200 lg:border-r-0">
    {{-- Close Button for Mobile --}}
    <button @click="sidebarOpen = false" class="lg:hidden absolute top-4 right-4 w-10 h-10 flex items-center justify-center text-on-surface-variant">
        <x-icon name="close" class="w-5 h-5" />
    </button>

    {{-- Logo --}}
    <div class="px-6 mb-10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-on-primary">
                <x-icon name="architecture" class="w-5 h-5" />
            </div>
            <div>
                <h1 class="font-bold text-slate-800 text-sm tracking-tight leading-none font-headline">E-REPORT</h1>
                <p class="text-[10px] text-on-surface-variant uppercase tracking-widest mt-1">Data Konsultasi</p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-4 space-y-1 overflow-y-auto custom-scrollbar">
        <a href="{{ route('dashboard') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors font-headline text-sm tracking-tight
                  {{ $currentRoute === 'dashboard' ? 'text-indigo-600 font-semibold border-r-2 border-indigo-600 bg-white/50' : 'text-slate-500 hover:bg-white/50' }}">
            <x-icon name="dashboard" class="w-5 h-5" />
            <span>Beranda</span>
        </a>

        @if($user->isSuperAdmin())
        <a href="{{ route('accounts.index') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors font-headline text-sm tracking-tight
                  {{ str_starts_with($currentRoute, 'accounts') ? 'text-indigo-600 font-semibold border-r-2 border-indigo-600 bg-white/50' : 'text-slate-500 hover:bg-white/50' }}">
            <x-icon name="folder_shared" class="w-5 h-5" />
            <span>AKUN</span>
        </a>
        @endif

        <a href="{{ route('consultations.index') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors font-headline text-sm tracking-tight
                  {{ str_starts_with($currentRoute, 'consultations') ? 'text-indigo-600 font-semibold border-r-2 border-indigo-600 bg-white/50' : 'text-slate-500 hover:bg-white/50' }}">
            <x-icon name="person_search" class="w-5 h-5" />
            <span>Daftar Lead</span>
        </a>

        <a href="{{ route('analytics') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors font-headline text-sm tracking-tight
                  {{ $currentRoute === 'analytics' ? 'text-indigo-600 font-semibold border-r-2 border-indigo-600 bg-white/50' : 'text-slate-500 hover:bg-white/50' }}">
            <x-icon name="leaderboard" class="w-5 h-5" />
            <span>Analisis</span>
        </a>

        @if($user->isSuperAdmin())
        <a href="{{ route('report-attendances.index') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors font-headline text-sm tracking-tight
                  {{ str_starts_with($currentRoute, 'report-attendances') ? 'text-indigo-600 font-semibold border-r-2 border-indigo-600 bg-white/50' : 'text-slate-500 hover:bg-white/50' }}">
            <x-icon name="assignment_turned_in" class="w-5 h-5" />
            <span>Monitoring Laporan</span>
        </a>
        @endif

        @if($user->isSuperAdmin())
        <a href="{{ route('master-data.index') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors font-headline text-sm tracking-tight
                  {{ str_starts_with($currentRoute, 'master-data') ? 'text-indigo-600 font-semibold border-r-2 border-indigo-600 bg-white/50' : 'text-slate-500 hover:bg-white/50' }}">
            <x-icon name="database" class="w-5 h-5" />
            <span>Master Data</span>
        </a>
        @endif

        <a href="{{ route('settings') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors font-headline text-sm tracking-tight
                  {{ $currentRoute === 'settings' ? 'text-indigo-600 font-semibold border-r-2 border-indigo-600 bg-white/50' : 'text-slate-500 hover:bg-white/50' }}">
            <x-icon name="settings" class="w-5 h-5" />
            <span>Pengaturan</span>
        </a>
    </nav>

    {{-- Bottom Actions --}}
    <div class="px-4 mt-auto space-y-4">
        <a href="{{ route('consultations.create') }}" @click="if(window.innerWidth < 1024) sidebarOpen = false"
           class="w-full bg-primary text-on-primary py-3 rounded-xl text-xs font-semibold shadow-lg shadow-primary/20 hover:scale-95 transition-transform duration-200 active:scale-90 flex items-center justify-center gap-2 text-center">
            <x-icon name="add_circle" class="w-4 h-4" />
            <span class="truncate">Tambah Konsultasi Baru</span>
        </a>
        <div class="pt-4 border-t border-slate-200/50">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-3 py-2 text-slate-500 hover:text-error transition-colors text-sm font-headline w-full text-left">
                    <x-icon name="logout" class="w-5 h-5" />
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
