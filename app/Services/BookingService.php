<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PassengerGender;
use App\Enums\PaymentReviewStatus;
use App\Enums\PaymentStatus;
use App\Enums\SeatStatus;
use App\Enums\SeatType;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly SeatLayoutService $seatLayoutService
    ) {
        //
    }

    /*
    |--------------------------------------------------------------------------
    | Hold Seat
    |--------------------------------------------------------------------------
    */

    public function holdSeat(
        Trip $trip,
        User $user,
        array $data
    ): Booking {
        /*
        |--------------------------------------------------------------------------
        | Ensure Seat Layout Exists
        |--------------------------------------------------------------------------
        |
        | مهم:
        |
        | قد يصل المستخدم مباشرة إلى POST بدون أن يفتح صفحة المقاعد GET.
        | لذلك لا نعتمد على SeatController@show لإنشاء المقاعد.
        |
        */

        $this->seatLayoutService
            ->ensureSeatsForTrip($trip);

        /*
         * تنظيف الحجوزات المؤقتة المنتهية قبل محاولة الحجز الجديدة.
         */
        $this->seatLayoutService
            ->releaseExpiredHolds($trip);

        return DB::transaction(
            function () use (
                $trip,
                $user,
                $data
            ): Booking {
                /*
                |--------------------------------------------------------------------------
                | Passenger Only
                |--------------------------------------------------------------------------
                */

                $role =
                    $user->role instanceof \BackedEnum
                        ? $user->role->value
                        : (string) $user->role;

                if (
                    $role !== 'passenger'
                ) {
                    throw ValidationException::withMessages([
                        'booking' => 'السائق أو الإداري لا يستطيع الحجز كراكب.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Required Data
                |--------------------------------------------------------------------------
                */

                $seatNumber =
                    (int) $data['seat_number'];

                $genderValue =
                    $data['passenger_gender']
                    instanceof PassengerGender
                        ? $data['passenger_gender']->value
                        : (string) $data['passenger_gender'];

                /*
                |--------------------------------------------------------------------------
                | Idempotency Key
                |--------------------------------------------------------------------------
                |
                | HoldSeatRequest لا يرسل idempotency_key حالياً.
                |
                | لذلك ننشئ مفتاحاً تجارياً ثابتاً من:
                |
                | trip + user + seat
                |
                | إذا أعاد نفس الراكب نفس الطلب،
                | نعيد نفس Booking.
                |
                */

                $idempotencyKey =
                    isset($data['idempotency_key'])
                    && filled(
                        $data['idempotency_key']
                    )
                        ? (string) $data['idempotency_key']
                        : 'booking:trip:'.
                            $trip->id.
                            ':user:'.
                            $user->id.
                            ':seat:'.
                            $seatNumber;

                /*
                |--------------------------------------------------------------------------
                | Existing Idempotent Request
                |--------------------------------------------------------------------------
                */

                $existingByIdempotency =
                    Booking::query()
                        ->where(
                            'idempotency_key',
                            $idempotencyKey
                        )
                        ->lockForUpdate()
                        ->first();

                if (
                    $existingByIdempotency
                ) {
                    return $existingByIdempotency;
                }

                /*
                |--------------------------------------------------------------------------
                | Lock Trip
                |--------------------------------------------------------------------------
                */

                $lockedTrip =
                    Trip::query()
                        ->whereKey(
                            $trip->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Lock Seat
                |--------------------------------------------------------------------------
                */

                $seat =
                    Seat::query()
                        ->where(
                            'trip_id',
                            $lockedTrip->id
                        )
                        ->where(
                            'seat_number',
                            $seatNumber
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Release Expired Hold
                |--------------------------------------------------------------------------
                |
                | هذا فحص إضافي داخل Transaction للحماية من Race Condition.
                |
                */

                $seatStatus =
                    $this->enumValue(
                        $seat->status
                    );

                if (
                    $seatStatus
                    === SeatStatus::Held->value
                    && $seat->hold_expires_at
                    && Carbon::parse(
                        $seat->hold_expires_at
                    )->isPast()
                ) {
                    Booking::query()
                        ->where(
                            'trip_id',
                            $lockedTrip->id
                        )
                        ->where(
                            'seat_number',
                            $seatNumber
                        )
                        ->whereIn(
                            'status',
                            [
                                BookingStatus::Held->value,
                                BookingStatus::PendingPayment->value,
                            ]
                        )
                        ->update([
                            'status' => BookingStatus::Expired->value,

                            'payment_status' => PaymentStatus::Failed->value,

                            'cancel_reason' => 'انتهت مهلة حجز المقعد.',

                            'cancelled_at' => now(),

                            'updated_at' => now(),
                        ]);

                    $seat->status =
                        SeatStatus::Available;

                    $seat->held_by_user_id =
                        null;

                    $seat->hold_expires_at =
                        null;

                    $seat->save();

                    $seatStatus =
                        SeatStatus::Available->value;
                }

                /*
                |--------------------------------------------------------------------------
                | Seat Must Be Available
                |--------------------------------------------------------------------------
                */

                if (
                    $seatStatus
                    !== SeatStatus::Available->value
                ) {
                    throw ValidationException::withMessages([
                        'seat_number' => 'هذا المقعد غير متاح حالياً.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Prevent Duplicate Active Booking
                |--------------------------------------------------------------------------
                */

                $existingSeatBooking =
                    Booking::query()
                        ->where(
                            'trip_id',
                            $lockedTrip->id
                        )
                        ->where(
                            'seat_number',
                            $seatNumber
                        )
                        ->whereIn(
                            'status',
                            [
                                BookingStatus::Held->value,
                                BookingStatus::PendingPayment->value,
                                BookingStatus::Confirmed->value,
                            ]
                        )
                        ->lockForUpdate()
                        ->first();

                if (
                    $existingSeatBooking
                ) {
                    throw ValidationException::withMessages([
                        'seat_number' => 'تم حجز هذا المقعد بالفعل.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Female Only Seat
                |--------------------------------------------------------------------------
                */

                $seatType =
                    $this->enumValue(
                        $seat->seat_type
                    );

                if (
                    $seatType
                    === SeatType::Female->value
                    && $genderValue
                    !== PassengerGender::Female->value
                ) {
                    throw ValidationException::withMessages([
                        'seat_number' => 'هذا المقعد مخصص للنساء فقط.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Male Beside Female
                |--------------------------------------------------------------------------
                */

                if (
                    $genderValue
                    === PassengerGender::Male->value
                    && $seat->adjacent_seat_number
                    !== null
                ) {
                    $adjacentBooking =
                        Booking::query()
                            ->where(
                                'trip_id',
                                $lockedTrip->id
                            )
                            ->where(
                                'seat_number',
                                $seat->adjacent_seat_number
                            )
                            ->where(
                                'passenger_gender',
                                PassengerGender::Female->value
                            )
                            ->whereIn(
                                'status',
                                [
                                    BookingStatus::Held->value,
                                    BookingStatus::PendingPayment->value,
                                    BookingStatus::Confirmed->value,
                                ]
                            )
                            ->lockForUpdate()
                            ->first();

                    if (
                        $adjacentBooking
                    ) {
                        $familyRelation =
                            isset(
                                $data['family_relation']
                            )
                                ? (string) $data['family_relation']
                                : null;

                        /*
                         * نفس القيم الموجودة فعلياً
                         * في HoldSeatRequest.
                         */
                        $allowedRelations = [
                            'husband',
                            'brother',
                            'father',
                            'son',
                            'paternal_uncle',
                            'maternal_uncle',
                        ];

                        if (
                            ! $familyRelation
                            || ! in_array(
                                $familyRelation,
                                $allowedRelations,
                                true
                            )
                        ) {
                            throw ValidationException::withMessages([
                                'seat_number' => 'لا يمكن اختيار مقعد بجانب راكبة إلا مع صلة قرابة مسموحة.',
                            ]);
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Maximum Two Seats Per Trip
                |--------------------------------------------------------------------------
                */

                $activeSeatsForUser =
                    Booking::query()
                        ->where(
                            'trip_id',
                            $lockedTrip->id
                        )
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->whereIn(
                            'status',
                            [
                                BookingStatus::Held->value,
                                BookingStatus::PendingPayment->value,
                                BookingStatus::Confirmed->value,
                            ]
                        )
                        ->lockForUpdate()
                        ->count();

                if (
                    $activeSeatsForUser >= 2
                ) {
                    throw ValidationException::withMessages([
                        'booking' => 'لا يمكنك حجز أكثر من مقعدين في نفس الرحلة.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Maximum Five Trips Per Day
                |--------------------------------------------------------------------------
                */

                $departureDate =
                    Carbon::parse(
                        $lockedTrip->departure_at
                    )->toDateString();

                $tripsForDay =
                    Booking::query()
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->whereIn(
                            'status',
                            [
                                BookingStatus::Held->value,
                                BookingStatus::PendingPayment->value,
                                BookingStatus::Confirmed->value,
                            ]
                        )
                        ->whereHas(
                            'trip',
                            function (
                                $query
                            ) use (
                                $departureDate
                            ): void {
                                $query->whereDate(
                                    'departure_at',
                                    $departureDate
                                );
                            }
                        )
                        ->distinct()
                        ->count(
                            'trip_id'
                        );

                if (
                    $tripsForDay >= 5
                ) {
                    throw ValidationException::withMessages([
                        'booking' => 'لا يمكنك حجز أكثر من خمس رحلات في اليوم الواحد.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Create Booking
                |--------------------------------------------------------------------------
                */

                $booking =
                    Booking::query()
                        ->create([
                            'trip_id' => $lockedTrip->id,

                            'user_id' => $user->id,

                            'booking_code' => $this->generateBookingCode(),

                            'seat_number' => $seatNumber,

                            'passenger_name' => $user->name,

                            'passenger_gender' => $genderValue,

                            'price' => $lockedTrip->price,

                            'commission' => 0,

                            'paid_amount' => 0,

                            'payment_status' => PaymentStatus::Unpaid,

                            'status' => BookingStatus::Held,

                            'idempotency_key' => $idempotencyKey,
                        ]);

                /*
                |--------------------------------------------------------------------------
                | Hold Seat For 15 Minutes
                |--------------------------------------------------------------------------
                */

                $seat->status =
                    SeatStatus::Held;

                $seat->held_by_user_id =
                    $user->id;

                $seat->hold_expires_at =
                    now()->addMinutes(15);

                $seat->save();

                return $booking->refresh();
            },
            3
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel Unpaid Booking
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Booking $booking,
        User $user,
        ?string $reason = null
    ): Booking {
        return DB::transaction(
            function () use (
                $booking,
                $user,
                $reason
            ): Booking {
                /*
                |--------------------------------------------------------------------------
                | Lock Booking
                |--------------------------------------------------------------------------
                */

                $lockedBooking =
                    Booking::query()
                        ->whereKey(
                            $booking->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Ownership
                |--------------------------------------------------------------------------
                */

                if (
                    (string) $lockedBooking->user_id
                    !== (string) $user->id
                ) {
                    abort(403);
                }

                $bookingStatus =
                    $this->enumValue(
                        $lockedBooking->status
                    );

                /*
                |--------------------------------------------------------------------------
                | Idempotent Cancellation
                |--------------------------------------------------------------------------
                */

                if (
                    $bookingStatus
                    === BookingStatus::Cancelled->value
                ) {
                    return $lockedBooking;
                }

                /*
                |--------------------------------------------------------------------------
                | Expired Booking
                |--------------------------------------------------------------------------
                */

                if (
                    $bookingStatus
                    === BookingStatus::Expired->value
                ) {
                    throw ValidationException::withMessages([
                        'booking' => 'هذا الحجز منتهي بالفعل.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Confirmed Booking
                |--------------------------------------------------------------------------
                */

                if (
                    $bookingStatus
                    === BookingStatus::Confirmed->value
                ) {
                    throw ValidationException::withMessages([
                        'booking' => 'الحجز مؤكد ومدفوع ويحتاج إلى طلب استرداد مالي.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Allowed Statuses
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $bookingStatus,
                        [
                            BookingStatus::Held->value,
                            BookingStatus::PendingPayment->value,
                        ],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'booking' => 'لا يمكن إلغاء هذا الحجز في حالته الحالية.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Lock Trip
                |--------------------------------------------------------------------------
                */

                $trip =
                    Trip::query()
                        ->whereKey(
                            $lockedBooking->trip_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    Carbon::parse(
                        $trip->departure_at
                    )->isPast()
                ) {
                    throw ValidationException::withMessages([
                        'booking' => 'لا يمكن إلغاء الحجز بعد موعد انطلاق الرحلة.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Lock Seat
                |--------------------------------------------------------------------------
                */

                $seat =
                    Seat::query()
                        ->where(
                            'trip_id',
                            $lockedBooking->trip_id
                        )
                        ->where(
                            'seat_number',
                            $lockedBooking->seat_number
                        )
                        ->lockForUpdate()
                        ->first();

                /*
                |--------------------------------------------------------------------------
                | Lock Payment
                |--------------------------------------------------------------------------
                */

                $payment =
                    Payment::query()
                        ->where(
                            'booking_id',
                            $lockedBooking->id
                        )
                        ->lockForUpdate()
                        ->first();

                /*
                |--------------------------------------------------------------------------
                | Reject Pending Payment
                |--------------------------------------------------------------------------
                */

                if ($payment) {
                    $paymentReviewStatus =
                        $this->enumValue(
                            $payment->status
                        );

                    if (
                        $paymentReviewStatus
                        === PaymentReviewStatus::Pending->value
                    ) {
                        $payment->status =
                            PaymentReviewStatus::Rejected;

                        $payment->rejection_reason =
                            'ألغى الراكب الحجز قبل اعتماد الدفع.';

                        $payment->reviewed_by =
                            null;

                        $payment->reviewed_at =
                            now();

                        $payment->save();
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Release Seat
                |--------------------------------------------------------------------------
                */

                if ($seat) {
                    $seatStatus =
                        $this->enumValue(
                            $seat->status
                        );

                    if (
                        $seatStatus
                        === SeatStatus::Held->value
                    ) {
                        $seat->status =
                            SeatStatus::Available;

                        $seat->held_by_user_id =
                            null;

                        $seat->hold_expires_at =
                            null;

                        $seat->save();
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Booking Payment Status
                |--------------------------------------------------------------------------
                */

                $bookingPaymentStatus =
                    $this->enumValue(
                        $lockedBooking->payment_status
                    );

                if (
                    $bookingPaymentStatus
                    === PaymentStatus::Pending->value
                ) {
                    $lockedBooking->payment_status =
                        PaymentStatus::Failed;
                }

                /*
                |--------------------------------------------------------------------------
                | Cancel Booking
                |--------------------------------------------------------------------------
                */

                $lockedBooking->status =
                    BookingStatus::Cancelled;

                $lockedBooking->cancel_reason =
                    filled($reason)
                        ? trim($reason)
                        : 'ألغى الراكب الحجز.';

                $lockedBooking->cancelled_at =
                    now();

                $lockedBooking->save();

                return $lockedBooking->refresh();
            },
            3
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Booking Code
    |--------------------------------------------------------------------------
    */

    private function generateBookingCode(): string
    {
        do {
            $code =
                'SHF-'.
                Str::upper(
                    Str::random(10)
                );
        } while (
            Booking::query()
                ->where(
                    'booking_code',
                    $code
                )
                ->exists()
        );

        return $code;
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize Enum / String
    |--------------------------------------------------------------------------
    */

    private function enumValue(
        mixed $value
    ): string {
        if (
            $value instanceof \BackedEnum
        ) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}
