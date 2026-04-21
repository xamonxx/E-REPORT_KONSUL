<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminder extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'remind_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope query berdasarkan hak akses user.
     * Admin hanya melihat pengingat miliknya, SuperAdmin melihat semua.
     */
    public function scopeForUser($query, $user)
    {
        if ($user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
