<?php

namespace Tests\Feature\Passenger;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassengerDashboardTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Passenger Dashboard
    |--------------------------------------------------------------------------
    */

    public function test_passenger_can_view_dashboard(): void
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
            )
            ->assertSee(
                $passenger->name
            )
            ->assertSee(
                'حجوزاتي القادمة'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Passenger Bookings
    |--------------------------------------------------------------------------
    */

    public function test_passenger_can_view_bookings_page(): void
    {
        $passenger = User::factory()
            ->passenger()
            ->create();

        $response = $this
            ->actingAs($passenger)
            ->get(
                route(
                    'dashboard.bookings.index'
                )
            );

        $response
            ->assertOk()
            ->assertViewIs(
                'passenger.bookings.index'
            )
            ->assertSee(
                'حجوزاتي'
            )
            ->assertSee(
                'قادمة'
            )
            ->assertSee(
                'سابقة'
            )
            ->assertSee(
                'ملغاة'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Guest Protection
    |--------------------------------------------------------------------------
    */

    public function test_guest_cannot_access_passenger_dashboard(): void
    {
        $this
            ->get(
                route('dashboard')
            )
            ->assertRedirect(
                route('login')
            );

        $this
            ->get(
                route(
                    'dashboard.bookings.index'
                )
            )
            ->assertRedirect(
                route('login')
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Protection
    |--------------------------------------------------------------------------
    */

    public function test_admin_cannot_access_passenger_dashboard(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $this
            ->actingAs($admin)
            ->get(
                route('dashboard')
            )
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | Driver Protection
    |--------------------------------------------------------------------------
    */

    public function test_driver_cannot_access_passenger_dashboard(): void
    {
        $driver = User::factory()
            ->driver()
            ->create();

        $this
            ->actingAs($driver)
            ->get(
                route('dashboard')
            )
            ->assertForbidden();
    }
}
