<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_view_admin_dashboard(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $this
            ->actingAs(
                $admin
            )
            ->get(
                route(
                    'admin.dashboard'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'admin.dashboard.index'
            )
            ->assertSee(
                'لوحة الإدارة'
            )
            ->assertSee(
                'الرسوم البيانية'
            )
            ->assertSee(
                'آخر النشاطات'
            )
            ->assertSee(
                'التنبيهات'
            );
    }

    public function test_passenger_cannot_view_admin_dashboard(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs(
                $passenger
            )
            ->get(
                route(
                    'admin.dashboard'
                )
            )
            ->assertForbidden();
    }

    public function test_dashboard_has_eight_stat_cards_and_four_charts(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        User::factory()
            ->passenger()
            ->count(2)
            ->create();

        $response =
            $this
                ->actingAs(
                    $admin
                )
                ->get(
                    route(
                        'admin.dashboard'
                    )
                );

        $response
            ->assertOk()
            ->assertViewHas(
                'stats',
                function (
                    array $stats
                ): bool {
                    return count(
                        $stats
                    ) === 8
                        && $stats['users']['value']
                            === 3;
                }
            )
            ->assertViewHas(
                'charts',
                function (
                    array $charts
                ): bool {
                    if (
                        count(
                            $charts
                        ) !== 4
                    ) {
                        return false;
                    }

                    foreach (
                        $charts
                        as $chart
                    ) {
                        if (
                            count(
                                $chart['points']
                            ) !== 7
                        ) {
                            return false;
                        }
                    }

                    return true;
                }
            );
    }

    public function test_dashboard_navigation_contains_twenty_two_items(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $this
            ->actingAs(
                $admin
            )
            ->get(
                route(
                    'admin.dashboard'
                )
            )
            ->assertOk()
            ->assertViewHas(
                'navigation',
                fn (
                    array $navigation
                ): bool =>
                    count(
                        $navigation
                    ) === 22
            );
    }
}
