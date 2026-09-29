<?php

namespace Database\Seeders;

use App\Enums\DriverStatus;
use App\Enums\TripStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

class TravelDemoSeeder extends Seeder
{
    /**
     * بيانات عرض محلية للمرحلة 2.
     */
    public function run(): void
    {
        if (
            ! app()->environment(
                'local',
                'testing'
            )
        ) {
            return;
        }

        $admin = User::query()
            ->where(
                'role',
                UserRole::Admin->value
            )
            ->first();

        /*
         * حساب السائق الذي أنشأناه في المرحلة 1.
         */
        $driverUser = User::query()
            ->where(
                'phone',
                '+967772222222'
            )
            ->first();

        if (
            $admin === null ||
            $driverUser === null
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Driver Profile
        |--------------------------------------------------------------------------
        */

        $driver = Driver::query()
            ->updateOrCreate(
                [
                    'user_id' => $driverUser->id,
                ],
                [
                    'national_id' => 'DEV-NATIONAL-0001',

                    'license_number' => 'DEV-LICENSE-0001',

                    'id_image_front' => 'drivers/demo/id-front.jpg',

                    'id_image_back' => 'drivers/demo/id-back.jpg',

                    'license_image' => 'drivers/demo/license.jpg',

                    'license_expiry' => now()
                        ->addYears(2)
                        ->toDateString(),

                    'experience_years' => 8,

                    'bio' => 'سائق تجريبي لاختبار منصة SHOFEER.',

                    'status' => DriverStatus::Approved,

                    'rejection_reason' => null,

                    'verified_at' => now(),

                    'verified_by' => $admin->id,

                    'rating' => 4.80,

                    'total_trips' => 0,

                    'is_online' => false,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Car
        |--------------------------------------------------------------------------
        */

        $car = Car::query()
            ->updateOrCreate(
                [
                    'plate_number' => 'SHOFEER-001',
                ],
                [
                    'driver_id' => $driver->id,

                    'make' => 'Toyota',

                    'model' => 'Hiace',

                    'year' => 2024,

                    'color' => 'أبيض',

                    'seat_count' => 12,

                    'type' => 'van',

                    'features' => [
                        'مكيف',
                        'USB',
                        'مقاعد مريحة',
                    ],

                    'image' => null,

                    'registration_image' => null,

                    'is_active' => true,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Cities
        |--------------------------------------------------------------------------
        */

        $sanaa = City::query()
            ->where(
                'name_ar',
                'صنعاء'
            )
            ->first();

        $riyadh = City::query()
            ->where(
                'name_ar',
                'الرياض'
            )
            ->first();

        $aden = City::query()
            ->where(
                'name_ar',
                'عدن'
            )
            ->first();

        $jeddah = City::query()
            ->where(
                'name_ar',
                'جدة'
            )
            ->first();

        if (
            $sanaa === null ||
            $riyadh === null ||
            $aden === null ||
            $jeddah === null
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Demo Trips
        |--------------------------------------------------------------------------
        */

        $demoTrips = [
            [
                'from' => $sanaa,

                'to' => $riyadh,

                'days' => 2,

                'hour' => 7,

                'price' => 300,
            ],

            [
                'from' => $aden,

                'to' => $jeddah,

                'days' => 4,

                'hour' => 9,

                'price' => 280,
            ],

            [
                'from' => $sanaa,

                'to' => $jeddah,

                'days' => 6,

                'hour' => 6,

                'price' => 320,
            ],
        ];

        foreach (
            $demoTrips as $index => $demo
        ) {
            Trip::query()->updateOrCreate(
                [
                    'driver_id' => $driver->id,

                    'car_id' => $car->id,

                    'from_city_id' => $demo['from']->id,

                    'to_city_id' => $demo['to']->id,

                    'notes' => 'DEMO-TRIP-'.
                        ($index + 1),
                ],
                [
                    'source_trip_request_id' => null,

                    'departure_at' => now()
                        ->addDays(
                            $demo['days']
                        )
                        ->setTime(
                            $demo['hour'],
                            0
                        ),

                    'meeting_point' => 'المحطة الرئيسية',

                    'destination_point' => 'المحطة الرئيسية',

                    'price' => $demo['price'],

                    'seat_count' => 12,

                    'available_seats' => 12,

                    'status' => TripStatus::Scheduled,

                    'is_published' => true,

                    'created_by' => $admin->id,
                ]
            );
        }
    }
}
