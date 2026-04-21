@extends('layouts.guest')

@section('content')
<div x-data="loginPage({ waNumber: '6285168112098' })" class="w-full max-w-md animate-fade-in">
    {{-- Logo --}}
    <div class="text-center mb-10">
        <div class="w-16 h-16 rounded-2xl bg-primary flex items-center justify-center text-on-primary mx-auto mb-4 shadow-xl shadow-primary/20">
            <x-icon name="architecture" class="w-8 h-8" />
        </div>
        <h1 class="text-2xl font-extrabold text-on-surface font-headline tracking-tight">E-REPORT</h1>
        <p class="text-sm text-on-surface-variant mt-1">Platform CRM Desain Interior</p>
    </div>

    {{-- Login Card --}}
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl shadow-black/5 p-8 space-y-6">
        <div>
            <h2 class="text-lg font-bold font-headline text-on-surface">Selamat Datang</h2>
            <p class="text-sm text-on-surface-variant mt-1">Masuk ke dashboard CRM Anda</p>
        </div>

        @if($errors->any())
        <div class="bg-error/10 text-error px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
            <x-icon name="error" class="w-[18px] h-[18px]" />
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Alamat Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-outline-variant">
                        <x-icon name="mail" class="w-[18px] h-[18px]" />
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="w-full bg-surface-container-high border-0 rounded-xl pl-10 pr-4 py-3 text-sm text-on-surface placeholder:text-outline-variant/60 focus:ring-2 focus:ring-primary/20 transition-all cursor-text text-left"
                           placeholder="Masukkan alamat email" autocomplete="email" required autofocus />
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">Kata Sandi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-outline-variant">
                        <x-icon name="lock" class="w-[18px] h-[18px]" />
                    </span>
                    <input type="password" id="password" name="password"
                           class="w-full bg-surface-container-high border-0 rounded-xl pl-10 pr-4 py-3 text-sm text-on-surface placeholder:text-outline-variant/60 focus:ring-2 focus:ring-primary/20 transition-all cursor-text text-left"
                           placeholder="Masukkan password" autocomplete="current-password" required />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/20">
                    <span class="text-xs text-on-surface-variant">Ingat saya</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full bg-primary text-on-primary py-3.5 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:bg-primary-dim transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                <x-icon name="login" class="w-[18px] h-[18px]" />
                Masuk
            </button>
        </form>
    </div>

    {{-- Bug Report Button --}}
    <div class="mt-8 text-center relative z-10">
        <button type="button" @click="showBugModal = true" class="inline-flex items-center gap-1.5 text-xs font-bold text-on-surface-variant hover:text-error transition-colors uppercase tracking-widest bg-surface-container-lowest/50 px-4 py-2 rounded-full shadow-sm">
            <x-icon name="bug_report" class="w-4 h-4" />
            Lapor Bug / Error Aplikasi
        </button>
    </div>

    {{-- Bug Report Modal --}}
    <template x-teleport="body">
        <div x-show="showBugModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-inverse-surface/60 backdrop-blur-sm"
             x-transition.opacity.duration.300ms>
            <div @click.away="showBugModal = false" class="bg-surface-container-lowest w-full max-w-sm rounded-2xl shadow-2xl animate-fade-in overflow-hidden">
                <div class="bg-error/10 px-6 py-4 flex items-center justify-between border-b border-error/10">
                    <div class="flex items-center gap-2 text-error">
                        <x-icon name="warning" class="w-5 h-5" />
                        <h3 class="font-bold font-headline">Lapor Kendala Aplikasi</h3>
                    </div>
                    <button @click="showBugModal = false" class="text-error/60 hover:text-error transition-colors">
                        <x-icon name="close" class="w-[18px] h-[18px]" />
                    </button>
                </div>
                
                <div class="p-6">
                    <p class="text-xs text-on-surface-variant mb-4">Tim Database kami siap membantu! Ceritakan kronologi error atau bug yang Anda temukan secara detail di bawah ini.</p>
                    
                    <div class="space-y-4">
                        <textarea x-model="bugMessage" rows="5" 
                                  class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-error/30 resize-none shadow-inner placeholder:text-outline-variant/60 font-medium"
                                  placeholder="Contoh: Saya tidak bisa login, muncul tulisan 'Akses ditolak' padahal password sudah benar..."></textarea>
                                  
                        <button type="button" 
                                @click="submitBugReport()"
                                class="w-full bg-[#25D366] text-white py-3.5 rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-[#25D366]/20 hover:bg-[#1DA851] transition-all hover:scale-[1.02] active:scale-[0.98]">
                            <x-icon name="chat" class="w-[18px] h-[18px]" />
                            <span>Kirim ke WhatsApp</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
