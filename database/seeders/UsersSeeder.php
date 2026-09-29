<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->createOrUpdateUser(
            name: 'SHOFEER Admin',
            email: 'admin@shofeer.local',
            phone: '+967770000001',
            role: 'admin',
            password: 'AdminPassword123!'
        );

        $this->createOrUpdateUser(
            name: 'SHOFEER Passenger',
            email: 'passenger@shofeer.local',
            phone: '+967771111111',
            role: 'passenger',
            password: 'Passenger123!'
        );

        $this->createOrUpdateUser(
            name: 'SHOFEER Driver',
            email: 'driver@shofeer.local',
            phone: '+967772222222',
            role: 'driver',
            password: 'DriverPassword123!'
        );
    }

    private function createOrUpdateUser(
        string $name,
        string $email,
        string $phone,
        string $role,
        string $password
    ): void {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user) {
            $user = new User();
        }

        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'password' => Hash::make($password),
            'is_active' => true,
            'phone_verified_at' => now(),
        ]);

        $user->save();
    }
}
