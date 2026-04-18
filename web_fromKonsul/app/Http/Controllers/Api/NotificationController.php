<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\ConsultationNote;
use App\Models\Reminder;
use App\Models\StatusCategory;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        
        $cacheKey = "api_notif_{$user->id}";
        
        $cachedResponse = Cache::remember($cacheKey, 60, function () use ($user) {
            $unreadNotesQuery = ConsultationNote::where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->whereHas('consultation', fn($q) => $q->forUser($user));

            $unreadNotesCount = $unreadNotesQuery->count();

            $upcomingRemindersQuery = Reminder::where('user_id', $user->id)
                ->where('is_read', false)
                ->where('remind_at', '<=', Carbon::now()->addMinutes(30))
                ->whereHas('consultation', fn($q) => $q->forUser($user));

            $upcomingRemindersCount = $upcomingRemindersQuery->count();

            // New leads berbasis status indikasi awal (direct ID lookup, no JOIN)
            $newContactStatusId = StatusCategory::where('name', config('statuses.new_contact', 'Kontak Masuk'))->value('id');

            $newLeadsCount = $newContactStatusId
                ? Consultation::where('status_category_id', $newContactStatusId)->forUser($user)->count()
                : 0;

            return [
                'unread_notes' => $unreadNotesCount,
                'upcoming_reminders' => $upcomingRemindersCount,
                'new_leads' => $newLeadsCount,
                'total' => $unreadNotesCount + $upcomingRemindersCount + $newLeadsCount,
            ];
        });

        $cachedResponse['timestamp'] = Carbon::now()->toIso8601String();

        return response()->json($cachedResponse);
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
        
        // Hapus cache Polling notifikasi milik user ini
        Cache::forget("api_notif_{$user->id}");
        
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
        
        // Hapus cache Polling notifikasi
        Cache::forget("api_notif_{$user->id}");
        
        return response()->json(['success' => true]);
    }
}
