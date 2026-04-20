@extends('layouts.app')
@section('title', 'Edit Konsultasi')

@section('content')
<div class="max-w-3xl mx-auto px-1 sm:px-0">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('consultations.index') }}" class="w-10 h-10 rounded-xl bg-surface-container flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-all active:scale-90 shrink-0">
            <x-icon name="arrow_back" class="w-5 h-5" />
        </a>
        <div class="min-w-0">
            <h2 class="text-2xl font-extrabold text-on-surface font-headline truncate">Update Data Lead</h2>
            <p class="text-[10px] sm:text-xs text-on-surface-variant truncate font-bold uppercase tracking-widest mt-1 opacity-70">
                ID: {{ $consultation->consultation_id }} • Aktual {{ $consultation->updated_at->diffForHumans() }}
            </p>
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

        <form method="POST" action="{{ route('consultations.update', $consultation) }}" class="space-y-6 sm:space-y-8">
            @csrf @method('PUT')

            {{-- Client Name + Phone --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                <div class="space-y-2">
                    <label for="client_name" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Nama Klien <span class="text-error">*</span></label>
                    <input type="text" id="client_name" name="client_name" value="{{ old('client_name', $consultation->client_name) }}" maxlength="100"
                           class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner font-bold"
                           placeholder="Nama lengkap klien" required />
                </div>
                <div class="space-y-2">
                    <label for="phone" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">No. Telepon/WA <span class="text-error">*</span></label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $consultation->phone) }}" maxlength="25"
                           oninput="this.value = this.value.replace(/[^0-9\s\-\+\(\)]/g, '')"
                           class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner font-bold"
                           placeholder="08xxxxxxxxxx" required />
                </div>
            </div>

            {{-- Province + City (with auto-fill) --}}
            <div x-data="cityAutoFill()" class="space-y-5 sm:space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                    <div class="space-y-2">
                        <label for="province" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">
                            Provinsi
                            <span x-show="loading" class="inline-flex items-center gap-1 text-primary ml-1">
                                <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span class="text-[9px]">Mencari...</span>
                            </span>
                        </label>
                        <div class="relative group">
                            <select id="province" name="province" x-model="province" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 appearance-none bg-none shadow-inner font-medium">
                                <option value="">Pilih Provinsi...</option>
                                @foreach($provinces as $prov)
                                <option value="{{ $prov }}" {{ old('province', $consultation->province) === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                                @endforeach
                            </select>
                            <x-icon name="expand_more" class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none transition-transform group-focus-within:rotate-180" />
                        </div>
                    </div>
                    <div class="space-y-2 relative">
                        <label for="city" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Kota / Kabupaten</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $consultation->city) }}" x-model="city" maxlength="100"
                               @input="onCityInput()" @blur="setTimeout(() => showSuggestions = false, 200)" @focus="onCityInput()"
                               class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner font-medium"
                               placeholder="Ketik nama kota..." autocomplete="off" />
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
                        <input type="text" id="district" name="district" value="{{ old('district', $consultation->district) }}" maxlength="100"
                               class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 placeholder:text-outline-variant shadow-inner font-medium"
                               placeholder="Nama kecamatan" />
                    </div>
                    <div class="space-y-2">
                        <label for="address" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Alamat Lengkap <span class="text-outline-variant font-medium normal-case">(opsional)</span></label>
                        <textarea id="address" name="address" rows="2" maxlength="500"
                                  class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 resize-none shadow-inner placeholder:text-outline-variant font-medium"
                                  placeholder="Masukkan alamat lengkap">{{ old('address', $consultation->address) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Account selection (Super Admin) --}}
            @if(auth()->user()->isSuperAdmin())
            <div class="space-y-2">
                <label for="account_id" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Akun Cabang Studio <span class="text-error">*</span></label>
                <div class="relative group">
                    <select id="account_id" name="account_id" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 appearance-none bg-none shadow-inner font-bold text-primary" required>
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ old('account_id', $consultation->account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                    <x-icon name="expand_more" class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none" />
                </div>
            </div>
            @endif

            {{-- Category + Status --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                <div class="space-y-2">
                    <label for="needs_category_id" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Jenis Kebutuhan <span class="text-error">*</span></label>
                    <div class="relative group">
                        <select id="needs_category_id" name="needs_category_id" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 appearance-none bg-none shadow-inner font-bold" required>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('needs_category_id', $consultation->needs_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <x-icon name="expand_more" class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label for="status_category_id" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Status Prospek <span class="text-error">*</span></label>
                    <div class="relative group">
                        <select id="status_category_id" name="status_category_id" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 appearance-none bg-none shadow-inner font-bold" required>
                            @foreach($statuses as $st)
                            <option value="{{ $st->id }}" {{ old('status_category_id', $consultation->status_category_id) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                        <x-icon name="expand_more" class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-outline-variant pointer-events-none" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="consultation_date" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Tanggal Konsultasi</label>
                    <input type="date" id="consultation_date" name="consultation_date" value="{{ old('consultation_date', $consultation->consultation_date?->format('Y-m-d')) }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner font-medium" />
                </div>
            </div>

            <div class="space-y-2">
                <label for="notes" class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest px-1">Catatan Tambahan</label>
                <textarea id="notes" name="notes" rows="4" maxlength="1000"
                          class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 resize-none shadow-inner placeholder:text-outline-variant leading-relaxed font-medium">{{ old('notes', $consultation->notes) }}</textarea>
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
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function cityAutoFill() {
    return {
        city: '{{ old("city", $consultation->city ?? "") }}',
        province: '{{ old("province", $consultation->province ?? "") }}',
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
