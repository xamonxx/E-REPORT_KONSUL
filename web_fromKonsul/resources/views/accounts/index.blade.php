@extends('layouts.app')
@section('title', 'Accounts')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-6">
    <div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight font-headline">Accounts</h2>
        <p class="text-sm sm:text-base text-on-surface-variant mt-1">Kelola akun dan cabang interior studio.</p>
    </div>
    <a href="{{ route('accounts.create') }}" class="w-full sm:w-auto bg-primary text-on-primary px-6 py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:bg-primary-dim transition-all active:scale-[0.98]">
        <x-icon name="add_circle" class="w-4 h-4" />
        <span>Tambah Akun Baru</span>
    </a>
</div>

{{-- Filters --}}
<div class="bg-surface-container-lowest p-4 sm:p-6 rounded-xl shadow-sm mb-6 no-print">
    <form method="GET" action="{{ route('accounts.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-end gap-4 overflow-hidden">
        {{-- Search --}}
        <div class="flex-1">
            <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 px-1">Cari Akun</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-outline-variant">
                    <x-icon name="search" class="w-[18px] h-[18px]" />
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full bg-surface-container-high border-0 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20"
                       placeholder="Cari nama akun..." />
            </div>
        </div>

        {{-- ID Filter --}}
        <div class="w-full lg:w-28">
            <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 px-1">ID Akun</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-outline-variant">
                    <x-icon name="tag" class="w-[18px] h-[18px]" />
                </span>
                <input type="number" name="account_id" value="{{ request('account_id') }}"
                       class="w-full bg-surface-container-high border-0 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20"
                       placeholder="ID..." min="1" />
            </div>
        </div>

        {{-- Category Filter --}}
        <div class="w-full lg:w-56">
            <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 px-1">Kategori Akun</label>
            <select name="category" class="w-full bg-surface-container-high border-0 rounded-xl py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3 mt-2 lg:mt-0">
            <button type="submit" class="flex-1 lg:flex-none bg-primary/10 text-primary px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary/20 transition-all active:scale-[0.98]">
                Filter
            </button>
            @if(request()->hasAny(['search', 'category', 'account_id']))
            <a href="{{ route('accounts.index') }}" class="text-on-surface-variant text-xs sm:text-sm hover:text-error transition-colors font-bold px-2">Reset</a>
            @endif
        </div>
    </form>
</div>

<div class="grid grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-8 stagger-children">
    @foreach($accounts as $account)
    <div class="bg-surface-container-lowest p-3 sm:p-8 rounded-2xl shadow-sm hover-lift transition-all group relative border border-surface-container-low max-w-full overflow-hidden">
        <div class="flex justify-between items-start mb-4 sm:mb-6">
            @if($account->logo_path)
                <img src="{{ Storage::url($account->logo_path) }}" alt="{{ $account->name }}" loading="lazy" class="w-10 h-10 sm:w-14 sm:h-14 rounded-lg sm:rounded-xl object-cover bg-surface shadow-sm ring-2 ring-surface">
            @else
                <div class="p-2 sm:p-3 bg-primary-container/30 rounded-lg sm:rounded-xl group-hover:bg-primary group-hover:text-on-primary transition-colors text-primary shrink-0">
                    <x-icon name="domain" class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
            @endif
            <div class="flex gap-1 no-print">
                <a href="{{ route('accounts.edit', $account) }}" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-primary transition-all active:scale-90" title="Edit">
                    <x-icon name="edit" class="w-4 h-4 sm:w-5 sm:h-5" />
                </a>
                <form id="delete-acct-{{ $account->id }}" method="POST" action="{{ route('accounts.destroy', $account) }}">
                    @csrf @method('DELETE')
                    <button type="button" onclick="confirmDeleteAccount('delete-acct-{{ $account->id }}', {{ \Illuminate\Support\Js::from($account->name) }})"
                            class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl hover:bg-error/10 flex items-center justify-center text-on-surface-variant hover:text-error transition-all active:scale-90"
                            title="Hapus">
                        <x-icon name="delete" class="w-4 h-4 sm:w-5 sm:h-5" />
                    </button>
                </form>
            </div>
        </div>
        <div class="space-y-1 mb-4 sm:mb-6">
            <h3 class="font-bold text-on-surface text-sm sm:text-xl group-hover:text-primary transition-colors leading-tight truncate pr-2">
                {{ $account->name }}
            </h3>
            <div class="flex items-center flex-wrap gap-1 sm:gap-2">
                <span class="text-[8px] sm:text-[10px] font-mono font-bold bg-primary-container/40 text-primary px-1.5 py-0.5 rounded-md sm:rounded-lg">ID: {{ str_pad($account->id, 3, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
        
        <p class="hidden sm:block text-xs sm:text-sm text-on-surface-variant mb-6 line-clamp-2 min-h-[2.5rem] leading-relaxed">{{ $account->description }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4 mb-4 sm:mb-6">
            <div class="bg-surface-container-low rounded-xl p-2 sm:p-4 flex flex-col items-center text-center shadow-inner">
                <p class="text-[8px] sm:text-[10px] font-bold text-on-surface-variant uppercase tracking-widest opacity-60">Lead</p>
                <p class="text-lg sm:text-2xl font-extrabold font-headline text-on-surface leading-none mt-0.5 sm:mt-1">{{ number_format($account->consultations_count) }}</p>
            </div>
            <div class="bg-surface-container-low rounded-xl p-2 sm:p-4 flex flex-col items-center text-center shadow-inner">
                <p class="text-[8px] sm:text-[10px] font-bold text-on-surface-variant uppercase tracking-widest opacity-60">Konversi</p>
                <p class="text-lg sm:text-2xl font-extrabold font-headline text-tertiary leading-none mt-0.5 sm:mt-1">{{ $account->conversion_rate }}%</p>
            </div>
        </div>

        <div class="space-y-1.5 sm:space-y-2">
            @php $progress = min($account->target_progress, 100); @endphp
            <div class="flex justify-between items-center text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-on-surface-variant px-0.5 sm:px-1">
                <span>Target</span>
                <span class="shrink-0">{{ $account->lead_count }}/{{ $account->target_leads }}</span>
            </div>
            <div class="w-full bg-surface-container h-1.5 sm:h-2 rounded-full overflow-hidden shadow-inner">
                <div class="h-full bg-primary rounded-full transition-all duration-700 ease-out" style="width: {{ $progress }}%"></div>
            </div>
        </div>

        @if($account->admins->count() > 0)
        <div class="mt-4 sm:mt-8 pt-3 sm:pt-6 border-t border-surface-container-low/50 hidden sm:block">
            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-3 px-1">Admin</p>
            <div class="flex gap-2 flex-wrap">
                @foreach($account->admins as $admin)
                <div class="flex items-center gap-1.5 bg-primary-container/20 text-primary px-2.5 py-1.5 rounded-lg text-[10px] font-bold ring-1 ring-primary/10">
                    <x-icon name="person" class="w-3 h-3" />
                    <span class="truncate">{{ $admin->name }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>


<div class="mt-8">
    {{ $accounts->appends(request()->query())->links() }}
</div>

@push('scripts')
<script>
    function confirmDeleteAccount(formId, accountName) {
        Swal.fire({
            title: 'Hapus akun pusat ini?',
            text: 'Tindakan ini akan menghapus akun "' + accountName + '" beserta seluruh data konsultasi, tim, dan histori yang terhubung secara permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#9f403d',
            cancelButtonColor: '#737c7f',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                title: 'text-xl font-headline font-bold text-on-surface',
                confirmButton: 'bg-error hover:bg-error-dim rounded-xl px-8 py-3 font-bold',
                cancelButton: 'bg-outline hover:bg-outline-variant rounded-xl px-8 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script>
@endpush
@endsection
