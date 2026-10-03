<?php

namespace Tests\Feature\Driver;

use App\Enums\BookingStatus;
use App\Enums\PassengerGender;
use App\Enums\PaymentStatus;
use App\Enums\TripStatus;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverPassengersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_driver_sees_only_first_name_and_seat_number(): void
    {
        [
            'user' =>
                $driverUser,

            'driver' =>
                $driver,
        ] = $this->approvedDriver();

        $trip =
            $this->tripFor(
                driver: $driver,
                departureAt: now()
                    ->addHours(2),
            );

        $booking =
            $this->confirmedBooking(
                trip: $trip,
                passengerName:
                    'أحمد محمد علي',
                whatsapp:
                    '+967777111222',
            );

        $response =
            $this
                ->actingAs(
                    $driverUser
                )
                ->get(
                    route(
                        'driver.passengers.index'
                    )
                );

        $response
            ->assertOk()
            ->assertViewIs(
                'driver.passengers.index'
            )
            ->assertSee(
                'أحمد'
            )
            ->assertSee(
                (string) $booking
                    ->seat_number
            )
            ->assertDontSee(
                'أحمد محمد علي'
            )
            ->assertDontSee(
                '+967777111222'
            )
            ->assertDontSee(
                '500.00'
            );
    }

    public function test_whatsapp_link_is_only_visible_in_last_thirty_minutes_before_departure(): void
    {
        [
            'user' =>
                $driverUser,

            'driver' =>
                $driver,
        ] = $this->approvedDriver();

        $trip =
            $this->tripFor(
                driver: $driver,
                departureAt: now()
                    ->addMinutes(20),
            );

        $this->confirmedBooking(
            trip: $trip,
            passengerName:
                'سالم عبدالله',
            whatsapp:
                '+967777333444',
        );

        $this
            ->actingAs(
                $driverUser
            )
            ->get(
                route(
                    'driver.passengers.index'
                )
            )
            ->assertOk()
            ->assertSee(
                'https://wa.me/967777333444',
                false
            )
            ->assertSee(
                'فتح واتساب'
            );
    }

    public function test_whatsapp_link_is_hidden_outside_the_thirty_minute_window(): void
    {
        [
            'user' =>
                $driverUser,

            'driver' =>
                $driver,
        ] = $this->approvedDriver();

        $trip =
            $this->tripFor(
                driver: $driver,
                departureAt: now()
                    ->addHours(3),
            );

        $this->confirmedBooking(
            trip: $trip,
            passengerName:
                'محمد صالح',
            whatsapp:
                '+967777555666',
        );

        $this
            ->actingAs(
                $driverUser
            )
            ->get(
                route(
                    'driver.passengers.index'
                )
            )
            ->assertOk()
            ->assertDontSee(
                'https://wa.me/967777555666',
                false
            )
            ->assertSee(
                'متاح فقط آخر 30 دقيقة'
            );
    }

    public function test_passenger_cannot_view_driver_passengers_page(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs(
                $passenger
            )
            ->get(
                route(
                    'driver.passengers.index'
                )
            )
            ->assertForbidden();
    }

    public function test_driver_can_contact_admin_about_own_passenger_without_exposing_passenger_identity(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        [
            'user' =>
                $driverUser,

            'driver' =>
                $driver,
        ] = $this->approvedDriver();

        $trip =
            $this->tripFor(
                driver: $driver,
                departureAt: now()
                    ->addMinutes(20),
            );

        $booking =
            $this->confirmedBooking(
                trip: $trip,
                passengerName:
                    'اسم سري للراكب',
                whatsapp:
                    '+967777000999',
            );

        $this
            ->actingAs(
                $driverUser
            )
            ->post(
                route(
                    'driver.passengers.contact-admin',
                    $booking
                )
            )
            ->assertRedirect()
            ->assertSessionHas(
                'success'
            );

        $notification =
            AppNotification::query()
                ->where(
                    'notifiable_id',
                    $admin->id
                )
                ->latest()
                ->firstOrFail();

        $message =
            (string) (
                $notification
                    ->data['message']
                ?? ''
            );

        $this->assertStringContainsString(
            $booking->booking_code,
            $message
        );

        $this->assertStringNotContainsString(
            'اسم سري للراكب',
            $message
        );

        $this->assertStringNotContainsString(
            '+967777000999',
            $message
        );
    }

    public function test_driver_cannot_contact_admin_about_another_drivers_passenger(): void
    {
        [
            'user' =>
                $firstDriverUser,
        ] = $this->approvedDriver();

        [
            'driver' =>
                $secondDriver,
        ] = $this->approvedDriver();

        $trip =
            $this->tripFor(
                driver: $secondDriver,
                departureAt: now()
                    ->addMinutes(20),
            );

        $booking =
            $this->confirmedBooking(
                trip: $trip,
                passengerName:
                    'راكب آخر',
                whatsapp:
                    '+967777888999',
            );

        $this
            ->actingAs(
                $firstDriverUser
            )
            ->post(
                route(
                    'driver.passengers.contact-admin',
                    $booking
                )
            )
            ->assertForbidden();
    }

    private function approvedDriver(): array
    {
        $user =
            User::factory()
                ->driver()
                ->create();

        $driver =
            Driver::factory()
                ->approved()
                ->create([
                    'user_id' =>
                        $user->id,
                ]);

        return [
            'user' =>
                $user,

            'driver' =>
                $driver,
        ];
    }

    private function tripFor(
        Driver $driver,
        $departureAt
    ): Trip {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $car =
            Car::factory()
                ->create([
                    'driver_id' =>
                        $driver->id,

                    'seat_count' =>
                        8,
                ]);

        $from =
            City::factory()
                ->create();

        $to =
            City::factory()
                ->create();

        return Trip::query()
            ->create([
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
                    500,

                'seat_count' =>
                    8,

                'available_seats' =>
                    7,

                'status' =>
                    TripStatus::Scheduled,

                'is_published' =>
                    true,

                'notes' =>
                    null,

                'created_by' =>
                    $admin->id,
            ]);
    }

    private function confirmedBooking(
        Trip $trip,
        string $passengerName,
        ?string $whatsapp
    ): Booking {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        return Booking::query()
            ->create([
                'trip_id' =>
                    $trip->id,

                'user_id' =>
                    $passenger->id,

                'booking_code' =>
                    'SHF-PASS-'.
                    strtoupper(
                        substr(
                            str_replace(
                                '-',
                                '',
                                fake()->uuid()
                            ),
                            0,
                            10
                        )
                    ),

                'seat_number' =>
                    1,

                'passenger_name' =>
                    $passengerName,

                'passenger_gender' =>
                    PassengerGender::Male,

                'passenger_phone' =>
                    null,

                'passenger_phone_hash' =>
                    null,

                'passenger_whatsapp' =>
                    $whatsapp,

                'passenger_id' =>
                    'PRIVATE-ID-SECRET',

                'passenger_notes' =>
                    'PRIVATE NOTES',

                'price' =>
                    500,

                'commission' =>
                    75,

                'paid_amount' =>
                    500,

                'payment_status' =>
                    PaymentStatus::Paid,

                'status' =>
                    BookingStatus::Confirmed,

                'cancel_reason' =>
                    null,

                'idempotency_key' =>
                    fake()->uuid(),

                'confirmed_at' =>
                    now(),

                'cancelled_at' =>
                    null,
            ]);
    }
}
