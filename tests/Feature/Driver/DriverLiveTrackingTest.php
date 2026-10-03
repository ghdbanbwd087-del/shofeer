<?php

namespace Tests\Feature\Driver;

use App\Enums\TripStatus;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverLiveTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_approved_driver_can_view_live_tracking_page(): void
    {
        [
            'driverUser' => $driverUser,
        ] = $this->createTrip();

        $this
            ->actingAs($driverUser)
            ->get(
                route(
                    'driver.live.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'driver.live.index'
            )
            ->assertSee(
                'التتبع المباشر'
            );
    }

    public function test_passenger_cannot_view_driver_live_tracking_page(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs($passenger)
            ->get(
                route(
                    'driver.live.index'
                )
            )
            ->assertForbidden();
    }

    public function test_driver_can_send_location_for_own_active_trip(): void
    {
        [
            'driverUser' => $driverUser,
            'driver' => $driver,
            'trip' => $trip,
        ] = $this->createTrip();

        $this
            ->actingAs($driverUser)
            ->postJson(
                route(
                    'driver.live.location.store'
                ),
                [
                    'trip_id' =>
                        $trip->id,

                    'latitude' =>
                        15.369445,

                    'longitude' =>
                        44.191006,

                    'accuracy_m' =>
                        10,

                    'speed_kmh' =>
                        35,

                    'heading' =>
                        90,

                    'recorded_at' =>
                        now()
                            ->toIso8601String(),
                ]
            )
            ->assertCreated()
            ->assertJsonPath(
                'location.latitude',
                15.369445
            );

        $this->assertDatabaseHas(
            'driver_locations',
            [
                'driver_id' =>
                    $driver->id,

                'trip_id' =>
                    $trip->id,
            ]
        );
    }

    public function test_driver_cannot_send_location_for_another_drivers_trip(): void
    {
        [
            'driverUser' => $firstDriverUser,
        ] = $this->createTrip();

        [
            'trip' => $secondTrip,
        ] = $this->createTrip();

        $this
            ->actingAs($firstDriverUser)
            ->postJson(
                route(
                    'driver.live.location.store'
                ),
                [
                    'trip_id' =>
                        $secondTrip->id,

                    'latitude' =>
                        15.369445,

                    'longitude' =>
                        44.191006,

                    'accuracy_m' =>
                        10,

                    'speed_kmh' =>
                        35,
                ]
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'trip_id'
            );

        $this->assertDatabaseMissing(
            'driver_locations',
            [
                'trip_id' =>
                    $secondTrip->id,

                'latitude' =>
                    15.369445,
            ]
        );
    }

    private function createTrip(): array
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $driverUser =
            User::factory()
                ->driver()
                ->create();

        $driver =
            Driver::factory()
                ->approved()
                ->create([
                    'user_id' =>
                        $driverUser->id,
                ]);

        $car =
            Car::factory()
                ->create([
                    'driver_id' =>
                        $driver->id,

                    'seat_count' =>
                        8,
                ]);

        $from =
            City::factory()->create();

        $to =
            City::factory()->create();

        $trip =
            Trip::query()->create([
                'driver_id' =>
                    $driver->id,

                'car_id' =>
                    $car->id,

                'source_trip_request_id' =>
                    null,

                'from_city_id' =>
                    $from->id,

                'to_city_id' =>
                    $to->id,

                'departure_at' =>
                    now()->subMinutes(10),

                'meeting_point' =>
                    'Meeting Point',

                'destination_point' =>
                    'Destination',

                'price' =>
                    300,

                'seat_count' =>
                    8,

                'available_seats' =>
                    8,

                'status' =>
                    TripStatus::InProgress,

                'is_published' =>
                    true,

                'notes' =>
                    null,

                'created_by' =>
                    $admin->id,
            ]);

        return [
            'driverUser' =>
                $driverUser,

            'driver' =>
                $driver,

            'trip' =>
                $trip,
        ];
    }
}
