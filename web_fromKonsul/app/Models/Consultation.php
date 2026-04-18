<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Consultation extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'consultation_id',
        'client_name',
        'phone',
        'province',
        'city',
        'district',
        'address',
        'account_id',
        'needs_category_id',
        'status_category_id',
        'notes',
        'created_by',
        'consultation_date',
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
        ];
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function needsCategory()
    {
        return $this->belongsTo(NeedsCategory::class);
    }

    public function statusCategory()
    {
        return $this->belongsTo(StatusCategory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timelineNotes()
    {
        return $this->hasMany(ConsultationNote::class, 'consultation_id')->latest();
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class)->latest();
    }

    // ── Query Scopes ─────────────────────────────────

    /**
     * Scope query berdasarkan hak akses user.
     * Admin hanya melihat data milik akunnya, SuperAdmin melihat semua.
     */
    public function scopeForUser($query, $user)
    {
        if ($user->isAdmin()) {
            $query->where('account_id', $user->account_id);
        }

        return $query;
    }

    /**
     * Auto-generate consultation ID in format YY.MMDD-NNN
     *
     * Menggunakan DB::transaction + lockForUpdate untuk mencegah
     * race condition / duplicate ID saat concurrent requests. (Fix #4)
     */
    public static function generateConsultationId($accountId = null): string
    {
        return DB::transaction(function () use ($accountId) {
            $now = Carbon::now();
            $prefix = $now->format('y.md');
            
            // Format ID Akun, default ke 000 jika tidak ada
            $accountPadded = $accountId ? str_pad($accountId, 3, '0', STR_PAD_LEFT) : '000';
            
            $basePrefix = $prefix . '-' . $accountPadded;

            $lastToday = static::where('consultation_id', 'like', $basePrefix . '-%')
                ->lockForUpdate()
                ->orderByDesc('consultation_id')
                ->first();

            if ($lastToday) {
                // Ekstrak 3 digit terakhir (NNN)
                $lastNum = (int) substr($lastToday->consultation_id, -3);
                $nextNum = $lastNum + 1;
            } else {
                $nextNum = 1;
            }

            return $basePrefix . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        });
    }
}
