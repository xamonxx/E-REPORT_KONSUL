@extends('layouts.app')
@section('title', 'Leads')

@section('content')
{{-- Page Header --}}
<div x-data="{ showImportModal: {{ $errors->has('csv_file') ? 'true' : 'false' }}, showCreateModal: {{ old('client_name') ? 'true' : 'false' }} }" class="flex flex-col xl:flex-row xl:items-end justify-between gap-6 mb-6">
    <div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight font-headline">Leads Management</h2>
        <p class="text-sm sm:text-base text-on-surface-variant mt-1">Kelola semua data konsultasi klien.</p>
    </div>
    <div class="flex flex-wrap gap-2 sm:gap-3 no-print">
        <button @click="showImportModal = true"
           class="flex-1 sm:flex-none border border-outline-variant/30 text-on-surface-variant px-3 sm:px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold hover:bg-surface-container transition-colors flex items-center justify-center gap-2">
            <x-icon name="upload_file" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            <span>Import</span>
        </button>
        <a href="{{ route('export.csv', request()->query()) }}"
           class="flex-1 sm:flex-none border border-outline-variant/30 text-on-surface-variant px-3 sm:px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold hover:bg-surface-container transition-colors flex items-center justify-center gap-2">
            <x-icon name="download" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            <span>Export</span>
        </a>
        <button @click="showCreateModal = true"
           class="w-full sm:w-auto bg-primary text-on-primary px-6 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:bg-primary-dim transition-colors">
            <x-icon name="add_circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            <span>Tambah Lead</span>
        </button>
    </div>

    {{-- Import Modal --}}
    <template x-teleport="body">
        <div x-show="showImportModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-inverse-surface/50 backdrop-blur-sm p-4"
             x-transition.opacity.duration.300ms>
            <div @click.away="showImportModal = false" class="bg-surface-container-lowest p-6 sm:p-8 rounded-2xl shadow-xl w-full max-w-lg animate-fade-in">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-on-surface font-headline text-xl">Import Data CSV</h3>
                    <button @click="showImportModal = false" class="text-on-surface-variant hover:text-error transition-colors">
                        <x-icon name="close" class="w-5 h-5" />
                    </button>
                </div>
                
                <p class="text-sm text-on-surface-variant mb-6">Unggah file CSV dengan urutan kolom: <br><code class="bg-surface-container px-1 rounded font-bold">Nama Klien, No Telepon, ID Akun</code></p>
                
                <div class="mb-6">
                    <a href="{{ route('consultations.template') }}" class="text-primary text-sm font-bold hover:underline flex items-center gap-1">
                        <x-icon name="download" class="w-3.5 h-3.5" /> Download Template CSV
                    </a>
                </div>

                <form action="{{ route('consultations.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div class="border-2 border-dashed border-outline-variant/30 rounded-xl p-6 sm:p-8 text-center hover:bg-surface-container-low transition-colors relative group">
                        <input type="file" name="csv_file" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"/>
                        <div class="space-y-2">
                            <x-icon name="cloud_upload" class="w-10 h-10 text-outline-variant group-hover:text-primary transition-colors" />
                            <p class="text-xs sm:text-sm text-on-surface-variant group-hover:text-on-surface transition-colors font-medium">Klik atau drop file CSV di sini</p>
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end mt-4">
                        <button type="button" @click="showImportModal = false" class="px-5 py-2.5 rounded-xl font-bold text-sm text-on-surface-variant hover:bg-surface-container-low transition-colors">Batal</button>
                        <button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:bg-primary-dim transition-colors">Mulai Import</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Create Modal --}}
    <template x-teleport="body">
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-[60] flex flex-col items-center justify-end sm:justify-center bg-inverse-surface/50 backdrop-blur-sm sm:p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="showCreateModal = false" 
                 class="bg-surface-container-lowest w-full sm:max-w-2xl sm:rounded-2xl shadow-2xl flex flex-col max-h-[90vh] sm:max-h-[85vh] rounded-t-3xl sm:rounded-t-2xl animate-fade-in"
                 x-transition:enter="transition ease-out duration-300 delay-75"
                 x-transition:enter-start="opacity-0 translate-y-10"
                 x-transition:enter-end="opacity-100 translate-y-0">
                 
                {{-- Modal Header --}}
                <div class="px-6 py-5 border-b border-surface-container-low shrink-0 flex justify-between items-center bg-surface-container-lowest sm:rounded-t-2xl rounded-t-3xl z-10 sticky top-0">
                    <div>
                        <h3 class="font-extrabold text-on-surface font-headline text-xl">Tambah Konsultasi Baru</h3>
                        <p class="text-xs text-on-surface-variant font-medium mt-0.5">Isi form untuk menambahkan data lead klien.</p>
                    </div>
                    <button @click="showCreateModal = false" class="w-8 h-8 rounded-full bg-surface-container hover:bg-error/10 hover:text-error text-on-surface-variant flex items-center justify-center transition-colors">
                        <x-icon name="close" class="w-[18px] h-[18px]" />
                    </button>
                </div>

                {{-- Modal Body (Scrollable) --}}
                <div class="p-6 overflow-y-auto scrollbar-thin scrollbar-thumb-surface-container flex-1">
                    @if($errors->any() && old('client_name'))
                    <div class="bg-error/10 text-error px-4 py-3 rounded-xl text-sm font-medium mb-6 animate-fade-in">
                        <div class="flex items-center gap-2 mb-1">
                            <x-icon name="error" class="w-4 h-4" />
                            <span class="font-bold">Terdapat kesalahan:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-0.5 opacity-90 pl-6">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" id="form-create-lead" action="{{ route('consultations.store') }}" class="space-y-6">
                        @csrf

                        {{-- Auto ID --}}
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">ID Konsultasi (Otomatis)</label>
                            <div class="bg-surface-container-low rounded-xl px-4 py-3 text-sm font-mono font-bold text-primary shadow-inner border border-surface-container text-center sm:text-left">
                                {{ $newId }}
                            </div>
                        </div>

                        {{-- Client Name + Phone --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label for="client_name" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Nama Klien <span class="text-error">*</span></label>
                                <input type="text" id="client_name" name="client_name" value="{{ old('client_name') }}"
                                       class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 placeholder:text-outline-variant shadow-inner font-bold"
                                       placeholder="Nama lengkap klien" required />
                            </div>
                            <div class="space-y-2">
                                <label for="phone" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">No. Telepon/WA <span class="text-error">*</span></label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                       class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 placeholder:text-outline-variant shadow-inner font-bold"
                                       placeholder="Contoh: 08123456789" required />
                            </div>
                        </div>

                        {{-- Province + City (with auto-fill) --}}
                        <div x-data="modalCityAutoFill()" class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-2">
                                    <label for="modal_province" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">
                                        Provinsi Domisili
                                        <span x-show="loading" class="inline-flex items-center gap-1 text-primary ml-1">
                                            <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            <span class="text-[9px]">Mencari...</span>
                                        </span>
                                    </label>
                                    <div class="relative group">
                                        <select id="modal_province" name="province" x-model="province" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 appearance-none bg-none shadow-inner font-medium">
                                            <option value="">Pilih Provinsi...</option>
                                            @foreach($provinces as $prov)
                                            <option value="{{ $prov }}" {{ old('province') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                                            @endforeach
                                        </select>
                                        <x-icon name="expand_more" class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none group-focus-within:rotate-180 transition-transform" />
                                    </div>
                                </div>
                                <div class="space-y-2 relative">
                                    <label for="modal_city" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Kota / Kabupaten</label>
                                    <input type="text" id="modal_city" name="city" value="{{ old('city') }}" x-model="city"
                                           @input="onCityInput()" @blur="setTimeout(() => showSuggestions = false, 200)" @focus="onCityInput()"
                                           class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 placeholder:text-outline-variant shadow-inner font-medium"
                                           placeholder="Ketik nama kota..." autocomplete="off" />
                                    {{-- Autocomplete Dropdown --}}
                                    <div x-show="showSuggestions && suggestions.length > 0" x-cloak
                                         x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                         class="absolute top-full left-0 right-0 mt-1 bg-surface-container-lowest border border-surface-container-low rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto divide-y divide-surface-container-low/50">
                                        <template x-for="s in suggestions" :key="s.city">
                                            <button type="button" @mousedown.prevent="selectCity(s)" class="w-full text-left px-4 py-2.5 text-sm hover:bg-primary/5 hover:text-primary transition-colors flex items-center justify-between gap-2 cursor-pointer">
                                                <span class="font-bold truncate" x-text="s.city"></span>
                                                <span class="text-[10px] text-outline-variant shrink-0 bg-surface-container-low px-2 py-0.5 rounded-md" x-text="s.province"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Kecamatan + Alamat Lengkap --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-2">
                                    <label for="modal_district" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Kecamatan <span class="text-outline-variant font-medium normal-case">(opsional)</span></label>
                                    <input type="text" id="modal_district" name="district" value="{{ old('district') }}"
                                           class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 placeholder:text-outline-variant shadow-inner font-medium"
                                           placeholder="Nama kecamatan" />
                                </div>
                                <div class="space-y-2">
                                    <label for="modal_address" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Alamat Lengkap <span class="text-outline-variant font-medium normal-case">(opsional)</span></label>
                                    <textarea id="modal_address" name="address" rows="2"
                                              class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 resize-none shadow-inner placeholder:text-outline-variant font-medium"
                                              placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Account Selection (Super Admin Only) --}}
                        @if(auth()->user()->isSuperAdmin())
                        <div class="space-y-2">
                            <label for="modal_account_id" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Akun Cabang Interior <span class="text-error">*</span></label>
                            <div class="relative group">
                                <select id="modal_account_id" name="account_id" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 appearance-none bg-none shadow-inner font-bold text-primary" required>
                                    <option value="">Pilih Cabang Studio...</option>
                                    @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                                <x-icon name="expand_more" class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none group-focus-within:rotate-180 transition-transform" />
                            </div>
                        </div>
                        @else
                        <input type="hidden" name="account_id" value="{{ auth()->user()->account_id }}" />
                        @endif

                        {{-- Category + Status --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label for="modal_needs_category_id" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Jenis Kebutuhan <span class="text-error">*</span></label>
                                <div class="relative group">
                                    <select id="modal_needs_category_id" name="needs_category_id" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 appearance-none bg-none shadow-inner font-bold" required>
                                        <option value="">Pilih Kategori...</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('needs_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-icon name="expand_more" class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="modal_status_category_id" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Status Prospek <span class="text-error">*</span></label>
                                <div class="relative group">
                                    <select id="modal_status_category_id" name="status_category_id" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 appearance-none bg-none shadow-inner font-bold" required>
                                        <option value="">Pilih Status...</option>
                                        @foreach($statuses as $st)
                                        <option value="{{ $st->id }}" {{ old('status_category_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-icon name="expand_more" class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none" />
                                </div>
                            </div>
                        </div>

                        {{-- Date + Notes --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label for="modal_consultation_date" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Tanggal Konsultasi Pertama</label>
                                <input type="date" id="modal_consultation_date" name="consultation_date" value="{{ old('consultation_date', now()->format('Y-m-d')) }}"
                                       class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner font-medium" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="modal_notes" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Catatan Follow-Up Awal</label>
                            <textarea id="modal_notes" name="notes" rows="3"
                                      class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 resize-none shadow-inner placeholder:text-outline-variant leading-relaxed font-medium"
                                      placeholder="Hasil brief awal atau info tambahan klien...">{{ old('notes') }}</textarea>
                        </div>
                    </form>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-surface-container-lowest border-t border-surface-container-low shrink-0 flex flex-col sm:flex-row justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="showCreateModal = false" class="px-6 py-2.5 rounded-xl font-bold text-sm text-on-surface-variant hover:bg-surface-container-low transition-colors w-full sm:w-auto">Batal</button>
                    <button type="submit" form="form-create-lead" class="bg-primary text-on-primary px-8 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:bg-primary-dim transition-colors flex items-center justify-center gap-2 w-full sm:w-auto">
                        <x-icon name="save" class="w-4 h-4" />
                        <span>Simpan Data</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

{{-- Filters --}}
<div class="bg-surface-container-lowest p-4 sm:p-6 rounded-xl shadow-sm mb-6 no-print border border-surface-container-low">
    <form method="GET" action="{{ route('consultations.index') }}" class="flex flex-col xl:flex-row xl:items-end gap-4">
        <div class="grid grid-cols-1 md:grid-cols-2 {{ auth()->user()->isSuperAdmin() ? 'xl:grid-cols-5' : 'lg:grid-cols-4' }} gap-4 flex-1">
            {{-- Search --}}
            <div class="xl:col-span-1">
                <label class="block text-[10px] font-extrabold text-on-surface-variant uppercase tracking-widest mb-1.5 px-1 opacity-70">Search</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-3 flex items-center text-outline-variant group-focus-within:text-primary transition-colors">
                        <x-icon name="search" class="w-[18px] h-[18px]" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner"
                           placeholder="Nama, telp, ID..." />
                </div>
            </div>

            {{-- Start Date --}}
            <div>
                <label class="block text-[10px] font-extrabold text-on-surface-variant uppercase tracking-widest mb-1.5 px-1 opacity-70">Tgl Mulai</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-3 flex items-center text-outline-variant group-focus-within:text-primary transition-colors">
                        <x-icon name="calendar_today" class="w-[18px] h-[18px]" />
                    </span>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner" />
                </div>
            </div>

            {{-- End Date --}}
            <div>
                <label class="block text-[10px] font-extrabold text-on-surface-variant uppercase tracking-widest mb-1.5 px-1 opacity-70">Tgl Akhir</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-3 flex items-center text-outline-variant group-focus-within:text-primary transition-colors">
                        <x-icon name="event" class="w-[18px] h-[18px]" />
                    </span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner" />
                </div>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-[10px] font-extrabold text-on-surface-variant uppercase tracking-widest mb-1.5 px-1 opacity-70">Status</label>
                <select name="status" class="w-full bg-surface-container-low border-0 rounded-xl py-2.5 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner font-semibold text-on-surface-variant">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Account Filter (Super Admin) --}}
            @if(auth()->user()->isSuperAdmin())
            <div>
                <label class="block text-[10px] font-extrabold text-on-surface-variant uppercase tracking-widest mb-1.5 px-1 opacity-70">Account</label>
                <select name="account" class="w-full bg-surface-container-low border-0 rounded-xl py-2.5 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner font-semibold text-on-surface-variant">
                    <option value="">Semua Akun</option>
                    @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ request('account') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <div class="flex items-center gap-3 shrink-0 w-full xl:w-auto xl:pb-0.5">
            @if(request()->hasAny(['search', 'status', 'account', 'start_date', 'end_date']))
            <a href="{{ route('consultations.index') }}" class="flex items-center justify-center gap-1.5 text-error text-xs font-bold bg-error/5 hover:bg-error/10 px-4 py-2.5 rounded-xl transition-all h-[42px] border border-error/10">
                <x-icon name="restart_alt" class="w-4 h-4" />
                <span class="hidden sm:inline">Reset</span>
            </a>
            @endif
            <button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-primary/20 hover:bg-primary-dim transition-all active:scale-[0.98] flex items-center justify-center gap-2 h-[42px] w-full xl:w-auto">
                <x-icon name="filter_alt" class="w-4 h-4" />
                <span>Terapkan Filter</span>
            </button>
        </div>
    </form>
</div>

{{-- Data Table --}}
<div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden flex flex-col">
    <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-surface-container shadow-inner">
        <table class="w-full min-w-[750px] text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-6 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest leading-none">ID Consultation</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest leading-none">Nama Klien</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest leading-none">Telepon</th>
                    @if(auth()->user()->isSuperAdmin())
                    <th class="px-6 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest leading-none">Cabang</th>
                    @endif
                    <th class="px-6 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest leading-none">Kebutuhan</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest leading-none">Status</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest leading-none">Tgl Konsul</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest leading-none text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @forelse($consultations as $c)
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-xs font-mono font-bold text-primary bg-primary-container/30 px-2 py-1 rounded-lg">{{ $c->consultation_id }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center font-bold text-xs text-on-surface-variant shrink-0">
                                {{ strtoupper(substr($c->client_name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('consultations.show', $c) }}" class="font-bold text-on-surface hover:text-primary transition-colors text-sm truncate block">{{ $c->client_name }}</a>
                                <p class="text-[10px] text-on-surface-variant mt-0.5">{{ $c->city }}, {{ $c->province }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-on-surface font-medium">{{ $c->phone }}</td>
                    @if(auth()->user()->isSuperAdmin())
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-on-surface-variant">{{ $c->account?->name ?? 'Pusat' }}</span>
                    </td>
                    @endif
                    <td class="px-6 py-4">
                        <span class="text-sm font-medium text-on-surface-variant">{{ $c->needsCategory?->name ?? 'Belum Ada' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $c->statusCategory->css_class ?? 'bg-surface-container text-on-surface-variant' }}">
                            {{ $c->statusCategory?->name ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-on-surface-variant font-medium">{{ $c->consultation_date?->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('consultations.show', $c) }}"
                               class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors"
                               title="Detail">
                                <x-icon name="visibility" class="w-[18px] h-[18px]" />
                            </a>
                            <a href="{{ route('consultations.edit', $c) }}"
                               class="w-8 h-8 rounded-lg hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors"
                               title="Edit">
                                <x-icon name="edit" class="w-[18px] h-[18px]" />
                            </a>
                            <form method="POST" action="{{ route('consultations.destroy', $c) }}"
                                  id="delete-form-{{ $c->id }}">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDelete('delete-form-{{ $c->id }}', '{{ addslashes($c->client_name) }}')"
                                        class="w-8 h-8 rounded-lg hover:bg-error/10 flex items-center justify-center text-on-surface-variant hover:text-error transition-all"
                                        title="Hapus">
                                    <x-icon name="delete" class="w-[18px] h-[18px]" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->isSuperAdmin() ? 8 : 7 }}" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <x-icon name="person_off" class="w-16 h-16 text-outline-variant/30 mb-4" />
                            <p class="text-on-surface-variant font-bold">Tidak ada data konsultasi ditemukan.</p>
                            <button @click="showCreateModal = true" class="text-primary font-bold text-sm hover:underline mt-4 flex items-center gap-1 cursor-pointer">
                                <x-icon name="add" class="w-4 h-4" />
                                <span>Buat Lead Baru</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($consultations->hasPages())
    <div class="px-6 py-4 border-t border-surface-container-low/50 bg-white">
        {{ $consultations->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
    function confirmDelete(formId, clientName) {
        Swal.fire({
            title: 'Hapus data konsultasi?',
            text: "Data lead atas nama '" + clientName + "' akan terhapus secara permanen dari sistem!",
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

    function modalCityAutoFill() {
        return {
            city: '{{ old("city", "") }}',
            province: '{{ old("province", "") }}',
            loading: false,
            suggestions: [],
            showSuggestions: false,
            _mapping: null,

            async getMapping() {
                if (this._mapping) return this._mapping;
                try {
                    const res = await fetch('{{ route("api.wilayah.kota") }}');
                    this._mapping = await res.json();
                } catch (e) {
                    this._mapping = {};
                }
                return this._mapping;
            },

            async onCityInput() {
                const val = this.city.trim().toLowerCase();
                if (val.length < 2) { this.suggestions = []; this.showSuggestions = false; return; }

                const mapping = await this.getMapping();
                this.suggestions = Object.entries(mapping)
                    .filter(([kota]) => kota.toLowerCase().includes(val))
                    .slice(0, 8)
                    .map(([kota, prov]) => ({ city: kota, province: prov }));
                this.showSuggestions = this.suggestions.length > 0;
            },

            selectCity(item) {
                this.city = item.city;
                this.province = item.province;
                this.showSuggestions = false;
                this.suggestions = [];
            }
        };
    }
</script>
@endpush
@endsection
