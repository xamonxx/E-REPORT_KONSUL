<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultationNote;
use App\Models\Reminder;
use App\Services\NotificationSummaryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationSummaryService $notificationSummaryService
    ) {
    }

    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();

        $summary = $this->notificationSummaryService->getForUser($user);

        return response()->json([
            'unread_notes' => $summary['unreadNotesCount'],
            'upcoming_reminders' => $summary['upcomingRemindersCount'],
            'total' => $summary['initialTotalAlerts'],
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }

    public function markNoteRead(ConsultationNote $note): JsonResponse
    {
        $user = Auth::user();

        // Otorisasi via Policy: cek akses terhadap konsultasi terkait
        $consultation = $note->consultation;
        if (!$consultation || Gate::denies('view', $consultation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $note->update(['is_read' => true]);
        $this->notificationSummaryService->forgetForUser($user->id);
        
        return response()->json(['success' => true]);
    }

    public function markReminderRead(Reminder $reminder): JsonResponse
    {
        $user = Auth::user();

        // Otorisasi via Policy
        if (Gate::denies('markAsRead', $reminder)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reminder->update(['is_read' => true]);
        $this->notificationSummaryService->forgetForUser($user->id);
        
        return response()->json(['success' => true]);
    }
}
