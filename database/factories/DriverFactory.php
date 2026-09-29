<?php

namespace Database\Factories;

use App\Enums\DriverStatus;
use App\Enums\UserRole;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    protected $model = Driver::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()
                ->state([
                    'role' => UserRole::Driver,
                ]),

            'national_id' => fake()
                ->unique()
                ->numerify(
                    '##########'
                ),

            'license_number' => fake()
                ->unique()
                ->bothify(
                    'LIC-####-????'
                ),

            /*
             * مسارات تجريبية.
             * لا تحتاج ملفات فعلية عند اختبارات Model.
             */
            'id_image_front' => 'drivers/testing/id-front.jpg',

            'id_image_back' => 'drivers/testing/id-back.jpg',

            'license_image' => 'drivers/testing/license.jpg',

            'license_expiry' => now()
                ->addYears(2)
                ->toDateString(),

            'experience_years' => fake()->numberBetween(
                1,
                20
            ),

            'bio' => fake()->sentence(),

            'status' => DriverStatus::Pending,

            'rejection_reason' => null,

            'verified_at' => null,

            'verified_by' => null,

            'rating' => 0,

            'total_trips' => 0,

            'is_online' => false,
        ];
    }

    /**
     * سائق معتمد.
     */
    public function approved(): static
    {
        return $this->state(
            fn (): array => [
                'status' => DriverStatus::Approved,

                'verified_at' => now(),

                'rejection_reason' => null,
            ]
        );
    }

    /**
     * سائق مرفوض.
     */
    public function rejected(): static
    {
        return $this->state(
            fn (): array => [
                'status' => DriverStatus::Rejected,

                'rejection_reason' => 'بيانات التوثيق غير مكتملة.',
            ]
        );
    }

    /**
     * سائق موقوف.
     */
    public function suspended(): static
    {
        return $this->state(
            fn (): array => [
                'status' => DriverStatus::Suspended,

                'rejection_reason' => 'الحساب موقوف مؤقتاً.',

                'is_online' => false,
            ]
        );
    }
}
