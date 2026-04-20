@php
    $user = auth()->user();
@endphp

<header x-data="notificationBadge({{ $initialTotalAlerts }}, '{{ route('api.notifications') }}', '{{ csrf_token() }}')"
        x-init="startPolling()"
        class="sticky top-0 w-full z-30 bg-white/90 backdrop-blur-lg flex justify-between items-center px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
    <div class="flex items-center gap-3 sm:gap-4">
        {{-- Hamburger Menu --}}
        <button @click="sidebarOpen = !sidebarOpen" class="w-10 h-10 rounded-xl flex items-center justify-center hover:bg-surface-container/50 transition-colors text-on-surface-variant shrink-0 relative z-40">
            <x-icon name="menu" class="w-5 h-5" />
        </button>

        @if($user->isAdmin() && $user->account)
            <div class="flex items-center gap-2 sm:gap-3">
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mt-0.5 hidden sm:inline">AKUN :</span>
                <div class="bg-surface-container-high px-2 sm:px-3 py-1.5 rounded-lg flex items-center gap-2 shadow-sm border border-outline-variant/10">
                    <div class="w-2 h-2 rounded-full bg-primary animate-pulse-soft"></div>
                    <span class="font-bold text-slate-800 text-xs tracking-wide truncate max-w-[120px] sm:max-w-none leading-none mt-0.5">{{ $user->account->name }}</span>
                </div>
            </div>
        @else
            <span class="text-base sm:text-lg font-extrabold text-slate-800 font-headline leading-none mt-0.5 tracking-tight">E-REPORT</span>
        @endif
    </div>

    <div class="flex items-center gap-3 sm:gap-6">
        <div class="flex items-center gap-2 sm:gap-4 text-on-surface-variant">
            {{-- Notifications / Reminders Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="relative opacity-80 hover:text-indigo-500 transition-colors">
                    <x-icon name="notifications" class="w-6 h-6" />
                    <span x-show="badgeCount > 0" x-cloak class="absolute -top-1 -right-1 w-4 h-4 bg-error text-on-error rounded-full text-[9px] font-bold flex items-center justify-center border border-white"
                          x-text="badgeCount"></span>
                </button>

                {{-- Dropdown Panel --}}
                <div x-show="open" x-transition.opacity x-cloak class="absolute right-0 mt-3 w-72 sm:w-80 bg-surface-container-lowest rounded-2xl shadow-xl border border-surface-container-low overflow-hidden focus:outline-none">
                    <div class="p-4 bg-surface-container-low/50 border-b border-surface-container">
                        <span class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Notifikasi & Chat</span>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        {{-- New Chat Notes Section --}}
                        @if($unreadNotes->count() > 0)
                        <div class="bg-primary/5 px-4 py-2 border-b border-primary/10">
                            <span class="text-[10px] font-bold text-primary uppercase tracking-wider">Chat / Catatan Baru</span>
                        </div>
                        @foreach($unreadNotes as $note)
                        <a href="{{ route('consultations.show', $note->consultation_id) }}" class="block p-4 border-b border-surface-container-low hover:bg-primary/[0.02] transition-colors group">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center text-primary font-bold text-[10px] shrink-0">
                                    {{ strtoupper(substr($note->user->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-on-surface truncate">{{ $note->user->name }} mencatat:</p>
                                    <p class="text-xs text-on-surface-variant line-clamp-1 mt-0.5 mt-1">"{{ $note->body }}"</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 bg-surface-container rounded text-on-surface-variant">{{ $note->consultation->client_name }}</span>
                                        <span class="text-[9px] text-outline-variant">{{ $note->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endforeach
                        @endif

                        {{-- Reminders Section --}}
                        <div class="bg-surface-container-low/30 px-4 py-2 border-b border-surface-container-low">
                            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">To-Do / Pengingat</span>
                        </div>
                        @forelse($activeReminders as $reminder)
                        <div class="p-4 border-b border-surface-container-low hover:bg-surface-container-low/30 transition-colors group">
                            <a href="{{ route('consultations.show', $reminder->consultation_id) }}" class="block">
                                <p class="text-sm font-semibold text-on-surface group-hover:text-primary transition-colors">{{ $reminder->message }}</p>
                                <p class="text-[10px] text-on-surface-variant mt-1 flex items-center gap-1">
                                    <x-icon name="schedule" class="w-3 h-3 {{ $reminder->remind_at < now() ? 'text-error' : '' }}" />
                                    <span class="{{ $reminder->remind_at < now() ? 'text-error font-bold' : '' }}">
                                        {{ $reminder->remind_at->diffForHumans() }} ({{ $reminder->remind_at->format('d M H:i') }})
                                    </span>
                                </p>
                            </a>
                            <div class="mt-2 flex justify-between items-center">
                                <span class="text-[10px] font-medium text-outline-variant">{{ $reminder->consultation->client_name }}</span>
                                <form method="POST" action="{{ route('reminders.read', $reminder) }}">
                                    @csrf
                                    <button type="submit" class="text-[10px] bg-primary/10 text-primary px-2 py-1 rounded font-bold hover:bg-primary/20 transition-colors">Tandai Selesai</button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="p-6 text-center">
                            <x-icon name="task" class="w-8 h-8 text-outline-variant/40 mx-auto mb-2" />
                            <p class="text-xs text-on-surface-variant font-medium">Yeay! Tidak ada tugas yang tertunda.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <button class="opacity-80 hover:text-indigo-500 transition-colors hidden sm:block">
                <x-icon name="help_outline" class="w-6 h-6" />
            </button>
        </div>
        <div class="h-6 w-px bg-outline-variant/30 hidden sm:block"></div>
        <div x-data="{ userMenu: false }" class="relative flex items-center gap-2 sm:gap-3 pl-0 sm:pl-2">
            <button @click="userMenu = !userMenu" @click.away="userMenu = false" class="flex items-center gap-2 sm:gap-3 hover:opacity-80 transition-all focus:outline-none">
                <div class="text-right hidden sm:flex flex-col justify-center">
                    <p class="text-xs font-bold text-slate-800 leading-tight">{{ $user->name }}</p>
                    <p class="text-[9px] text-on-surface-variant uppercase tracking-widest leading-tight">{{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin Akun' }}</p>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-primary-container flex items-center justify-center text-primary font-bold text-sm ring-2 ring-surface-container-highest shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            </button>

            {{-- Profile Dropdown --}}
            <div x-show="userMenu" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 x-cloak
                 class="absolute right-0 top-full mt-3 w-56 bg-surface-container-lowest rounded-2xl shadow-xl border border-surface-container-low overflow-hidden z-50">
                
                {{-- User Info Header (Mobile Only) --}}
                <div class="p-4 bg-surface-container-low/50 border-b border-surface-container sm:hidden">
                    <p class="text-xs font-bold text-on-surface">{{ $user->name }}</p>
                    <p class="text-[9px] text-on-surface-variant uppercase tracking-wider mt-0.5">{{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin Akun' }}</p>
                </div>

                <div class="py-2">
                    <a href="{{ route('settings') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-on-surface-variant hover:bg-surface-container-low hover:text-primary transition-colors">
                        <x-icon name="settings" class="w-[18px] h-[18px]" />
                        <span>Pengaturan Akun</span>
                    </a>
                    
                    <div class="my-2 border-t border-surface-container-low"></div>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-error hover:bg-error/5 transition-colors">
                            <x-icon name="logout" class="w-[18px] h-[18px]" />
                            <span>Keluar Sistem</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function notificationBadge(initialCount, apiUrl, csrfToken) {
    return {
        badgeCount: initialCount,
        startPolling() {
            setInterval(() => {
                fetch(apiUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(res => res.ok ? res.json() : Promise.reject(res))
                .then(data => { this.badgeCount = data.total || 0; })
                .catch(err => console.error('Polling error:', err));
            }, 15000);
        }
    };
}
</script>
