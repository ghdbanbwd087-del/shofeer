<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentReviewStatus;
use App\Enums\PaymentStatus;
use App\Enums\SeatStatus;
use App\Enums\SeatType;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeatLayoutService
{
    public function ensureSeatsForTrip(
        Trip $trip
    ): void {
        if ($trip->seat_count <= 0) {
            return;
        }

        $now = now();

        $rows = [];

        $femaleZoneStart =
            $trip->seat_count >= 6
                ? $trip->seat_count - 1
                : null;

        for (
            $seatNumber = 1;
            $seatNumber <=
                $trip->seat_count;
            $seatNumber++
        ) {
            $adjacentSeatNumber =
                $seatNumber % 2 === 1
                    ? $seatNumber + 1
                    : $seatNumber - 1;

            if (
                $adjacentSeatNumber >
                $trip->seat_count
            ) {
                $adjacentSeatNumber =
                    null;
            }

            $seatType =
                $femaleZoneStart !== null
                && $seatNumber >=
                    $femaleZoneStart
                    ? SeatType::Female->value
                    : SeatType::Standard->value;

            $rows[] = [
                'id' => (string) Str::uuid(),

                'trip_id' => $trip->id,

                'seat_number' => $seatNumber,

                'seat_type' => $seatType,

                'status' => SeatStatus::Available
                    ->value,

                'held_by_user_id' => null,

                'hold_expires_at' => null,

                'adjacent_seat_number' => $adjacentSeatNumber,

                'created_at' => $now,

                'updated_at' => $now,
            ];
        }

        DB::table('seats')
            ->insertOrIgnore($rows);
    }

    /**
     * تحرير المقاعد ذات المهلة المنتهية.
     */
    public function releaseExpiredHolds(
        Trip $trip
    ): void {
        DB::transaction(
            function () use ($trip): void {
                $expiredSeats =
                    Seat::query()
                        ->where(
                            'trip_id',
                            $trip->id
                        )
                        ->where(
                            'status',
                            SeatStatus::Held->value
                        )
                        ->whereNotNull(
                            'hold_expires_at'
                        )
                        ->where(
                            'hold_expires_at',
                            '<=',
                            now()
                        )
                        ->lockForUpdate()
                        ->get();

                foreach (
                    $expiredSeats as $seat
                ) {
                    /*
                     * قد يكون الحجز في Hold 15 دقيقة
                     * أو Pending Payment لمدة 60 دقيقة.
                     */
                    $booking =
                        Booking::query()
                            ->where(
                                'trip_id',
                                $trip->id
                            )
                            ->where(
                                'seat_number',
                                $seat->seat_number
                            )
                            ->whereIn(
                                'status',
                                [
                                    BookingStatus::Held
                                        ->value,

                                    BookingStatus::PendingPayment
                                        ->value,
                                ]
                            )
                            ->lockForUpdate()
                            ->first();

                    if ($booking !== null) {
                        if (
                            $booking->status ===
                            BookingStatus::PendingPayment
                        ) {
                            Payment::query()
                                ->where(
                                    'booking_id',
                                    $booking->id
                                )
                                ->where(
                                    'status',
                                    PaymentReviewStatus::Pending
                                        ->value
                                )
                                ->update([
                                    'status' => PaymentReviewStatus::Rejected
                                        ->value,

                                    'rejection_reason' => 'انتهت مهلة التحقق من الدفع.',

                                    'reviewed_at' => now(),
                                ]);
                        }

                        $booking->update([
                            'status' => BookingStatus::Expired,

                            'payment_status' => $booking->status ===
                                BookingStatus::PendingPayment
                                    ? PaymentStatus::Failed
                                    : $booking
                                        ->payment_status,

                            'cancel_reason' => 'انتهت المهلة الزمنية للحجز.',
                        ]);
                    }

                    $seat->update([
                        'status' => SeatStatus::Available,

                        'held_by_user_id' => null,

                        'hold_expires_at' => null,
                    ]);
                }
            },
            3
        );
    }
}
