<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed قاعدة بيانات التطوير.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Cities
        |--------------------------------------------------------------------------
        */

        $this->call(
            CitySeeder::class
        );

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        $this->call(
            AdminUserSeeder::class
        );

        /*
         * بقية البيانات التجريبية محلية فقط.
         */
        if (
            ! app()->environment(
                'local',
                'testing'
            )
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Passenger
        |--------------------------------------------------------------------------
        */

        User::query()->updateOrCreate(
            [
                'phone' => '+967771111111',
            ],
            [
                'name' => 'Test Passenger',

                'email' => 'passenger@shofeer.local',

                'password' => 'Passenger123!',

                'role' => UserRole::Passenger,

                'is_active' => true,

                'phone_verified_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Driver User
        |--------------------------------------------------------------------------
        */

        User::query()->updateOrCreate(
            [
                'phone' => '+967772222222',
            ],
            [
                'name' => 'Test Driver',

                'email' => 'driver@shofeer.local',

                'password' => 'DriverPassword123!',

                'role' => UserRole::Driver,

                'is_active' => true,

                'phone_verified_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Demo Driver / Car / Trips
        |--------------------------------------------------------------------------
        */

        $this->call(
            TravelDemoSeeder::class
        );
    }
}
