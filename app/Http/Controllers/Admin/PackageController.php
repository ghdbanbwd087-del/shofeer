<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DriverStatus;
use App\Enums\PackageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignPackageRequest;
use App\Http\Requests\Admin\UpdatePackageStatusRequest;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Trip;
use App\Services\PackageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(
        Request $request
    ): View {
        $status =
            $request
                ->string(
                    'status'
                )
                ->toString();

        if (
            ! in_array(
                $status,
                [
                    'all',
                    'received',
                    'assigned',
                    'in_transit',
                    'delivered',
                    'cancelled',
                ],
                true
            )
        ) {
            $status = 'all';
        }

        $query =
            Package::query()
                ->with([
                    'user',
                    'driver.user',
                    'trip',
                ])
                ->latest();

        if ($status !== 'all') {
            $query->where(
                'status',
                $status
            );
        }

        $packages =
            $query
                ->paginate(20)
                ->withQueryString();

        $drivers =
            Driver::query()
                ->where(
                    'status',
                    DriverStatus::Approved->value
                )
                ->with('user')
                ->orderBy('created_at')
                ->get();

        $trips =
            Trip::query()
                ->whereNotIn(
                    'status',
                    [
                        'completed',
                        'cancelled',
                    ]
                )
                ->with([
                    'driver.user',
                ])
                ->orderBy('departure_at')
                ->get();

        $counts = [
            'all' =>
                Package::query()->count(),

            'received' =>
                Package::query()
                    ->where(
                        'status',
                        PackageStatus::Received->value
                    )
                    ->count(),

            'assigned' =>
                Package::query()
                    ->where(
                        'status',
                        PackageStatus::Assigned->value
                    )
                    ->count(),

            'in_transit' =>
                Package::query()
                    ->where(
                        'status',
                        PackageStatus::InTransit->value
                    )
                    ->count(),

            'delivered' =>
                Package::query()
                    ->where(
                        'status',
                        PackageStatus::Delivered->value
                    )
                    ->count(),

            'cancelled' =>
                Package::query()
                    ->where(
                        'status',
                        PackageStatus::Cancelled->value
                    )
                    ->count(),
        ];

        return view(
            'admin.packages.index',
            compact(
                'packages',
                'drivers',
                'trips',
                'status',
                'counts'
            )
        );
    }

    public function assign(
        AssignPackageRequest $request,
        Package $package,
        PackageService $packageService
    ): RedirectResponse {
        $driver =
            Driver::query()
                ->findOrFail(
                    $request->validated(
                        'driver_id'
                    )
                );

        $trip = null;

        if (
            $request->filled(
                'trip_id'
            )
        ) {
            $trip =
                Trip::query()
                    ->findOrFail(
                        $request->validated(
                            'trip_id'
                        )
                    );
        }

        $packageService->assign(
            package: $package,
            driver: $driver,
            trip: $trip,
        );

        return back()->with(
            'success',
            'تم تعيين السائق للبضاعة بنجاح.'
        );
    }

    public function updateStatus(
        UpdatePackageStatusRequest $request,
        Package $package,
        PackageService $packageService
    ): RedirectResponse {
        $status =
            $request->validated(
                'status'
            );

        if (
            $status
            === PackageStatus::InTransit->value
        ) {
            $packageService->markInTransit(
                $package
            );

            return back()->with(
                'success',
                'تم تحديث حالة البضاعة إلى: في الطريق.'
            );
        }

        $packageService->markDelivered(
            $package
        );

        return back()->with(
            'success',
            'تم تسجيل تسليم البضاعة بنجاح.'
        );
    }
}
