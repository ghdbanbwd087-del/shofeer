<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DriverStatus;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DriverReviewController extends Controller
{
    public function index(
        Request $request
    ): View {
        $allowedStatuses =
            array_map(
                fn (
                    DriverStatus $status
                ): string =>
                    $status->value,
                DriverStatus::cases()
            );

        $status =
            $request
                ->string('status')
                ->toString();

        if (
            ! in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            $status =
                DriverStatus::Pending
                    ->value;
        }

        $search =
            trim(
                $request
                    ->string('q')
                    ->toString()
            );

        $query =
            Driver::query()
                ->with([
                    'user',
                    'cars',
                ])
                ->where(
                    'status',
                    $status
                )
                ->latest();

        if (
            $search !== ''
        ) {
            $query->whereHas(
                'user',
                function (
                    $userQuery
                ) use (
                    $search
                ): void {
                    $userQuery
                        ->where(
                            'name',
                            'like',
                            '%'.$search.'%'
                        )
                        ->orWhere(
                            'email',
                            'like',
                            '%'.$search.'%'
                        );
                }
            );
        }

        $drivers =
            $query
                ->paginate(20)
                ->withQueryString();

        $counts =
            Driver::query()
                ->select(
                    'status',
                    DB::raw(
                        'COUNT(*) as total'
                    )
                )
                ->groupBy(
                    'status'
                )
                ->pluck(
                    'total',
                    'status'
                );

        return view(
            'admin.drivers.index',
            [
                'drivers' =>
                    $drivers,

                'counts' =>
                    $counts,

                'status' =>
                    $status,

                'search' =>
                    $search,

                'tabs' => [
                    DriverStatus::Pending,
                    DriverStatus::Approved,
                    DriverStatus::Rejected,
                    DriverStatus::Suspended,
                ],
            ]
        );
    }
}
