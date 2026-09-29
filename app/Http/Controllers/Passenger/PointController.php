<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use App\Models\Point;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointController extends Controller
{
    public function index(
        Request $request,
        PointService $pointService
    ): View {
        $user = $request->user();

        $currentPoints =
            $pointService->currentBalance(
                $user
            );

        $transactions =
            Point::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->latest()
                ->paginate(15);

        return view(
            'passenger.points.index',
            [
                'user' => $user,
                'currentPoints' => $currentPoints,
                'transactions' => $transactions,
            ]
        );
    }
}
