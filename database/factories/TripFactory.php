<?php

namespace Database\Factories;

use App\Enums\TripStatus;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver_id' => Driver::factory()
                ->approved(),

            'car_id' => Car::factory(),

            'source_trip_request_id' => null,

            'from_city_id' => City::factory(),

            'to_city_id' => City::factory(),

            'departure_at' => now()
                ->addDays(
                    fake()->numberBetween(
                        2,
                        20
                    )
                )
                ->setTime(
                    8,
                    0
                ),

            'meeting_point' => 'نقطة التجمع الرئيسية',

            'destination_point' => 'المحطة الرئيسية',

            'price' => fake()->randomElement([
                150,
                200,
                250,
                300,
            ]),

            'seat_count' => 7,

            'available_seats' => 7,

            'status' => TripStatus::Scheduled,

            'is_published' => true,

            'notes' => null,

            'created_by' => User::factory()
                ->admin(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(
            fn (): array => [
                'is_published' => false,
            ]
        );
    }

    public function cancelled(): static
    {
        return $this->state(
            fn (): array => [
                'status' => TripStatus::Cancelled,

                'is_published' => false,
            ]
        );
    }
}
