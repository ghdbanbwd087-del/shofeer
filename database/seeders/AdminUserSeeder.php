<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * إنشاء حساب Admin محلي للتطوير فقط.
     */
    public function run(): void
    {
        /*
         * لا ننشئ كلمة مرور تجريبية تلقائياً في الإنتاج.
         */
        if (
            ! app()->environment(
                'local',
                'testing'
            )
        ) {
            return;
        }

        User::query()->updateOrCreate(
            [
                'phone' => '+967770000001',
            ],
            [
                'name' => 'SHOFEER Admin',

                'email' => 'admin@shofeer.local',

                'google_id' => null,

                'password' => 'AdminPassword123!',

                'role' => UserRole::Admin,

                'is_active' => true,

                'phone_verified_at' => now(),
            ]
        );
    }
}
