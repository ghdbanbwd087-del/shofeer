<?php

namespace Tests\Feature\Driver;

use App\Models\Car;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_can_add_car(): void
    {
        $user = User::factory()
            ->driver()
            ->create();

        $driver = Driver::factory()
            ->approved()
            ->create([
                'user_id' => $user->id,
            ]);

        $response = $this
            ->actingAs($user)
            ->post(
                route(
                    'driver.cars.store'
                ),
                [
                    'make' => 'Toyota',

                    'model' => 'Hiace',

                    'year' => 2024,

                    'plate_number' => 'TEST-123',

                    'color' => 'أبيض',

                    'seat_count' => 12,

                    'type' => 'van',

                    'features' => [
                        'مكيف',
                        'USB',
                    ],
                ]
            );

        $response->assertRedirect(
            route(
                'driver.cars.index'
            )
        );

        $this->assertDatabaseHas(
            'cars',
            [
                'driver_id' => $driver->id,

                'plate_number' => 'TEST-123',

                'seat_count' => 12,
            ]
        );
    }

    public function test_driver_cannot_delete_another_drivers_car(): void
    {
        $firstUser = User::factory()
            ->driver()
            ->create();

        Driver::factory()
            ->approved()
            ->create([
                'user_id' => $firstUser->id,
            ]);

        $secondUser = User::factory()
            ->driver()
            ->create();

        $secondDriver =
            Driver::factory()
                ->approved()
                ->create([
                    'user_id' => $secondUser->id,
                ]);

        $car = Car::factory()->create([
            'driver_id' => $secondDriver->id,
        ]);

        $response = $this
            ->actingAs($firstUser)
            ->delete(
                route(
                    'driver.cars.destroy',
                    $car
                )
            );

        $response->assertForbidden();

        $this->assertDatabaseHas(
            'cars',
            [
                'id' => $car->id,
            ]
        );
    }
}
