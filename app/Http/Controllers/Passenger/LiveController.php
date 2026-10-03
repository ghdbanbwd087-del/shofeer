<?php

namespace App\Http\Controllers\Passenger;

use App\Enums\BookingStatus;
use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LiveController extends Controller
{
    public function index(
        Request $request,
        LocationService $locationService
    ): View {
        $bookings = $this->activeBookings($request);

        $selectedBooking =
            $this->selectedBooking(
                request: $request,
                bookings: $bookings,
            );

        $latestLocation =
            $selectedBooking !== null
                ? $locationService->latestForTrip($selectedBooking->trip)
                : null;

        return view(
            'passenger.live.index',
            [
                'bookings' => $bookings,
                'selectedBooking' => $selectedBooking,
                'latestLocation' => $latestLocation,
                'locationIsFresh' => $latestLocation?->isFresh() ?? false,
            ]
        );
    }

    public function data(
        Request $request,
        LocationService $locationService
    ): JsonResponse {
        $bookings = $this->activeBookings($request);

        $selectedBooking =
            $this->selectedBooking(
                request: $request,
                bookings: $bookings,
            );

        if ($selectedBooking === null) {
            return response()->json([
                'available' => false,
                'message' => 'لا توجد رحلة جارية متاحة للتتبع.',
            ]);
        }

        $location =
            $locationService->latestForTrip(
                $selectedBooking->trip
            );

        if ($location === null) {
            return response()->json([
                'available' => false,
                'booking_id' => $selectedBooking->id,
                'trip_id' => $selectedBooking->trip_id,
                'message' => 'لم يصل موقع من السائق بعد.',
            ]);
        }

        return response()->json([
            'available' => true,
            'booking_id' => $selectedBooking->id,
            'trip_id' => $selectedBooking->trip_id,
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'accuracy_m' =>
                $location->accuracy_m !== null
                    ? (float) $location->accuracy_m
                    : null,
            'speed_kmh' =>
                $location->speed_kmh !== null
                    ? (float) $location->speed_kmh
                    : null,
            'heading' => $location->heading,
            'eta_at' => $location->eta_at?->toIso8601String(),
            'recorded_at' => $location->recorded_at?->toIso8601String(),
            'fresh' => $location->isFresh(),
        ]);
    }

    private function activeBookings(Request $request): Collection
    {
        return Booking::query()
            ->where('user_id', $request->user()->id)
            ->where('status', BookingStatus::Confirmed->value)
            ->whereHas(
                'trip',
                fn ($query) =>
                    $query->whereIn(
                        'status',
                        [
                            TripStatus::Boarding->value,
                            TripStatus::InProgress->value,
                        ]
                    )
            )
            ->with([
                'trip.driver.user',
            ])
            ->latest()
            ->get();
    }

    private function selectedBooking(
        Request $request,
        Collection $bookings
    ): ?Booking {
        if ($bookings->isEmpty()) {
            return null;
        }

        $requestedId =
            $request->string('booking')->toString();

        if ($requestedId === '') {
            return $bookings->first();
        }

        return $bookings->first(
            fn (Booking $booking): bool =>
                (string) $booking->id === $requestedId
        ) ?? $bookings->first();
    }
}
