<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * صفحة الدخول تعمل.
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get(
            route('login')
        );

        $response
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    /**
     * مستخدم نشط يستطيع تسجيل الدخول.
     */
    public function test_active_user_can_login_with_phone_and_password(): void
    {
        $user = User::factory()
            ->passenger()
            ->create([
                'phone' => '+967771234567',

                'password' => 'Password123!',
            ]);

        $response = $this->post(
            route('login.store'),
            [
                /*
                 * Local format.
                 */
                'phone' => '771234567',

                'password' => 'Password123!',
            ]
        );

        $this->assertAuthenticatedAs(
            $user
        );

        $response->assertRedirect(
            route('dashboard')
        );
    }

    /**
     * كلمة مرور خاطئة لا تسجل المستخدم.
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'phone' => '+967771234567',

            'password' => 'Password123!',
        ]);

        $response = $this->post(
            route('login.store'),
            [
                'phone' => '771234567',

                'password' => 'WrongPassword123!',
            ]
        );

        $response->assertSessionHasErrors(
            'phone'
        );

        $this->assertGuest();
    }

    /**
     * الحساب الموقوف لا يستطيع تسجيل الدخول.
     */
    public function test_inactive_user_cannot_login(): void
    {
        User::factory()
            ->inactive()
            ->create([
                'phone' => '+967771234567',

                'password' => 'Password123!',
            ]);

        $response = $this->post(
            route('login.store'),
            [
                'phone' => '771234567',

                'password' => 'Password123!',
            ]
        );

        $response->assertSessionHasErrors(
            'phone'
        );

        $this->assertGuest();
    }

    /**
     * Admin يتم تحويله إلى منطقة الإدارة.
     */
    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()
            ->admin()
            ->create([
                'phone' => '+967770000001',

                'password' => 'AdminPassword123!',
            ]);

        $response = $this->post(
            route('login.store'),
            [
                'phone' => '+967770000001',

                'password' => 'AdminPassword123!',
            ]
        );

        $this->assertAuthenticatedAs(
            $admin
        );

        $this->assertSame(
            UserRole::Admin,
            $admin->role
        );

        $response->assertRedirect(
            route('admin.dashboard')
        );
    }

    /**
     * Driver يتم تحويله إلى منطقة السائق.
     */
    public function test_driver_is_redirected_to_driver_dashboard(): void
    {
        $driver = User::factory()
            ->driver()
            ->create([
                'phone' => '+967772222222',

                'password' => 'DriverPassword123!',
            ]);

        $response = $this->post(
            route('login.store'),
            [
                'phone' => '+967772222222',

                'password' => 'DriverPassword123!',
            ]
        );

        $this->assertAuthenticatedAs(
            $driver
        );

        $this->assertSame(
            UserRole::Driver,
            $driver->role
        );

        $response->assertRedirect(
            route('driver.dashboard')
        );
    }
}
