@extends('layouts.app')
@section('title', 'Tambah Konsultasi')

@section('content')
<div class="max-w-3xl mx-auto px-1 sm:px-0">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('consultations.index') }}" class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-all active:scale-90 shrink-0">
            <x-icon name="arrow_back" class="w-5 h-5" />
        </a>
        <div class="min-w-0">
            <h2 class="text-2xl font-extrabold text-on-surface font-headline truncate">Tambah Konsultasi Baru</h2>
            <p class="text-xs sm:text-sm text-on-surface-variant truncate">Isi form di bawah untuk menambahkan data klien baru.</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-surface-container-lowest rounded-2xl shadow-sm p-5 sm:p-8 border border-surface-container-low">
        @if($errors->any())
        <div class="bg-error/10 text-error px-4 py-3 rounded-xl text-sm font-medium mb-6">
            <div class="flex items-center gap-2 mb-2">
                <x-icon name="error" class="w-[18px] h-[18px]" />
                <span class="font-bold">Terdapat kesalahan:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 opacity-90">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('consultations.store') }}" class="space-y-6 sm:space-y-8">
            @csrf

            {{-- Auto ID --}}
            <div class="space-y-2">
                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">ID Konsultasi (Otomatis)</label>
                <div class="bg-surface-container-low rounded-xl px-4 py-3.5 text-sm font-mono font-bold text-primary shadow-inner border border-surface-container text-center sm:text-left">
                    {{ $newId }}
                </div>
            </div>

            {{-- Client Name + Phone --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                <div class="space-y-2">
                    <label for="client_name" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Nama Klien <span class="text-error">*</span></label>
                    <input type="text" id="client_name" name="client_name" value="{{ old('client_name') }}" maxlength="100"
                           class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 placeholder:text-outline-variant shadow-inner font-bold"
                           placeholder="Nama lengkap klien" required />
                </div>
                <div class="space-y-2">
                    <label for="phone" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">No. Telepon/WA <span class="text-error">*</span></label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" maxlength="25"
                           oninput="this.value = this.value.replace(/[^0-9\s\-\+\(\)]/g, '')"
                           class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 placeholder:text-outline-variant shadow-inner font-bold"
                           placeholder="Contoh: 08123456789" required />
                </div>
            </div>

            {{-- Province + City (with auto-fill) --}}
            <div x-data="cityAutoFill(@js(old('city', '')), @js(old('province', '')))" class="space-y-5 sm:space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                    <div class="space-y-2">
                        <label for="province" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">
                            Provinsi Domisili
                            <span x-show="loading" class="inline-flex items-center gap-1 text-primary ml-1">
                                <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span class="text-[9px]">Mencari...</span>
                            </span>
                        </label>
                        <div x-data="searchableOptions(@js($provinces))"
                             @click.outside="close()"
                             @keydown.escape.prevent.stop="close()"
                             class="relative">
                            <input type="hidden" name="province" :value="province">
                            <button type="button"
                                    id="province"
                                    @click="toggle()"
                                    class="w-full bg-surface-container-low rounded-xl pl-4 pr-12 py-3 text-left text-sm shadow-inner transition focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    :class="open ? 'ring-2 ring-primary/20' : ''"
                                    :aria-expanded="open.toString()"
                                    aria-haspopup="listbox">
                                <span class="block truncate"
                                      :class="province ? 'font-medium text-on-surface' : 'font-medium text-outline-variant'"
                                      x-text="province || 'Pilih Provinsi...'"></span>
                            </button>
                            <button x-show="province"
                                    x-cloak
                                    type="button"
                                    @click.stop="province = ''; close()"
                                    class="absolute right-10 top-1/2 -translate-y-1/2 rounded-md p-1 text-outline-variant transition hover:bg-surface-container hover:text-on-surface"
                                    aria-label="Kosongkan provinsi">
                                <x-icon name="close" class="w-4 h-4" />
                            </button>
                            <x-icon name="expand_more"
                                    class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none transition-transform"
                                    x-bind:class="open ? 'rotate-180' : ''" />

                            <div x-show="open"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute left-0 right-0 top-full z-40 mt-2 overflow-hidden rounded-2xl border border-surface-container-low bg-surface-container-lowest shadow-2xl">
                                <div class="border-b border-surface-container-low p-3">
                                    <input x-ref="searchInput"
                                           type="text"
                                           x-model="search"
                                           class="w-full rounded-xl border-0 bg-surface-container-low px-4 py-3 text-sm shadow-inner focus:ring-2 focus:ring-primary/20"
                                           placeholder="Cari provinsi..."
                                           autocomplete="off">
                                </div>
                                <div class="max-h-60 overflow-y-auto p-1.5">
                                    <template x-if="filteredOptions().length === 0">
                                        <div class="px-4 py-3 text-sm text-outline-variant">Provinsi tidak ditemukan.</div>
                                    </template>
                                    <template x-for="option in filteredOptions()" :key="option.value">
                                        <button type="button"
                                                @mousedown.prevent="province = option.value; close()"
                                                class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 text-left text-sm transition hover:bg-primary/5 hover:text-primary">
                                            <span class="truncate font-semibold" x-text="option.label"></span>
                                            <x-icon name="check"
                                                    class="h-4 w-4 text-primary"
                                                    x-show="province === option.value"></x-icon>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2 relative">
                        <label for="city" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Kota / Kabupaten</label>
                        <input type="text" id="city" name="city" value="{{ old('city') }}" x-model="city" maxlength="100"
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                    <div class="space-y-2">
                        <label for="district" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Kecamatan <span class="text-outline-variant font-medium normal-case">(opsional)</span></label>
                        <input type="text" id="district" name="district" value="{{ old('district') }}" maxlength="100"
                               class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 placeholder:text-outline-variant shadow-inner font-medium"
                               placeholder="Nama kecamatan" />
                    </div>
                    <div class="space-y-2">
                        <label for="address" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Alamat Lengkap <span class="text-outline-variant font-medium normal-case">(opsional)</span></label>
                        <textarea id="address" name="address" rows="2" maxlength="500"
                                  class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 resize-none shadow-inner placeholder:text-outline-variant font-medium"
                                  placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Account Selection (Super Admin Only) --}}
            @if(auth()->user()->isSuperAdmin())
            <div class="space-y-2">
                <label for="account_id" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Akun Interior <span class="text-error">*</span></label>
                <div x-data="searchableSelect(@js($accounts->map(fn($account) => ['value' => (string) $account->id, 'label' => $account->name])->values()), @js(old('account_id', '')))"
                     @click.outside="close()"
                     @keydown.escape.prevent.stop="close()"
                     class="relative">
                    <input type="hidden" name="account_id" :value="selected" required>
                    <button type="button"
                            id="account_id"
                            @click="toggle()"
                            class="w-full bg-surface-container-low rounded-xl pl-4 pr-12 py-3 text-left text-sm shadow-inner transition focus:outline-none focus:ring-2 focus:ring-primary/20"
                            :class="open ? 'ring-2 ring-primary/20' : ''"
                            :aria-expanded="open.toString()"
                            aria-haspopup="listbox">
                        <span class="block truncate"
                              :class="selected ? 'font-bold text-primary' : 'font-bold text-outline-variant'"
                              x-text="selectedLabel('Pilih Akun...')"></span>
                    </button>
                    <button x-show="selected"
                            x-cloak
                            type="button"
                            @click.stop="clear()"
                            class="absolute right-10 top-1/2 -translate-y-1/2 rounded-md p-1 text-outline-variant transition hover:bg-surface-container hover:text-on-surface"
                            aria-label="Kosongkan akun">
                        <x-icon name="close" class="w-4 h-4" />
                    </button>
                    <x-icon name="expand_more"
                            class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none transition-transform"
                            x-bind:class="open ? 'rotate-180' : ''" />
                    <div x-show="open"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute left-0 right-0 top-full z-40 mt-2 overflow-hidden rounded-2xl border border-surface-container-low bg-surface-container-lowest shadow-2xl">
                        <div class="border-b border-surface-container-low p-3">
                            <input x-ref="searchInput" type="text" x-model="search"
                                   class="w-full rounded-xl border-0 bg-surface-container-low px-4 py-3 text-sm shadow-inner focus:ring-2 focus:ring-primary/20"
                                   placeholder="Cari akun..." autocomplete="off">
                        </div>
                        <div class="max-h-60 overflow-y-auto p-1.5">
                            <template x-if="filteredOptions().length === 0">
                                <div class="px-4 py-3 text-sm text-outline-variant">Akun tidak ditemukan.</div>
                            </template>
                            <template x-for="option in filteredOptions()" :key="option.value">
                                <button type="button" @mousedown.prevent="setSelected(option.value)"
                                        class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 text-left text-sm transition hover:bg-primary/5 hover:text-primary">
                                    <span class="truncate font-semibold" x-text="option.label"></span>
                                    <x-icon name="check" class="h-4 w-4 text-primary" x-show="selected === option.value"></x-icon>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <input type="hidden" name="account_id" value="{{ auth()->user()->account_id }}" />
            @endif

            {{-- Category + Status --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                <div class="space-y-2">
                    <label for="needs_category_id" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Jenis Kebutuhan <span class="text-error">*</span></label>
                    <div x-data="searchableSelect(@js($categories->map(fn($category) => ['value' => (string) $category->id, 'label' => $category->name])->values()), @js(old('needs_category_id', '')))"
                         @click.outside="close()"
                         @keydown.escape.prevent.stop="close()"
                         class="relative">
                        <input type="hidden" name="needs_category_id" :value="selected" required>
                        <button type="button"
                                id="needs_category_id"
                                @click="toggle()"
                                class="w-full bg-surface-container-low rounded-xl pl-4 pr-12 py-3 text-left text-sm shadow-inner transition focus:outline-none focus:ring-2 focus:ring-primary/20"
                                :class="open ? 'ring-2 ring-primary/20' : ''"
                                :aria-expanded="open.toString()"
                                aria-haspopup="listbox">
                            <span class="block truncate"
                                  :class="selected ? 'font-bold text-on-surface' : 'font-bold text-outline-variant'"
                                  x-text="selectedLabel('Pilih Kategori...')"></span>
                        </button>
                        <button x-show="selected"
                                x-cloak
                                type="button"
                                @click.stop="clear()"
                                class="absolute right-10 top-1/2 -translate-y-1/2 rounded-md p-1 text-outline-variant transition hover:bg-surface-container hover:text-on-surface"
                                aria-label="Kosongkan kategori">
                            <x-icon name="close" class="w-4 h-4" />
                        </button>
                        <x-icon name="expand_more" class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 right-0 top-full z-40 mt-2 overflow-hidden rounded-2xl border border-surface-container-low bg-surface-container-lowest shadow-2xl">
                            <div class="border-b border-surface-container-low p-3">
                                <input x-ref="searchInput" type="text" x-model="search"
                                       class="w-full rounded-xl border-0 bg-surface-container-low px-4 py-3 text-sm shadow-inner focus:ring-2 focus:ring-primary/20"
                                       placeholder="Cari kategori..." autocomplete="off">
                            </div>
                            <div class="max-h-60 overflow-y-auto p-1.5">
                                <template x-if="filteredOptions().length === 0">
                                    <div class="px-4 py-3 text-sm text-outline-variant">Kategori tidak ditemukan.</div>
                                </template>
                                <template x-for="option in filteredOptions()" :key="option.value">
                                    <button type="button" @mousedown.prevent="setSelected(option.value)"
                                            class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 text-left text-sm transition hover:bg-primary/5 hover:text-primary">
                                        <span class="truncate font-semibold" x-text="option.label"></span>
                                        <x-icon name="check" class="h-4 w-4 text-primary" x-show="selected === option.value"></x-icon>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label for="status_category_id" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Status Prospek <span class="text-error">*</span></label>
                    <div x-data="searchableSelect(@js($statuses->map(fn($status) => ['value' => (string) $status->id, 'label' => $status->name])->values()), @js(old('status_category_id', '')))"
                         @click.outside="close()"
                         @keydown.escape.prevent.stop="close()"
                         class="relative">
                        <input type="hidden" name="status_category_id" :value="selected" required>
                        <button type="button"
                                id="status_category_id"
                                @click="toggle()"
                                class="w-full bg-surface-container-low rounded-xl pl-4 pr-12 py-3 text-left text-sm shadow-inner transition focus:outline-none focus:ring-2 focus:ring-primary/20"
                                :class="open ? 'ring-2 ring-primary/20' : ''"
                                :aria-expanded="open.toString()"
                                aria-haspopup="listbox">
                            <span class="block truncate"
                                  :class="selected ? 'font-bold text-on-surface' : 'font-bold text-outline-variant'"
                                  x-text="selectedLabel('Pilih Status...')"></span>
                        </button>
                        <button x-show="selected"
                                x-cloak
                                type="button"
                                @click.stop="clear()"
                                class="absolute right-10 top-1/2 -translate-y-1/2 rounded-md p-1 text-outline-variant transition hover:bg-surface-container hover:text-on-surface"
                                aria-label="Kosongkan status">
                            <x-icon name="close" class="w-4 h-4" />
                        </button>
                        <x-icon name="expand_more" class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 right-0 top-full z-40 mt-2 overflow-hidden rounded-2xl border border-surface-container-low bg-surface-container-lowest shadow-2xl">
                            <div class="border-b border-surface-container-low p-3">
                                <input x-ref="searchInput" type="text" x-model="search"
                                       class="w-full rounded-xl border-0 bg-surface-container-low px-4 py-3 text-sm shadow-inner focus:ring-2 focus:ring-primary/20"
                                       placeholder="Cari status..." autocomplete="off">
                            </div>
                            <div class="max-h-60 overflow-y-auto p-1.5">
                                <template x-if="filteredOptions().length === 0">
                                    <div class="px-4 py-3 text-sm text-outline-variant">Status tidak ditemukan.</div>
                                </template>
                                <template x-for="option in filteredOptions()" :key="option.value">
                                    <button type="button" @mousedown.prevent="setSelected(option.value)"
                                            class="flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 text-left text-sm transition hover:bg-primary/5 hover:text-primary">
                                        <span class="truncate font-semibold" x-text="option.label"></span>
                                        <x-icon name="check" class="h-4 w-4 text-primary" x-show="selected === option.value"></x-icon>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Date + Notes --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="consultation_date" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Tanggal Konsultasi Pertama</label>
                    <input type="date" id="consultation_date" name="consultation_date" value="{{ old('consultation_date', now()->format('Y-m-d')) }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner font-medium" />
                </div>
                <div class="hidden md:block"></div>
            </div>

            <div class="space-y-2">
                <label for="notes" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Catatan Follow-Up Awal</label>
                <textarea id="notes" name="notes" rows="4" maxlength="1000"
                          class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 resize-none shadow-inner placeholder:text-outline-variant leading-relaxed font-medium"
                          placeholder="Hasil brief awal atau info tambahan klien...">{{ old('notes') }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col-reverse sm:flex-row gap-4 pt-6 border-t border-surface-container-low">
                <a href="{{ route('consultations.index') }}"
                   class="flex-1 sm:flex-none flex items-center justify-center border border-outline-variant/30 text-on-surface-variant px-8 py-3.5 rounded-xl text-sm font-bold hover:bg-surface-container transition-all active:scale-95">
                    Batal
                </a>
                <button type="submit"
                        class="flex-1 sm:flex-none flex items-center justify-center bg-primary text-on-primary px-10 py-3.5 rounded-xl font-bold text-sm shadow-xl shadow-primary/20 hover:bg-primary-dim transition-all hover:scale-[1.02] active:scale-[0.98] gap-2">
                    <x-icon name="save" class="w-4 h-4" />
                    <span>Simpan Data Konsultasi</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
