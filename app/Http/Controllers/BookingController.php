<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentReviewStatus;
use App\Enums\SeatStatus;
use App\Http\Requests\Booking\SubmitPaymentRequest;
use App\Models\Booking;
use App\Models\Seat;
use App\Services\PaymentService;
use App\Services\SeatLayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * صفحة الدفع.
     */
    public function pay(
        Booking $booking,
        SeatLayoutService $seatLayoutService
    ): View|RedirectResponse {
        $this->authorizeOwner(
            $booking
        );

        $booking->load([
            'trip.fromCity',
            'trip.toCity',
            'trip.car',
            'trip.driver.user',
            'payment',
        ]);

        $seatLayoutService
            ->releaseExpiredHolds(
                $booking->trip
            );

        $booking->refresh();

        $booking->load([
            'trip.fromCity',
            'trip.toCity',
            'trip.car',
            'trip.driver.user',
            'payment',
        ]);

        if (
            $booking->status ===
            BookingStatus::Confirmed
        ) {
            return redirect()
                ->route(
                    'booking.success',
                    $booking
                );
        }

        if (
            $booking->status ===
            BookingStatus::PendingPayment
        ) {
            return redirect()
                ->route(
                    'booking.pending',
                    $booking
                );
        }

        if (
            in_array(
                $booking->status,
                [
                    BookingStatus::Expired,
                    BookingStatus::Cancelled,
                ],
                true
            )
        ) {
            return redirect()
                ->route(
                    'booking.failed',
                    $booking
                );
        }

        if (
            $booking->status !==
            BookingStatus::Held
        ) {
            return redirect()
                ->route(
                    'trips.show',
                    $booking->trip_id
                );
        }

        $seat = Seat::query()
            ->where(
                'trip_id',
                $booking->trip_id
            )
            ->where(
                'seat_number',
                $booking->seat_number
            )
            ->first();

        if (
            $seat === null
            || $seat->status !==
                SeatStatus::Held
            || $seat->held_by_user_id !==
                request()->user()->id
            || $seat->hold_expires_at ===
                null
        ) {
            return redirect()
                ->route(
                    'trips.seats.show',
                    [
                        'trip' => $booking->trip_id,

                        'gender' => $booking
                            ->passenger_gender
                            ->value,
                    ]
                )
                ->withErrors([
                    'booking' => 'المقعد لم يعد محجوزاً لك.',
                ]);
        }

        $paymentMethods =
            config(
                'payments.methods',
                []
            );

        return view(
            'bookings.pay',
            compact(
                'booking',
                'seat',
                'paymentMethods'
            )
        );
    }

    /**
     * إرسال إثبات الدفع.
     */
    public function submitPayment(
        SubmitPaymentRequest $request,
        Booking $booking,
        PaymentService $paymentService
    ): RedirectResponse {
        $paymentService->submit(
            $booking,
            $request->user(),
            $request->validated(),
            $request->file(
                'payment_proof'
            )
        );

        return redirect()
            ->route(
                'booking.pending',
                $booking
            )
            ->with(
                'success',
                'تم إرسال الدفع للمراجعة.'
            );
    }

    /**
     * انتظار تحقق الإدارة.
     */
    public function pending(
        Booking $booking,
        SeatLayoutService $seatLayoutService
    ): View|RedirectResponse {
        $this->authorizeOwner(
            $booking
        );

        $booking->load([
            'trip.fromCity',
            'trip.toCity',
            'trip.car',
            'trip.driver.user',
            'payment',
        ]);

        $seatLayoutService
            ->releaseExpiredHolds(
                $booking->trip
            );

        $booking->refresh();

        $booking->load([
            'trip.fromCity',
            'trip.toCity',
            'trip.car',
            'trip.driver.user',
            'payment',
        ]);

        if (
            $booking->status ===
            BookingStatus::Confirmed
        ) {
            return redirect()
                ->route(
                    'booking.success',
                    $booking
                );
        }

        if (
            $booking->payment !== null
            && $booking
                ->payment
                ->status ===
                PaymentReviewStatus::Rejected
        ) {
            return redirect()
                ->route(
                    'booking.failed',
                    $booking
                );
        }

        if (
            in_array(
                $booking->status,
                [
                    BookingStatus::Expired,
                    BookingStatus::Cancelled,
                ],
                true
            )
        ) {
            return redirect()
                ->route(
                    'booking.failed',
                    $booking
                );
        }

        if (
            $booking->payment === null
        ) {
            return redirect()
                ->route(
                    'booking.pay',
                    $booking
                );
        }

        return view(
            'bookings.pending',
            compact('booking')
        );
    }

    /**
     * نجاح الحجز.
     */
    public function success(
        Booking $booking
    ): View|RedirectResponse {
        $this->authorizeOwner(
            $booking
        );

        $booking->load([
            'trip.fromCity',
            'trip.toCity',
            'trip.car',
            'trip.driver.user',
            'payment',
        ]);

        if (
            $booking->status !==
            BookingStatus::Confirmed
        ) {
            if (
                $booking->payment !== null
                && $booking
                    ->payment
                    ->status ===
                    PaymentReviewStatus::Rejected
            ) {
                return redirect()
                    ->route(
                        'booking.failed',
                        $booking
                    );
            }

            return redirect()
                ->route(
                    'booking.pending',
                    $booking
                );
        }

        return view(
            'bookings.success',
            compact('booking')
        );
    }

    /**
     * فشل أو رفض الحجز.
     */
    public function failed(
        Booking $booking
    ): View|RedirectResponse {
        $this->authorizeOwner(
            $booking
        );

        $booking->load([
            'trip.fromCity',
            'trip.toCity',
            'trip.car',
            'trip.driver.user',
            'payment',
        ]);

        if (
            $booking->status ===
            BookingStatus::Confirmed
        ) {
            return redirect()
                ->route(
                    'booking.success',
                    $booking
                );
        }

        $isFailed =
            in_array(
                $booking->status,
                [
                    BookingStatus::Expired,
                    BookingStatus::Cancelled,
                ],
                true
            )
            || (
                $booking->payment !== null
                && $booking
                    ->payment
                    ->status ===
                    PaymentReviewStatus::Rejected
            );

        if (! $isFailed) {
            return redirect()
                ->route(
                    'booking.pending',
                    $booking
                );
        }

        $reason =
            $booking->payment
                ?->rejection_reason
            ?? $booking->cancel_reason
            ?? 'تعذر تأكيد الحجز.';

        return view(
            'bookings.failed',
            compact(
                'booking',
                'reason'
            )
        );
    }

    /**
     * المستخدم لا يستطيع فتح حجز
     * لا يملكه.
     */
    private function authorizeOwner(
        Booking $booking
    ): void {
        abort_unless(
            $booking->user_id ===
                request()->user()->id,
            403
        );
    }
}
