@extends('layouts.app')
@section('title', 'Edit Akun')

@section('content')
<div class="max-w-2xl mx-auto px-1 sm:px-0">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('accounts.index') }}" class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-all active:scale-90 shrink-0 shadow-sm">
            <x-icon name="arrow_back" class="w-5 h-5" />
        </a>
        <div class="min-w-0">
            <h2 class="text-2xl font-extrabold text-on-surface font-headline truncate pr-2">Update Profil Akun</h2>
            <p class="text-[10px] sm:text-xs text-on-surface-variant mt-1.5 font-bold uppercase tracking-widest opacity-70 truncate px-0.5">Edit: {{ $account->name }}</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-surface-container-lowest rounded-2xl shadow-sm p-6 sm:p-8 border border-surface-container-low">
        @if($errors->any())
        <div class="bg-error/10 text-error px-4 py-3 rounded-xl text-sm font-medium mb-6">
            <div class="flex items-center gap-2 mb-2">
                <x-icon name="error" class="w-[18px] h-[18px]" />
                <span class="font-bold">Gagal memperbarui data:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 opacity-90">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('accounts.update', $account) }}" enctype="multipart/form-data" class="space-y-6 sm:space-y-8">
            @csrf @method('PUT')
            
            {{-- Logo Section --}}
            <div class="space-y-3">
                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Logo / Branding Akun</label>
                <div class="flex flex-col sm:flex-row items-center gap-6 p-4 bg-surface-container-low rounded-2xl border border-surface-container shadow-inner">
                    @if($account->logo_path)
                        <img src="{{ Storage::url($account->logo_path) }}" alt="{{ $account->name }} Logo" loading="lazy" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shadow-sm bg-white p-1 ring-1 ring-surface-container" />
                    @else
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white flex items-center justify-center text-primary shadow-sm border border-surface-container shrink-0">
                            <x-icon name="domain" class="w-8 h-8" />
                        </div>
                    @endif
                    <div class="flex-1 w-full">
                        <input type="file" name="logo" accept="image/*" class="block w-full text-xs text-on-surface-variant
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-xl file:border-0
                            file:text-xs file:font-bold
                            file:bg-primary file:text-on-primary
                            hover:file:bg-primary-dim transition-all cursor-pointer shadow-sm
                        "/>
                        <p class="text-[9px] text-on-surface-variant mt-2 font-medium italic opacity-60">Gunakan format JPG/PNG, ukuran ideal 200x200px.</p>
                    </div>
                </div>
            </div>

            {{-- Account Name --}}
            <div class="space-y-2">
                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Nama Akun <span class="text-error">*</span></label>
                <input type="text" name="name" value="{{ old('name', $account->name) }}" 
                       class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3.5 text-sm focus:ring-2 focus:ring-primary/20 placeholder:text-outline-variant shadow-inner font-bold" 
                       required />
            </div>

            {{-- Category/Description --}}
            <div class="space-y-2">
                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Kategori / Tagline Akun</label>
                <input type="text" name="description" value="{{ old('description', $account->description) }}" 
                       class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner font-bold" />
            </div>


            {{-- Target Setting --}}
            <div class="space-y-2">
                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Target Leads Bulanan</label>
                <div class="relative w-full sm:max-w-[180px]">
                    <input type="number" name="target_leads" value="{{ old('target_leads', $account->target_leads) }}" 
                           class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner font-bold text-center" min="1" />
                </div>
            </div>

            {{-- Actions --}}
            <div class="form-actions-responsive pt-6 border-t border-surface-container-low">
                <a href="{{ route('accounts.index') }}" 
                   class="flex-1 sm:flex-none flex items-center justify-center border border-outline-variant/30 text-on-surface-variant px-8 py-3.5 rounded-xl text-sm font-bold hover:bg-surface-container transition-all active:scale-95">
                    Batal
                </a>
                <button type="submit" 
                        class="flex-1 sm:flex-none flex items-center justify-center bg-primary text-on-primary px-10 py-3.5 rounded-xl font-bold text-sm shadow-xl shadow-primary/20 hover:bg-primary-dim transition-all hover:scale-[1.02] active:scale-[0.98] gap-2">
                    <x-icon name="save_as" class="w-4 h-4" />
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
