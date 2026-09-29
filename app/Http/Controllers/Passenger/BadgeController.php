<?php

namespace App\Http\Controllers\Passenger;

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
        $summary = $badgeService->summaryFor(
            $request->user()
        );

        return view(
            'passenger.badges.index',
            [
                'user' => $request->user(),
                ...$summary,
            ]
        );
    }
}
