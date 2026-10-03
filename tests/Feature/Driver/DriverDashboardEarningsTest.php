<?php

namespace Tests\Feature\Driver;

use App\Enums\BookingStatus;
use App\Enums\PassengerGender;
use App\Enums\PaymentStatus;
use App\Enums\TripStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverDashboardEarningsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_approved_driver_can_view_dashboard(): void
    {
        [
            'user' => $driverUser,
        ] = $this->approvedDriver();

        $this
            ->actingAs($driverUser)
            ->get(
                route(
                    'driver.dashboard'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'driver.dashboard.index'
            )
            ->assertSee(
                'لوحة السائق'
            );
    }

    public function test_passenger_cannot_view_driver_dashboard(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs($passenger)
            ->get(
                route(
                    'driver.dashboard'
                )
            )
            ->assertForbidden();
    }

    public function test_earnings_page_shows_net_only_for_completed_confirmed_bookings(): void
    {
        [
            'user' => $driverUser,
            'driver' => $driver,
        ] = $this->approvedDriver();

        $trip =
            $this->completedTrip(
                $driver
            );

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        Booking::query()->create([
            'trip_id' => $trip->id,
            'user_id' => $passenger->id,
            'booking_code' => 'SHF-EARN-001',
            'seat_number' => 1,
            'passenger_name' => $passenger->name,
            'passenger_gender' => PassengerGender::Male,
            'passenger_phone' => null,
            'passenger_phone_hash' => null,
            'passenger_whatsapp' => null,
            'passenger_id' => null,
            'passenger_notes' => null,
            'price' => 500,
            'commission' => 100,
            'paid_amount' => 500,
            'payment_status' => PaymentStatus::Paid,
            'status' => BookingStatus::Confirmed,
            'cancel_reason' => null,
            'idempotency_key' => 'driver-earnings-test-1',
            'confirmed_at' => now()->subDay(),
            'cancelled_at' => null,
        ]);

        $this
            ->actingAs($driverUser)
            ->get(
                route(
                    'driver.earnings.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'driver.earnings.index'
            )
            ->assertSee(
                '400.00'
            )
            ->assertDontSee(
                '500.00'
            )
            ->assertDontSee(
                '100.00'
            );
    }

    public function test_driver_cannot_see_another_drivers_earnings(): void
    {
        [
            'user' => $firstDriverUser,
        ] = $this->approvedDriver();

        [
            'driver' => $secondDriver,
        ] = $this->approvedDriver();

        $trip =
            $this->completedTrip(
                $secondDriver
            );

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        Booking::query()->create([
            'trip_id' => $trip->id,
            'user_id' => $passenger->id,
            'booking_code' => 'SHF-EARN-002',
            'seat_number' => 1,
            'passenger_name' => $passenger->name,
            'passenger_gender' => PassengerGender::Male,
            'passenger_phone' => null,
            'passenger_phone_hash' => null,
            'passenger_whatsapp' => null,
            'passenger_id' => null,
            'passenger_notes' => null,
            'price' => 999,
            'commission' => 99,
            'paid_amount' => 999,
            'payment_status' => PaymentStatus::Paid,
            'status' => BookingStatus::Confirmed,
            'cancel_reason' => null,
            'idempotency_key' => 'driver-earnings-test-2',
            'confirmed_at' => now()->subDay(),
            'cancelled_at' => null,
        ]);

        $this
            ->actingAs($firstDriverUser)
            ->get(
                route(
                    'driver.earnings.index'
                )
            )
            ->assertOk()
            ->assertDontSee(
                '900.00'
            );
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
            'user' => $user,
            'driver' => $driver,
        ];
    }

    private function completedTrip(
        Driver $driver
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
            City::factory()->create();

        $to =
            City::factory()->create();

        return Trip::query()->create([
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
                now()->subDay(),

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
                TripStatus::Completed,

            'is_published' =>
                true,

            'notes' =>
                null,

            'created_by' =>
                $admin->id,
        ]);
    }
}
