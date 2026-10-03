<?php

namespace App\Http\Controllers\Passenger;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Passenger\CancelBookingRequest;
use App\Http\Requests\Passenger\RequestRefundRequest;
use App\Models\Booking;
use App\Models\Refund;
use App\Services\BookingService;
use App\Services\NotificationService;
use App\Services\RefundService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly RefundService $refundService,
        private readonly NotificationService $notificationService
    ) {
        //
    }

    public function index(
        Request $request
    ): View {
        $user =
            $request->user();

        $baseQuery =
            Booking::query()
                ->where(
                    'user_id',
                    $user->id
                );

        $stats = [
            'total_bookings' =>
                (clone $baseQuery)
                    ->count(),

            'upcoming_bookings' =>
                (clone $baseQuery)
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
                            Builder $query
                        ): void {
                            $query->where(
                                'departure_at',
                                '>=',
                                now()
                            );
                        }
                    )
                    ->count(),

            'pending_payments' =>
                (clone $baseQuery)
                    ->where(
                        'status',
                        BookingStatus::PendingPayment->value
                    )
                    ->count(),

            'completed_trips' =>
                (clone $baseQuery)
                    ->where(
                        'status',
                        BookingStatus::Confirmed->value
                    )
                    ->whereHas(
                        'trip',
                        function (
                            Builder $query
                        ): void {
                            $query->where(
                                'departure_at',
                                '<',
                                now()
                            );
                        }
                    )
                    ->count(),

            'total_paid' =>
                (float) (
                    (clone $baseQuery)
                        ->sum(
                            'paid_amount'
                        )
                ),
        ];

        $upcomingBookings =
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
                        Builder $query
                    ): void {
                        $query->where(
                            'departure_at',
                            '>=',
                            now()
                        );
                    }
                )
                ->with([
                    'trip',
                ])
                ->latest()
                ->limit(5)
                ->get();

        $latestNotifications =
            $this
                ->notificationService
                ->latestForUser(
                    user: $user,
                    limit: 5
                );

        $unreadNotificationsCount =
            $this
                ->notificationService
                ->unreadCount(
                    $user
                );

        return view(
            'passenger.dashboard.index',
            [
                'user' =>
                    $user,

                'stats' =>
                    $stats,

                'upcomingBookings' =>
                    $upcomingBookings,

                'latestNotifications' =>
                    $latestNotifications,

                'unreadNotificationsCount' =>
                    $unreadNotificationsCount,
            ]
        );
    }

    public function bookings(
        Request $request
    ): View {
        $user =
            $request->user();

        $tab =
            $request
                ->string('tab')
                ->toString();

        if (
            ! in_array(
                $tab,
                [
                    'upcoming',
                    'past',
                    'cancelled',
                ],
                true
            )
        ) {
            $tab =
                'upcoming';
        }

        $query =
            Booking::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->with([
                    'trip',
                ]);

        if (
            $tab === 'upcoming'
        ) {
            $query
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
                        Builder $tripQuery
                    ): void {
                        $tripQuery->where(
                            'departure_at',
                            '>=',
                            now()
                        );
                    }
                );
        }

        if (
            $tab === 'past'
        ) {
            $query
                ->where(
                    'status',
                    BookingStatus::Confirmed->value
                )
                ->whereHas(
                    'trip',
                    function (
                        Builder $tripQuery
                    ): void {
                        $tripQuery->where(
                            'departure_at',
                            '<',
                            now()
                        );
                    }
                );
        }

        if (
            $tab === 'cancelled'
        ) {
            $query->whereIn(
                'status',
                [
                    BookingStatus::Cancelled->value,
                    BookingStatus::Expired->value,
                ]
            );
        }

        $bookings =
            $query
                ->latest()
                ->paginate(10)
                ->withQueryString();

        return view(
            'passenger.bookings.index',
            [
                'user' =>
                    $user,

                'bookings' =>
                    $bookings,

                'tab' =>
                    $tab,
            ]
        );
    }

    public function show(
        Request $request,
        Booking $booking
    ): View {
        Gate::authorize(
            'view',
            $booking
        );

        $booking->load([
            'trip',
            'payment',
        ]);

        $latestRefund =
            Refund::query()
                ->where(
                    'booking_id',
                    $booking->id
                )
                ->latest()
                ->first();

        return view(
            'passenger.bookings.show',
            [
                'user' =>
                    $request->user(),

                'booking' =>
                    $booking,

                'latestRefund' =>
                    $latestRefund,
            ]
        );
    }

    public function cancel(
        CancelBookingRequest $request,
        Booking $booking
    ): RedirectResponse {
        $booking =
            $this
                ->bookingService
                ->cancel(
                    booking: $booking,
                    user: $request->user(),
                    reason: $request->validated(
                        'cancel_reason'
                    )
                );

        return redirect()
            ->route(
                'dashboard.bookings.show',
                $booking
            )
            ->with(
                'success',
                'تم إلغاء الحجز وتحرير المقعد بنجاح.'
            );
    }

    public function requestRefund(
        RequestRefundRequest $request,
        Booking $booking
    ): RedirectResponse {
        $refund =
            $this
                ->refundService
                ->request(
                    booking: $booking,
                    user: $request->user(),
                    reason: $request->validated(
                        'reason'
                    )
                );

        $message =
            $refund->wasRecentlyCreated
                ? 'تم إرسال طلب الاسترداد للإدارة بنجاح.'
                : 'طلب الاسترداد مسجل بالفعل.';

        return redirect()
            ->route(
                'dashboard.bookings.show',
                $booking
            )
            ->with(
                'success',
                $message
            );
    }
}
