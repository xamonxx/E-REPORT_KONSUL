@extends('layouts.app')

@section('title', 'Audit Logs')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Audit Logs</h2>
    </div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border">
    <div class="p-4 border-b">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi atau user..." class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-40">
                <select name="action" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Action</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Mulai">
            </div>
            <div class="w-40">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Selesai">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
                <a href="{{ route('audit-logs.index') }}" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Reset</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $log->user_name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php $actionClass = ['created' => 'bg-green-100 text-green-800', 'updated' => 'bg-blue-100 text-blue-800', 'deleted' => 'bg-red-100 text-red-800', 'retrieved' => 'bg-gray-100 text-gray-800'][@$log->action] @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $actionClass ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($log->action) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $log->description }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $log->ip_address ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('audit-logs.show', $log) }}" class="text-blue-600 hover:text-blue-800">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada data audit logs.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="p-4 border-t">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection