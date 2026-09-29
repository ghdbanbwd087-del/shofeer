<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\FinancialReward;
use App\Models\MonthlyReward;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RewardService
{
    public function __construct(
        private readonly BalanceService $balanceService
    ) {
        //
    }

    public function completedTripsForPeriod(
        User $user,
        CarbonImmutable $period
    ): int {
        $start = $period->startOfMonth();
        $end = $period->endOfMonth();

        return Booking::query()
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
                function ($query) use (
                    $start,
                    $end
                ): void {
                    $query->whereBetween(
                        'departure_at',
                        [
                            $start,
                            $end,
                        ]
                    );
                }
            )
            ->distinct()
            ->count(
                'trip_id'
            );
    }

    public function isEligible(
        User $user,
        FinancialReward $reward,
        CarbonImmutable $period
    ): bool {
        if (! $reward->is_active) {
            return false;
        }

        return $this->completedTripsForPeriod(
            $user,
            $period
        ) >= $reward->min_completed_trips;
    }

    public function awardMonthly(
        User $user,
        FinancialReward $reward,
        CarbonImmutable $period
    ): MonthlyReward {
        $period = $period->startOfMonth();

        return DB::transaction(
            function () use (
                $user,
                $reward,
                $period
            ): MonthlyReward {
                /*
                 * Serialize monthly rewards for this user.
                 */
                $lockedUser =
                    User::query()
                        ->whereKey(
                            $user->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                $lockedReward =
                    FinancialReward::query()
                        ->whereKey(
                            $reward->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (! $lockedReward->is_active) {
                    throw ValidationException::withMessages([
                        'reward' => 'قاعدة الجائزة غير مفعلة.',
                    ]);
                }

                $periodKey =
                    $period->format(
                        'Y-m'
                    );

                $idempotencyKey =
                    'monthly-reward:reward:'.
                    $lockedReward->id.
                    ':user:'.
                    $lockedUser->id.
                    ':period:'.
                    $periodKey;

                $existing =
                    MonthlyReward::query()
                        ->where(
                            'idempotency_key',
                            $idempotencyKey
                        )
                        ->lockForUpdate()
                        ->first();

                if ($existing !== null) {
                    return $existing;
                }

                $completedTrips =
                    $this->completedTripsForPeriod(
                        $lockedUser,
                        $period
                    );

                if (
                    $completedTrips
                    < $lockedReward->min_completed_trips
                ) {
                    throw ValidationException::withMessages([
                        'reward' => 'المستخدم لا يحقق شرط هذه الجائزة للشهر المحدد.',
                    ]);
                }

                $monthlyReward =
                    MonthlyReward::query()
                        ->create([
                            'financial_reward_id' => $lockedReward->id,
                            'user_id' => $lockedUser->id,
                            'period_key' => $periodKey,
                            'completed_trips' => $completedTrips,
                            'amount' => $lockedReward->amount,
                            'balance_transaction_id' => null,
                            'idempotency_key' => $idempotencyKey,
                            'awarded_at' => null,
                        ]);

                $balanceTransaction =
                    $this->balanceService->credit(
                        user: $lockedUser,
                        amount: (float) $lockedReward->amount,
                        idempotencyKey: 'balance:'.$idempotencyKey,
                        description: 'جائزة شهرية: '.$lockedReward->name,
                        sourceType: 'monthly_reward',
                        sourceId: $monthlyReward->id,
                        metadata: [
                            'financial_reward_id' => $lockedReward->id,
                            'period_key' => $periodKey,
                            'completed_trips' => $completedTrips,
                        ],
                    );

                $monthlyReward->forceFill([
                    'balance_transaction_id' => $balanceTransaction->id,
                    'awarded_at' => now(),
                ])->save();

                return $monthlyReward->fresh([
                    'financialReward',
                    'user',
                    'balanceTransaction',
                ]);
            },
            3
        );
    }

    /**
     * @return array{
     *     awarded:int,
     *     already_awarded:int,
     *     ineligible:int
     * }
     */
    public function runMonthly(
        CarbonImmutable $period
    ): array {
        $period = $period->startOfMonth();

        $rewards =
            FinancialReward::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy(
                    'min_completed_trips'
                )
                ->get();

        $users =
            User::query()
                ->where(
                    'role',
                    UserRole::Passenger->value
                )
                ->where(
                    'is_active',
                    true
                )
                ->get();

        $result = [
            'awarded' => 0,
            'already_awarded' => 0,
            'ineligible' => 0,
        ];

        foreach ($users as $user) {
            foreach ($rewards as $reward) {
                if (
                    ! $this->isEligible(
                        $user,
                        $reward,
                        $period
                    )
                ) {
                    $result['ineligible']++;

                    continue;
                }

                $periodKey =
                    $period->format(
                        'Y-m'
                    );

                $exists =
                    MonthlyReward::query()
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->where(
                            'financial_reward_id',
                            $reward->id
                        )
                        ->where(
                            'period_key',
                            $periodKey
                        )
                        ->exists();

                if ($exists) {
                    $result['already_awarded']++;

                    continue;
                }

                $this->awardMonthly(
                    $user,
                    $reward,
                    $period
                );

                $result['awarded']++;
            }
        }

        return $result;
    }
}
