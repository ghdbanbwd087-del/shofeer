<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverLocation;
use App\Models\Trip;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LocationService
{
    public function record(
        Driver $driver,
        ?Trip $trip,
        array $data
    ): DriverLocation {
        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];

        $accuracy =
            isset($data['accuracy_m']) && $data['accuracy_m'] !== null
                ? (float) $data['accuracy_m']
                : null;

        $reportedSpeed =
            isset($data['speed_kmh']) && $data['speed_kmh'] !== null
                ? (float) $data['speed_kmh']
                : null;

        $heading =
            isset($data['heading']) && $data['heading'] !== null
                ? (int) $data['heading']
                : null;

        $recordedAt =
            filled($data['recorded_at'] ?? null)
                ? CarbonImmutable::parse((string) $data['recorded_at'])
                : CarbonImmutable::now();

        $etaAt =
            filled($data['eta_at'] ?? null)
                ? CarbonImmutable::parse((string) $data['eta_at'])
                : null;

        $this->validateCoordinates(
            latitude: $latitude,
            longitude: $longitude,
            accuracy: $accuracy,
            reportedSpeed: $reportedSpeed,
            heading: $heading,
            recordedAt: $recordedAt,
        );

        return DB::transaction(
            function () use (
                $driver,
                $trip,
                $latitude,
                $longitude,
                $accuracy,
                $reportedSpeed,
                $heading,
                $recordedAt,
                $etaAt
            ): DriverLocation {
                $lockedDriver =
                    Driver::query()
                        ->whereKey($driver->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                $lockedTrip = null;

                if ($trip !== null) {
                    $lockedTrip =
                        Trip::query()
                            ->whereKey($trip->id)
                            ->lockForUpdate()
                            ->firstOrFail();

                    if ((string) $lockedTrip->driver_id !== (string) $lockedDriver->id) {
                        throw ValidationException::withMessages([
                            'trip_id' => 'الرحلة لا تتبع هذا السائق.',
                        ]);
                    }
                }

                $previous =
                    DriverLocation::query()
                        ->where('driver_id', $lockedDriver->id)
                        ->orderByDesc('recorded_at')
                        ->first();

                if ($previous !== null) {
                    $this->validateImpliedSpeed(
                        previous: $previous,
                        latitude: $latitude,
                        longitude: $longitude,
                        recordedAt: $recordedAt,
                    );
                }

                return DriverLocation::query()->create([
                    'driver_id' => $lockedDriver->id,
                    'trip_id' => $lockedTrip?->id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'accuracy_m' => $accuracy,
                    'speed_kmh' => $reportedSpeed,
                    'heading' => $heading,
                    'eta_at' => $etaAt,
                    'recorded_at' => $recordedAt,
                ]);
            },
            3
        );
    }

    public function latestForTrip(Trip $trip): ?DriverLocation
    {
        return DriverLocation::query()
            ->where('trip_id', $trip->id)
            ->where('driver_id', $trip->driver_id)
            ->latest('recorded_at')
            ->first();
    }

    private function validateCoordinates(
        float $latitude,
        float $longitude,
        ?float $accuracy,
        ?float $reportedSpeed,
        ?int $heading,
        CarbonImmutable $recordedAt
    ): void {
        if ($latitude < -90 || $latitude > 90) {
            throw ValidationException::withMessages([
                'latitude' => 'خط العرض غير صالح.',
            ]);
        }

        if ($longitude < -180 || $longitude > 180) {
            throw ValidationException::withMessages([
                'longitude' => 'خط الطول غير صالح.',
            ]);
        }

        if (
            $accuracy !== null
            && (
                $accuracy < 0
                || $accuracy > (float) config('tracking.max_accuracy_m', 1000)
            )
        ) {
            throw ValidationException::withMessages([
                'accuracy_m' => 'دقة الموقع خارج الحد المسموح.',
            ]);
        }

        if (
            $reportedSpeed !== null
            && (
                $reportedSpeed < 0
                || $reportedSpeed > (float) config('tracking.max_reported_speed_kmh', 180)
            )
        ) {
            throw ValidationException::withMessages([
                'speed_kmh' => 'السرعة المرسلة غير منطقية.',
            ]);
        }

        if ($heading !== null && ($heading < 0 || $heading > 359)) {
            throw ValidationException::withMessages([
                'heading' => 'اتجاه الحركة غير صالح.',
            ]);
        }

        $maxFuture =
            CarbonImmutable::now()->addSeconds(
                (int) config('tracking.max_future_skew_seconds', 120)
            );

        if ($recordedAt->greaterThan($maxFuture)) {
            throw ValidationException::withMessages([
                'recorded_at' => 'وقت الموقع متقدم عن وقت الخادم.',
            ]);
        }
    }

    private function validateImpliedSpeed(
        DriverLocation $previous,
        float $latitude,
        float $longitude,
        CarbonImmutable $recordedAt
    ): void {
        if ($previous->recorded_at === null) {
            return;
        }

        $previousAt =
            CarbonImmutable::instance($previous->recorded_at);

        if ($recordedAt->lessThanOrEqualTo($previousAt)) {
            return;
        }

        $seconds =
            $previousAt->diffInSeconds($recordedAt);

        if ($seconds < 5) {
            return;
        }

        $distanceKm =
            $this->haversineKm(
                (float) $previous->latitude,
                (float) $previous->longitude,
                $latitude,
                $longitude
            );

        $impliedSpeed =
            $distanceKm / ($seconds / 3600);

        if (
            $impliedSpeed
            > (float) config('tracking.max_implied_speed_kmh', 220)
        ) {
            throw ValidationException::withMessages([
                'location' => 'تم رفض الموقع لأن الانتقال بين نقطتين غير منطقي.',
            ]);
        }
    }

    private function haversineKm(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadiusKm = 6371.0;

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a =
            sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($lonDelta / 2) ** 2;

        $c =
            2 * atan2(
                sqrt($a),
                sqrt(1 - $a)
            );

        return $earthRadiusKm * $c;
    }
}
