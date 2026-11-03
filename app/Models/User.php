<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_OWNER = 'business_owner';
    public const ROLE_ADMIN = 'admin';

    // "role" and "is_active" are intentionally NOT fillable, so a request can never set them.
    protected $fillable = ['name', 'email', 'password', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_OWNER => 'Business Owner',
            default => 'Customer',
        };
    }

    public function establishments(): HasMany
    {
        return $this->hasMany(Establishment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
