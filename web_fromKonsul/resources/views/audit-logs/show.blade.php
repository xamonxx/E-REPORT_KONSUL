@extends('layouts.app')

@section('title', 'Detail Audit Log')

@section('content')
<div class="page-header mb-6">
    <div class="page-header__content">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight font-headline">Detail Audit Log</h2>
        <p class="text-sm sm:text-base text-on-surface-variant mt-1">Lihat detail perubahan data, payload lama dan baru, serta ringkasan diff dengan tampilan yang lebih enak dibaca.</p>
    </div>
    <div class="page-header__actions">
        <a href="{{ route('audit-logs.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-primary/10 text-primary hover:bg-primary/20 px-5 py-3 text-sm font-bold transition-colors">
            Kembali ke Audit Logs
        </a>
    </div>
</div>

<div class="space-y-6">
    <div class="table-panel p-6 sm:p-8">
        <h3 class="text-lg font-bold text-on-surface mb-5 font-headline">Informasi Umum</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl bg-surface-container-low p-4">
                <dt class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Waktu</dt>
                <dd class="text-sm text-on-surface font-semibold mt-2">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</dd>
            </div>
            <div class="rounded-xl bg-surface-container-low p-4">
                <dt class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Action</dt>
                <dd class="text-sm mt-2">
                    @php
                        $actionClass = [
                            'created' => 'bg-green-100 text-green-800 border border-green-200',
                            'updated' => 'bg-blue-100 text-blue-800 border border-blue-200',
                            'deleted' => 'bg-red-100 text-red-800 border border-red-200',
                            'retrieved' => 'bg-surface-container text-on-surface-variant border border-surface-container-high',
                        ][@$auditLog->action];
                    @endphp
                    <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full {{ $actionClass ?? 'bg-surface-container text-on-surface-variant border border-surface-container-high' }}">{{ ucfirst($auditLog->action) }}</span>
                </dd>
            </div>
            <div class="rounded-xl bg-surface-container-low p-4">
                <dt class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">User</dt>
                <dd class="text-sm text-on-surface font-semibold mt-2">{{ $auditLog->user_name ?? '-' }}</dd>
            </div>
            <div class="rounded-xl bg-surface-container-low p-4">
                <dt class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">IP Address</dt>
                <dd class="text-sm text-on-surface font-semibold mt-2">{{ $auditLog->ip_address ?? '-' }}</dd>
            </div>
            <div class="rounded-xl bg-surface-container-low p-4 md:col-span-2">
                <dt class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Deskripsi</dt>
                <dd class="text-sm text-on-surface mt-2 leading-relaxed">{{ $auditLog->description }}</dd>
            </div>
            <div class="rounded-xl bg-surface-container-low p-4 md:col-span-2">
                <dt class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">User Agent</dt>
                <dd class="text-sm text-on-surface-variant mt-2 break-words">{{ $auditLog->user_agent ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    @if($auditLog->action !== 'retrieved')
    <div class="table-panel p-6 sm:p-8">
        <h3 class="text-lg font-bold text-on-surface mb-5 font-headline">Perubahan Data</h3>

        @if($auditLog->old_values)
        <div class="mb-6">
            <h4 class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-3">Nilai Lama</h4>
            <pre class="bg-surface-container-low p-4 rounded-xl overflow-x-auto text-sm text-on-surface-variant leading-relaxed">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif

        @if($auditLog->new_values)
        <div>
            <h4 class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-3">Nilai Baru</h4>
            <pre class="bg-surface-container-low p-4 rounded-xl overflow-x-auto text-sm text-on-surface-variant leading-relaxed">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif

        @if($auditLog->action === 'updated' && $auditLog->old_values && $auditLog->new_values)
        <div class="mt-6">
            <h4 class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-3">Diff</h4>
            <div class="bg-surface-container-low p-4 rounded-xl overflow-x-auto text-sm">
                @foreach($auditLog->new_values as $key => $newValue)
                    @if(isset($auditLog->old_values[$key]) && $auditLog->old_values[$key] !== $newValue)
                        <div class="mb-2 last:mb-0 break-words">
                            <span class="font-bold text-on-surface">{{ $key }}</span>:
                            <span class="text-error">{{ $auditLog->old_values[$key] }}</span>
                            <span class="mx-1 text-on-surface-variant">-></span>
                            <span class="text-green-700">{{ $newValue }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
