<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
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
        'make',
        'model',
        'year',
        'plate_number',
        'color',
        'seat_count',
        'type',
        'features',
        'image',
        'registration_image',
        'is_active',
    ];

    /**
     * Casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',

            'seat_count' => 'integer',

            'features' => 'array',

            'is_active' => 'boolean',
        ];
    }

    /**
     * صاحب السيارة.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(
            Driver::class
        );
    }

    /**
     * الرحلات التي استخدمت السيارة.
     */
    public function trips(): HasMany
    {
        return $this->hasMany(
            Trip::class
        );
    }
}
