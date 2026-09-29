<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use HasUuids;
    use Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'phone',
        'password',
        'role',
        'is_active',
        'phone_verified_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'phone',
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',

            'role' => UserRole::class,

            'is_active' => 'boolean',

            'phone_verified_at' => 'datetime',
        ];
    }

    /**
     * ملف السائق.
     */
    public function driver(): HasOne
    {
        return $this->hasOne(
            Driver::class
        );
    }

    /**
     * حجوزات الراكب.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(
            Booking::class
        );
    }

    public function isAdmin(): bool
    {
        return $this->role ===
            UserRole::Admin;
    }

    public function isDriver(): bool
    {
        return $this->role ===
            UserRole::Driver;
    }

    public function isPassenger(): bool
    {
        return $this->role ===
            UserRole::Passenger;
    }
}
