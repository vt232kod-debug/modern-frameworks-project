<?php

namespace App\Models;

use App\Services\QueryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    public const TYPES = ['2D', '3D', 'IMAX', 'VIP'];

    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'name' => QueryFilter::STRING,
        'type' => QueryFilter::ENUM,
        'rows_count' => QueryFilter::INT,
        'seats_per_row' => QueryFilter::INT,
    ];

    protected $fillable = [
        'name',
        'type',
        'rows_count',
        'seats_per_row',
    ];

    protected function casts(): array
    {
        return [
            'rows_count' => 'integer',
            'seats_per_row' => 'integer',
        ];
    }

    public function getCapacityAttribute(): int
    {
        return $this->rows_count * $this->seats_per_row;
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }
}
