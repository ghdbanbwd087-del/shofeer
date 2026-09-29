<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PassengerGender;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'trip_id',
        'user_id',
        'booking_code',
        'seat_number',
        'passenger_name',
        'passenger_gender',
        'passenger_phone',
        'passenger_phone_hash',
        'passenger_whatsapp',
        'passenger_id',
        'passenger_notes',
        'price',
        'commission',
        'paid_amount',
        'payment_status',
        'status',
        'cancel_reason',
        'idempotency_key',
        'confirmed_at',
        'cancelled_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'passenger_phone',
        'passenger_phone_hash',
        'passenger_whatsapp',
        'passenger_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seat_number' => 'integer',

            'passenger_gender' => PassengerGender::class,

            'price' => 'decimal:2',

            'commission' => 'decimal:2',

            'paid_amount' => 'decimal:2',

            'payment_status' => PaymentStatus::class,

            'status' => BookingStatus::class,

            'confirmed_at' => 'datetime',

            'cancelled_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(
            Trip::class
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function payment(): HasOne
    {
        return $this->hasOne(
            Payment::class
        );
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}
