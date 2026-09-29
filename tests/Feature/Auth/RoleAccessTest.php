<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Passenger يستطيع دخول لوحة الراكب.
     */
    public function test_passenger_can_access_passenger_dashboard(): void
    {
        $passenger = User::factory()
            ->passenger()
            ->create();

        $response = $this
            ->actingAs($passenger)
            ->get(
                route('dashboard')
            );

        $response
            ->assertOk()
            ->assertViewIs(
                'passenger.dashboard.index'
            );
    }

    /**
     * Admin يستطيع دخول /admin.
     */
    public function test_admin_can_access_admin_area(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $response = $this
            ->actingAs($admin)
            ->get(
                route('admin.dashboard')
            );

        $response
            ->assertOk()
            ->assertJsonPath(
                'user.role',
                'admin'
            );
    }

    /**
     * Driver يستطيع دخول /driver.
     */
    public function test_driver_can_access_driver_area(): void
    {
        $driver = User::factory()
            ->driver()
            ->create();

        $response = $this
            ->actingAs($driver)
            ->get(
                route('driver.dashboard')
            );

        $response
            ->assertOk()
            ->assertJsonPath(
                'user.role',
                'driver'
            );
    }

    /**
     * Passenger لا يستطيع دخول الإدارة.
     */
    public function test_passenger_cannot_access_admin_area(): void
    {
        $passenger = User::factory()
            ->passenger()
            ->create();

        $response = $this
            ->actingAs($passenger)
            ->get(
                route('admin.dashboard')
            );

        $response->assertForbidden();
    }

    /**
     * Passenger لا يستطيع دخول لوحة السائق.
     */
    public function test_passenger_cannot_access_driver_area(): void
    {
        $passenger = User::factory()
            ->passenger()
            ->create();

        $response = $this
            ->actingAs($passenger)
            ->get(
                route('driver.dashboard')
            );

        $response->assertForbidden();
    }

    /**
     * Driver لا يستطيع دخول الإدارة.
     */
    public function test_driver_cannot_access_admin_area(): void
    {
        $driver = User::factory()
            ->driver()
            ->create();

        $response = $this
            ->actingAs($driver)
            ->get(
                route('admin.dashboard')
            );

        $response->assertForbidden();
    }

    /**
     * Admin لا يدخل Passenger Dashboard.
     */
    public function test_admin_cannot_access_passenger_dashboard(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $response = $this
            ->actingAs($admin)
            ->get(
                route('dashboard')
            );

        $response->assertForbidden();
    }

    /**
     * Guest يتم إرجاعه للدخول.
     */
    public function test_guest_is_redirected_from_protected_areas(): void
    {
        $this->get(
            route('dashboard')
        )->assertRedirect(
            route('login')
        );

        $this->get(
            route('admin.dashboard')
        )->assertRedirect(
            route('login')
        );

        $this->get(
            route('driver.dashboard')
        )->assertRedirect(
            route('login')
        );
    }

    /**
     * الحساب الموقوف يفقد الوصول حتى لو لديه Session.
     */
    public function test_inactive_authenticated_user_is_logged_out(): void
    {
        $admin = User::factory()
            ->admin()
            ->inactive()
            ->create();

        $response = $this
            ->actingAs($admin)
            ->get(
                route('admin.dashboard')
            );

        $response
            ->assertRedirect(
                route('login')
            )
            ->assertSessionHasErrors(
                'account'
            );

        $this->assertGuest();
    }
}
