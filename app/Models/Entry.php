<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_date',
        'mood',
        'weather',
        'rating',
        'positive_highlight',
        'negative_reflection',
        'photo_paths'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'photo_paths' => 'array', // Agar otomatis jadi JSON saat simpan, dan Array saat diambil
    ];
}