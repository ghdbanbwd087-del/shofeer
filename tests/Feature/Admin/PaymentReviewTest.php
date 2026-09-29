<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\PaymentReviewStatus;
use App\Enums\PaymentStatus;
use App\Enums\SeatStatus;
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

class PaymentReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_payment_and_confirm_booking(): void
    {
        Storage::fake('local');

        [
            'admin' => $admin,

            'booking' => $booking,

            'payment' => $payment,

            'trip' => $trip,
        ] = $this->createPendingPayment();

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.payments.approve',
                    $payment
                )
            );

        $response
            ->assertRedirect()
            ->assertSessionHas(
                'success'
            );

        $booking->refresh();

        $payment->refresh();

        $trip->refresh();

        $seat = Seat::query()
            ->where(
                'trip_id',
                $trip->id
            )
            ->where(
                'seat_number',
                $booking->seat_number
            )
            ->firstOrFail();

        $this->assertSame(
            PaymentReviewStatus::Approved,
            $payment->status
        );

        $this->assertSame(
            PaymentStatus::Paid,
            $booking->payment_status
        );

        $this->assertSame(
            BookingStatus::Confirmed,
            $booking->status
        );

        $this->assertSame(
            SeatStatus::Booked,
            $seat->status
        );

        $this->assertSame(
            7,
            $trip->available_seats
        );

        /*
         * الضغط على تأكيد مرة ثانية
         * يجب ألا ينقص المقاعد مجدداً.
         */
        $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.payments.approve',
                    $payment
                )
            )
            ->assertRedirect();

        $this->assertSame(
            7,
            $trip
                ->fresh()
                ->available_seats
        );

        /*
         * الراكب يصل إلى صفحة النجاح.
         */
        $this
            ->actingAs(
                $booking->user
            )
            ->get(
                route(
                    'booking.success',
                    $booking
                )
            )
            ->assertOk()
            ->assertSee(
                $booking->booking_code
            );
    }

    public function test_admin_can_reject_payment_and_release_seat(): void
    {
        Storage::fake('local');

        [
            'admin' => $admin,

            'booking' => $booking,

            'payment' => $payment,

            'trip' => $trip,
        ] = $this->createPendingPayment();

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.payments.reject',
                    $payment
                ),
                [
                    'rejection_reason' => 'إثبات الدفع غير واضح.',
                ]
            );

        $response
            ->assertRedirect()
            ->assertSessionHas(
                'success'
            );

        $booking->refresh();

        $payment->refresh();

        $seat = Seat::query()
            ->where(
                'trip_id',
                $trip->id
            )
            ->where(
                'seat_number',
                $booking->seat_number
            )
            ->firstOrFail();

        $this->assertSame(
            PaymentReviewStatus::Rejected,
            $payment->status
        );

        $this->assertSame(
            PaymentStatus::Failed,
            $booking->payment_status
        );

        $this->assertSame(
            BookingStatus::Cancelled,
            $booking->status
        );

        $this->assertSame(
            SeatStatus::Available,
            $seat->status
        );

        /*
         * لم يتم تأكيد المقعد، لذلك
         * available_seats لا ينقص.
         */
        $this->assertSame(
            8,
            $trip
                ->fresh()
                ->available_seats
        );

        $this
            ->actingAs(
                $booking->user
            )
            ->get(
                route(
                    'booking.failed',
                    $booking
                )
            )
            ->assertOk()
            ->assertSee(
                'إثبات الدفع غير واضح.'
            );
    }

    /**
     * @return array{
     *     admin:User,
     *     booking:Booking,
     *     payment:Payment,
     *     trip:Trip
     * }
     */
    private function createPendingPayment(): array
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $passenger =
            User::factory()
                ->passenger()
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

        $trip =
            Trip::query()
                ->create([
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

        /*
         * اختيار المقعد.
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
        $this
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

                    'whatsapp_same' => '1',

                    'payment_method' => 'karimi',

                    'transaction_number' => 'TX-'.
                        fake()->unique()
                            ->numerify(
                                '########'
                            ),

                    'payment_proof' => UploadedFile::fake()
                        ->image(
                            'proof.jpg'
                        ),

                    'terms' => '1',
                ]
            )
            ->assertSessionHasNoErrors();

        $booking->refresh();

        $payment =
            Payment::query()
                ->where(
                    'booking_id',
                    $booking->id
                )
                ->firstOrFail();

        return [
            'admin' => $admin,

            'booking' => $booking,

            'payment' => $payment,

            'trip' => $trip,
        ];
    }
}
