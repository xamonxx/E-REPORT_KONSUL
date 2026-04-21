<?php

namespace App\Providers;

use App\Models\Consultation;
use App\Models\ConsultationNote;
use App\Models\Reminder;
use App\Models\ReportAttendance;
use App\Observers\AuditObserver;
use App\Policies\ConsultationNotePolicy;
use App\Policies\ConsultationPolicy;
use App\Policies\ReminderPolicy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Enums\UserRole;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Register Policies ────────────────────────────────
        Gate::policy(Consultation::class, ConsultationPolicy::class);
        Gate::policy(ConsultationNote::class, ConsultationNotePolicy::class);
        Gate::policy(Reminder::class, ReminderPolicy::class);

        Consultation::observe(AuditObserver::class);

        // ── Cache Invalidation ───────────────────────────────
        $clearDashboardCache = function ($model = null) {
            Cache::forget('dashboard:super_admin');

            // Also clear admin dashboard cache for the affected account
            $accountId = null;
            if ($model instanceof Consultation) {
                $accountId = $model->account_id;
            } elseif ($model instanceof ReportAttendance) {
                $accountId = $model->account_id;
            }
            if ($accountId) {
                Cache::forget("dashboard:admin:{$accountId}");
            }
        };

        Consultation::created($clearDashboardCache);
        Consultation::updated($clearDashboardCache);
        Consultation::deleted($clearDashboardCache);
        ReportAttendance::created($clearDashboardCache);

        $forgetNotificationCaches = function (?Consultation $consultation, ?int $ownerUserId = null) {
            if (!$consultation) {
                return;
            }

            $accountId = $consultation->account_id;
            $users = \App\Models\User::query()
                ->where(function ($q) use ($accountId, $ownerUserId) {
                    $q->where('account_id', $accountId)
                        ->orWhere('role', UserRole::SuperAdmin);

                    if ($ownerUserId) {
                        $q->orWhere('id', $ownerUserId);
                    }
                })
                ->pluck('id')
                ->unique();

            foreach ($users as $userId) {
                Cache::forget("unread_notes_count_{$userId}");
                Cache::forget("api_notif_{$userId}");
            }
        };

        // Invalidate per-user notification caches when notes are created
        ConsultationNote::created(function (ConsultationNote $note) use ($forgetNotificationCaches) {
            $forgetNotificationCaches($note->consultation, $note->user_id);
        });

        Reminder::created(function (Reminder $reminder) use ($forgetNotificationCaches) {
            $forgetNotificationCaches($reminder->consultation, $reminder->user_id);
        });

        Reminder::updated(function (Reminder $reminder) use ($forgetNotificationCaches) {
            $forgetNotificationCaches($reminder->consultation, $reminder->user_id);
        });

        Reminder::deleted(function (Reminder $reminder) use ($forgetNotificationCaches) {
            $forgetNotificationCaches($reminder->consultation, $reminder->user_id);
        });

        // ── View Composer: header notifications ──────────────
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();

                $unreadNotesCount = ConsultationNote::where('is_read', false)
                    ->where('user_id', '!=', $user->id)
                    ->whereHas('consultation', fn($q) => $q->forUser($user))
                    ->count();

                // Header dropdown: active reminders (eager load consultation & user)
                $activeReminders = Reminder::forUser($user)
                    ->where('is_read', false)
                    ->with(['consultation:id,client_name', 'user:id,name'])
                    ->orderBy('remind_at', 'asc')
                    ->take(5)
                    ->get();

                // Header dropdown: unread notes (eager load user & consultation)
                $unreadNotes = ConsultationNote::with('user:id,name', 'consultation:id,client_name')
                    ->where('is_read', false)
                    ->where('user_id', '!=', $user->id)
                    ->whereHas('consultation', fn($q) => $q->forUser($user))
                    ->latest()
                    ->take(5)
                    ->get();

                $initialTotalAlerts = $activeReminders->count() + $unreadNotesCount;

                $view->with(compact('unreadNotesCount', 'activeReminders', 'unreadNotes', 'initialTotalAlerts'));
            } else {
                $view->with([
                    'unreadNotesCount' => 0,
                    'activeReminders' => collect(),
                    'unreadNotes' => collect(),
                    'initialTotalAlerts' => 0,
                ]);
            }
        });
    }
}
