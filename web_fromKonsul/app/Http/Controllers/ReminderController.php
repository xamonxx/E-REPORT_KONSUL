<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReminderController extends Controller
{
    public function store(Request $request, Consultation $consultation)
    {
        $this->authorize('addReminder', $consultation);

        $user = auth()->user();

        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'remind_at' => 'required|date|after:now',
        ]);

        $consultation->reminders()->create([
            'user_id' => $user->id,
            'message' => $validated['message'],
            'remind_at' => $validated['remind_at'],
        ]);

        Cache::forget("api_notif_{$user->id}");

        return back()->with('success', 'Pengingat berhasil dibuat.');
    }

    public function markAsRead(Reminder $reminder)
    {
        $this->authorize('markAsRead', $reminder);

        $reminder->update(['is_read' => true]);
        Cache::forget('api_notif_' . auth()->id());

        return back()->with('success', 'Pengingat ditandai selesai.');
    }

    public function destroy(Consultation $consultation, Reminder $reminder)
    {
        // Validasi: consultation dan reminder harus berkaitan
        if ($reminder->consultation_id !== $consultation->id) {
            abort(404);
        }

        $this->authorize('delete', $reminder);

        $reminder->delete();
        Cache::forget('api_notif_' . auth()->id());

        return back()->with('success', 'Pengingat berhasil dihapus.');
    }
}
