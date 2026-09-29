<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RunMonthlyRewardsRequest;
use App\Http\Requests\Admin\StoreFinancialRewardRequest;
use App\Http\Requests\Admin\UpdateFinancialRewardRequest;
use App\Models\FinancialReward;
use App\Models\MonthlyReward;
use App\Services\RewardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function index(
        Request $request
    ): View {
        $period =
            $request
                ->string(
                    'period'
                )
                ->toString();

        if (
            ! preg_match(
                '/^\d{4}-\d{2}$/',
                $period
            )
        ) {
            $period =
                now()->format(
                    'Y-m'
                );
        }

        $rewards =
            FinancialReward::query()
                ->orderBy(
                    'sort_order'
                )
                ->orderBy(
                    'min_completed_trips'
                )
                ->get();

        $monthlyRewards =
            MonthlyReward::query()
                ->with([
                    'financialReward',
                    'user',
                    'balanceTransaction',
                ])
                ->where(
                    'period_key',
                    $period
                )
                ->latest(
                    'awarded_at'
                )
                ->paginate(20)
                ->withQueryString();

        return view(
            'admin.rewards.index',
            [
                'rewards' => $rewards,
                'monthlyRewards' => $monthlyRewards,
                'period' => $period,
            ]
        );
    }

    public function store(
        StoreFinancialRewardRequest $request
    ): RedirectResponse {
        FinancialReward::query()->create([
            'code' =>
                $request->validated(
                    'code'
                ),

            'name' =>
                $request->validated(
                    'name'
                ),

            'description' =>
                $request->validated(
                    'description'
                ),

            'amount' =>
                $request->validated(
                    'amount'
                ),

            'min_completed_trips' =>
                $request->validated(
                    'min_completed_trips'
                ),

            'sort_order' =>
                $request->validated(
                    'sort_order'
                ) ?? 0,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ]);

        return back()->with(
            'success',
            'تم إنشاء قاعدة الجائزة المالية.'
        );
    }

    public function update(
        UpdateFinancialRewardRequest $request,
        FinancialReward $financialReward
    ): RedirectResponse {
        $financialReward->update([
            'code' =>
                $request->validated(
                    'code'
                ),

            'name' =>
                $request->validated(
                    'name'
                ),

            'description' =>
                $request->validated(
                    'description'
                ),

            'amount' =>
                $request->validated(
                    'amount'
                ),

            'min_completed_trips' =>
                $request->validated(
                    'min_completed_trips'
                ),

            'sort_order' =>
                $request->validated(
                    'sort_order'
                ) ?? 0,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ]);

        return back()->with(
            'success',
            'تم تحديث قاعدة الجائزة.'
        );
    }

    public function runMonthly(
        RunMonthlyRewardsRequest $request,
        RewardService $rewardService
    ): RedirectResponse {
        $period =
            CarbonImmutable::createFromFormat(
                'Y-m',
                $request->validated(
                    'period'
                )
            )->startOfMonth();

        $result =
            $rewardService->runMonthly(
                $period
            );

        return redirect()
            ->route(
                'admin.rewards.index',
                [
                    'period' =>
                        $period->format(
                            'Y-m'
                        ),
                ]
            )
            ->with(
                'success',
                sprintf(
                    'اكتمل تشغيل الجوائز: %d جديدة، %d موجودة مسبقاً، %d غير مؤهلة.',
                    $result['awarded'],
                    $result['already_awarded'],
                    $result['ineligible']
                )
            );
    }
}
