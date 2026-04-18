<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\User;

class ConsultationPolicy
{
    /**
     * Super Admin bisa mengakses semua konsultasi.
     * Jika return true, method policy lain akan di-bypass.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::SuperAdmin) {
            return true;
        }

        return null;
    }

    /**
     * Menentukan apakah user bisa melihat daftar konsultasi.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah user bisa melihat detail konsultasi tertentu.
     */
    public function view(User $user, Consultation $consultation): bool
    {
        return $user->account_id === $consultation->account_id;
    }

    /**
     * Menentukan apakah user bisa membuat konsultasi baru.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah user bisa mengedit konsultasi tertentu.
     */
    public function update(User $user, Consultation $consultation): bool
    {
        return $user->account_id === $consultation->account_id;
    }

    /**
     * Menentukan apakah user bisa menghapus konsultasi tertentu.
     */
    public function delete(User $user, Consultation $consultation): bool
    {
        return $user->account_id === $consultation->account_id;
    }

    /**
     * Menentukan apakah user bisa melihat riwayat audit konsultasi.
     */
    public function viewHistory(User $user, Consultation $consultation): bool
    {
        return $user->account_id === $consultation->account_id;
    }

    /**
     * Menentukan apakah user bisa menambah catatan pada konsultasi.
     */
    public function addNote(User $user, Consultation $consultation): bool
    {
        return $user->account_id === $consultation->account_id;
    }

    /**
     * Menentukan apakah user bisa menambah reminder pada konsultasi.
     */
    public function addReminder(User $user, Consultation $consultation): bool
    {
        return $user->account_id === $consultation->account_id;
    }
}
