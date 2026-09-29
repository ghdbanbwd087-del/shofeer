<?php

namespace App\Models;

use App\Enums\SeatStatus;
use App\Enums\SeatType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'trip_id',
        'seat_number',
        'seat_type',
        'status',
        'held_by_user_id',
        'hold_expires_at',
        'adjacent_seat_number',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seat_number' => 'integer',

            'seat_type' => SeatType::class,

            'status' => SeatStatus::class,

            'hold_expires_at' => 'datetime',

            'adjacent_seat_number' => 'integer',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(
            Trip::class
        );
    }

    public function heldBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'held_by_user_id'
        );
    }

    public function isAvailable(): bool
    {
        return $this->status ===
            SeatStatus::Available;
    }

    public function isExpiredHold(): bool
    {
        return $this->status ===
            SeatStatus::Held
            && $this->hold_expires_at !== null
            && $this->hold_expires_at->isPast();
    }
}
