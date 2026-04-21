@extends('layouts.app')
@section('title', 'Monitoring Laporan Harian')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8">
    <div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight font-headline">Monitoring Laporan Harian</h2>
        <p class="text-sm sm:text-base text-on-surface-variant mt-1">Rekap per tanggal absensi pelaporan admin.</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm mb-8 animate-fade-in border border-surface-container-low">
    <form action="{{ route('report-attendances.index') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-end gap-4">
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

{{-- Main Table --}}
<div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden flex flex-col animate-fade-in border border-surface-container-low max-w-full">
    <div class="px-6 sm:px-8 py-6 flex flex-col xl:flex-row justify-between items-start xl:items-center bg-white border-b border-surface-container-low gap-6">
        <div>
            <h2 class="text-xl font-bold font-headline text-on-surface">Data Rekap Laporan Admin</h2>
            <p class="text-xs text-on-surface-variant mt-1.5 flex items-center gap-1.5">
                <x-icon name="event" class="w-3.5 h-3.5" />
                <span>Tanggal: <span class="font-bold text-primary">{{ $date->translatedFormat('d F Y') }}</span></span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-[10px] sm:text-xs font-semibold no-print">
            <span class="flex items-center gap-1.5 text-green-700 bg-green-50 px-2 py-1 rounded-lg border border-green-100 shadow-sm"><span class="w-3 h-3 rounded bg-green-500 shrink-0"></span> Ada WA Baru</span>
            <span class="flex items-center gap-1.5 text-yellow-700 bg-yellow-50 px-2 py-1 rounded-lg border border-yellow-100 shadow-sm"><span class="w-3 h-3 rounded bg-yellow-500 shrink-0"></span> 0 Data WA</span>
            <span class="flex items-center gap-1.5 text-sky-700 bg-sky-50 px-2 py-1 rounded-lg border border-sky-100 shadow-sm"><span class="w-3 h-3 rounded bg-sky-500 shrink-0"></span> Libur / Susulan</span>
            <span class="flex items-center gap-1.5 text-error bg-error/5 px-2 py-1 rounded-lg border border-error/10 shadow-sm"><span class="w-3 h-3 rounded bg-error shrink-0"></span> Tidak Laporan</span>
        </div>
    </div>
    
    <div class="table-scroll-mobile overflow-x-auto scrollbar-thin scrollbar-thumb-surface-container shadow-inner">
        <table class="w-full min-w-[750px] text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-6 sm:px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest text-center">Waktu Report</th>
                    <th class="px-6 sm:px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest font-headline">Administrator</th>
                    <th class="px-6 sm:px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Akun</th>
                    <th class="px-6 sm:px-8 py-4 text-[10px] font-bold text-on-surface-variant uppercase tracking-widest text-right">Status Absensi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @forelse($adminAttendances as $att)
                <tr class="hover:bg-surface-container-low/30 transition-colors">
                    <td class="px-6 sm:px-8 py-5 text-center">
                        @if($att->has_reported)
                            <div class="flex flex-col items-center">
                                <span class="text-on-surface font-bold text-base leading-none">{{ $att->reported_at->format('H:i') }}</span>
                                <span class="text-[9px] text-on-surface-variant font-bold uppercase mt-1">WIB</span>
                            </div>
                        @else
                            <span class="text-error/30 font-bold text-xl leading-none">—</span>
                        @endif
                    </td>
                    <td class="px-6 sm:px-8 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center font-bold text-xs text-on-surface-variant shrink-0 ring-2 ring-white shadow-sm">
                                {{ strtoupper(substr($att->admin->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-on-surface text-sm block truncate max-w-[150px]">{{ $att->admin->name }}</span>
                                <span class="text-[9px] text-on-surface-variant font-bold uppercase tracking-wider block mt-0.5">ADMIN AKUN</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 sm:px-8 py-5">
                        <span class="text-sm font-bold text-on-surface-variant truncate max-w-[140px] block">{{ $att->account?->name ?? 'Belum Terhubung' }}</span>
                    </td>
                    <td class="px-6 sm:px-8 py-5 text-right">
                        <div class="flex justify-end">
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
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 sm:px-8 py-16 text-center">
                        <div class="flex flex-col items-center opacity-40">
                            <x-icon name="person_off" class="w-12 h-12 mb-2" />
                            <p class="text-sm font-bold">Belum ada data administrator yang terdaftar.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
