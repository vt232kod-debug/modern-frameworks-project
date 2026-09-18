<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    public const AGE_RATINGS = ['0+', '6+', '12+', '16+', '18+'];

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

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }
}
