<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * الصفحة الرئيسية تعمل.
     */
    public function test_home_page_can_be_rendered(): void
    {
        $response = $this->get(
            route('home')
        );

        $response
            ->assertOk()
            ->assertViewIs('home')
            ->assertSee('سافر بثقة')
            ->assertSee('وصل بأمان')
            ->assertSee('SHOFEER');
    }

    /**
     * صفحة الدخول تحتوي العناصر الأساسية.
     */
    public function test_login_page_contains_expected_authentication_elements(): void
    {
        $response = $this->get(
            route('login')
        );

        $response
            ->assertOk()
            ->assertSee('تسجيل الدخول')
            ->assertSee('رقم الجوال')
            ->assertSee('كلمة المرور')
            ->assertSee('متابعة عبر Google');
    }

    /**
     * صفحة التسجيل تحتوي العناصر المطلوبة.
     */
    public function test_register_page_contains_expected_registration_elements(): void
    {
        $response = $this->get(
            route('register')
        );

        $response
            ->assertOk()
            ->assertSee('تسجيل راكب')
            ->assertSee('رقم الجوال')
            ->assertSee('تأكيد كلمة المرور')
            ->assertSee('الشروط والأحكام');
    }

    /**
     * المستخدم المسجل لا يعود إلى صفحة الدخول.
     */
    public function test_authenticated_passenger_is_redirected_away_from_login(): void
    {
        $passenger = User::factory()
            ->passenger()
            ->create();

        $response = $this
            ->actingAs($passenger)
            ->get(
                route('login')
            );

        $response->assertRedirect(
            route('dashboard')
        );
    }

    /**
     * Admin المسجل يتم تحويله من login إلى Admin.
     */
    public function test_authenticated_admin_is_redirected_away_from_login(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $response = $this
            ->actingAs($admin)
            ->get(
                route('login')
            );

        $response->assertRedirect(
            route('admin.dashboard')
        );
    }

    /**
     * Driver المسجل يتم تحويله من login إلى Driver.
     */
    public function test_authenticated_driver_is_redirected_away_from_login(): void
    {
        $driver = User::factory()
            ->driver()
            ->create();

        $response = $this
            ->actingAs($driver)
            ->get(
                route('register')
            );

        $response->assertRedirect(
            route('driver.dashboard')
        );
    }
}
