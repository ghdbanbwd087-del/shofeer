<?php

namespace Tests\Feature\Driver;

use App\Enums\DriverStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DriverRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_can_submit_verification_request(): void
    {
        Storage::fake('local');

        $user = User::factory()
            ->driver()
            ->create();

        $response = $this
            ->actingAs($user)
            ->post(
                route(
                    'driver.register.store'
                ),
                [
                    'national_id' => '1234567890',

                    'license_number' => 'LIC-12345',

                    'license_expiry' => now()
                        ->addYear()
                        ->toDateString(),

                    'experience_years' => 5,

                    'bio' => 'Experienced driver',

                    'id_image_front' => UploadedFile::fake()
                        ->image('front.jpg'),

                    'id_image_back' => UploadedFile::fake()
                        ->image('back.jpg'),

                    'license_image' => UploadedFile::fake()
                        ->image('license.jpg'),
                ]
            );

        $response->assertRedirect(
            route('driver.register')
        );

        $this->assertDatabaseHas(
            'drivers',
            [
                'user_id' => $user->id,

                'national_id' => '1234567890',

                'status' => DriverStatus::Pending
                    ->value,
            ]
        );

        $this->assertSame(
            DriverStatus::Pending,
            $user
                ->fresh()
                ->driver
                ->status
        );
    }

    public function test_passenger_cannot_submit_driver_verification(): void
    {
        $passenger = User::factory()
            ->passenger()
            ->create();

        $response = $this
            ->actingAs($passenger)
            ->post(
                route(
                    'driver.register.store'
                ),
                []
            );

        $response->assertForbidden();
    }
}
