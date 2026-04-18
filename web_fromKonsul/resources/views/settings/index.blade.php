@extends('layouts.app')
@section('title', 'Settings')

@section('content')
{{-- Page Header --}}
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-2">
    <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight font-headline">Settings</h2>
        <p class="text-on-surface-variant mt-1">Kelola profil dan keamanan akun Anda.</p>
    </div>
</div>

@php $user = auth()->user(); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Profile Card --}}
    <div class="bg-surface-container-lowest p-8 rounded-2xl shadow-sm animate-fade-in flex flex-col items-center">
        <div class="w-20 h-20 rounded-full flex items-center justify-center font-bold text-2xl mb-4
                    {{ $user->isSuperAdmin() ? 'bg-primary-container text-primary' : 'bg-secondary-container text-secondary-dim' }}">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <h3 class="font-bold text-on-surface text-lg font-headline">{{ $user->name }}</h3>
        <p class="text-sm text-on-surface-variant">{{ $user->email }}</p>
        <div class="mt-3">
            @if($user->isSuperAdmin())
            <span class="px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary">Super Admin</span>
            @else
            <span class="px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-secondary-container text-secondary-dim">Admin</span>
            @endif
        </div>
        @if($user->account)
        <div class="mt-4 w-full p-4 bg-surface-container-low rounded-xl text-center">
            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Account</p>
            <p class="text-sm font-semibold text-on-surface">{{ $user->account->name }}</p>
        </div>
        @endif
        <div class="mt-4 w-full p-4 bg-surface-container-low rounded-xl text-center">
            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Member Since</p>
            <p class="text-sm font-semibold text-on-surface">{{ $user->created_at?->translatedFormat('d F Y') ?? '—' }}</p>
        </div>
    </div>

    {{-- Forms Column --}}
    <div class="lg:col-span-2 space-y-8">

        {{-- Update Profile --}}
        <div class="bg-surface-container-lowest p-8 rounded-2xl shadow-sm animate-fade-in">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-primary-container/30 rounded-lg">
                    <x-icon name="person" class="w-5 h-5 text-primary" />
                </div>
                <div>
                    <h3 class="font-bold text-on-surface font-headline text-lg">Update Profil</h3>
                    <p class="text-xs text-on-surface-variant">Perbarui nama dan email akun Anda</p>
                </div>
            </div>

            <form method="POST" action="{{ route('settings.profile') }}" class="space-y-5">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full bg-surface-container-high border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20" />
                        @error('name') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full bg-surface-container-high border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20" />
                        @error('email') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                            class="bg-primary text-on-primary px-8 py-3 rounded-xl text-sm font-bold shadow-lg shadow-primary/20 hover:bg-primary-dim transition-colors flex items-center gap-2">
                        <x-icon name="save" class="w-3.5 h-3.5" />
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="bg-surface-container-lowest p-8 rounded-2xl shadow-sm animate-fade-in">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-error-container/20 rounded-lg">
                    <x-icon name="lock" class="w-5 h-5 text-error" />
                </div>
                <div>
                    <h3 class="font-bold text-on-surface font-headline text-lg">Ubah Password</h3>
                    <p class="text-xs text-on-surface-variant">Pastikan menggunakan password yang kuat</p>
                </div>
            </div>

            <form method="POST" action="{{ route('settings.password') }}" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Password Lama</label>
                    <input type="password" name="current_password" required
                           class="w-full bg-surface-container-high border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20"
                           placeholder="Masukkan password saat ini" />
                    @error('current_password') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Password Baru</label>
                        <input type="password" name="password" required
                               class="w-full bg-surface-container-high border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20"
                               placeholder="Min. 6 karakter" />
                        @error('password') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full bg-surface-container-high border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20"
                               placeholder="Ulangi password baru" />
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                            class="bg-error/90 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-lg shadow-error/20 hover:bg-error transition-colors flex items-center gap-2">
                        <x-icon name="lock_reset" class="w-3.5 h-3.5" />
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>

        {{-- App Info --}}
        <div class="bg-primary/5 p-6 rounded-2xl border border-primary/10 relative overflow-hidden animate-fade-in">
            <div class="relative z-10 flex items-start gap-4">
                <div class="p-3 bg-primary/10 rounded-xl">
                    <x-icon name="info" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <h3 class="font-bold text-primary font-headline text-lg">E-REPORT</h3>
                    <p class="text-on-surface-variant text-sm mt-1">Interior Design Client Management System</p>
                    <div class="flex flex-wrap gap-4 mt-3 text-xs text-on-surface-variant">
                        <span class="flex items-center gap-1">
                            <x-icon name="code" class="w-3.5 h-3.5" /> Laravel 11
                        </span>
                        <span class="flex items-center gap-1">
                            <x-icon name="palette" class="w-3.5 h-3.5" /> Atelier Slate Design
                        </span>
                        <span class="flex items-center gap-1">
                            <x-icon name="storage" class="w-3.5 h-3.5" /> SQLite
                        </span>
                    </div>
                </div>
            </div>
            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-primary/10 rounded-full blur-2xl"></div>
        </div>
    </div>
</div>
@endsection
