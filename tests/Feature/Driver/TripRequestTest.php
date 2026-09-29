<?php

namespace Tests\Feature\Driver;

use App\Enums\DriverStatus;
use App\Enums\TripRequestStatus;
use App\Models\City;
use App\Models\Driver;
use App\Models\TripRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_driver_can_submit_trip_request(): void
    {
        $user = User::factory()
            ->driver()
            ->create();

        $driver = Driver::factory()
            ->approved()
            ->create([
                'user_id' => $user->id,
            ]);

        $from = City::factory()->create();

        $to = City::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route(
                    'driver.request-trip.store'
                ),
                [
                    'from_city_id' => $from->id,

                    'to_city_id' => $to->id,

                    'travel_date' => now()
                        ->addDays(3)
                        ->toDateString(),

                    'departure_time' => '08:00',

                    'requested_seats' => 7,

                    'notes' => 'Test request',
                ]
            );

        $response->assertRedirect(
            route(
                'driver.request-trip'
            )
        );

        $this->assertDatabaseHas(
            'trip_requests',
            [
                'driver_id' => $driver->id,

                'from_city_id' => $from->id,

                'to_city_id' => $to->id,

                'status' => TripRequestStatus::Pending
                    ->value,
            ]
        );
    }

    public function test_unapproved_driver_cannot_access_trip_request_form(): void
    {
        $user = User::factory()
            ->driver()
            ->create();

        Driver::factory()->create([
            'user_id' => $user->id,

            'status' => DriverStatus::Pending,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(
                route(
                    'driver.request-trip'
                )
            );

        $response->assertRedirect(
            route('driver.register')
        );
    }

    public function test_duplicate_pending_request_is_rejected(): void
    {
        $user = User::factory()
            ->driver()
            ->create();

        Driver::factory()
            ->approved()
            ->create([
                'user_id' => $user->id,
            ]);

        $from = City::factory()->create();

        $to = City::factory()->create();

        $payload = [
            'from_city_id' => $from->id,

            'to_city_id' => $to->id,

            'travel_date' => now()
                ->addDays(4)
                ->toDateString(),

            'departure_time' => '09:00',

            'requested_seats' => 8,
        ];

        $this
            ->actingAs($user)
            ->post(
                route(
                    'driver.request-trip.store'
                ),
                $payload
            );

        $response = $this
            ->actingAs($user)
            ->post(
                route(
                    'driver.request-trip.store'
                ),
                $payload
            );

        $response
            ->assertSessionHasErrors(
                'trip_request'
            );

        $this->assertSame(
            1,
            TripRequest::query()
                ->count()
        );
    }
}
