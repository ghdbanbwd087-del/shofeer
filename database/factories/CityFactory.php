<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_ar' => fake()->unique()->city(),

            'name_en' => fake()->unique()->city(),

            'country_code' => fake()->randomElement([
                'YE',
                'SA',
            ]),

            'is_active' => true,

            'sort_order' => fake()->numberBetween(
                1,
                100
            ),
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
