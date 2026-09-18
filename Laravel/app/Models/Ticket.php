<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    public const STATUSES = ['reserved', 'paid', 'cancelled'];

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
