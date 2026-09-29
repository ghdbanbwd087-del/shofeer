<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\FinancialReward;
use App\Models\User;
use App\Services\RewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class FinancialRewardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_view_rewards_settings_page(): void
    {
        $admin =
            User::factory()->create([
                'role' => UserRole::Admin,
            ]);

        $this
            ->actingAs($admin)
            ->get(
                route('admin.rewards.index')
            )
            ->assertOk()
            ->assertViewIs(
                'admin.rewards.index'
            )
            ->assertSee(
                'الجوائز المالية'
            );
    }

    public function test_passenger_cannot_view_rewards_settings_page(): void
    {
        $passenger =
            User::factory()->create([
                'role' => UserRole::Passenger,
            ]);

        $this
            ->actingAs($passenger)
            ->get(
                route('admin.rewards.index')
            )
            ->assertForbidden();
    }

    public function test_admin_can_create_reward_rule(): void
    {
        $admin =
            User::factory()->create([
                'role' => UserRole::Admin,
            ]);

        $this
            ->actingAs($admin)
            ->post(
                route('admin.rewards.store'),
                [
                    'code' => 'monthly_test',
                    'name' => 'جائزة شهرية اختبارية',
                    'description' => 'اختبار',
                    'amount' => 100,
                    'min_completed_trips' => 0,
                    'sort_order' => 10,
                    'is_active' => 1,
                ]
            )
            ->assertRedirect();

        $this->assertDatabaseHas(
            'financial_rewards',
            [
                'code' => 'monthly_test',
                'name' => 'جائزة شهرية اختبارية',
                'amount' => 100,
                'min_completed_trips' => 0,
                'is_active' => 1,
            ]
        );
    }

    public function test_monthly_reward_credits_balance_only_once(): void
    {
        $passenger =
            User::factory()->create([
                'role' => UserRole::Passenger,
                'is_active' => true,
            ]);

        $reward =
            FinancialReward::query()->create([
                'code' => 'monthly_zero',
                'name' => 'جائزة اختبار',
                'description' => null,
                'amount' => 75,
                'min_completed_trips' => 0,
                'is_active' => true,
                'sort_order' => 10,
            ]);

        $service =
            app(
                RewardService::class
            );

        $period =
            CarbonImmutable::parse(
                '2026-09-01'
            );

        $first =
            $service->awardMonthly(
                $passenger,
                $reward,
                $period
            );

        $second =
            $service->awardMonthly(
                $passenger,
                $reward,
                $period
            );

        $this->assertSame(
            $first->id,
            $second->id
        );

        $this->assertDatabaseCount(
            'monthly_rewards',
            1
        );

        $this->assertDatabaseCount(
            'balance_transactions',
            1
        );

        $this->assertDatabaseHas(
            'balances',
            [
                'user_id' => $passenger->id,
                'amount' => 75,
            ]
        );
    }

    public function test_monthly_runner_skips_ineligible_passenger(): void
    {
        User::factory()->create([
            'role' => UserRole::Passenger,
            'is_active' => true,
        ]);

        FinancialReward::query()->create([
            'code' => 'needs_trip',
            'name' => 'جائزة تحتاج رحلة',
            'description' => null,
            'amount' => 50,
            'min_completed_trips' => 1,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $result =
            app(
                RewardService::class
            )->runMonthly(
                CarbonImmutable::parse(
                    '2026-09-01'
                )
            );

        $this->assertSame(
            0,
            $result['awarded']
        );

        $this->assertSame(
            1,
            $result['ineligible']
        );

        $this->assertDatabaseCount(
            'monthly_rewards',
            0
        );

        $this->assertDatabaseCount(
            'balance_transactions',
            0
        );
    }
}
