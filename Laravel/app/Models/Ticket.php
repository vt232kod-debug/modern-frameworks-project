<?php

namespace App\Models;

use App\Services\QueryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    public const STATUSES = ['reserved', 'paid', 'cancelled'];

    /** Filters available on GET /api/... (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'screening_id' => QueryFilter::RELATION,
        'customer_id' => QueryFilter::RELATION,
        'seat_row' => QueryFilter::INT,
        'seat_number' => QueryFilter::INT,
        'price' => QueryFilter::DECIMAL,
        'status' => QueryFilter::ENUM,
        'purchased_at' => QueryFilter::DATETIME,
    ];

    protected $fillable = [
        'screening_id',
        'customer_id',
        'seat_row',
        'seat_number',
        'price',
        'status',
    ];

    protected $attributes = [
        'status' => 'reserved',
    ];

    protected function casts(): array
    {
        return [
            'seat_row' => 'integer',
            'seat_number' => 'integer',
            'price' => 'float',
            'purchased_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            $ticket->purchased_at ??= now();
            // Ticket price defaults to the screening price
            $ticket->price ??= $ticket->screening?->price;
        });
    }

    public function screening(): BelongsTo
    {
        return $this->belongsTo(Screening::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
