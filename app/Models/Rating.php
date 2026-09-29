<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'booking_id',
        'trip_id',
        'driver_id',
        'user_id',
        'score',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(
            Booking::class
        );
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(
            Trip::class
        );
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(
            Driver::class
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }
}
