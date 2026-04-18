@extends('layouts.app')

@section('title', 'Detail Audit Log')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Detail Audit Log</h2>
        <a href="{{ route('audit-logs.index') }}" class="text-blue-600 hover:text-blue-800">← Kembali</a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Umum</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Waktu</dt>
                <dd class="text-sm text-gray-900">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Action</dt>
                <dd class="text-sm">
                    @php $actionClass = ['created' => 'bg-green-100 text-green-800', 'updated' => 'bg-blue-100 text-blue-800', 'deleted' => 'bg-red-100 text-red-800', 'retrieved' => 'bg-gray-100 text-gray-800'][@$auditLog->action] @endphp
                    <span class="px-2 py-1 text-xs rounded-full {{ $actionClass ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($auditLog->action) }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">User</dt>
                <dd class="text-sm text-gray-900">{{ $auditLog->user_name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                <dd class="text-sm text-gray-900">{{ $auditLog->ip_address ?? '-' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                <dd class="text-sm text-gray-900">{{ $auditLog->description }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                <dd class="text-sm text-gray-500">{{ $auditLog->user_agent ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    @if($auditLog->action !== 'retrieved')
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Perubahan Data</h3>
        
        @if($auditLog->old_values)
        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-500 mb-2">Nilai Lama</h4>
            <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif

        @if($auditLog->new_values)
        <div>
            <h4 class="text-sm font-medium text-gray-500 mb-2">Nilai Baru</h4>
            <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif

        @if($auditLog->action === 'updated' && $auditLog->old_values && $auditLog->new_values)
        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-500 mb-2">Diff</h4>
            <div class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm">
                @foreach($auditLog->new_values as $key => $newValue)
                    @if(isset($auditLog->old_values[$key]) && $auditLog->old_values[$key] !== $newValue)
                        <div class="mb-2">
                            <span class="font-medium text-gray-700">{{ $key }}</span>: 
                            <span class="text-red-600">{{ $auditLog->old_values[$key] }}</span>
                            <span class="mx-1">→</span>
                            <span class="text-green-600">{{ $newValue }}</span>
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