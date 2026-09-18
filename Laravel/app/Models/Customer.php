<?php

namespace App\Models;

use App\Services\QueryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'first_name' => QueryFilter::STRING,
        'last_name' => QueryFilter::STRING,
        'email' => QueryFilter::STRING,
        'phone' => QueryFilter::STRING,
        'birth_date' => QueryFilter::DATE,
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
