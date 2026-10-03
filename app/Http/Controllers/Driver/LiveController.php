<?php

namespace App\Http\Controllers\Driver;

use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\StoreDriverLocationRequest;
use App\Jobs\SyncDriverLocationJob;
use App\Models\DriverLocation;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LiveController extends Controller
{
    public function index(
        Request $request
    ): View {
        $driver =
            $request
                ->user()
                ->driver()
                ->firstOrFail();

        $trips =
            $this->activeTrips(
                $driver->id
            );

        $selectedTrip =
            $this->selectedTrip(
                request: $request,
                trips: $trips,
            );

        $latestLocation =
            $selectedTrip !== null
                ? DriverLocation::query()
                    ->where(
                        'driver_id',
                        $driver->id
                    )
                    ->where(
                        'trip_id',
                        $selectedTrip->id
                    )
                    ->latest(
                        'recorded_at'
                    )
                    ->first()
                : null;

        return view(
            'driver.live.index',
            [
                'driver' =>
                    $driver,

                'trips' =>
                    $trips,

                'selectedTrip' =>
                    $selectedTrip,

                'latestLocation' =>
                    $latestLocation,
            ]
        );
    }

    public function store(
        StoreDriverLocationRequest $request
    ): JsonResponse {
        $driver =
            $request
                ->user()
                ->driver()
                ->firstOrFail();

        $trip =
            Trip::query()
                ->whereKey(
                    $request->validated(
                        'trip_id'
                    )
                )
                ->where(
                    'driver_id',
                    $driver->id
                )
                ->whereIn(
                    'status',
                    [
                        TripStatus::Boarding->value,
                        TripStatus::InProgress->value,
                    ]
                )
                ->first();

        if ($trip === null) {
            return response()->json(
                [
                    'message' =>
                        'لا يمكنك إرسال موقع إلا لرحلة جارية تخصك.',
                    'errors' => [
                        'trip_id' => [
                            'الرحلة غير متاحة للتتبع من هذا الحساب.',
                        ],
                    ],
                ],
                422
            );
        }

        /*
         * dispatchSync keeps the realtime endpoint immediate while
         * preserving the dedicated job architecture required by SHOFEER.
         */
        SyncDriverLocationJob::dispatchSync(
            driver: $driver,
            trip: $trip,
            data: $request->validated(),
        );

        $location =
            DriverLocation::query()
                ->where(
                    'driver_id',
                    $driver->id
                )
                ->where(
                    'trip_id',
                    $trip->id
                )
                ->latest(
                    'recorded_at'
                )
                ->firstOrFail();

        return response()->json(
            [
                'message' =>
                    'تم تحديث موقعك.',

                'location' => [
                    'latitude' =>
                        (float) $location->latitude,

                    'longitude' =>
                        (float) $location->longitude,

                    'accuracy_m' =>
                        $location->accuracy_m !== null
                            ? (float) $location->accuracy_m
                            : null,

                    'speed_kmh' =>
                        $location->speed_kmh !== null
                            ? (float) $location->speed_kmh
                            : null,

                    'heading' =>
                        $location->heading,

                    'recorded_at' =>
                        $location
                            ->recorded_at
                            ?->toIso8601String(),
                ],
            ],
            201
        );
    }

    /**
     * @return Collection<int, Trip>
     */
    private function activeTrips(
        string $driverId
    ): Collection {
        return Trip::query()
            ->where(
                'driver_id',
                $driverId
            )
            ->whereIn(
                'status',
                [
                    TripStatus::Boarding->value,
                    TripStatus::InProgress->value,
                ]
            )
            ->orderBy(
                'departure_at'
            )
            ->get();
    }

    /**
     * @param Collection<int, Trip> $trips
     */
    private function selectedTrip(
        Request $request,
        Collection $trips
    ): ?Trip {
        if ($trips->isEmpty()) {
            return null;
        }

        $requestedId =
            $request
                ->string(
                    'trip'
                )
                ->toString();

        if ($requestedId === '') {
            return $trips->first();
        }

        return $trips->first(
            fn (Trip $trip): bool =>
                (string) $trip->id
                === $requestedId
        ) ?? $trips->first();
    }
}
