<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PassengerGender;
use App\Enums\SeatStatus;
use App\Enums\SeatType;
use App\Http\Requests\Booking\HoldSeatRequest;
use App\Models\Booking;
use App\Models\Seat;
use App\Models\Trip;
use App\Services\BookingService;
use App\Services\SeatLayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SeatController extends Controller
{
    /**
     * عرض مخطط المقاعد.
     */
    public function show(
        Request $request,
        Trip $trip,
        SeatLayoutService $seatLayoutService
    ): View {
        /*
         * لا نسمح بالحجز على رحلة غير منشورة
         * أو غير قابلة للحجز.
         */
        abort_unless(
            $trip->canAcceptBookings(),
            404
        );

        $trip->load([
            'fromCity',
            'toCity',
            'car',
            'driver.user',
        ]);

        /*
         * ضمان وجود مخطط المقاعد.
         */
        $seatLayoutService
            ->ensureSeatsForTrip($trip);

        /*
         * تنظيف أي Holds انتهت مدتها.
         */
        $seatLayoutService
            ->releaseExpiredHolds($trip);

        $seats = $trip
            ->seats()
            ->orderBy('seat_number')
            ->get();

        /*
         * الجنس المحدد من واجهة المستخدم.
         */
        $gender =
            PassengerGender::tryFrom(
                $request
                    ->string('gender')
                    ->toString()
            );

        /*
         * الحجوزات الفعالة مهمة لمعرفة
         * جنس الشخص في المقعد المجاور.
         */
        $activeBookings =
            Booking::query()
                ->where(
                    'trip_id',
                    $trip->id
                )
                ->whereIn(
                    'status',
                    [
                        BookingStatus::Held
                            ->value,

                        BookingStatus::PendingPayment
                            ->value,

                        BookingStatus::Confirmed
                            ->value,
                    ]
                )
                ->get([
                    'id',
                    'user_id',
                    'trip_id',
                    'seat_number',
                    'passenger_gender',
                    'status',
                ]);

        /*
         * المقاعد التي توجد بجانب راكبة.
         */
        $femaleOccupiedSeatNumbers =
            $activeBookings
                ->filter(
                    fn (Booking $booking): bool => $booking
                        ->passenger_gender ===
                        PassengerGender::Female
                )
                ->pluck('seat_number')
                ->map(
                    fn ($number): int => (int) $number
                )
                ->values();

        $adjacentFemaleSeatNumbers =
            $seats
                ->filter(
                    function (
                        Seat $seat
                    ) use (
                        $femaleOccupiedSeatNumbers
                    ): bool {
                        if (
                            $seat
                                ->adjacent_seat_number ===
                            null
                        ) {
                            return false;
                        }

                        return $femaleOccupiedSeatNumbers
                            ->contains(
                                $seat
                                    ->adjacent_seat_number
                            );
                    }
                )
                ->pluck('seat_number')
                ->map(
                    fn ($number): int => (int) $number
                )
                ->values()
                ->all();

        /*
         * الاقتراحات الثلاثة.
         */
        $suggestedSeatNumbers =
            $this->suggestSeats(
                $seats,
                $gender,
                $adjacentFemaleSeatNumbers
            );

        /*
         * حجوزات المستخدم المؤقتة الحالية
         * لهذه الرحلة.
         */
        $currentHoldBookings =
            Booking::query()
                ->where(
                    'trip_id',
                    $trip->id
                )
                ->where(
                    'user_id',
                    $request->user()->id
                )
                ->where(
                    'status',
                    BookingStatus::Held->value
                )
                ->latest()
                ->get();

        $availableSeatsCount =
            $seats
                ->where(
                    'status',
                    SeatStatus::Available
                )
                ->count();

        return view(
            'trips.seats',
            compact(
                'trip',
                'seats',
                'gender',
                'suggestedSeatNumbers',
                'adjacentFemaleSeatNumbers',
                'currentHoldBookings',
                'availableSeatsCount'
            )
        );
    }

    /**
     * حجز المقعد مؤقتاً.
     */
    public function store(
        HoldSeatRequest $request,
        Trip $trip,
        BookingService $bookingService
    ): RedirectResponse {
        $booking =
            $bookingService->holdSeat(
                $trip,
                $request->user(),
                $request->validated()
            );

        return redirect()
            ->route(
                'booking.pay',
                $booking
            )
            ->with(
                'success',
                'تم حجز المقعد لمدة 15 دقيقة.'
            );
    }

    /**
     * اقتراح مقاعد مناسبة.
     *
     * @return array<int>
     */
    private function suggestSeats(
        Collection $seats,
        ?PassengerGender $gender,
        array $adjacentFemaleSeatNumbers
    ): array {
        if ($gender === null) {
            return [];
        }

        /*
         * المقاعد المتاحة فقط.
         */
        $available =
            $seats->filter(
                fn (Seat $seat): bool => $seat->status ===
                    SeatStatus::Available
            );

        /*
        |--------------------------------------------------------------------------
        | Female Suggestions
        |--------------------------------------------------------------------------
        |
        | نحاول توفير:
        |
        | 1. مقعد في منطقة النساء.
        | 2. مقعد بجانب امرأة.
        | 3. مقعد خلفي.
        |
        */

        if (
            $gender ===
            PassengerGender::Female
        ) {
            $suggestions = [];

            $femaleZone =
                $available->first(
                    fn (Seat $seat): bool => $seat->seat_type ===
                        SeatType::Female
                );

            if ($femaleZone !== null) {
                $suggestions[] =
                    $femaleZone->seat_number;
            }

            $nextToFemale =
                $available->first(
                    fn (Seat $seat): bool => in_array(
                        $seat->seat_number,
                        $adjacentFemaleSeatNumbers,
                        true
                    )
                );

            if ($nextToFemale !== null) {
                $suggestions[] =
                    $nextToFemale
                        ->seat_number;
            }

            /*
             * المقعد الخلفي:
             * أعلى رقم مقعد متاح.
             */
            $rearSeat =
                $available
                    ->sortByDesc(
                        'seat_number'
                    )
                    ->first();

            if ($rearSeat !== null) {
                $suggestions[] =
                    $rearSeat->seat_number;
            }

            /*
             * إذا كانت الاقتراحات السابقة
             * أقل من 3 نملأها بمقاعد متاحة.
             */
            foreach (
                $available as $seat
            ) {
                $suggestions[] =
                    $seat->seat_number;

                $suggestions =
                    array_values(
                        array_unique(
                            $suggestions
                        )
                    );

                if (
                    count($suggestions)
                    >= 3
                ) {
                    break;
                }
            }

            return array_slice(
                array_values(
                    array_unique(
                        $suggestions
                    )
                ),
                0,
                3
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Male Suggestions
        |--------------------------------------------------------------------------
        |
        | للرجل نقترح فقط:
        |
        | - مقعد عادي.
        | - متاح.
        | - ليس بجانب راكبة.
        |
        */

        return $available
            ->filter(
                fn (Seat $seat): bool => $seat->seat_type ===
                        SeatType::Standard
                    && ! in_array(
                        $seat->seat_number,
                        $adjacentFemaleSeatNumbers,
                        true
                    )
            )
            ->take(3)
            ->pluck('seat_number')
            ->map(
                fn ($number): int => (int) $number
            )
            ->values()
            ->all();
    }
}
