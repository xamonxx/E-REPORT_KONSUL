<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\ConsultationNote;
use App\Models\User;

class ConsultationNotePolicy
{
    /**
     * Super Admin bisa melakukan semua aksi pada catatan.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::SuperAdmin) {
            return true;
        }

        return null;
    }

    /**
     * Menentukan apakah user bisa menghapus catatan.
     * Admin hanya bisa menghapus catatan miliknya sendiri
     * yang berada di konsultasi milik akunnya.
     */
    public function delete(User $user, ConsultationNote $note): bool
    {
        $consultation = $note->consultation ?? Consultation::find($note->consultation_id);

        if (!$consultation) {
            return false;
        }

        if ($user->account_id !== $consultation->account_id) {
            return false;
        }

        // Admin hanya bisa menghapus catatan miliknya sendiri
        return $note->user_id === $user->id;
    }
}
