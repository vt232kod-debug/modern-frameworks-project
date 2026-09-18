<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    public const TYPES = ['2D', '3D', 'IMAX', 'VIP'];

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
