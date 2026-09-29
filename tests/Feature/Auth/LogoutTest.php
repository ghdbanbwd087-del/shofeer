<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * المستخدم المسجل يستطيع تسجيل الخروج.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()
            ->passenger()
            ->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route('logout')
            );

        $this->assertGuest();

        $response
            ->assertRedirect(
                route('home')
            )
            ->assertSessionHas(
                'success',
                'تم تسجيل خروجك بنجاح.'
            );
    }

    /**
     * Route الخاصة بالخروج تتطلب Authentication.
     */
    public function test_guest_cannot_use_authenticated_logout_route(): void
    {
        $response = $this->post(
            route('logout')
        );

        $response->assertRedirect(
            route('login')
        );

        $this->assertGuest();
    }
}
