<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentReviewStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Enums\SeatStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Seat;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        private readonly AuditLogger $auditLogger
    ) {
        //
    }

    /*
    |--------------------------------------------------------------------------
    | Request Refund
    |--------------------------------------------------------------------------
    */

    public function request(
        Booking $booking,
        User $user,
        string $reason
    ): Refund {
        return DB::transaction(
            function () use (
                $booking,
                $user,
                $reason
            ): Refund {
                $lockedBooking = Booking::query()
                    ->whereKey($booking->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    (string) $lockedBooking->user_id
                    !== (string) $user->id
                ) {
                    abort(403);
                }

                $bookingStatus =
                    $lockedBooking->status instanceof \BackedEnum
                        ? $lockedBooking->status->value
                        : (string) $lockedBooking->status;

                if (
                    $bookingStatus
                    !== BookingStatus::Confirmed->value
                ) {
                    throw ValidationException::withMessages([
                        'refund' =>
                            'طلب الاسترداد متاح للحجوزات المؤكدة والمدفوعة فقط.',
                    ]);
                }

                $lockedBooking->loadMissing('trip');

                if (! $lockedBooking->trip) {
                    throw ValidationException::withMessages([
                        'refund' =>
                            'لا توجد رحلة مرتبطة بهذا الحجز.',
                    ]);
                }

                $departureAt = Carbon::parse(
                    $lockedBooking->trip->departure_at
                );

                if ($departureAt->isPast()) {
                    throw ValidationException::withMessages([
                        'refund' =>
                            'لا يمكن طلب الاسترداد بعد موعد الرحلة.',
                    ]);
                }

                $payment = Payment::query()
                    ->where('booking_id', $lockedBooking->id)
                    ->lockForUpdate()
                    ->first();

                if (! $payment) {
                    throw ValidationException::withMessages([
                        'refund' =>
                            'لا توجد عملية دفع مرتبطة بهذا الحجز.',
                    ]);
                }

                $paymentStatus =
                    $payment->status instanceof \BackedEnum
                        ? $payment->status->value
                        : (string) $payment->status;

                if (
                    $paymentStatus
                    !== PaymentReviewStatus::Approved->value
                ) {
                    throw ValidationException::withMessages([
                        'refund' =>
                            'لا يمكن طلب الاسترداد قبل اعتماد عملية الدفع.',
                    ]);
                }

                $idempotencyKey =
                    'refund:payment:'.
                    $payment->id.
                    ':booking:'.
                    $lockedBooking->id.
                    ':full:v1';

                $existingRefund = Refund::query()
                    ->where(
                        'refund_idempotency_key',
                        $idempotencyKey
                    )
                    ->lockForUpdate()
                    ->first();

                if ($existingRefund) {
                    return $existingRefund;
                }

                $pendingRefund = Refund::query()
                    ->where('payment_id', $payment->id)
                    ->where(
                        'status',
                        RefundStatus::Pending->value
                    )
                    ->lockForUpdate()
                    ->first();

                if ($pendingRefund) {
                    return $pendingRefund;
                }

                $alreadyRefunded = (float) Refund::query()
                    ->where('payment_id', $payment->id)
                    ->where(
                        'status',
                        RefundStatus::Processed->value
                    )
                    ->sum('amount');

                $paymentAmount = (float) $payment->amount;

                $remainingAmount = round(
                    $paymentAmount - $alreadyRefunded,
                    2
                );

                if ($remainingAmount <= 0) {
                    throw ValidationException::withMessages([
                        'refund' =>
                            'تم استرداد كامل مبلغ هذه العملية بالفعل.',
                    ]);
                }

                return Refund::query()->create([
                    'payment_id' => $payment->id,
                    'booking_id' => $lockedBooking->id,
                    'requested_by' => $user->id,
                    'amount' => $remainingAmount,
                    'reason' => trim($reason),
                    'status' => RefundStatus::Pending,
                    'refund_idempotency_key' => $idempotencyKey,
                ]);
            },
            3
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Process Refund
    |--------------------------------------------------------------------------
    */

    public function process(
        Refund $refund,
        User $admin,
        string $adminReference
    ): Refund {
        if (! $admin->isAdmin()) {
            throw ValidationException::withMessages([
                'refund' => 'هذه العملية متاحة للإدارة فقط.',
            ]);
        }

        return DB::transaction(
            function () use (
                $refund,
                $admin,
                $adminReference
            ): Refund {
                $lockedRefund = Refund::query()
                    ->whereKey($refund->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedRefund->status
                    === RefundStatus::Processed
                ) {
                    return $lockedRefund;
                }

                if (
                    $lockedRefund->status
                    === RefundStatus::Rejected
                ) {
                    throw ValidationException::withMessages([
                        'refund' =>
                            'لا يمكن تنفيذ طلب استرداد تم رفضه.',
                    ]);
                }

                $payment = Payment::query()
                    ->whereKey($lockedRefund->payment_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $payment->status
                    !== PaymentReviewStatus::Approved
                ) {
                    throw ValidationException::withMessages([
                        'refund' =>
                            'عملية الدفع المرتبطة بالاسترداد ليست معتمدة.',
                    ]);
                }

                $booking = Booking::query()
                    ->whereKey($lockedRefund->booking_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $alreadyProcessed = (float) Refund::query()
                    ->where('payment_id', $payment->id)
                    ->where(
                        'status',
                        RefundStatus::Processed->value
                    )
                    ->sum('amount');

                $refundAmount = (float) $lockedRefund->amount;
                $paymentAmount = (float) $payment->amount;

                if (
                    $refundAmount <= 0
                    || round(
                        $alreadyProcessed + $refundAmount,
                        2
                    ) > round($paymentAmount, 2)
                ) {
                    throw ValidationException::withMessages([
                        'refund' =>
                            'مبلغ الاسترداد غير صالح أو يتجاوز مبلغ الدفع.',
                    ]);
                }

                $lockedRefund->update([
                    'status' => RefundStatus::Processed,
                    'reviewed_by' => $admin->id,
                    'rejection_reason' => null,
                    'admin_reference' => trim($adminReference),
                    'reviewed_at' => now(),
                    'processed_at' => now(),
                ]);

                $totalProcessed = round(
                    $alreadyProcessed + $refundAmount,
                    2
                );

                $this->auditLogger->log(
                    $admin,
                    'refund.processed',
                    $lockedRefund,
                    [
                        'payment_id' => $payment->id,
                        'booking_id' => $booking->id,
                        'amount' => $refundAmount,
                        'admin_reference' => trim($adminReference),
                    ]
                );

                $this->auditLogger->log(
                    $admin,
                    'payment.refunded',
                    $payment,
                    [
                        'refund_id' => $lockedRefund->id,
                        'amount' => $refundAmount,
                        'total_refunded' => $totalProcessed,
                    ]
                );

                /*
                 * عند استرداد كامل مبلغ الدفع فقط
                 * نلغي الحجز ونحرر المقعد.
                 */
                if (
                    $totalProcessed
                    >= round($paymentAmount, 2)
                ) {
                    $seat = Seat::query()
                        ->where('trip_id', $booking->trip_id)
                        ->where(
                            'seat_number',
                            $booking->seat_number
                        )
                        ->lockForUpdate()
                        ->first();

                    $trip = Trip::query()
                        ->whereKey($booking->trip_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $seatWasBooked =
                        $seat !== null
                        && $seat->status === SeatStatus::Booked;

                    $bookingWasRefunded =
                        $booking->payment_status
                        === PaymentStatus::Refunded;

                    if (! $bookingWasRefunded) {
                        $booking->update([
                            'paid_amount' => 0,
                            'payment_status' => PaymentStatus::Refunded,
                            'status' => BookingStatus::Cancelled,
                            'cancel_reason' =>
                                'تم استرداد كامل مبلغ الحجز.',
                            'cancelled_at' => now(),
                        ]);
                    }

                    if ($seatWasBooked) {
                        $seat->update([
                            'status' => SeatStatus::Available,
                            'held_by_user_id' => null,
                            'hold_expires_at' => null,
                        ]);

                        $trip->update([
                            'available_seats' => min(
                                (int) $trip->seat_count,
                                (int) $trip->available_seats + 1
                            ),
                        ]);
                    }

                    $this->auditLogger->log(
                        $admin,
                        'booking.refunded',
                        $booking,
                        [
                            'refund_id' => $lockedRefund->id,
                            'payment_id' => $payment->id,
                            'amount' => $totalProcessed,
                        ]
                    );
                }

                return $lockedRefund->fresh();
            },
            3
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reject Refund
    |--------------------------------------------------------------------------
    */

    public function reject(
        Refund $refund,
        User $admin,
        string $reason
    ): Refund {
        if (! $admin->isAdmin()) {
            throw ValidationException::withMessages([
                'refund' => 'هذه العملية متاحة للإدارة فقط.',
            ]);
        }

        return DB::transaction(
            function () use (
                $refund,
                $admin,
                $reason
            ): Refund {
                $lockedRefund = Refund::query()
                    ->whereKey($refund->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedRefund->status
                    === RefundStatus::Rejected
                ) {
                    return $lockedRefund;
                }

                if (
                    $lockedRefund->status
                    === RefundStatus::Processed
                ) {
                    throw ValidationException::withMessages([
                        'refund' =>
                            'لا يمكن رفض استرداد تم تنفيذه.',
                    ]);
                }

                $lockedRefund->update([
                    'status' => RefundStatus::Rejected,
                    'reviewed_by' => $admin->id,
                    'rejection_reason' => trim($reason),
                    'admin_reference' => null,
                    'reviewed_at' => now(),
                    'processed_at' => null,
                ]);

                $this->auditLogger->log(
                    $admin,
                    'refund.rejected',
                    $lockedRefund,
                    [
                        'payment_id' => $lockedRefund->payment_id,
                        'booking_id' => $lockedRefund->booking_id,
                        'reason' => trim($reason),
                    ]
                );

                return $lockedRefund->fresh();
            },
            3
        );
    }
}
