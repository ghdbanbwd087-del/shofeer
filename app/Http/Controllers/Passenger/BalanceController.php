<?php

namespace App\Http\Controllers\Passenger;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use App\Models\Booking;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BalanceController extends Controller
{
    public function index(
        Request $request,
        BalanceService $balanceService
    ): View {
        $user = $request->user();

        $balance =
            $balanceService->balanceFor(
                $user
            );

        $completedTrips =
            Booking::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'status',
                    BookingStatus::Confirmed->value
                )
                ->whereHas(
                    'trip',
                    fn ($query) =>
                        $query->where(
                            'departure_at',
                            '<',
                            now()
                        )
                )
                ->distinct()
                ->count(
                    'trip_id'
                );

        $transactions =
            BalanceTransaction::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->latest()
                ->paginate(15);

        $rewardTransactions =
            BalanceTransaction::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'type',
                    'credit'
                )
                ->latest()
                ->limit(5)
                ->get();

        return view(
            'passenger.balance.index',
            [
                'user' => $user,
                'balance' => $balance,
                'completedTrips' => $completedTrips,
                'transactions' => $transactions,
                'rewardTransactions' => $rewardTransactions,
            ]
        );
    }
}
