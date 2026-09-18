<?php

namespace App\Models;

use App\Services\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_CLIENT = 'client';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';
    public const ROLES = [self::ROLE_CLIENT, self::ROLE_MANAGER, self::ROLE_ADMIN];

    /** Role hierarchy: admin > manager > client */
    private const ROLE_LEVELS = [self::ROLE_CLIENT => 1, self::ROLE_MANAGER => 2, self::ROLE_ADMIN => 3];

    /** Filters available on GET /api/users (see QueryFilter) */
    public const FILTERS = [
        'id' => QueryFilter::INT,
        'name' => QueryFilter::STRING,
        'email' => QueryFilter::STRING,
        'role' => QueryFilter::ENUM,
        'customer_id' => QueryFilter::RELATION,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'customer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    protected $attributes = [
        'role' => self::ROLE_CLIENT,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * True if the user's role is $role or higher (admin > manager > client).
     */
    public function hasRole(string $role): bool
    {
        return (self::ROLE_LEVELS[$this->role] ?? 0) >= (self::ROLE_LEVELS[$role] ?? PHP_INT_MAX);
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /** The role is also put into the token payload */
    public function getJWTCustomClaims(): array
    {
        return ['role' => $this->role];
    }
}
