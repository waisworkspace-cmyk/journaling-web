<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_date',
        'user_id',
        'mood',
        'weather', // Baru
        'rating',
        'positive_highlight',
        'negative_reflection',
        'gratitude', // Baru
        'goals', // Baru
        'affirmations', // Baru
        'photo_paths',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'photo_paths' => 'array',
    ];
}