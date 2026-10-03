<?php

namespace Tests\Feature\Admin;

use App\Enums\TripStatus;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\DriverLocation;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLiveTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_view_live_map_page(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $this
            ->actingAs($admin)
            ->get(
                route(
                    'admin.live.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'admin.live.index'
            )
            ->assertSee(
                'الخريطة المباشرة'
            );
    }

    public function test_passenger_cannot_view_admin_live_map_page(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs($passenger)
            ->get(
                route(
                    'admin.live.index'
                )
            )
            ->assertForbidden();
    }

    public function test_admin_live_data_includes_live_driver_location(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        [
            'trip' => $trip,
            'driver' => $driver,
        ] = $this->createActiveTrip();

        DriverLocation::query()->create([
            'driver_id' =>
                $driver->id,

            'trip_id' =>
                $trip->id,

            'latitude' =>
                15.369445,

            'longitude' =>
                44.191006,

            'accuracy_m' =>
                10,

            'speed_kmh' =>
                45,

            'heading' =>
                90,

            'eta_at' =>
                null,

            'recorded_at' =>
                now(),
        ]);

        $this
            ->actingAs($admin)
            ->getJson(
                route(
                    'admin.live.data'
                )
            )
            ->assertOk()
            ->assertJsonPath(
                'stats.live',
                1
            )
            ->assertJsonPath(
                'stats.stale',
                0
            )
            ->assertJsonPath(
                'stats.missing',
                0
            )
            ->assertJsonPath(
                'trips.0.trip_id',
                $trip->id
            )
            ->assertJsonPath(
                'trips.0.tracking_state',
                'live'
            );
    }

    public function test_missing_location_is_reported_as_alert(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $this->createActiveTrip();

        $this
            ->actingAs($admin)
            ->getJson(
                route(
                    'admin.live.data'
                )
            )
            ->assertOk()
            ->assertJsonPath(
                'stats.missing',
                1
            )
            ->assertJsonPath(
                'trips.0.tracking_state',
                'missing'
            )
            ->assertJsonPath(
                'trips.0.alert_message',
                'لم يصل موقع من السائق بعد.'
            );
    }

    public function test_old_location_is_reported_as_stale_alert(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        [
            'trip' => $trip,
            'driver' => $driver,
        ] = $this->createActiveTrip();

        DriverLocation::query()->create([
            'driver_id' =>
                $driver->id,

            'trip_id' =>
                $trip->id,

            'latitude' =>
                15.369445,

            'longitude' =>
                44.191006,

            'accuracy_m' =>
                10,

            'speed_kmh' =>
                0,

            'heading' =>
                null,

            'eta_at' =>
                null,

            'recorded_at' =>
                now()
                    ->subMinutes(10),
        ]);

        $this
            ->actingAs($admin)
            ->getJson(
                route(
                    'admin.live.data'
                )
            )
            ->assertOk()
            ->assertJsonPath(
                'stats.stale',
                1
            )
            ->assertJsonPath(
                'trips.0.tracking_state',
                'stale'
            );
    }

    private function createActiveTrip(): array
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
                    now()
                        ->subMinutes(10),

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
            'trip' =>
                $trip,

            'driver' =>
                $driver,
        ];
    }
}
