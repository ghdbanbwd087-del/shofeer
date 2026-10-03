<?php

namespace Tests\Feature\Tracking;

use App\Enums\TripStatus;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Services\LocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LocationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_location_can_be_recorded(): void
    {
        [
            'driver' => $driver,
            'trip' => $trip,
        ] = $this->createTrip();

        $location =
            app(LocationService::class)->record(
                driver: $driver,
                trip: $trip,
                data: [
                    'latitude' => 15.369445,
                    'longitude' => 44.191006,
                    'accuracy_m' => 12,
                    'speed_kmh' => 35,
                    'heading' => 90,
                    'recorded_at' => now()->toIso8601String(),
                ],
            );

        $this->assertDatabaseHas(
            'driver_locations',
            [
                'id' => $location->id,
                'driver_id' => $driver->id,
                'trip_id' => $trip->id,
            ]
        );
    }

    public function test_impossible_coordinate_is_rejected(): void
    {
        [
            'driver' => $driver,
            'trip' => $trip,
        ] = $this->createTrip();

        $this->expectException(
            ValidationException::class
        );

        app(LocationService::class)->record(
            driver: $driver,
            trip: $trip,
            data: [
                'latitude' => 120,
                'longitude' => 44.191006,
            ],
        );
    }

    public function test_trip_must_belong_to_driver(): void
    {
        [
            'driver' => $driver,
        ] = $this->createTrip();

        [
            'trip' => $otherTrip,
        ] = $this->createTrip();

        $this->expectException(
            ValidationException::class
        );

        app(LocationService::class)->record(
            driver: $driver,
            trip: $otherTrip,
            data: [
                'latitude' => 15.369445,
                'longitude' => 44.191006,
            ],
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
                    'user_id' => $driverUser->id,
                ]);

        $car =
            Car::factory()
                ->create([
                    'driver_id' => $driver->id,
                    'seat_count' => 8,
                ]);

        $from = City::factory()->create();
        $to = City::factory()->create();

        $trip =
            Trip::query()->create([
                'driver_id' => $driver->id,
                'car_id' => $car->id,
                'source_trip_request_id' => null,
                'from_city_id' => $from->id,
                'to_city_id' => $to->id,
                'departure_at' => now(),
                'meeting_point' => 'Meeting Point',
                'destination_point' => 'Destination',
                'price' => 300,
                'seat_count' => 8,
                'available_seats' => 8,
                'status' => TripStatus::InProgress,
                'is_published' => true,
                'notes' => null,
                'created_by' => $admin->id,
            ]);

        return [
            'driver' => $driver,
            'trip' => $trip,
        ];
    }
}
