<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LiveController extends Controller
{
    public function index(): View
    {
        $rows =
            $this->liveRows();

        return view(
            'admin.live.index',
            [
                'rows' =>
                    $rows,

                'stats' => [
                    'total' =>
                        $rows->count(),

                    'live' =>
                        $rows
                            ->where(
                                'tracking_state',
                                'live'
                            )
                            ->count(),

                    'stale' =>
                        $rows
                            ->where(
                                'tracking_state',
                                'stale'
                            )
                            ->count(),

                    'missing' =>
                        $rows
                            ->where(
                                'tracking_state',
                                'missing'
                            )
                            ->count(),
                ],
            ]
        );
    }

    public function data(): JsonResponse
    {
        $rows =
            $this->liveRows();

        return response()->json([
            'generated_at' =>
                now()->toIso8601String(),

            'stats' => [
                'total' =>
                    $rows->count(),

                'live' =>
                    $rows
                        ->where(
                            'tracking_state',
                            'live'
                        )
                        ->count(),

                'stale' =>
                    $rows
                        ->where(
                            'tracking_state',
                            'stale'
                        )
                        ->count(),

                'missing' =>
                    $rows
                        ->where(
                            'tracking_state',
                            'missing'
                        )
                        ->count(),
            ],

            'trips' =>
                $rows->values(),
        ]);
    }

    private function liveRows()
    {
        $trips =
            Trip::query()
                ->whereIn(
                    'status',
                    [
                        TripStatus::Boarding->value,
                        TripStatus::InProgress->value,
                    ]
                )
                ->with([
                    'driver.user',
                    'fromCity',
                    'toCity',
                ])
                ->orderBy(
                    'departure_at'
                )
                ->get();

        if ($trips->isEmpty()) {
            return collect();
        }

        $locations =
            DriverLocation::query()
                ->whereIn(
                    'trip_id',
                    $trips->pluck('id')
                )
                ->orderByDesc(
                    'recorded_at'
                )
                ->get()
                ->unique(
                    'trip_id'
                )
                ->keyBy(
                    'trip_id'
                );

        return $trips->map(
            function (
                Trip $trip
            ) use (
                $locations
            ): array {
                $location =
                    $locations->get(
                        $trip->id
                    );

                $state =
                    $location === null
                        ? 'missing'
                        : (
                            $location->isFresh()
                                ? 'live'
                                : 'stale'
                        );

                $alertMessage =
                    match ($state) {
                        'missing' =>
                            'لم يصل موقع من السائق بعد.',

                        'stale' =>
                            'آخر موقع قديم ويحتاج متابعة.',

                        default =>
                            null,
                    };

                return [
                    'trip_id' =>
                        $trip->id,

                    'driver_id' =>
                        $trip->driver_id,

                    'driver_name' =>
                        $trip
                            ->driver
                            ?->user
                            ?->name
                        ?? '—',

                    'from_city' =>
                        $trip
                            ->fromCity
                            ?->name
                        ?? '—',

                    'to_city' =>
                        $trip
                            ->toCity
                            ?->name
                        ?? '—',

                    'departure_at' =>
                        $trip
                            ->departure_at
                            ?->toIso8601String(),

                    'trip_status' =>
                        $trip
                            ->status
                            ->value,

                    'trip_status_label' =>
                        $trip
                            ->status
                            ->label(),

                    'tracking_state' =>
                        $state,

                    'alert_message' =>
                        $alertMessage,

                    'latitude' =>
                        $location !== null
                            ? (float) $location->latitude
                            : null,

                    'longitude' =>
                        $location !== null
                            ? (float) $location->longitude
                            : null,

                    'accuracy_m' =>
                        $location?->accuracy_m !== null
                            ? (float) $location->accuracy_m
                            : null,

                    'speed_kmh' =>
                        $location?->speed_kmh !== null
                            ? (float) $location->speed_kmh
                            : null,

                    'recorded_at' =>
                        $location
                            ?->recorded_at
                            ?->toIso8601String(),
                ];
            }
        );
    }
}
