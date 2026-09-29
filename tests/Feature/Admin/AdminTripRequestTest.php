<?php

namespace Tests\Feature\Admin;

use App\Enums\TripRequestStatus;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\TripRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTripRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_trip_request_and_create_trip(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $driverUser = User::factory()
            ->driver()
            ->create();

        $driver = Driver::factory()
            ->approved()
            ->create([
                'user_id' => $driverUser->id,
            ]);

        $car = Car::factory()->create([
            'driver_id' => $driver->id,

            'seat_count' => 12,
        ]);

        $from = City::factory()->create();

        $to = City::factory()->create();

        $tripRequest =
            TripRequest::factory()
                ->create([
                    'driver_id' => $driver->id,

                    'from_city_id' => $from->id,

                    'to_city_id' => $to->id,

                    'travel_date' => now()
                        ->addDays(3)
                        ->toDateString(),

                    'departure_time' => '08:00',

                    'requested_seats' => 10,
                ]);

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.trip-requests.approve',
                    $tripRequest
                ),
                [
                    'car_id' => $car->id,

                    'price' => 300,

                    'meeting_point' => 'المحطة الرئيسية',

                    'destination_point' => 'محطة الوصول',

                    'is_published' => true,
                ]
            );

        $response->assertSessionHas(
            'success'
        );

        $tripRequest->refresh();

        $this->assertSame(
            TripRequestStatus::Approved,
            $tripRequest->status
        );

        $this->assertDatabaseHas(
            'trips',
            [
                'source_trip_request_id' => $tripRequest->id,

                'driver_id' => $driver->id,

                'car_id' => $car->id,

                'seat_count' => 10,

                'available_seats' => 10,
            ]
        );
    }

    public function test_trip_request_cannot_be_approved_twice(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $driverUser = User::factory()
            ->driver()
            ->create();

        $driver = Driver::factory()
            ->approved()
            ->create([
                'user_id' => $driverUser->id,
            ]);

        $car = Car::factory()->create([
            'driver_id' => $driver->id,

            'seat_count' => 12,
        ]);

        $from = City::factory()->create();

        $to = City::factory()->create();

        $tripRequest =
            TripRequest::factory()
                ->create([
                    'driver_id' => $driver->id,

                    'from_city_id' => $from->id,

                    'to_city_id' => $to->id,

                    'travel_date' => now()
                        ->addDays(5)
                        ->toDateString(),

                    'departure_time' => '08:00',

                    'requested_seats' => 7,
                ]);

        $payload = [
            'car_id' => $car->id,

            'price' => 250,

            'meeting_point' => 'Meeting Point',

            'is_published' => true,
        ];

        $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.trip-requests.approve',
                    $tripRequest
                ),
                $payload
            );

        $secondResponse = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.trip-requests.approve',
                    $tripRequest
                ),
                $payload
            );

        $secondResponse
            ->assertSessionHasErrors(
                'trip_request'
            );

        $this->assertSame(
            1,
            Trip::query()
                ->where(
                    'source_trip_request_id',
                    $tripRequest->id
                )
                ->count()
        );
    }

    public function test_admin_can_reject_trip_request(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $tripRequest =
            TripRequest::factory()
                ->create();

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.trip-requests.reject',
                    $tripRequest
                ),
                [
                    'rejection_reason' => 'الموعد غير متاح حالياً.',
                ]
            );

        $response->assertSessionHas(
            'success'
        );

        $this->assertDatabaseHas(
            'trip_requests',
            [
                'id' => $tripRequest->id,

                'status' => TripRequestStatus::Rejected
                    ->value,

                'rejection_reason' => 'الموعد غير متاح حالياً.',
            ]
        );
    }
}
