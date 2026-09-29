<?php

namespace Tests\Feature\Passenger;

use App\Enums\BookingStatus;
use App\Enums\PassengerGender;
use App\Enums\PaymentStatus;
use App\Enums\TripStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Rating;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassengerRatingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_ratings_page(): void
    {
        $this
            ->get(
                route(
                    'dashboard.ratings.index'
                )
            )
            ->assertRedirect(
                route('login')
            );
    }

    public function test_passenger_can_view_ratings_page(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs($passenger)
            ->get(
                route(
                    'dashboard.ratings.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'passenger.ratings.index'
            )
            ->assertSee(
                'تقييماتي'
            );
    }

    public function test_passenger_can_rate_completed_confirmed_booking(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        [
            'booking' => $booking,
            'driver' => $driver,
        ] = $this->createBooking(
            passenger: $passenger,
            departureAt: now()->subDay(),
        );

        $this
            ->actingAs($passenger)
            ->post(
                route(
                    'dashboard.bookings.rating.store',
                    $booking
                ),
                [
                    'score' => 5,
                    'comment' => 'رحلة ممتازة',
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertRedirect(
                route(
                    'dashboard.ratings.index'
                )
            );

        $this->assertDatabaseHas(
            'ratings',
            [
                'booking_id' => $booking->id,
                'user_id' => $passenger->id,
                'driver_id' => $driver->id,
                'score' => 5,
                'comment' => 'رحلة ممتازة',
            ]
        );

        $this->assertSame(
            5.0,
            (float) $driver
                ->refresh()
                ->rating
        );
    }

    public function test_passenger_cannot_rate_future_trip(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        [
            'booking' => $booking,
        ] = $this->createBooking(
            passenger: $passenger,
            departureAt: now()->addDay(),
        );

        $this
            ->actingAs($passenger)
            ->post(
                route(
                    'dashboard.bookings.rating.store',
                    $booking
                ),
                [
                    'score' => 5,
                    'comment' => 'مبكر',
                ]
            )
            ->assertSessionHasErrors(
                'rating'
            );

        $this->assertDatabaseCount(
            'ratings',
            0
        );
    }

    public function test_passenger_cannot_rate_another_users_booking(): void
    {
        $owner =
            User::factory()
                ->passenger()
                ->create();

        $other =
            User::factory()
                ->passenger()
                ->create();

        [
            'booking' => $booking,
        ] = $this->createBooking(
            passenger: $owner,
            departureAt: now()->subDay(),
        );

        $this
            ->actingAs($other)
            ->post(
                route(
                    'dashboard.bookings.rating.store',
                    $booking
                ),
                [
                    'score' => 4,
                ]
            )
            ->assertSessionHasErrors(
                'rating'
            );

        $this->assertDatabaseCount(
            'ratings',
            0
        );
    }

    public function test_same_booking_cannot_create_duplicate_rating(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        [
            'booking' => $booking,
        ] = $this->createBooking(
            passenger: $passenger,
            departureAt: now()->subDay(),
        );

        $this
            ->actingAs($passenger)
            ->post(
                route(
                    'dashboard.bookings.rating.store',
                    $booking
                ),
                [
                    'score' => 5,
                ]
            )
            ->assertRedirect();

        $this
            ->actingAs($passenger)
            ->post(
                route(
                    'dashboard.bookings.rating.store',
                    $booking
                ),
                [
                    'score' => 3,
                ]
            )
            ->assertRedirect();

        $this->assertDatabaseCount(
            'ratings',
            1
        );

        $this->assertDatabaseHas(
            'ratings',
            [
                'booking_id' => $booking->id,
                'score' => 5,
            ]
        );
    }

    public function test_passenger_can_update_own_rating(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        [
            'booking' => $booking,
            'trip' => $trip,
            'driver' => $driver,
        ] = $this->createBooking(
            passenger: $passenger,
            departureAt: now()->subDay(),
        );

        $rating =
            Rating::query()->create([
                'booking_id' => $booking->id,
                'trip_id' => $trip->id,
                'driver_id' => $driver->id,
                'user_id' => $passenger->id,
                'score' => 3,
                'comment' => 'جيد',
            ]);

        $this
            ->actingAs($passenger)
            ->patch(
                route(
                    'dashboard.ratings.update',
                    $rating
                ),
                [
                    'score' => 4,
                    'comment' => 'جيد جدًا',
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertRedirect(
                route(
                    'dashboard.ratings.index'
                )
            );

        $this->assertDatabaseHas(
            'ratings',
            [
                'id' => $rating->id,
                'score' => 4,
                'comment' => 'جيد جدًا',
            ]
        );

        $this->assertSame(
            4.0,
            (float) $driver
                ->refresh()
                ->rating
        );
    }

    private function createBooking(
        User $passenger,
        $departureAt
    ): array {
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
                    'seat_count' => 8,
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
                    $departureAt,

                'meeting_point' =>
                    'Meeting Point',

                'destination_point' =>
                    'Destination',

                'price' =>
                    300,

                'seat_count' =>
                    8,

                'available_seats' =>
                    7,

                'status' =>
                    TripStatus::Completed,

                'is_published' =>
                    true,

                'notes' =>
                    null,

                'created_by' =>
                    $admin->id,
            ]);

        $booking =
            Booking::query()->create([
                'trip_id' =>
                    $trip->id,

                'user_id' =>
                    $passenger->id,

                'booking_code' =>
                    'SHF-RATE-'.
                    strtoupper(
                        substr(
                            str_replace(
                                '-',
                                '',
                                $trip->id
                            ),
                            0,
                            10
                        )
                    ),

                'seat_number' =>
                    1,

                'passenger_name' =>
                    $passenger->name,

                'passenger_gender' =>
                    PassengerGender::Male,

                'passenger_phone' =>
                    null,

                'passenger_phone_hash' =>
                    null,

                'passenger_whatsapp' =>
                    null,

                'passenger_id' =>
                    null,

                'passenger_notes' =>
                    null,

                'price' =>
                    300,

                'commission' =>
                    0,

                'paid_amount' =>
                    300,

                'payment_status' =>
                    PaymentStatus::Paid,

                'status' =>
                    BookingStatus::Confirmed,

                'cancel_reason' =>
                    null,

                'idempotency_key' =>
                    'rating-test-booking:'.
                    $trip->id.
                    ':'.
                    $passenger->id,

                'confirmed_at' =>
                    now()->subDays(2),

                'cancelled_at' =>
                    null,
            ]);

        return [
            'booking' => $booking,
            'trip' => $trip,
            'driver' => $driver,
        ];
    }
}
