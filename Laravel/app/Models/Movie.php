<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'title',
        'description',
        'genre',
        'duration_minutes',
        'release_date',
        'age_rating',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'release_date' => 'date:Y-m-d',
        ];
    }
}
