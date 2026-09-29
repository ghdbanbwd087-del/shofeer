<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentReviewStatus;
use App\Enums\PaymentStatus;
use App\Enums\SeatStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaymentService
{
    public function __construct(
        private readonly SeatLayoutService $seatLayoutService
    ) {}

    /**
     * إرسال إثبات الدفع للمراجعة.
     *
     * @param  array<string, mixed>  $data
     */
    public function submit(
        Booking $booking,
        User $user,
        array $data,
        UploadedFile $proof
    ): Payment {
        if (
            ! $user->isPassenger()
            || $booking->user_id !== $user->id
        ) {
            throw ValidationException::withMessages([
                'payment' => 'لا يمكنك إرسال دفع لهذا الحجز.',
            ]);
        }

        $booking->loadMissing('trip');

        /*
         * تحرير أي Hold منتهي قبل الدفع.
         */
        $this->seatLayoutService
            ->releaseExpiredHolds(
                $booking->trip
            );

        $booking->refresh();

        /*
         * إذا كانت العملية أرسلت سابقاً
         * وكانت لا تزال قيد المراجعة
         * نعيد نفس سجل الدفع.
         */
        if (
            $booking->status ===
            BookingStatus::PendingPayment
        ) {
            $existingPayment =
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
                    ->first();

            if ($existingPayment !== null) {
                return $existingPayment;
            }
        }

        if (
            $booking->status !==
            BookingStatus::Held
        ) {
            throw ValidationException::withMessages([
                'payment' => 'الحجز غير متاح لإرسال الدفع.',
            ]);
        }

        /*
         * إثبات الدفع خاص وغير Public.
         */
        $proofPath =
            $proof->store(
                'payments/proofs/'.
                $booking->id,
                'local'
            );

        $oldProofPath = null;

        try {
            $payment = DB::transaction(
                function () use (
                    $booking,
                    $user,
                    $data,
                    $proofPath,
                    &$oldProofPath
                ): Payment {
                    /*
                     * قفل الحجز.
                     */
                    $lockedBooking =
                        Booking::query()
                            ->whereKey(
                                $booking->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    /*
                     * قفل سجل الدفع الموجود
                     * إن وجد.
                     */
                    $existingPayment =
                        Payment::query()
                            ->where(
                                'booking_id',
                                $lockedBooking->id
                            )
                            ->lockForUpdate()
                            ->first();

                    /*
                     * Double Submit:
                     * الطلب وصل مرتين بعد نجاح
                     * الطلب الأول.
                     */
                    if (
                        $lockedBooking->status ===
                            BookingStatus::PendingPayment
                        && $existingPayment !== null
                        && $existingPayment->status ===
                            PaymentReviewStatus::Pending
                    ) {
                        Storage::disk('local')
                            ->delete(
                                $proofPath
                            );

                        return $existingPayment;
                    }

                    if (
                        $lockedBooking->status !==
                        BookingStatus::Held
                    ) {
                        throw ValidationException::withMessages([
                            'payment' => 'الحجز لم يعد في حالة انتظار الدفع.',
                        ]);
                    }

                    /*
                     * قفل المقعد.
                     */
                    $seat = Seat::query()
                        ->where(
                            'trip_id',
                            $lockedBooking->trip_id
                        )
                        ->where(
                            'seat_number',
                            $lockedBooking->seat_number
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        $seat->status !==
                            SeatStatus::Held
                        || $seat->held_by_user_id !==
                            $user->id
                        || $seat->hold_expires_at === null
                        || $seat
                            ->hold_expires_at
                            ->isPast()
                    ) {
                        throw ValidationException::withMessages([
                            'payment' => 'انتهت مهلة حجز المقعد.',
                        ]);
                    }

                    if (
                        $data['passenger_gender'] !==
                        $lockedBooking
                            ->passenger_gender
                            ->value
                    ) {
                        throw ValidationException::withMessages([
                            'passenger_gender' => 'لا يمكن تغيير جنس الراكب بعد اختيار المقعد.',
                        ]);
                    }

                    $method =
                        $data['payment_method'];

                    $destinationAccount =
                        config(
                            'payments.methods.'.
                            $method.
                            '.account_number'
                        );

                    /*
                     * مفتاح تجاري ثابت.
                     */
                    $idempotencyKey =
                        'payment:booking:'.
                        $lockedBooking->id;

                    $expiresAt =
                        now()->addMinutes(
                            (int) config(
                                'payments.verification_minutes',
                                60
                            )
                        );

                    /*
                     * تحديث بيانات الراكب.
                     */
                    $lockedBooking->update([
                        'passenger_name' => $data['passenger_name'],

                        'passenger_phone' => $data['passenger_phone'],

                        'passenger_whatsapp' => ! empty(
                            $data['whatsapp_same']
                            ?? false
                        )
                                ? $data['passenger_phone']
                                : $data[
                                    'passenger_whatsapp'
                                ],

                        'passenger_id' => $data['passenger_id'],

                        'passenger_notes' => $data[
                                'passenger_notes'
                            ] ?? null,

                        'payment_status' => PaymentStatus::Pending,

                        'status' => BookingStatus::PendingPayment,

                        'cancel_reason' => null,

                        'cancelled_at' => null,
                    ]);

                    /*
                     * بعد إرسال الدفع تصبح
                     * مهلة المراجعة 60 دقيقة.
                     */
                    $seat->update([
                        'status' => SeatStatus::Held,

                        'held_by_user_id' => $user->id,

                        'hold_expires_at' => $expiresAt,
                    ]);

                    /*
                     * إذا كانت هناك دفعة سابقة
                     * مرفوضة نعيد استخدام نفس
                     * السجل بدلاً من إنشاء Duplicate.
                     */
                    if ($existingPayment !== null) {
                        if (
                            $existingPayment->status ===
                            PaymentReviewStatus::Approved
                        ) {
                            throw ValidationException::withMessages([
                                'payment' => 'تم اعتماد هذا الدفع مسبقاً.',
                            ]);
                        }

                        $oldProofPath =
                            $existingPayment
                                ->proof_path;

                        $existingPayment->update([
                            'payment_method' => $method,

                            'destination_account' => $destinationAccount,

                            'amount' => $lockedBooking->price,

                            'transaction_number' => $data[
                                    'transaction_number'
                                ],

                            'proof_path' => $proofPath,

                            'status' => PaymentReviewStatus::Pending,

                            'rejection_reason' => null,

                            'idempotency_key' => $idempotencyKey,

                            'submitted_at' => now(),

                            'expires_at' => $expiresAt,

                            'reviewed_by' => null,

                            'reviewed_at' => null,
                        ]);

                        return $existingPayment
                            ->fresh();
                    }

                    return Payment::query()
                        ->create([
                            'booking_id' => $lockedBooking->id,

                            'payment_method' => $method,

                            'destination_account' => $destinationAccount,

                            'amount' => $lockedBooking->price,

                            'transaction_number' => $data[
                                    'transaction_number'
                                ],

                            'proof_path' => $proofPath,

                            'status' => PaymentReviewStatus::Pending,

                            'rejection_reason' => null,

                            'idempotency_key' => $idempotencyKey,

                            'submitted_at' => now(),

                            'expires_at' => $expiresAt,

                            'reviewed_by' => null,

                            'reviewed_at' => null,
                        ]);
                },
                3
            );

            /*
             * نحذف الإثبات القديم فقط بعد
             * نجاح Transaction.
             */
            if (
                $oldProofPath !== null
                && $oldProofPath !==
                    $proofPath
            ) {
                Storage::disk('local')
                    ->delete(
                        $oldProofPath
                    );
            }

            return $payment;
        } catch (Throwable $exception) {
            if (
                Storage::disk('local')
                    ->exists($proofPath)
            ) {
                Storage::disk('local')
                    ->delete($proofPath);
            }

            throw $exception;
        }
    }

    /**
     * اعتماد الدفع نهائياً.
     */
    public function approve(
        Payment $payment,
        User $admin
    ): Payment {
        if (! $admin->isAdmin()) {
            throw ValidationException::withMessages([
                'payment' => 'هذه العملية متاحة للإدارة فقط.',
            ]);
        }

        return DB::transaction(
            function () use (
                $payment,
                $admin
            ): Payment {
                /*
                 * قفل الدفع لمنع اعتماد
                 * نفس العملية مرتين.
                 */
                $lockedPayment =
                    Payment::query()
                        ->whereKey(
                            $payment->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                 * Idempotency:
                 * لو اعتمد سابقاً لا نفعل شيئاً.
                 */
                if (
                    $lockedPayment->status ===
                    PaymentReviewStatus::Approved
                ) {
                    return $lockedPayment;
                }

                if (
                    $lockedPayment->status ===
                    PaymentReviewStatus::Rejected
                ) {
                    throw ValidationException::withMessages([
                        'payment' => 'تم رفض هذه الدفعة مسبقاً.',
                    ]);
                }

                $booking =
                    Booking::query()
                        ->whereKey(
                            $lockedPayment->booking_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                $seat = Seat::query()
                    ->where(
                        'trip_id',
                        $booking->trip_id
                    )
                    ->where(
                        'seat_number',
                        $booking->seat_number
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $trip = Trip::query()
                    ->whereKey(
                        $booking->trip_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * لا نعتمد دفعة انتهت
                 * مهلة مراجعتها.
                 */
                if (
                    $lockedPayment
                        ->expires_at
                        ->isPast()
                ) {
                    $lockedPayment->update([
                        'status' => PaymentReviewStatus::Rejected,

                        'rejection_reason' => 'انتهت مهلة التحقق من الدفع.',

                        'reviewed_by' => $admin->id,

                        'reviewed_at' => now(),
                    ]);

                    $booking->update([
                        'payment_status' => PaymentStatus::Failed,

                        'status' => BookingStatus::Expired,

                        'cancel_reason' => 'انتهت مهلة التحقق من الدفع.',
                    ]);

                    if (
                        $seat->status ===
                        SeatStatus::Held
                    ) {
                        $seat->update([
                            'status' => SeatStatus::Available,

                            'held_by_user_id' => null,

                            'hold_expires_at' => null,
                        ]);
                    }

                    return $lockedPayment
                        ->fresh();
                }

                if (
                    $booking->status !==
                    BookingStatus::PendingPayment
                ) {
                    throw ValidationException::withMessages([
                        'payment' => 'الحجز ليس في حالة انتظار التحقق من الدفع.',
                    ]);
                }

                if (
                    $seat->status !==
                    SeatStatus::Held
                ) {
                    throw ValidationException::withMessages([
                        'payment' => 'المقعد لم يعد محجوزاً مؤقتاً.',
                    ]);
                }

                /*
                 * اعتماد الدفع.
                 */
                $lockedPayment->update([
                    'status' => PaymentReviewStatus::Approved,

                    'rejection_reason' => null,

                    'reviewed_by' => $admin->id,

                    'reviewed_at' => now(),
                ]);

                /*
                 * تأكيد الحجز.
                 */
                $booking->update([
                    'paid_amount' => $lockedPayment->amount,

                    'payment_status' => PaymentStatus::Paid,

                    'status' => BookingStatus::Confirmed,

                    'cancel_reason' => null,

                    'confirmed_at' => now(),

                    'cancelled_at' => null,
                ]);

                /*
                 * المقعد يصبح محجوزاً نهائياً.
                 */
                $seat->update([
                    'status' => SeatStatus::Booked,

                    'held_by_user_id' => null,

                    'hold_expires_at' => null,
                ]);

                /*
                 * لا نستخدم:
                 *
                 * lockForUpdate()->decrement()
                 *
                 * بل نفصل القفل عن التحديث.
                 *
                 * وبما أن الدفع لا ينتقل إلى
                 * Approved إلا مرة واحدة،
                 * لن ينقص العدد مرتين.
                 */
                $trip->update([
                    'available_seats' => max(
                        0,
                        $trip->available_seats
                        - 1
                    ),
                ]);

                return $lockedPayment
                    ->fresh();
            },
            3
        );
    }

    /**
     * رفض الدفع وإعادة المقعد للمتاح.
     */
    public function reject(
        Payment $payment,
        User $admin,
        string $reason
    ): Payment {
        if (! $admin->isAdmin()) {
            throw ValidationException::withMessages([
                'payment' => 'هذه العملية متاحة للإدارة فقط.',
            ]);
        }

        return DB::transaction(
            function () use (
                $payment,
                $admin,
                $reason
            ): Payment {
                $lockedPayment =
                    Payment::query()
                        ->whereKey(
                            $payment->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                 * إعادة نفس الرفض لا تسبب
                 * أي تعديل إضافي.
                 */
                if (
                    $lockedPayment->status ===
                    PaymentReviewStatus::Rejected
                ) {
                    return $lockedPayment;
                }

                if (
                    $lockedPayment->status ===
                    PaymentReviewStatus::Approved
                ) {
                    throw ValidationException::withMessages([
                        'payment' => 'لا يمكن رفض دفعة تم اعتمادها.',
                    ]);
                }

                $booking =
                    Booking::query()
                        ->whereKey(
                            $lockedPayment->booking_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                $seat = Seat::query()
                    ->where(
                        'trip_id',
                        $booking->trip_id
                    )
                    ->where(
                        'seat_number',
                        $booking->seat_number
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedPayment->update([
                    'status' => PaymentReviewStatus::Rejected,

                    'rejection_reason' => $reason,

                    'reviewed_by' => $admin->id,

                    'reviewed_at' => now(),
                ]);

                $booking->update([
                    'paid_amount' => 0,

                    'payment_status' => PaymentStatus::Failed,

                    'status' => BookingStatus::Cancelled,

                    'cancel_reason' => $reason,

                    'confirmed_at' => null,

                    'cancelled_at' => now(),
                ]);

                /*
                 * إعادة المقعد للحجز.
                 */
                if (
                    $seat->status ===
                    SeatStatus::Held
                ) {
                    $seat->update([
                        'status' => SeatStatus::Available,

                        'held_by_user_id' => null,

                        'hold_expires_at' => null,
                    ]);
                }

                return $lockedPayment
                    ->fresh();
            },
            3
        );
    }
}
