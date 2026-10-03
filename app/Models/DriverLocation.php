<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocation extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'driver_id',
        'trip_id',
        'latitude',
        'longitude',
        'accuracy_m',
        'speed_kmh',
        'heading',
        'eta_at',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy_m' => 'decimal:2',
            'speed_kmh' => 'decimal:2',
            'heading' => 'integer',
            'eta_at' => 'datetime',
            'recorded_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function isFresh(): bool
    {
        if ($this->recorded_at === null) {
            return false;
        }

        return $this->recorded_at->greaterThanOrEqualTo(
            now()->subSeconds((int) config('tracking.fresh_seconds', 300))
        );
    }
}
