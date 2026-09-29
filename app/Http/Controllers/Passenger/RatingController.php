<?php

namespace App\Http\Controllers\Passenger;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rating\StoreRatingRequest;
use App\Http\Requests\Rating\UpdateRatingRequest;
use App\Models\Booking;
use App\Models\Rating;
use App\Services\RatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RatingController extends Controller
{
    public function index(
        Request $request
    ): View {
        $user =
            $request->user();

        $ratings =
            Rating::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->with([
                    'booking',
                    'trip',
                    'driver.user',
                ])
                ->latest()
                ->paginate(10);

        $ratedBookingIds =
            Rating::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->pluck(
                    'booking_id'
                );

        $eligibleBookings =
            Booking::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'status',
                    BookingStatus::Confirmed->value
                )
                ->whereNotIn(
                    'id',
                    $ratedBookingIds
                )
                ->whereHas(
                    'trip',
                    fn ($query) =>
                        $query->where(
                            'departure_at',
                            '<',
                            now()
                        )
                )
                ->with([
                    'trip.driver.user',
                ])
                ->latest()
                ->limit(10)
                ->get();

        $averageScore =
            Rating::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->avg(
                    'score'
                );

        return view(
            'passenger.ratings.index',
            [
                'ratings' => $ratings,
                'eligibleBookings' =>
                    $eligibleBookings,
                'averageScore' =>
                    $averageScore === null
                        ? null
                        : round(
                            (float) $averageScore,
                            2
                        ),
            ]
        );
    }

    public function store(
        StoreRatingRequest $request,
        Booking $booking,
        RatingService $ratingService
    ): RedirectResponse {
        $rating =
            $ratingService->createForBooking(
                booking: $booking,
                user: $request->user(),
                data: $request->validated(),
            );

        return redirect()
            ->route(
                'dashboard.ratings.index'
            )
            ->with(
                'success',
                $rating->wasRecentlyCreated
                    ? 'تم حفظ تقييمك بنجاح.'
                    : 'هذا الحجز مُقيّم مسبقًا.'
            );
    }

    public function update(
        UpdateRatingRequest $request,
        Rating $rating,
        RatingService $ratingService
    ): RedirectResponse {
        Gate::authorize(
            'update',
            $rating
        );

        $ratingService->update(
            rating: $rating,
            user: $request->user(),
            data: $request->validated(),
        );

        return redirect()
            ->route(
                'dashboard.ratings.index'
            )
            ->with(
                'success',
                'تم تحديث تقييمك بنجاح.'
            );
    }
}
