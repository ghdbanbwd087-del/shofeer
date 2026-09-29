<?php

namespace App\Models;

use App\Enums\DriverStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * الحقول المسموح بتعبئتها.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'national_id',
        'license_number',
        'id_image_front',
        'id_image_back',
        'license_image',
        'license_expiry',
        'experience_years',
        'bio',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by',
        'rating',
        'total_trips',
        'is_online',
    ];

    /**
     * إخفاء المعلومات الحساسة.
     *
     * @var list<string>
     */
    protected $hidden = [
        'national_id',
        'license_number',
        'id_image_front',
        'id_image_back',
        'license_image',
    ];

    /**
     * Casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',

            'experience_years' => 'integer',

            'status' => DriverStatus::class,

            'verified_at' => 'datetime',

            'rating' => 'decimal:2',

            'total_trips' => 'integer',

            'is_online' => 'boolean',
        ];
    }

    /**
     * حساب المستخدم.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * السيارات.
     */
    public function cars(): HasMany
    {
        return $this->hasMany(
            Car::class
        );
    }

    /**
     * طلبات الرحلات.
     */
    public function tripRequests(): HasMany
    {
        return $this->hasMany(
            TripRequest::class
        );
    }

    /**
     * الرحلات الفعلية.
     */
    public function trips(): HasMany
    {
        return $this->hasMany(
            Trip::class
        );
    }

    /**
     * الإداري الذي اعتمد السائق.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    public function isApproved(): bool
    {
        return $this->status ===
            DriverStatus::Approved;
    }

    public function isPending(): bool
    {
        return $this->status ===
            DriverStatus::Pending;
    }

    public function isSuspended(): bool
    {
        return $this->status ===
            DriverStatus::Suspended;
    }
}
