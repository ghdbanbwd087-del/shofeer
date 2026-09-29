<?php

namespace Tests\Feature\Driver;

use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTripPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_trip_page_does_not_show_trip_price(): void
    {
        $user = User::factory()
            ->driver()
            ->create();

        $driver = Driver::factory()
            ->approved()
            ->create([
                'user_id' => $user->id,
            ]);

        $car = Car::factory()->create([
            'driver_id' => $driver->id,
        ]);

        $from = City::factory()->create([
            'name_ar' => 'صنعاء الاختبار',
        ]);

        $to = City::factory()->create([
            'name_ar' => 'الرياض الاختبار',
        ]);

        Trip::factory()->create([
            'driver_id' => $driver->id,

            'car_id' => $car->id,

            'from_city_id' => $from->id,

            'to_city_id' => $to->id,

            'price' => 987654.32,

            'departure_at' => now()->addDays(5),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(
                route(
                    'driver.trips.index'
                )
            );

        $response
            ->assertOk()
            ->assertViewIs(
                'driver.trips'
            )
            ->assertSee(
                'صنعاء الاختبار'
            )
            ->assertSee(
                'الرياض الاختبار'
            )
            ->assertDontSee(
                '987654.32'
            )
            ->assertDontSee(
                '987,654.32'
            );
    }
}
