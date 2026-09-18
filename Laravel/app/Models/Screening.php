<?php

namespace App\Models;

use App\Services\QueryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Screening extends Model
{
    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'movie_id' => QueryFilter::RELATION,
        'hall_id' => QueryFilter::RELATION,
        'starts_at' => QueryFilter::DATETIME,
        'price' => QueryFilter::DECIMAL,
        'language' => QueryFilter::STRING,
    ];

    protected $fillable = [
        'movie_id',
        'hall_id',
        'starts_at',
        'price',
        'language',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime:Y-m-d H:i',
            'price' => 'float',
        ];
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
