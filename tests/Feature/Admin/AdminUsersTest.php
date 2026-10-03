<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_view_users_table(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $passenger =
            User::factory()
                ->passenger()
                ->create([
                    'name' =>
                        'Passenger Example',

                    'email' =>
                        'passenger-list@example.test',
                ]);

        $this
            ->actingAs(
                $admin
            )
            ->get(
                route(
                    'admin.users.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'admin.users.index'
            )
            ->assertSee(
                'المستخدمون'
            )
            ->assertSee(
                $passenger->name
            )
            ->assertSee(
                $passenger->email
            );
    }

    public function test_passenger_cannot_view_admin_users_page(): void
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
                    'admin.users.index'
                )
            )
            ->assertForbidden();
    }

    public function test_admin_can_filter_users_by_role_and_status(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $activePassenger =
            User::factory()
                ->passenger()
                ->create([
                    'name' =>
                        'Active Passenger',

                    'is_active' =>
                        true,
                ]);

        $inactivePassenger =
            User::factory()
                ->passenger()
                ->create([
                    'name' =>
                        'Inactive Passenger',

                    'is_active' =>
                        false,
                ]);

        User::factory()
            ->driver()
            ->create([
                'name' =>
                    'Driver Result',
            ]);

        $this
            ->actingAs(
                $admin
            )
            ->get(
                route(
                    'admin.users.index',
                    [
                        'role' =>
                            'passenger',

                        'status' =>
                            'active',
                    ]
                )
            )
            ->assertOk()
            ->assertSee(
                $activePassenger->name
            )
            ->assertDontSee(
                $inactivePassenger->name
            )
            ->assertDontSee(
                'Driver Result'
            );
    }

    public function test_admin_can_disable_and_enable_user(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $passenger =
            User::factory()
                ->passenger()
                ->create([
                    'is_active' =>
                        true,
                ]);

        $this
            ->actingAs(
                $admin
            )
            ->patch(
                route(
                    'admin.users.status.update',
                    $passenger
                ),
                [
                    'is_active' =>
                        false,
                ]
            )
            ->assertRedirect()
            ->assertSessionHas(
                'success'
            );

        $this->assertFalse(
            (bool) $passenger
                ->fresh()
                ->is_active
        );

        $this
            ->actingAs(
                $admin
            )
            ->patch(
                route(
                    'admin.users.status.update',
                    $passenger
                ),
                [
                    'is_active' =>
                        true,
                ]
            )
            ->assertRedirect()
            ->assertSessionHas(
                'success'
            );

        $this->assertTrue(
            (bool) $passenger
                ->fresh()
                ->is_active
        );
    }

    public function test_admin_cannot_disable_own_current_account(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create([
                    'is_active' =>
                        true,
                ]);

        $this
            ->actingAs(
                $admin
            )
            ->from(
                route(
                    'admin.users.index'
                )
            )
            ->patch(
                route(
                    'admin.users.status.update',
                    $admin
                ),
                [
                    'is_active' =>
                        false,
                ]
            )
            ->assertRedirect(
                route(
                    'admin.users.index'
                )
            )
            ->assertSessionHasErrors(
                'user'
            );

        $this->assertTrue(
            (bool) $admin
                ->fresh()
                ->is_active
        );
    }

    public function test_last_active_admin_cannot_be_disabled(): void
    {
        $currentAdmin =
            User::factory()
                ->admin()
                ->create([
                    'is_active' =>
                        true,
                ]);

        $secondAdmin =
            User::factory()
                ->admin()
                ->create([
                    'is_active' =>
                        false,
                ]);

        $this
            ->actingAs(
                $currentAdmin
            )
            ->patch(
                route(
                    'admin.users.status.update',
                    $secondAdmin
                ),
                [
                    'is_active' =>
                        false,
                ]
            )
            ->assertRedirect();

        $this->assertFalse(
            (bool) $secondAdmin
                ->fresh()
                ->is_active
        );

        /*
         * The current admin also cannot disable itself, so there remains at
         * least one active admin account.
         */
        $this
            ->actingAs(
                $currentAdmin
            )
            ->from(
                route(
                    'admin.users.index'
                )
            )
            ->patch(
                route(
                    'admin.users.status.update',
                    $currentAdmin
                ),
                [
                    'is_active' =>
                        false,
                ]
            )
            ->assertSessionHasErrors(
                'user'
            );
    }
}
