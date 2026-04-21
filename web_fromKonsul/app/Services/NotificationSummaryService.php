<?php

namespace App\Services;

use App\Models\ConsultationNote;
use App\Models\Reminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class NotificationSummaryService
{
    public function getForUser(User $user): array
    {
        return Cache::remember(
            $this->cacheKey($user->id),
            now()->addMinutes(2),
            fn () => $this->buildSummary($user)
        );
    }

    public function forgetForUser(int $userId): void
    {
        Cache::forget($this->cacheKey($userId));
    }

    private function buildSummary(User $user): array
    {
        $unreadNotesCount = ConsultationNote::query()
            ->where('is_read', false)
            ->where('user_id', '!=', $user->id)
            ->whereHas('consultation', fn ($query) => $query->forUser($user))
            ->count();

        $activeReminders = Reminder::query()
            ->forUser($user)
            ->where('is_read', false)
            ->with(['consultation:id,client_name', 'user:id,name'])
            ->orderBy('remind_at')
            ->take(5)
            ->get();

        $upcomingRemindersCount = Reminder::query()
            ->forUser($user)
            ->where('is_read', false)
            ->where('remind_at', '<=', Carbon::now()->addMinutes(30))
            ->count();

        $unreadNotes = ConsultationNote::query()
            ->with(['user:id,name', 'consultation:id,client_name'])
            ->where('is_read', false)
            ->where('user_id', '!=', $user->id)
            ->whereHas('consultation', fn ($query) => $query->forUser($user))
            ->latest()
            ->take(5)
            ->get();

        return [
            'unreadNotesCount' => $unreadNotesCount,
            'upcomingRemindersCount' => $upcomingRemindersCount,
            'activeReminders' => $activeReminders,
            'unreadNotes' => $unreadNotes,
            'initialTotalAlerts' => $unreadNotesCount + $upcomingRemindersCount,
        ];
    }

    private function cacheKey(int $userId): string
    {
        return "api_notif_{$userId}";
    }
}
