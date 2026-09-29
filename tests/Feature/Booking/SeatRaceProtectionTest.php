<?php

namespace Tests\Feature\Booking;

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

class SeatRaceProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_passenger_cannot_hold_same_seat(): void
    {
        $trip = $this->createTrip();

        $firstPassenger =
            User::factory()
                ->passenger()
                ->create();

        $secondPassenger =
            User::factory()
                ->passenger()
                ->create();

        /*
         * الراكب الأول يحجز المقعد.
         */
        $firstResponse = $this
            ->actingAs($firstPassenger)
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

        $firstResponse
            ->assertSessionHasNoErrors();

        /*
         * راكب آخر يحاول نفس المقعد.
         */
        $secondResponse = $this
            ->actingAs($secondPassenger)
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

        $secondResponse
            ->assertSessionHasErrors(
                'seat_number'
            );

        /*
         * يجب أن يبقى لدينا حجز واحد فقط
         * على المقعد.
         */
        $this->assertSame(
            1,
            Booking::query()
                ->where(
                    'trip_id',
                    $trip->id
                )
                ->where(
                    'seat_number',
                    1
                )
                ->count()
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
            $firstPassenger->id,
            $seat->held_by_user_id
        );
    }

    public function test_repeated_same_request_is_idempotent(): void
    {
        $trip = $this->createTrip();

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $payload = [
            'seat_number' => 1,

            'passenger_gender' => 'male',
        ];

        $this
            ->actingAs($passenger)
            ->post(
                route(
                    'trips.seats.store',
                    $trip
                ),
                $payload
            )
            ->assertSessionHasNoErrors();

        /*
         * نفس المستخدم يعيد نفس الطلب.
         *
         * يجب إعادة نفس الحجز بدلاً
         * من إنشاء حجز جديد.
         */
        $this
            ->actingAs($passenger)
            ->post(
                route(
                    'trips.seats.store',
                    $trip
                ),
                $payload
            )
            ->assertSessionHasNoErrors();

        $this->assertSame(
            1,
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
                ->count()
        );
    }

    public function test_seat_layout_is_not_duplicated(): void
    {
        $trip = $this->createTrip(
            seatCount: 10
        );

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        /*
         * فتح الصفحة أكثر من مرة
         * يستدعي ensureSeatsForTrip أكثر
         * من مرة.
         */
        $this
            ->actingAs($passenger)
            ->get(
                route(
                    'trips.seats.show',
                    [
                        'trip' => $trip,

                        'gender' => 'male',
                    ]
                )
            )
            ->assertOk();

        $this
            ->actingAs($passenger)
            ->get(
                route(
                    'trips.seats.show',
                    [
                        'trip' => $trip,

                        'gender' => 'male',
                    ]
                )
            )
            ->assertOk();

        $this->assertSame(
            10,
            Seat::query()
                ->where(
                    'trip_id',
                    $trip->id
                )
                ->count()
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

            'departure_at' => now()->addDays(3),

            'meeting_point' => 'Meeting Point',

            'destination_point' => 'Destination',

            'price' => 300,

            'seat_count' => $seatCount,

            'available_seats' => $seatCount,

            'status' => TripStatus::Scheduled,

            'is_published' => true,

            'notes' => null,

            'created_by' => $admin->id,
        ]);
    }
}
