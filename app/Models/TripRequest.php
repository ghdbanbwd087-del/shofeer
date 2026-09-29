<?php

namespace App\Models;

use App\Enums\TripRequestStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TripRequest extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * الحقول المسموح بتعبئتها.
     *
     * @var list<string>
     */
    protected $fillable = [
        'driver_id',
        'from_city_id',
        'to_city_id',
        'travel_date',
        'departure_time',
        'requested_seats',
        'notes',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'travel_date' => 'date',

            'requested_seats' => 'integer',

            'status' => TripRequestStatus::class,

            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * السائق صاحب الطلب.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(
            Driver::class
        );
    }

    /**
     * مدينة الانطلاق.
     */
    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
            'from_city_id'
        );
    }

    /**
     * مدينة الوصول.
     */
    public function toCity(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
            'to_city_id'
        );
    }

    /**
     * الإداري الذي راجع الطلب.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    /**
     * الرحلة الناتجة عن الطلب.
     */
    public function trip(): HasOne
    {
        return $this->hasOne(
            Trip::class,
            'source_trip_request_id'
        );
    }

    /**
     * هل الطلب ما زال pending؟
     */
    public function isPending(): bool
    {
        return $this->status ===
            TripRequestStatus::Pending;
    }

    /**
     * هل تمت الموافقة؟
     */
    public function isApproved(): bool
    {
        return $this->status ===
            TripRequestStatus::Approved;
    }

    /**
     * هل تم الرفض؟
     */
    public function isRejected(): bool
    {
        return $this->status ===
            TripRequestStatus::Rejected;
    }
}
