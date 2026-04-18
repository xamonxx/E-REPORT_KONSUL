<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\ConsultationNote;
use Illuminate\Http\Request;

class ConsultationNoteController extends Controller
{
    public function store(Request $request, Consultation $consultation)
    {
        $this->authorize('addNote', $consultation);

        $user = auth()->user();

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $consultation->timelineNotes()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Catatan berhasil ditambahkan.');
    }

    public function destroy(Consultation $consultation, ConsultationNote $note)
    {
        // Validasi: note harus berkaitan dengan consultation ini
        if ($note->consultation_id !== $consultation->id) {
            abort(404);
        }

        $this->authorize('delete', $note);

        $note->delete();

        return back()->with('success', 'Catatan berhasil dihapus.');
    }
}
