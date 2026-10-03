<?php

namespace App\Http\Controllers\Driver;

use App\Enums\BookingStatus;
use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Request $request
    ): View {
        $driver =
            $request
                ->user()
                ->driver()
                ->firstOrFail();

        $tripIds =
            Trip::query()
                ->where(
                    'driver_id',
                    $driver->id
                )
                ->pluck('id');

        $confirmedPassengers =
            Booking::query()
                ->whereIn(
                    'trip_id',
                    $tripIds
                )
                ->where(
                    'status',
                    BookingStatus::Confirmed->value
                )
                ->count();

        $completedTripIds =
            Trip::query()
                ->where(
                    'driver_id',
                    $driver->id
                )
                ->where(
                    'status',
                    TripStatus::Completed->value
                )
                ->pluck('id');

        $netEarnings =
            $this->netForBookings(
                Booking::query()
                    ->whereIn(
                        'trip_id',
                        $completedTripIds
                    )
                    ->where(
                        'status',
                        BookingStatus::Confirmed->value
                    )
                    ->get([
                        'paid_amount',
                        'commission',
                    ])
            );

        $upcomingTrips =
            Trip::query()
                ->where(
                    'driver_id',
                    $driver->id
                )
                ->whereIn(
                    'status',
                    [
                        TripStatus::Scheduled->value,
                        TripStatus::Boarding->value,
                        TripStatus::InProgress->value,
                    ]
                )
                ->orderBy(
                    'departure_at'
                )
                ->limit(5)
                ->get();

        return view(
            'driver.dashboard.index',
            [
                'driver' =>
                    $driver,

                'stats' => [
                    'trips' =>
                        Trip::query()
                            ->where(
                                'driver_id',
                                $driver->id
                            )
                            ->count(),

                    'passengers' =>
                        $confirmedPassengers,

                    'rating' =>
                        (float) $driver->rating,

                    'earnings' =>
                        $netEarnings,
                ],

                'upcomingTrips' =>
                    $upcomingTrips,
            ]
        );
    }

    public function earnings(
        Request $request
    ): View {
        $driver =
            $request
                ->user()
                ->driver()
                ->firstOrFail();

        $trips =
            Trip::query()
                ->where(
                    'driver_id',
                    $driver->id
                )
                ->where(
                    'status',
                    TripStatus::Completed->value
                )
                ->with([
                    'bookings' => function (
                        $query
                    ): void {
                        $query
                            ->where(
                                'status',
                                BookingStatus::Confirmed->value
                            )
                            ->select([
                                'id',
                                'trip_id',
                                'paid_amount',
                                'commission',
                            ]);
                    },
                    'fromCity',
                    'toCity',
                ])
                ->orderByDesc(
                    'departure_at'
                )
                ->paginate(15);

        $trips->getCollection()
            ->transform(
                function (
                    Trip $trip
                ): Trip {
                    $trip->setAttribute(
                        'passengers_count',
                        $trip
                            ->bookings
                            ->count()
                    );

                    $trip->setAttribute(
                        'net_earnings',
                        $this->netForBookings(
                            $trip->bookings
                        )
                    );

                    return $trip;
                }
            );

        $completedTripIds =
            Trip::query()
                ->where(
                    'driver_id',
                    $driver->id
                )
                ->where(
                    'status',
                    TripStatus::Completed->value
                )
                ->pluck('id');

        $totalNet =
            $this->netForBookings(
                Booking::query()
                    ->whereIn(
                        'trip_id',
                        $completedTripIds
                    )
                    ->where(
                        'status',
                        BookingStatus::Confirmed->value
                    )
                    ->get([
                        'paid_amount',
                        'commission',
                    ])
            );

        return view(
            'driver.earnings.index',
            [
                'driver' =>
                    $driver,

                'trips' =>
                    $trips,

                'totalNet' =>
                    $totalNet,
            ]
        );
    }

    private function netForBookings(
        Collection $bookings
    ): float {
        return round(
            (float) $bookings->sum(
                function (
                    $booking
                ): float {
                    $paid =
                        (float) (
                            $booking->paid_amount
                            ?? 0
                        );

                    $commission =
                        (float) (
                            $booking->commission
                            ?? 0
                        );

                    return max(
                        0,
                        $paid - $commission
                    );
                }
            ),
            2
        );
    }
}
