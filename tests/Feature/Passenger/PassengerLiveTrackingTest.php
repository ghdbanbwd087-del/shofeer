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
use App\Models\DriverLocation;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassengerLiveTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_passenger_live_page(): void
    {
        $this
            ->get(route('dashboard.live.index'))
            ->assertRedirect(route('login'));
    }

    public function test_passenger_can_view_live_page(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs($passenger)
            ->get(route('dashboard.live.index'))
            ->assertOk()
            ->assertViewIs('passenger.live.index')
            ->assertSee('التتبع المباشر');
    }

    public function test_passenger_receives_location_for_own_active_trip(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        [
            'booking' => $booking,
            'trip' => $trip,
            'driver' => $driver,
        ] = $this->createActiveBooking($passenger);

        DriverLocation::query()->create([
            'driver_id' => $driver->id,
            'trip_id' => $trip->id,
            'latitude' => 15.369445,
            'longitude' => 44.191006,
            'accuracy_m' => 10,
            'speed_kmh' => 42,
            'heading' => 90,
            'eta_at' => now()->addMinutes(20),
            'recorded_at' => now(),
        ]);

        $this
            ->actingAs($passenger)
            ->getJson(
                route(
                    'dashboard.live.data',
                    [
                        'booking' => $booking->id,
                    ]
                )
            )
            ->assertOk()
            ->assertJson([
                'available' => true,
                'booking_id' => $booking->id,
                'trip_id' => $trip->id,
                'latitude' => 15.369445,
                'longitude' => 44.191006,
                'fresh' => true,
            ]);
    }

    public function test_passenger_cannot_request_another_passengers_trip_location(): void
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
            'booking' => $ownerBooking,
            'trip' => $trip,
            'driver' => $driver,
        ] = $this->createActiveBooking($owner);

        DriverLocation::query()->create([
            'driver_id' => $driver->id,
            'trip_id' => $trip->id,
            'latitude' => 15.369445,
            'longitude' => 44.191006,
            'accuracy_m' => 10,
            'speed_kmh' => 42,
            'heading' => 90,
            'eta_at' => null,
            'recorded_at' => now(),
        ]);

        $this
            ->actingAs($other)
            ->getJson(
                route(
                    'dashboard.live.data',
                    [
                        'booking' => $ownerBooking->id,
                    ]
                )
            )
            ->assertOk()
            ->assertJson([
                'available' => false,
            ])
            ->assertJsonMissing([
                'latitude' => 15.369445,
                'longitude' => 44.191006,
            ]);
    }

    public function test_old_location_is_marked_as_not_fresh(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        [
            'booking' => $booking,
            'trip' => $trip,
            'driver' => $driver,
        ] = $this->createActiveBooking($passenger);

        DriverLocation::query()->create([
            'driver_id' => $driver->id,
            'trip_id' => $trip->id,
            'latitude' => 15.369445,
            'longitude' => 44.191006,
            'accuracy_m' => 10,
            'speed_kmh' => 0,
            'heading' => null,
            'eta_at' => null,
            'recorded_at' => now()->subMinutes(10),
        ]);

        $this
            ->actingAs($passenger)
            ->getJson(
                route(
                    'dashboard.live.data',
                    [
                        'booking' => $booking->id,
                    ]
                )
            )
            ->assertOk()
            ->assertJson([
                'available' => true,
                'fresh' => false,
            ]);
    }

    private function createActiveBooking(User $passenger): array
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
                'departure_at' => now()->subMinutes(20),
                'meeting_point' => 'Meeting Point',
                'destination_point' => 'Destination',
                'price' => 300,
                'seat_count' => 8,
                'available_seats' => 7,
                'status' => TripStatus::InProgress,
                'is_published' => true,
                'notes' => null,
                'created_by' => $admin->id,
            ]);

        $booking =
            Booking::query()->create([
                'trip_id' => $trip->id,
                'user_id' => $passenger->id,
                'booking_code' =>
                    'SHF-LIVE-'.
                    strtoupper(
                        substr(
                            str_replace('-', '', $trip->id),
                            0,
                            10
                        )
                    ),
                'seat_number' => 1,
                'passenger_name' => $passenger->name,
                'passenger_gender' => PassengerGender::Male,
                'passenger_phone' => null,
                'passenger_phone_hash' => null,
                'passenger_whatsapp' => null,
                'passenger_id' => null,
                'passenger_notes' => null,
                'price' => 300,
                'commission' => 0,
                'paid_amount' => 300,
                'payment_status' => PaymentStatus::Paid,
                'status' => BookingStatus::Confirmed,
                'cancel_reason' => null,
                'idempotency_key' =>
                    'live-test:'.
                    $trip->id.
                    ':'.
                    $passenger->id,
                'confirmed_at' => now()->subHour(),
                'cancelled_at' => null,
            ]);

        return [
            'booking' => $booking,
            'trip' => $trip,
            'driver' => $driver,
        ];
    }
}
