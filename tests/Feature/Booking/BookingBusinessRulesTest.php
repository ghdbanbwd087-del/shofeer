<?php

namespace Tests\Feature\Booking;

use App\Enums\TripStatus;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_cannot_hold_more_than_two_seats_in_same_trip(): void
    {
        $trip = $this->createTrip(
            seatCount: 10
        );

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        foreach ([1, 3] as $seatNumber) {
            $this
                ->actingAs($passenger)
                ->post(
                    route(
                        'trips.seats.store',
                        $trip
                    ),
                    [
                        'seat_number' => $seatNumber,

                        'passenger_gender' => 'male',
                    ]
                )
                ->assertSessionHasNoErrors();
        }

        $response = $this
            ->actingAs($passenger)
            ->post(
                route(
                    'trips.seats.store',
                    $trip
                ),
                [
                    'seat_number' => 5,

                    'passenger_gender' => 'male',
                ]
            );

        $response
            ->assertSessionHasErrors(
                'booking'
            );

        $this->assertSame(
            2,
            $passenger
                ->bookings()
                ->where(
                    'trip_id',
                    $trip->id
                )
                ->count()
        );
    }

    public function test_passenger_cannot_book_more_than_five_trips_per_day(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        /*
         * أول خمس رحلات مسموحة.
         */
        for (
            $index = 1;
            $index <= 5;
            $index++
        ) {
            $trip =
                $this->createTrip();

            $this
                ->actingAs($passenger)
                ->post(
                    route(
                        'trips.seats.store',
                        $trip
                    ),
                    [
                        'seat_number' => 1,

                        'passenger_gender' => 'male',
                    ]
                )
                ->assertSessionHasNoErrors();
        }

        /*
         * الرحلة السادسة يجب رفضها.
         */
        $sixthTrip =
            $this->createTrip();

        $response = $this
            ->actingAs($passenger)
            ->post(
                route(
                    'trips.seats.store',
                    $sixthTrip
                ),
                [
                    'seat_number' => 1,

                    'passenger_gender' => 'male',
                ]
            );

        $response
            ->assertSessionHasErrors(
                'booking'
            );
    }

    public function test_driver_cannot_book_trip_as_passenger(): void
    {
        $trip = $this->createTrip();

        $driverUser =
            User::factory()
                ->driver()
                ->create();

        $response = $this
            ->actingAs($driverUser)
            ->post(
                route(
                    'trips.seats.store',
                    $trip
                ),
                [
                    'seat_number' => 1,

                    'passenger_gender' => 'male',
                ]
            );

        $response->assertForbidden();
    }

    private function createTrip(
        int $seatCount = 8
    ): Trip {
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

                    'seat_count' => $seatCount,
                ]);

        $from =
            City::factory()
                ->create();

        $to =
            City::factory()
                ->create();

        return Trip::query()->create([
            'driver_id' => $driver->id,

            'car_id' => $car->id,

            'source_trip_request_id' => null,

            'from_city_id' => $from->id,

            'to_city_id' => $to->id,

            'departure_at' => now()->addDays(7),

            'meeting_point' => 'Meeting Point',

            'destination_point' => 'Destination',

            'price' => 250,

            'seat_count' => $seatCount,

            'available_seats' => $seatCount,

            'status' => TripStatus::Scheduled,

            'is_published' => true,

            'notes' => null,

            'created_by' => $admin->id,
        ]);
    }
}
