@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="page-header mb-6">
    <div class="page-header__content">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight font-headline">Audit Logs</h2>
        <p class="text-sm sm:text-base text-on-surface-variant mt-1">Pantau histori perubahan data secara detail dengan tampilan yang tetap nyaman di mobile maupun desktop.</p>
    </div>
</div>

<div class="filter-card mb-6">
    <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-col gap-4">
        <div class="filter-grid">
            <div>
                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 px-1">Cari Aktivitas</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi atau user..." class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 px-1">Jenis Action</label>
                <select name="action" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner">
                    <option value="">Semua Action</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 px-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2 px-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20 shadow-inner">
            </div>
        </div>
        <div class="filter-actions">
            <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary text-on-primary rounded-xl hover:bg-primary-dim font-bold text-sm shadow-lg shadow-primary/20">Filter</button>
            <a href="{{ route('audit-logs.index') }}" class="w-full sm:w-auto text-center px-6 py-3 bg-surface-container-low text-on-surface-variant rounded-xl hover:bg-surface-container font-bold text-sm transition-colors">Reset</a>
        </div>
    </form>
</div>

<div class="table-panel">
    <div class="table-scroll-mobile overflow-x-auto">
        <table class="w-full min-w-[820px] border-collapse whitespace-nowrap">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-4 sm:px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Waktu</th>
                    <th class="px-4 sm:px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">User</th>
                    <th class="px-4 sm:px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Action</th>
                    <th class="px-4 sm:px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Deskripsi</th>
                    <th class="px-4 sm:px-6 py-4 text-left text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">IP Address</th>
                    <th class="px-4 sm:px-6 py-4 text-right text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-container-low">
                @forelse($logs as $log)
                    <tr class="hover:bg-surface-container-low/30 transition-colors">
                        <td class="px-4 sm:px-6 py-4 text-sm text-on-surface-variant font-medium">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm font-bold text-on-surface">{{ $log->user_name ?? '-' }}</td>
                        <td class="px-4 sm:px-6 py-4">
                            @php
                                $actionClass = [
                                    'created' => 'bg-green-100 text-green-800 border border-green-200',
                                    'updated' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                    'deleted' => 'bg-red-100 text-red-800 border border-red-200',
                                    'retrieved' => 'bg-surface-container text-on-surface-variant border border-surface-container-high',
                                ][@$log->action];
                            @endphp
                            <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full {{ $actionClass ?? 'bg-surface-container text-on-surface-variant border border-surface-container-high' }}">{{ ucfirst($log->action) }}</span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-on-surface">{{ $log->description }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-on-surface-variant">{{ $log->ip_address ?? '-' }}</td>
                        <td class="px-4 sm:px-6 py-4 text-right">
                            <a href="{{ route('audit-logs.show', $log) }}" class="inline-flex items-center justify-center rounded-xl bg-primary/10 text-primary hover:bg-primary/20 px-4 py-2 text-sm font-bold transition-colors">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 sm:px-6 py-12 text-center text-on-surface-variant">Tidak ada data audit logs.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="p-4 sm:p-6 border-t border-surface-container-low">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
