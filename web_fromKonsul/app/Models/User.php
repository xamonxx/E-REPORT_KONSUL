<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_id',
        'primary_color',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'role' => UserRole::class,
        ];
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'created_by');
    }

    public function reportAttendances()
    {
        return $this->hasMany(ReportAttendance::class);
    }

    public function consultationNotes()
    {
        return $this->hasMany(ConsultationNote::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
