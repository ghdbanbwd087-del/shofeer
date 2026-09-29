<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\PaymentReviewStatus;
use App\Enums\PaymentStatus;
use App\Enums\TripStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_can_submit_payment_proof(): void
    {
        Storage::fake('local');

        $trip =
            $this->createTrip();

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        /*
         * حجز المقعد أولاً.
         */
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

        $booking =
            Booking::query()
                ->where(
                    'user_id',
                    $passenger->id
                )
                ->firstOrFail();

        /*
         * إرسال الدفع.
         */
        $response = $this
            ->actingAs($passenger)
            ->post(
                route(
                    'booking.pay.submit',
                    $booking
                ),
                [
                    'passenger_name' => 'Passenger Test',

                    'passenger_phone' => '+967771234567',

                    'passenger_id' => 'ID-123456',

                    'passenger_gender' => 'male',

                    'passenger_notes' => 'Test note',

                    'whatsapp_same' => '1',

                    'payment_method' => 'karimi',

                    'transaction_number' => 'TX-TEST-123',

                    'payment_proof' => UploadedFile::fake()
                        ->image(
                            'proof.jpg'
                        ),

                    'terms' => '1',
                ]
            );

        $response->assertRedirect(
            route(
                'booking.pending',
                $booking
            )
        );

        $booking->refresh();

        $this->assertSame(
            BookingStatus::PendingPayment,
            $booking->status
        );

        $this->assertSame(
            PaymentStatus::Pending,
            $booking->payment_status
        );

        $this->assertSame(
            '+967771234567',
            $booking->passenger_whatsapp
        );

        $payment =
            Payment::query()
                ->where(
                    'booking_id',
                    $booking->id
                )
                ->firstOrFail();

        $this->assertSame(
            PaymentReviewStatus::Pending,
            $payment->status
        );

        $this->assertSame(
            'TX-TEST-123',
            $payment->transaction_number
        );

        $this->assertSame(
            'payment:booking:'.
            $booking->id,
            $payment->idempotency_key
        );

        Storage::disk('local')
            ->assertExists(
                $payment->proof_path
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

        /*
         * بعد إرسال الدفع تتحول المهلة
         * من 15 دقيقة إلى قرابة 60 دقيقة.
         */
        $this->assertTrue(
            $seat->hold_expires_at
                ->greaterThan(
                    now()->addMinutes(50)
                )
        );
    }

    public function test_user_cannot_submit_payment_for_another_users_booking(): void
    {
        Storage::fake('local');

        $trip =
            $this->createTrip();

        $owner =
            User::factory()
                ->passenger()
                ->create();

        $attacker =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs($owner)
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

        $response = $this
            ->actingAs($attacker)
            ->post(
                route(
                    'booking.pay.submit',
                    $booking
                ),
                [
                    'passenger_name' => 'Other User',
                ]
            );

        $response->assertForbidden();

        $this->assertDatabaseCount(
            'payments',
            0
        );
    }

    private function createTrip(): Trip
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

            'seat_count' => 8,

            'available_seats' => 8,

            'status' => TripStatus::Scheduled,

            'is_published' => true,

            'notes' => null,

            'created_by' => $admin->id,
        ]);
    }
}
