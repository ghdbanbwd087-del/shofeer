<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * مستخدم Google جديد يتم إنشاؤه كPassenger.
     */
    public function test_new_google_user_can_be_created_and_authenticated(): void
    {
        $googleUser = Mockery::mock(
            SocialiteUser::class
        );

        $googleUser
            ->shouldReceive('getId')
            ->once()
            ->andReturn('google-user-123');

        $googleUser
            ->shouldReceive('getEmail')
            ->once()
            ->andReturn(
                'google-user@example.com'
            );

        $googleUser
            ->shouldReceive('getName')
            ->once()
            ->andReturn(
                'Google User'
            );

        $googleUser
            ->shouldReceive('getNickname')
            ->zeroOrMoreTimes()
            ->andReturn(null);

        $provider = Mockery::mock();

        $provider
            ->shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get(
            route('auth.google.callback')
        );

        $user = User::query()
            ->where(
                'google_id',
                'google-user-123'
            )
            ->first();

        $this->assertNotNull($user);

        $this->assertSame(
            'google-user@example.com',
            $user->email
        );

        $this->assertSame(
            UserRole::Passenger,
            $user->role
        );

        $this->assertNull(
            $user->password
        );

        $this->assertAuthenticatedAs(
            $user
        );

        $response->assertRedirect(
            route('dashboard')
        );
    }

    /**
     * إذا كان البريد موجوداً يربط Google بالحساب نفسه.
     */
    public function test_google_can_be_linked_to_existing_user_with_same_email(): void
    {
        $existingUser = User::factory()
            ->passenger()
            ->create([
                'email' => 'existing@example.com',

                'google_id' => null,
            ]);

        $googleUser = Mockery::mock(
            SocialiteUser::class
        );

        $googleUser
            ->shouldReceive('getId')
            ->once()
            ->andReturn(
                'google-existing-123'
            );

        $googleUser
            ->shouldReceive('getEmail')
            ->once()
            ->andReturn(
                'existing@example.com'
            );

        $googleUser
            ->shouldReceive('getName')
            ->once()
            ->andReturn(
                'Existing User'
            );

        $googleUser
            ->shouldReceive('getNickname')
            ->zeroOrMoreTimes()
            ->andReturn(null);

        $provider = Mockery::mock();

        $provider
            ->shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get(
            route('auth.google.callback')
        );

        $existingUser->refresh();

        $this->assertSame(
            'google-existing-123',
            $existingUser->google_id
        );

        $this->assertAuthenticatedAs(
            $existingUser
        );

        $this->assertSame(
            1,
            User::query()->count()
        );

        $response->assertRedirect(
            route('dashboard')
        );
    }

    /**
     * الحساب الموقوف عبر Google لا يسجل دخوله.
     */
    public function test_inactive_google_user_cannot_login(): void
    {
        User::factory()
            ->passenger()
            ->inactive()
            ->create([
                'email' => 'blocked@example.com',

                'google_id' => 'blocked-google-id',
            ]);

        $googleUser = Mockery::mock(
            SocialiteUser::class
        );

        $googleUser
            ->shouldReceive('getId')
            ->once()
            ->andReturn(
                'blocked-google-id'
            );

        $googleUser
            ->shouldReceive('getEmail')
            ->once()
            ->andReturn(
                'blocked@example.com'
            );

        $googleUser
            ->shouldReceive('getName')
            ->once()
            ->andReturn(
                'Blocked User'
            );

        $googleUser
            ->shouldReceive('getNickname')
            ->zeroOrMoreTimes()
            ->andReturn(null);

        $provider = Mockery::mock();

        $provider
            ->shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get(
            route('auth.google.callback')
        );

        $this->assertGuest();

        $response
            ->assertRedirect(
                route('login')
            )
            ->assertSessionHasErrors(
                'google'
            );
    }
}
