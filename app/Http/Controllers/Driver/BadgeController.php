<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(
        Request $request,
        BadgeService $badgeService
    ): View {
        $user =
            $request->user();

        $driver =
            $user
                ->driver()
                ->firstOrFail();

        $summary =
            $badgeService
                ->driverSummaryFor(
                    user: $user,
                    driver: $driver,
                );

        return view(
            'driver.badges.index',
            [
                'user' =>
                    $user,

                'driver' =>
                    $driver,

                ...$summary,
            ]
        );
    }
}
