<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name_ar',
        'name_en',
        'country_code',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',

            'sort_order' => 'integer',
        ];
    }

    /**
     * المدن المفعلة.
     */
    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'is_active',
            true
        );
    }

    /**
     * الرحلات المنطلقة من المدينة.
     */
    public function outgoingTrips(): HasMany
    {
        return $this->hasMany(
            Trip::class,
            'from_city_id'
        );
    }

    /**
     * الرحلات الواصلة للمدينة.
     */
    public function incomingTrips(): HasMany
    {
        return $this->hasMany(
            Trip::class,
            'to_city_id'
        );
    }

    /**
     * طلبات الرحلات المنطلقة.
     */
    public function outgoingTripRequests(): HasMany
    {
        return $this->hasMany(
            TripRequest::class,
            'from_city_id'
        );
    }

    /**
     * طلبات الرحلات الواصلة.
     */
    public function incomingTripRequests(): HasMany
    {
        return $this->hasMany(
            TripRequest::class,
            'to_city_id'
        );
    }
}
