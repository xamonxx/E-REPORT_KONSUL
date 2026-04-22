@extends('layouts.app')
@section('title', 'Monitoring Laporan Harian')

@php
    $statusFilterChips = [
        'all' => [
            'label' => 'Semua Data',
            'dot' => 'bg-slate-500',
            'text' => 'text-slate-700',
            'bg' => 'bg-slate-50',
            'border' => 'border-slate-200',
            'active' => 'bg-slate-700 text-white border-slate-700 shadow-lg shadow-slate-700/15',
        ],
        'ada_wa' => [
            'label' => 'Ada WA Baru',
            'dot' => 'bg-green-500',
            'text' => 'text-green-700',
            'bg' => 'bg-green-50',
            'border' => 'border-green-100',
            'active' => 'bg-green-600 text-white border-green-600 shadow-lg shadow-green-600/15',
        ],
        'nol_wa' => [
            'label' => '0 Data WA',
            'dot' => 'bg-yellow-500',
            'text' => 'text-yellow-700',
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-100',
            'active' => 'bg-yellow-500 text-white border-yellow-500 shadow-lg shadow-yellow-500/15',
        ],
        'libur_susulan' => [
            'label' => 'Libur / Susulan',
            'dot' => 'bg-sky-500',
            'text' => 'text-sky-700',
            'bg' => 'bg-sky-50',
            'border' => 'border-sky-100',
            'active' => 'bg-sky-600 text-white border-sky-600 shadow-lg shadow-sky-600/15',
        ],
        'belum_laporan' => [
            'label' => 'Tidak Laporan',
            'dot' => 'bg-error',
            'text' => 'text-error',
            'bg' => 'bg-error/5',
            'border' => 'border-error/10',
            'active' => 'bg-error text-white border-error shadow-lg shadow-error/20',
        ],
    ];

    $activeFilterLabel = $statusFilterChips[$selectedStatus]['label'] ?? 'Semua Data';
@endphp

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8">
    <div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight font-headline">Monitoring Laporan Harian</h2>
        <p class="text-sm sm:text-base text-on-surface-variant mt-1">Rekap per tanggal absensi pelaporan admin.</p>
    </div>
</div>

<div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm mb-8 animate-fade-in border border-surface-container-low">
    <form action="{{ route('report-attendances.index') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-end gap-4">
        <input type="hidden" name="status" value="{{ $selectedStatus }}">
        <div class="flex-1 min-w-0">
            <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 px-1">Pilih Tanggal Rekap</label>
            <div class="relative group">
                <x-icon name="calendar_month" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant group-focus-within:text-primary transition-colors" />
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                       class="w-full bg-surface-container-low pl-12 pr-4 py-3 rounded-xl border-none focus:ring-2 focus:ring-primary/20 text-sm font-bold shadow-inner">
            </div>
        </div>
        <button type="submit" class="w-full md:w-auto bg-primary text-on-primary px-10 py-3 rounded-xl font-bold hover:bg-primary-dim transition-all shadow-xl shadow-primary/20 flex items-center justify-center gap-2 active:scale-[0.98]">
            <x-icon name="filter_list" class="w-4 h-4" />
            <span>Tampilkan Data</span>
        </button>
    </form>
</div>

<div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden flex flex-col animate-fade-in border border-surface-container-low max-w-full">
    <div class="px-6 sm:px-8 py-6 flex flex-col xl:flex-row justify-between items-start xl:items-center bg-white border-b border-surface-container-low gap-6">
        <div>
            <h2 class="text-xl font-bold font-headline text-on-surface">Data Rekap Laporan Admin</h2>
            <p class="text-xs text-on-surface-variant mt-1.5 flex items-center gap-1.5">
                <x-icon name="event" class="w-3.5 h-3.5" />
                <span>Tanggal: <span class="font-bold text-primary">{{ $date->translatedFormat('d F Y') }}</span></span>
            </p>
            <p class="text-[11px] text-on-surface-variant mt-2">
                Filter aktif: <span class="font-bold text-on-surface">{{ $activeFilterLabel }}</span>
                <span class="mx-1 text-outline-variant">&bull;</span>
                <span>{{ $adminAttendances->count() }} data tampil</span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-[10px] sm:text-xs font-semibold no-print">
            @foreach($statusFilterChips as $statusKey => $chip)
                @php $isActive = $selectedStatus === $statusKey; @endphp
                <a href="{{ route('report-attendances.index', ['date' => $date->format('Y-m-d'), 'status' => $statusKey]) }}"
                   class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 transition-all {{ $isActive ? $chip['active'] : $chip['bg'].' '.$chip['text'].' '.$chip['border'].' hover:-translate-y-0.5 hover:shadow-md' }}">
                    <span class="w-3 h-3 rounded {{ $isActive ? 'bg-white/90' : $chip['dot'] }} shrink-0"></span>
                    <span>{{ $chip['label'] }}</span>
                    <span class="rounded-full px-1.5 py-0.5 text-[9px] font-extrabold {{ $isActive ? 'bg-white/15 text-white' : 'bg-white/80 text-on-surface-variant' }}">
                        {{ $statusCounts[$statusKey] ?? 0 }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="table-scroll-mobile overflow-x-auto overflow-y-auto max-h-[34rem] scrollbar-thin scrollbar-thumb-surface-container shadow-inner">
        <table class="w-full min-w-[980px] text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-surface-container-low/95 backdrop-blur supports-[backdrop-filter]:bg-surface-container-low/85">
                    <th class="sticky top-0 z-10 bg-surface-container-low/95 px-4 sm:px-5 py-3 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest text-center">Waktu</th>
                    <th class="sticky top-0 z-10 bg-surface-container-low/95 px-4 sm:px-5 py-3 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest font-headline">Administrator</th>
                    <th class="sticky top-0 z-10 bg-surface-container-low/95 px-4 sm:px-5 py-3 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Akun</th>
                    <th class="sticky top-0 z-10 bg-surface-container-low/95 px-4 sm:px-5 py-3 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Status Absensi</th>
                    <th class="sticky top-0 z-10 bg-surface-container-low/95 px-4 sm:px-5 py-3 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest text-right">Ubah Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @forelse($adminAttendances as $att)
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                    <td class="px-4 sm:px-5 py-3 text-center">
                        @if($att->has_reported)
                            <div class="inline-flex flex-col items-center rounded-xl bg-surface px-3 py-2 border border-surface-container-low">
                                <span class="text-on-surface font-bold text-sm leading-none">{{ $att->reported_at->format('H:i') }}</span>
                                <span class="text-[9px] text-on-surface-variant font-bold uppercase mt-1">WIB</span>
                            </div>
                        @else
                            <span class="text-error/30 font-bold text-xl leading-none">&mdash;</span>
                        @endif
                    </td>
                    <td class="px-4 sm:px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center font-bold text-xs text-on-surface-variant shrink-0 ring-2 ring-white shadow-sm">
                                {{ strtoupper(substr($att->admin->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-on-surface text-sm block truncate max-w-[150px]">{{ $att->admin->name }}</span>
                                <span class="text-[9px] text-on-surface-variant font-bold uppercase tracking-wider block mt-0.5">Admin Akun</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 sm:px-5 py-3">
                        <span class="text-xs font-bold text-on-surface-variant truncate max-w-[180px] block">{{ $att->account?->name ?? 'Belum Terhubung' }}</span>
                    </td>
                    <td class="px-4 sm:px-5 py-3">
                        <div class="flex justify-start">
                            @if($att->has_reported)
                                @if($att->report_category === 'ada_wa')
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-green-100 text-green-700 border border-green-200 flex items-center gap-1.5 shadow-sm">
                                        <x-icon name="done_all" class="w-3.5 h-3.5" /> Laporan - Ada WA
                                    </span>
                                @elseif($att->report_category === 'nol_wa')
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-yellow-100 text-yellow-700 border border-yellow-200 flex items-center gap-1.5 shadow-sm">
                                        <x-icon name="horizontal_rule" class="w-3.5 h-3.5" /> Laporan - 0 Data
                                    </span>
                                @elseif($att->report_category === 'libur_susulan')
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-sky-100 text-sky-700 border border-sky-200 flex items-center gap-1.5 shadow-sm">
                                        <x-icon name="event_note" class="w-3.5 h-3.5" /> Susulan / Libur
                                    </span>
                                @else
                                    <span class="px-4 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-tertiary-container/30 text-tertiary border border-tertiary/20 flex items-center gap-1.5">
                                        <x-icon name="done" class="w-3.5 h-3.5" /> Recorded
                                    </span>
                                @endif
                            @else
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-error/10 text-error border border-error/10 flex items-center gap-1.5 shadow-sm uppercase">
                                    <x-icon name="close" class="w-3.5 h-3.5" /> Belum Laporan
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 sm:px-5 py-3 text-right">
                        <form action="{{ route('report-attendances.upsert') }}" method="POST" class="flex items-center justify-end gap-2">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $att->admin->id }}">
                            <input type="hidden" name="report_date" value="{{ $date->format('Y-m-d') }}">
                            <div x-data="{ 
                                    open: false, 
                                    selected: @js($att->report_category ?? ''), 
                                    selectedLabel: @js(match($att->report_category) {
                                        'ada_wa' => 'Ada WA Baru',
                                        'nol_wa' => '0 Data WA',
                                        'libur_susulan' => 'Libur / Susulan',
                                        default => 'Belum Laporan',
                                    })
                                }"
                                @click.outside="open = false"
                                class="relative min-w-[190px] text-left">
                                <input type="hidden" name="report_category" :value="selected">
                                <button type="button"
                                        @click="open = !open"
                                        class="flex w-full items-center justify-between rounded-xl border border-surface-container-low bg-surface px-4 py-2.5 text-xs font-semibold text-on-surface shadow-inner transition-all hover:border-primary/20 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        :class="open ? 'border-primary/30 bg-surface-container-lowest ring-2 ring-primary/20' : ''">
                                    <span x-text="selectedLabel" class="truncate"></span>
                                    <x-icon name="expand_more" class="h-4 w-4 shrink-0 text-outline-variant transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                                </button>
                                <div x-show="open"
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-xl border border-surface-container-low bg-surface-container-lowest shadow-2xl">
                                    <button type="button"
                                            @click="selected = ''; selectedLabel = 'Belum Laporan'; open = false"
                                            class="flex w-full items-center justify-between px-4 py-2.5 text-left text-xs font-semibold transition-colors"
                                            :class="selected === '' ? 'bg-primary text-on-primary' : 'text-on-surface hover:bg-primary/5 hover:text-primary'">
                                        <span>Belum Laporan</span>
                                        <x-icon name="check" class="h-3.5 w-3.5" x-show="selected === ''"></x-icon>
                                    </button>
                                    <button type="button"
                                            @click="selected = 'ada_wa'; selectedLabel = 'Ada WA Baru'; open = false"
                                            class="flex w-full items-center justify-between px-4 py-2.5 text-left text-xs font-semibold transition-colors"
                                            :class="selected === 'ada_wa' ? 'bg-primary text-on-primary' : 'text-on-surface hover:bg-primary/5 hover:text-primary'">
                                        <span>Ada WA Baru</span>
                                        <x-icon name="check" class="h-3.5 w-3.5" x-show="selected === 'ada_wa'"></x-icon>
                                    </button>
                                    <button type="button"
                                            @click="selected = 'nol_wa'; selectedLabel = '0 Data WA'; open = false"
                                            class="flex w-full items-center justify-between px-4 py-2.5 text-left text-xs font-semibold transition-colors"
                                            :class="selected === 'nol_wa' ? 'bg-primary text-on-primary' : 'text-on-surface hover:bg-primary/5 hover:text-primary'">
                                        <span>0 Data WA</span>
                                        <x-icon name="check" class="h-3.5 w-3.5" x-show="selected === 'nol_wa'"></x-icon>
                                    </button>
                                    <button type="button"
                                            @click="selected = 'libur_susulan'; selectedLabel = 'Libur / Susulan'; open = false"
                                            class="flex w-full items-center justify-between px-4 py-2.5 text-left text-xs font-semibold transition-colors"
                                            :class="selected === 'libur_susulan' ? 'bg-primary text-on-primary' : 'text-on-surface hover:bg-primary/5 hover:text-primary'">
                                        <span>Libur / Susulan</span>
                                        <x-icon name="check" class="h-3.5 w-3.5" x-show="selected === 'libur_susulan'"></x-icon>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-[11px] font-bold text-on-primary shadow-lg shadow-primary/15 transition-all hover:bg-primary-dim active:scale-[0.98]">
                                Simpan
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 sm:px-8 py-16 text-center">
                        <div class="flex flex-col items-center opacity-60">
                            <x-icon name="filter_alt" class="w-12 h-12 mb-2 text-on-surface-variant" />
                            <p class="text-sm font-bold text-on-surface">Tidak ada data untuk filter {{ strtolower($activeFilterLabel) }}.</p>
                            <a href="{{ route('report-attendances.index', ['date' => $date->format('Y-m-d'), 'status' => 'all']) }}"
                               class="mt-3 inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-bold text-on-primary shadow-lg shadow-primary/15 hover:bg-primary-dim transition-all">
                                <x-icon name="restart_alt" class="w-3.5 h-3.5" />
                                <span>Tampilkan Semua Data</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
