<?php

namespace App\Models;

use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'driver_id',
        'car_id',
        'source_trip_request_id',
        'from_city_id',
        'to_city_id',
        'departure_at',
        'meeting_point',
        'destination_point',
        'price',
        'seat_count',
        'available_seats',
        'status',
        'is_published',
        'notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'departure_at' => 'datetime',

            'price' => 'decimal:2',

            'seat_count' => 'integer',

            'available_seats' => 'integer',

            'status' => TripStatus::class,

            'is_published' => 'boolean',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(
            Driver::class
        );
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(
            Car::class
        );
    }

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
            'from_city_id'
        );
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
            'to_city_id'
        );
    }

    public function sourceRequest(): BelongsTo
    {
        return $this->belongsTo(
            TripRequest::class,
            'source_trip_request_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * مقاعد الرحلة.
     */
    public function seats(): HasMany
    {
        return $this->hasMany(
            Seat::class
        );
    }

    /**
     * حجوزات الرحلة.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(
            Booking::class
        );
    }

    public function scopePublished(
        Builder $query
    ): Builder {
        return $query->where(
            'is_published',
            true
        );
    }

    public function scopeUpcoming(
        Builder $query
    ): Builder {
        return $query
            ->where(
                'departure_at',
                '>',
                now()
            )
            ->whereNot(
                'status',
                TripStatus::Cancelled
                    ->value
            );
    }

    public function canAcceptBookings(): bool
    {
        return $this->is_published
            && $this->status
                ->canAcceptBookings()
            && $this->available_seats > 0
            && $this
                ->departure_at
                ->isFuture();
    }
}
