<?php

namespace App\Models;

use App\Services\QueryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    public const AGE_RATINGS = ['0+', '6+', '12+', '16+', '18+'];

    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'title' => QueryFilter::STRING,
        'description' => QueryFilter::STRING,
        'genre' => QueryFilter::STRING,
        'duration_minutes' => QueryFilter::INT,
        'release_date' => QueryFilter::DATE,
        'age_rating' => QueryFilter::ENUM,
    ];

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
