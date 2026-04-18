@extends('layouts.app')
@section('title', 'Analytics')

@section('content')
{{-- Page Header --}}
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight font-headline">Analytics</h2>
        <p class="text-on-surface-variant mt-1">Analisis performa leads & konversi secara visual.</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
    <form method="GET" action="{{ route('analytics') }}" class="flex flex-wrap items-end gap-4">
        @if(auth()->user()->isSuperAdmin())
        <div class="w-48">
            <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Account</label>
            <select name="account" class="w-full bg-surface-container-high border-0 rounded-xl py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                <option value="">Semua Akun</option>
                @foreach($accounts as $account)
                <option value="{{ $account->id }}" {{ $selectedAccount == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="w-40">
            <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Bulan</label>
            <select name="month" class="w-full bg-surface-container-high border-0 rounded-xl py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                @foreach($months as $month)
                <option value="{{ $month['value'] }}" {{ $selectedMonth == $month['value'] ? 'selected' : '' }}>{{ $month['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-32">
            <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Tahun</label>
            <select name="year" class="w-full bg-surface-container-high border-0 rounded-xl py-2.5 text-sm focus:ring-2 focus:ring-primary/20">
                @foreach($years as $year)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="bg-primary/10 text-primary px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary/20 transition-all active:scale-[0.98]">
                Filter
            </button>
            @if(request()->hasAny(['account', 'month', 'year']))
            <a href="{{ route('analytics') }}" class="text-on-surface-variant text-xs sm:text-sm font-bold px-2 hover:text-error transition-colors">Reset</a>
            @endif
        </div>
    </form>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 stagger-children">
    {{-- Total Leads --}}
    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-transparent hover:border-primary/10 transition-all group hover-lift animate-fade-in">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-primary-container/30 rounded-lg group-hover:bg-primary group-hover:text-on-primary transition-colors">
                <x-icon name="groups" class="w-5 h-5" />
            </div>
        </div>
        <h3 class="text-on-surface-variant text-xs font-medium uppercase tracking-wider mb-1">Total Leads</h3>
        <p class="text-3xl font-extrabold font-headline text-on-surface">{{ number_format($totalLeads) }}</p>
    </div>

    {{-- Conversion Rate --}}
    @php
        $surveyCount = $statusDistribution->firstWhere('name', config('statuses.survey', 'Masuk Survey'));
        $surveys = $surveyCount ? $surveyCount['count'] : 0;
        $convRate = $totalLeads > 0 ? round(($surveys / $totalLeads) * 100, 1) : 0;
    @endphp
    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-transparent hover:border-primary/10 transition-all group hover-lift animate-fade-in">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-tertiary-container/30 rounded-lg group-hover:bg-tertiary group-hover:text-on-primary transition-colors">
                <x-icon name="trending_up" class="w-5 h-5" />
            </div>
        </div>
        <h3 class="text-on-surface-variant text-xs font-medium uppercase tracking-wider mb-1">Conversion Rate</h3>
        <p class="text-3xl font-extrabold font-headline text-on-surface">{{ $convRate }}%</p>
    </div>

    {{-- Surveys Output --}}
    <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-transparent hover:border-primary/10 transition-all group hover-lift animate-fade-in">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-inverse-primary/20 rounded-lg group-hover:bg-inverse-primary group-hover:text-on-primary transition-colors">
                <x-icon name="assignment" class="w-5 h-5" />
            </div>
        </div>
        <h3 class="text-on-surface-variant text-xs font-medium uppercase tracking-wider mb-1">Total Survey</h3>
        <p class="text-3xl font-extrabold font-headline text-on-surface">{{ number_format($surveys) }}</p>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
    {{-- Bar Chart: Status Distribution --}}
    <div class="lg:col-span-3 bg-surface-container-lowest p-8 rounded-2xl shadow-sm animate-fade-in">
        <div class="mb-6">
            <h2 class="text-xl font-bold font-headline text-on-surface">Status Distribution</h2>
            <p class="text-xs text-on-surface-variant">Jumlah leads per kategori status</p>
        </div>

        @php
            $maxCount = $statusDistribution->max('count') ?: 1;
        @endphp

        <div class="space-y-4 mt-6">
            @foreach($statusDistribution as $item)
            @php $barWidth = ($item['count'] / $maxCount) * 100; @endphp
            <div class="group">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold text-on-surface">{{ $item['name'] }}</span>
                    <span class="text-xs font-bold text-on-surface-variant">{{ $item['count'] }}</span>
                </div>
                <div class="w-full h-3 bg-surface-container-high rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 ease-out group-hover:opacity-80"
                         style="width: {{ $barWidth }}%; background-color: {{ $item['color'] }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Doughnut Chart --}}
    <div class="lg:col-span-2 bg-surface-container-lowest p-8 rounded-2xl shadow-sm flex flex-col items-center animate-fade-in">
        <div class="w-full mb-8">
            <h2 class="text-xl font-bold font-headline text-on-surface">Pipeline Health</h2>
            <p class="text-xs text-on-surface-variant">Proporsi setiap status terhadap total</p>
        </div>

        @php
            $total = collect($statusDistribution)->sum('count');
            $cumulative = 0;
        @endphp

        <div class="relative w-48 h-48 mb-8">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                @if($total > 0)
                    @foreach($statusDistribution as $item)
                        @php
                            $pct = ($item['count'] / $total) * 100;
                            $offset = -$cumulative;
                            $cumulative += $pct;
                        @endphp
                        <circle cx="18" cy="18" r="15.915" fill="transparent"
                                stroke="{{ $item['color'] }}"
                                stroke-width="4"
                                stroke-dasharray="{{ $pct }} {{ 100 - $pct }}"
                                stroke-dashoffset="{{ $offset }}"></circle>
                    @endforeach
                @else
                    <circle cx="18" cy="18" r="15.915" fill="transparent"
                            stroke="#dbe4e7" stroke-width="4"
                            stroke-dasharray="100 0" stroke-dashoffset="0"></circle>
                @endif
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-2xl font-extrabold text-on-surface">{{ number_format($total) }}</span>
                <span class="text-[9px] text-on-surface-variant font-bold uppercase">Total Leads</span>
            </div>
        </div>

        <div class="w-full space-y-3">
            @foreach($statusDistribution as $item)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item['color'] }}"></span>
                    <span class="text-xs font-medium text-on-surface">{{ $item['name'] }}</span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-on-surface">{{ $item['count'] }}</span>
                    <span class="text-[10px] text-on-surface-variant ml-1">({{ $total > 0 ? round(($item['count'] / $total) * 100, 1) : 0 }}%)</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Needs Category Chart Row --}}
<div class="mt-8 bg-surface-container-lowest p-8 rounded-2xl shadow-sm animate-fade-in">
    <div class="mb-6">
        <h2 class="text-xl font-bold font-headline text-on-surface">Kategori Kebutuhan</h2>
        <p class="text-xs text-on-surface-variant">Distribusi leads berdasarkan kategori minat pelayanan</p>
    </div>

    @php
        $maxNeedsCount = $needsDistribution->max('count') ?: 1;
    @endphp

    @if($needsDistribution->isEmpty())
        <div class="flex flex-col items-center justify-center py-10 text-on-surface-variant opacity-60">
            <x-icon name="pie_chart" class="w-10 h-10 mb-2" />
            <p class="text-[13px] font-medium">Belum ada data kategori kebutuhan pada periode ini.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-8 gap-y-6 mt-6">
            @foreach($needsDistribution as $item)
            @php $barWidth = ($item['count'] / $maxNeedsCount) * 100; @endphp
            <div class="group">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-on-surface truncate pr-2" title="{{ $item['name'] }}">{{ $item['name'] }}</span>
                    <span class="text-xs font-extrabold text-on-surface-variant">{{ $item['count'] }}</span>
                </div>
                <div class="w-full h-2.5 bg-surface-container-high rounded-full overflow-hidden shadow-inner">
                    <div class="h-full rounded-full transition-all duration-700 ease-out bg-primary group-hover:brightness-110"
                         style="width: {{ $barWidth }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Super Admin Rankings --}}
@if(auth()->user()->isSuperAdmin())
<div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mt-8">

    {{-- Account Ranking --}}
    @if($accountRanking->count())
    <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden animate-fade-in flex flex-col">
        <div class="px-8 py-6 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold font-headline text-on-surface">Account Ranking</h2>
                <p class="text-xs text-on-surface-variant">Peringkat berdasarkan rasio survey</p>
            </div>
        </div>
        <div class="overflow-x-auto flex-1">
            <table class="w-full min-w-max text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-surface-container-low/50">
                        <th class="px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Rank</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Account</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Total Leads</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Survey</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Conversion Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low">
                    @foreach($accountRanking as $index => $item)
                    <tr class="hover:bg-surface-container-low/30 transition-colors">
                        <td class="px-8 py-5">
                            @php
                                $badgeColors = ['bg-amber-100 text-amber-700', 'bg-slate-200 text-slate-700', 'bg-orange-100 text-orange-700'];
                                $badgeClass = $badgeColors[$index] ?? 'bg-surface-container text-on-surface-variant';
                            @endphp
                            <div class="w-8 h-8 rounded-full {{ $badgeClass }} flex items-center justify-center font-bold text-sm">{{ $index + 1 }}</div>
                        </td>
                        <td class="px-8 py-5">
                            <span class="font-semibold text-on-surface">{{ $item['name'] }}</span>
                        </td>
                        <td class="px-8 py-5">
                            <span class="font-bold text-on-surface">{{ $item['total'] }}</span>
                        </td>
                        <td class="px-8 py-5">
                            <span class="text-sm font-bold text-tertiary">{{ $item['surveys'] }}</span>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-24 h-1.5 bg-surface-container-high rounded-full overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" style="width: {{ min($item['rate'], 100) }}%"></div>
                                </div>
                                <span class="text-sm font-bold {{ $item['rate'] > 20 ? 'text-tertiary' : 'text-on-surface-variant' }}">
                                    {{ $item['rate'] }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Admin Ranking --}}
    @if(isset($adminRanking) && $adminRanking->count())
    <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden animate-fade-in flex flex-col">
        <div class="px-8 py-6 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold font-headline text-on-surface">Admin Ranking</h2>
                <p class="text-xs text-on-surface-variant">Peringkat berdasarkan volume klien yang masuk</p>
            </div>
        </div>
        <div class="overflow-x-auto flex-1">
            <table class="w-full min-w-max text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-surface-container-low/50">
                        <th class="px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest w-16">Rank</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Admin Name</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Account / Cabang</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest text-right">Total Klien Input</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-low">
                    @foreach($adminRanking as $index => $item)
                    <tr class="hover:bg-surface-container-low/30 transition-colors">
                        <td class="px-8 py-5">
                            @php
                                $badgeColors = ['bg-amber-100 text-amber-700', 'bg-slate-200 text-slate-700', 'bg-orange-100 text-orange-700'];
                                $badgeClass = $badgeColors[$index] ?? 'bg-surface-container text-on-surface-variant';
                            @endphp
                            <div class="w-8 h-8 rounded-full {{ $badgeClass }} flex items-center justify-center font-bold text-sm">{{ $index + 1 }}</div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-secondary-container text-secondary-dim flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($item['name'], 0, 2)) }}
                                </div>
                                <span class="font-semibold text-on-surface">{{ $item['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <span class="text-on-surface-variant text-sm">{{ $item['account'] }}</span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <span class="text-lg font-bold text-primary">{{ $item['total'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endif
@endsection
