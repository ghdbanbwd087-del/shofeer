<?php

namespace App\Services;

use App\Enums\DriverStatus;
use App\Enums\PackageStatus;
use App\Enums\TripStatus;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PackageService
{
    public function assign(
        Package $package,
        Driver $driver,
        ?Trip $trip = null
    ): Package {
        return DB::transaction(
            function () use (
                $package,
                $driver,
                $trip
            ): Package {
                $lockedPackage =
                    Package::query()
                        ->whereKey(
                            $package->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                $lockedDriver =
                    Driver::query()
                        ->whereKey(
                            $driver->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    $lockedDriver->status
                    !== DriverStatus::Approved
                ) {
                    throw ValidationException::withMessages([
                        'driver_id' => 'يجب اختيار سائق موثق ومعتمد.',
                    ]);
                }

                if (
                    ! in_array(
                        $lockedPackage->status,
                        [
                            PackageStatus::Received,
                            PackageStatus::Assigned,
                        ],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'package' => 'لا يمكن تعيين سائق للبضاعة في حالتها الحالية.',
                    ]);
                }

                $lockedTrip = null;

                if ($trip !== null) {
                    $lockedTrip =
                        Trip::query()
                            ->whereKey(
                                $trip->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    if (
                        (string) $lockedTrip->driver_id
                        !== (string) $lockedDriver->id
                    ) {
                        throw ValidationException::withMessages([
                            'trip_id' => 'الرحلة المختارة لا تتبع السائق المحدد.',
                        ]);
                    }

                    if (
                        in_array(
                            $lockedTrip->status,
                            [
                                TripStatus::Completed,
                                TripStatus::Cancelled,
                            ],
                            true
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'trip_id' => 'لا يمكن تعيين بضاعة إلى رحلة مكتملة أو ملغاة.',
                        ]);
                    }
                }

                $lockedPackage->forceFill([
                    'assigned_driver_id' =>
                        $lockedDriver->id,

                    'trip_id' =>
                        $lockedTrip?->id,

                    'status' =>
                        PackageStatus::Assigned,

                    'assigned_at' =>
                        now(),

                    'in_transit_at' =>
                        null,

                    'delivered_at' =>
                        null,
                ])->save();

                return $lockedPackage->fresh([
                    'driver.user',
                    'trip',
                ]);
            },
            3
        );
    }

    public function markInTransit(
        Package $package
    ): Package {
        return DB::transaction(
            function () use (
                $package
            ): Package {
                $lockedPackage =
                    Package::query()
                        ->whereKey(
                            $package->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    $lockedPackage->status
                    === PackageStatus::InTransit
                ) {
                    return $lockedPackage;
                }

                if (
                    $lockedPackage->status
                    !== PackageStatus::Assigned
                ) {
                    throw ValidationException::withMessages([
                        'status' => 'يجب تعيين سائق للبضاعة قبل بدء النقل.',
                    ]);
                }

                if (
                    $lockedPackage->assigned_driver_id
                    === null
                ) {
                    throw ValidationException::withMessages([
                        'driver_id' => 'لا يوجد سائق معين لهذه البضاعة.',
                    ]);
                }

                $lockedPackage->forceFill([
                    'status' =>
                        PackageStatus::InTransit,

                    'in_transit_at' =>
                        now(),
                ])->save();

                return $lockedPackage;
            },
            3
        );
    }

    public function markDelivered(
        Package $package
    ): Package {
        return DB::transaction(
            function () use (
                $package
            ): Package {
                $lockedPackage =
                    Package::query()
                        ->whereKey(
                            $package->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    $lockedPackage->status
                    === PackageStatus::Delivered
                ) {
                    return $lockedPackage;
                }

                if (
                    $lockedPackage->status
                    !== PackageStatus::InTransit
                ) {
                    throw ValidationException::withMessages([
                        'status' => 'يجب أن تكون البضاعة في الطريق قبل تسجيل التسليم.',
                    ]);
                }

                $lockedPackage->forceFill([
                    'status' =>
                        PackageStatus::Delivered,

                    'delivered_at' =>
                        now(),
                ])->save();

                return $lockedPackage;
            },
            3
        );
    }
}
