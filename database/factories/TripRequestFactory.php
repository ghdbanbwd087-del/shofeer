<?php

namespace Database\Factories;

use App\Enums\TripRequestStatus;
use App\Models\City;
use App\Models\Driver;
use App\Models\TripRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TripRequest>
 */
class TripRequestFactory extends Factory
{
    protected $model = TripRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver_id' => Driver::factory()
                ->approved(),

            'from_city_id' => City::factory(),

            'to_city_id' => City::factory(),

            'travel_date' => now()
                ->addDays(
                    fake()->numberBetween(
                        2,
                        20
                    )
                )
                ->toDateString(),

            'departure_time' => '08:00',

            'requested_seats' => 7,

            'notes' => null,

            'status' => TripRequestStatus::Pending,

            'rejection_reason' => null,

            'reviewed_by' => null,

            'reviewed_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(
            fn (): array => [
                'status' => TripRequestStatus::Approved,

                'reviewed_at' => now(),
            ]
        );
    }

    public function rejected(): static
    {
        return $this->state(
            fn (): array => [
                'status' => TripRequestStatus::Rejected,

                'rejection_reason' => 'تعذر اعتماد الموعد المطلوب.',

                'reviewed_at' => now(),
            ]
        );
    }
}
