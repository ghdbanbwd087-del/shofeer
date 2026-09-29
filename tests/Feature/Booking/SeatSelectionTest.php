<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\SeatStatus;
use App\Enums\TripStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Seat;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_can_view_seat_selection_page(): void
    {
        $trip = $this->createTrip();

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $response = $this
            ->actingAs($passenger)
            ->get(
                route(
                    'trips.seats.show',
                    [
                        'trip' => $trip,

                        'gender' => 'female',
                    ]
                )
            );

        $response
            ->assertOk()
            ->assertViewIs(
                'trips.seats'
            )
            ->assertSee(
                'اختر مقعدك'
            )
            ->assertSee(
                'منطقة النساء'
            );
    }

    public function test_passenger_can_hold_available_seat(): void
    {
        $trip = $this->createTrip();

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $response = $this
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
            );

        $booking =
            Booking::query()
                ->where(
                    'trip_id',
                    $trip->id
                )
                ->where(
                    'user_id',
                    $passenger->id
                )
                ->where(
                    'seat_number',
                    1
                )
                ->firstOrFail();

        $response->assertRedirect(
            route(
                'booking.pay',
                $booking
            )
        );

        $this->assertSame(
            BookingStatus::Held,
            $booking->status
        );

        $seat = Seat::query()
            ->where(
                'trip_id',
                $trip->id
            )
            ->where(
                'seat_number',
                1
            )
            ->firstOrFail();

        $this->assertSame(
            SeatStatus::Held,
            $seat->status
        );

        $this->assertSame(
            $passenger->id,
            $seat->held_by_user_id
        );

        $this->assertNotNull(
            $seat->hold_expires_at
        );
    }

    public function test_male_cannot_hold_female_only_seat(): void
    {
        $trip = $this->createTrip(
            seatCount: 8
        );

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $response = $this
            ->actingAs($passenger)
            ->post(
                route(
                    'trips.seats.store',
                    $trip
                ),
                [
                    /*
                     * في التصميم الافتراضي
                     * آخر مقعدين للنساء.
                     */
                    'seat_number' => 8,

                    'passenger_gender' => 'male',
                ]
            );

        $response
            ->assertSessionHasErrors(
                'seat_number'
            );

        $this->assertDatabaseMissing(
            'bookings',
            [
                'trip_id' => $trip->id,

                'user_id' => $passenger->id,

                'seat_number' => 8,
            ]
        );
    }

    public function test_expired_hold_is_released(): void
    {
        $trip = $this->createTrip();

        $passenger =
            User::factory()
                ->passenger()
                ->create();

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
            );

        $booking =
            Booking::query()
                ->firstOrFail();

        $seat = Seat::query()
            ->where(
                'trip_id',
                $trip->id
            )
            ->where(
                'seat_number',
                1
            )
            ->firstOrFail();

        $seat->update([
            'hold_expires_at' => now()->subMinute(),
        ]);

        $response = $this
            ->actingAs($passenger)
            ->get(
                route(
                    'booking.pay',
                    $booking
                )
            );

        $response->assertRedirect();

        $this->assertSame(
            BookingStatus::Expired,
            $booking
                ->fresh()
                ->status
        );

        $this->assertSame(
            SeatStatus::Available,
            $seat
                ->fresh()
                ->status
        );

        $this->assertNull(
            $seat
                ->fresh()
                ->held_by_user_id
        );
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

            'departure_at' => now()->addDays(5),

            'meeting_point' => 'Test Meeting Point',

            'destination_point' => 'Test Destination',

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
