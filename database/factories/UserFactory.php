<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Model المرتبط بالـFactory.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * كلمة المرور الافتراضية للاختبارات والتطوير.
     */
    protected static ?string $password;

    /**
     * إنشاء مستخدم افتراضي.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),

            'email' => fake()
                ->unique()
                ->safeEmail(),

            'google_id' => null,

            /*
             * رقم يمني دولي صالح للاختبارات.
             */
            'phone' => fake()
                ->unique()
                ->numerify('+9677########'),

            'email_verified_at' => now(),

            'phone_verified_at' => now(),

            'password' => static::$password ??= Hash::make(
                'Password123!'
            ),

            'role' => UserRole::Passenger,

            'is_active' => true,

            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Passenger.
     */
    public function passenger(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'role' => UserRole::Passenger,
            ]
        );
    }

    /**
     * Driver.
     */
    public function driver(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'role' => UserRole::Driver,
            ]
        );
    }

    /**
     * Admin.
     */
    public function admin(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'role' => UserRole::Admin,
            ]
        );
    }

    /**
     * مستخدم موقوف.
     */
    public function inactive(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'is_active' => false,
            ]
        );
    }

    /**
     * مستخدم غير موثق البريد.
     */
    public function unverified(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'email_verified_at' => null,
            ]
        );
    }

    /**
     * مستخدم مسجل عبر Google.
     */
    public function google(): static
    {
        return $this->state(
            fn (array $attributes): array => [
                'google_id' => fake()->unique()->numerify(
                    'google-############'
                ),

                'phone' => null,

                'password' => null,
            ]
        );
    }
}
