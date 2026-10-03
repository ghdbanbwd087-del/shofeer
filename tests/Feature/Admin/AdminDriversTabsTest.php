<?php

namespace Tests\Feature\Admin;

use App\Enums\DriverStatus;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDriversTabsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_view_four_driver_status_tabs(): void
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
                    'admin.drivers.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'admin.drivers.index'
            )
            ->assertSee(
                DriverStatus::Pending
                    ->label()
            )
            ->assertSee(
                DriverStatus::Approved
                    ->label()
            )
            ->assertSee(
                DriverStatus::Rejected
                    ->label()
            )
            ->assertSee(
                DriverStatus::Suspended
                    ->label()
            );
    }

    public function test_pending_tab_only_lists_pending_drivers(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $pendingUser =
            User::factory()
                ->driver()
                ->create([
                    'name' =>
                        'Pending Driver Name',
                ]);

        Driver::factory()
            ->create([
                'user_id' =>
                    $pendingUser->id,

                'status' =>
                    DriverStatus::Pending,
            ]);

        $approvedUser =
            User::factory()
                ->driver()
                ->create([
                    'name' =>
                        'Approved Driver Name',
                ]);

        Driver::factory()
            ->approved()
            ->create([
                'user_id' =>
                    $approvedUser->id,
            ]);

        $this
            ->actingAs(
                $admin
            )
            ->get(
                route(
                    'admin.drivers.index',
                    [
                        'status' =>
                            DriverStatus::Pending
                                ->value,
                    ]
                )
            )
            ->assertOk()
            ->assertSee(
                'Pending Driver Name'
            )
            ->assertDontSee(
                'Approved Driver Name'
            )
            ->assertSee(
                'مراجعة الطلب'
            );
    }

    public function test_approved_tab_only_lists_approved_drivers(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $approvedUser =
            User::factory()
                ->driver()
                ->create([
                    'name' =>
                        'Approved Driver Visible',
                ]);

        Driver::factory()
            ->approved()
            ->create([
                'user_id' =>
                    $approvedUser->id,
            ]);

        $pendingUser =
            User::factory()
                ->driver()
                ->create([
                    'name' =>
                        'Pending Driver Hidden',
                ]);

        Driver::factory()
            ->create([
                'user_id' =>
                    $pendingUser->id,

                'status' =>
                    DriverStatus::Pending,
            ]);

        $this
            ->actingAs(
                $admin
            )
            ->get(
                route(
                    'admin.drivers.index',
                    [
                        'status' =>
                            DriverStatus::Approved
                                ->value,
                    ]
                )
            )
            ->assertOk()
            ->assertSee(
                'Approved Driver Visible'
            )
            ->assertDontSee(
                'Pending Driver Hidden'
            )
            ->assertSee(
                'عرض الملف'
            );
    }

    public function test_passenger_cannot_view_admin_drivers_page(): void
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
                    'admin.drivers.index'
                )
            )
            ->assertForbidden();
    }
}
