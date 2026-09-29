<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * صفحة التسجيل تعمل.
     */
    public function test_registration_page_can_be_rendered(): void
    {
        $response = $this->get(
            route('register')
        );

        $response
            ->assertOk()
            ->assertViewIs('auth.register');
    }

    /**
     * يستطيع الراكب التسجيل يدوياً.
     */
    public function test_passenger_can_register_manually(): void
    {
        $response = $this->post(
            route('register.store'),
            [
                'name' => 'أحمد محمد',

                /*
                 * نستخدم الصيغة المحلية للتأكد
                 * من أن PhoneNormalizer يعمل.
                 */
                'phone' => '771234567',

                'password' => 'StrongPassword123!',

                'password_confirmation' => 'StrongPassword123!',

                'terms' => '1',
            ]
        );

        $user = User::query()
            ->where(
                'phone',
                '+967771234567'
            )
            ->first();

        $this->assertNotNull($user);

        $this->assertSame(
            UserRole::Passenger,
            $user->role
        );

        $this->assertTrue(
            $user->is_active
        );

        $this->assertTrue(
            Hash::check(
                'StrongPassword123!',
                $user->password
            )
        );

        $this->assertAuthenticatedAs(
            $user
        );

        $response->assertRedirect(
            route('dashboard')
        );
    }

    /**
     * يجب الموافقة على الشروط.
     */
    public function test_registration_requires_terms_acceptance(): void
    {
        $response = $this
            ->from(route('register'))
            ->post(
                route('register.store'),
                [
                    'name' => 'أحمد محمد',

                    'phone' => '771234567',

                    'password' => 'StrongPassword123!',

                    'password_confirmation' => 'StrongPassword123!',
                ]
            );

        $response
            ->assertRedirect(
                route('register')
            )
            ->assertSessionHasErrors(
                'terms'
            );

        $this->assertGuest();
    }

    /**
     * لا يمكن تسجيل نفس الجوال مرتين.
     */
    public function test_phone_number_must_be_unique(): void
    {
        User::factory()->create([
            'phone' => '+967771234567',
        ]);

        $response = $this->post(
            route('register.store'),
            [
                'name' => 'مستخدم آخر',

                'phone' => '771234567',

                'password' => 'StrongPassword123!',

                'password_confirmation' => 'StrongPassword123!',

                'terms' => '1',
            ]
        );

        $response->assertSessionHasErrors(
            'phone'
        );
    }

    /**
     * كلمة المرور يجب أن تتوافق مع الحد الأدنى.
     */
    public function test_password_must_respect_minimum_length(): void
    {
        $response = $this->post(
            route('register.store'),
            [
                'name' => 'أحمد محمد',

                'phone' => '771234567',

                'password' => 'short',

                'password_confirmation' => 'short',

                'terms' => '1',
            ]
        );

        $response->assertSessionHasErrors(
            'password'
        );

        $this->assertGuest();
    }
}
