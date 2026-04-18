<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Consultation;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = AuditLog::with(['user']);

        if ($user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        $actions = ['created', 'updated', 'deleted', 'retrieved'];

        return view('audit-logs.index', compact('logs', 'actions'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load(['user']);

        return view('audit-logs.show', compact('auditLog'));
    }

    public function consultationHistory(Consultation $consultation)
    {
        $this->authorize('viewHistory', $consultation);

        $logs = AuditLog::where('loggable_type', Consultation::class)
            ->where('loggable_id', $consultation->id)
            ->with(['user'])
            ->latest()
            ->paginate(20);

        return view('audit-logs.consultation', compact('consultation', 'logs'));
    }
}
