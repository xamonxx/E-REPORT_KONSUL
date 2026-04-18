<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'css_class', 'sort_order'];

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
}
