<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    protected $model = Car::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver_id' => Driver::factory()
                ->approved(),

            'make' => fake()->randomElement([
                'Toyota',
                'Hyundai',
                'Kia',
                'Nissan',
            ]),

            'model' => fake()->randomElement([
                'Hiace',
                'Staria',
                'Carnival',
                'Urvan',
            ]),

            'year' => fake()->numberBetween(
                2018,
                now()->year
            ),

            'plate_number' => strtoupper(
                fake()
                    ->unique()
                    ->bothify(
                        '???-####'
                    )
            ),

            'color' => fake()->randomElement([
            'أبيض',
            'فضي',
            'أسود',
            ]),

            'seat_count' => fake()->randomElement([
            7,
            12,
            15,
            18,
            ]),

            'type' => fake()->randomElement([
            'van',
            'bus',
            'vip',
            ]),

            'features' => [
            'مكيف',
            'USB',
            ],

            'image' => null,

            'registration_image' => null,

            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(
            fn (): array => [
                'is_active' => false,
            ]
        );
    }
}
