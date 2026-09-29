<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\PassengerGender;
use App\Enums\PaymentMethod;
use App\Enums\PaymentReviewStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Enums\SeatStatus;
use App\Enums\SeatType;
use App\Enums\TripStatus;
use App\Jobs\ProcessRefundJob;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Seat;
use App\Models\Trip;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RefundReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_queue_refund_processing(): void
    {
        Queue::fake();

        [
            'admin' => $admin,
            'refund' => $refund,
        ] = $this->createRefundFixture();

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.refunds.process',
                    $refund
                ),
                [
                    'admin_reference' => 'REF-TEST-001',
                ]
            );

        $response->assertSessionHasNoErrors();

        Queue::assertPushed(
            ProcessRefundJob::class,
            function (
                ProcessRefundJob $job
            ) use (
                $refund,
                $admin
            ): bool {
                return $job->refundId === $refund->id
                    && $job->adminId === $admin->id
                    && $job->adminReference === 'REF-TEST-001';
            }
        );
    }

    public function test_refund_processing_is_idempotent_and_releases_seat_once(): void
    {
        [
            'admin' => $admin,
            'booking' => $booking,
            'refund' => $refund,
            'seat' => $seat,
            'trip' => $trip,
        ] = $this->createRefundFixture();

        $service = app(
            RefundService::class
        );

        $service->process(
            $refund,
            $admin,
            'REF-TEST-002'
        );

        $service->process(
            $refund->fresh(),
            $admin,
            'REF-TEST-002'
        );

        $this->assertSame(
            RefundStatus::Processed,
            $refund->fresh()->status
        );

        $this->assertSame(
            PaymentStatus::Refunded,
            $booking->fresh()->payment_status
        );

        $this->assertSame(
            BookingStatus::Cancelled,
            $booking->fresh()->status
        );

        $this->assertSame(
            SeatStatus::Available,
            $seat->fresh()->status
        );

        $this->assertSame(
            8,
            (int) $trip->fresh()->available_seats
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'event' => 'payment.refunded',
                'auditable_id' => $refund->payment_id,
            ]
        );

        $this->assertDatabaseHas(
            'audit_logs',
            [
                'event' => 'booking.refunded',
                'auditable_id' => $booking->id,
            ]
        );

        $this->assertSame(
            3,
            AuditLog::query()->count()
        );
    }

    public function test_admin_can_reject_refund_without_cancelling_booking(): void
    {
        [
            'admin' => $admin,
            'booking' => $booking,
            'refund' => $refund,
            'seat' => $seat,
        ] = $this->createRefundFixture();

        $response = $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.refunds.reject',
                    $refund
                ),
                [
                    'rejection_reason' =>
                        'سبب اختبار صالح لرفض الاسترداد.',
                ]
            );

        $response->assertSessionHasNoErrors();

        $this->assertSame(
            RefundStatus::Rejected,
            $refund->fresh()->status
        );

        $this->assertSame(
            BookingStatus::Confirmed,
            $booking->fresh()->status
        );

        $this->assertSame(
            PaymentStatus::Paid,
            $booking->fresh()->payment_status
        );

        $this->assertSame(
            SeatStatus::Booked,
            $seat->fresh()->status
        );
    }

    public function test_passenger_cannot_access_admin_refund_processing(): void
    {
        [
            'passenger' => $passenger,
            'refund' => $refund,
        ] = $this->createRefundFixture();

        $this
            ->actingAs($passenger)
            ->patch(
                route(
                    'admin.refunds.process',
                    $refund
                ),
                [
                    'admin_reference' => 'REF-NOT-ALLOWED',
                ]
            )
            ->assertForbidden();
    }

    /**
     * @return array{
     *     admin: User,
     *     passenger: User,
     *     booking: Booking,
     *     payment: Payment,
     *     refund: Refund,
     *     seat: Seat,
     *     trip: Trip
     * }
     */
    private function createRefundFixture(): array
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $passenger = User::factory()
            ->passenger()
            ->create();

        $driverUser = User::factory()
            ->driver()
            ->create();

        $driver = Driver::factory()
            ->approved()
            ->create([
                'user_id' => $driverUser->id,
            ]);

        $car = Car::factory()
            ->create([
                'driver_id' => $driver->id,
                'seat_count' => 8,
            ]);

        $from = City::factory()->create();
        $to = City::factory()->create();

        $trip = Trip::query()->create([
            'driver_id' => $driver->id,
            'car_id' => $car->id,
            'source_trip_request_id' => null,
            'from_city_id' => $from->id,
            'to_city_id' => $to->id,
            'departure_at' => now()->addDays(5),
            'meeting_point' => 'Refund Test Meeting Point',
            'destination_point' => 'Refund Test Destination',
            'price' => 250,
            'seat_count' => 8,
            'available_seats' => 7,
            'status' => TripStatus::Scheduled,
            'is_published' => true,
            'notes' => null,
            'created_by' => $admin->id,
        ]);

        $seat = Seat::query()->create([
            'trip_id' => $trip->id,
            'seat_number' => 1,
            'seat_type' => SeatType::Standard,
            'status' => SeatStatus::Booked,
            'held_by_user_id' => null,
            'hold_expires_at' => null,
            'adjacent_seat_number' => 2,
        ]);

        $booking = Booking::query()->create([
            'trip_id' => $trip->id,
            'user_id' => $passenger->id,
            'booking_code' => 'SHF-REFUND-TEST',
            'seat_number' => 1,
            'passenger_name' => $passenger->name,
            'passenger_gender' => PassengerGender::Male,
            'price' => 250,
            'commission' => 0,
            'paid_amount' => 250,
            'payment_status' => PaymentStatus::Paid,
            'status' => BookingStatus::Confirmed,
            'idempotency_key' =>
                'booking:refund:test:'.$passenger->id,
            'confirmed_at' => now(),
        ]);

        $payment = Payment::query()->create([
            'booking_id' => $booking->id,
            'payment_method' => PaymentMethod::BankTransfer,
            'destination_account' => 'TEST-ACCOUNT',
            'amount' => 250,
            'transaction_number' =>
                'TX-REFUND-'.$booking->id,
            'proof_path' =>
                'payments/proofs/test/refund.jpg',
            'status' => PaymentReviewStatus::Approved,
            'rejection_reason' => null,
            'idempotency_key' =>
                'payment:booking:'.$booking->id,
            'submitted_at' => now()->subMinutes(10),
            'expires_at' => now()->addMinutes(50),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $refund = Refund::query()->create([
            'payment_id' => $payment->id,
            'booking_id' => $booking->id,
            'requested_by' => $passenger->id,
            'amount' => 250,
            'reason' => 'سبب اختبار صالح لطلب الاسترداد.',
            'status' => RefundStatus::Pending,
            'refund_idempotency_key' =>
                'refund:payment:'.
                $payment->id.
                ':booking:'.
                $booking->id.
                ':full:v1',
        ]);

        return compact(
            'admin',
            'passenger',
            'booking',
            'payment',
            'refund',
            'seat',
            'trip'
        );
    }
}
