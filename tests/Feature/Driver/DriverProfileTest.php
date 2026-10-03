<?php

namespace Tests\Feature\Driver;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DriverProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_approved_driver_can_view_profile(): void
    {
        [
            'user' =>
                $driverUser,

            'driver' =>
                $driver,
        ] = $this->approvedDriver();

        $this
            ->actingAs(
                $driverUser
            )
            ->get(
                route(
                    'driver.profile.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'driver.profile.index'
            )
            ->assertSee(
                'ملفي'
            )
            ->assertSee(
                $driverUser->name
            )
            ->assertSee(
                $driver->status->label()
            );
    }

    public function test_passenger_cannot_view_driver_profile(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs(
                $passenger
            )
            ->get(
                route(
                    'driver.profile.index'
                )
            )
            ->assertForbidden();
    }

    public function test_driver_can_change_password_with_correct_current_password(): void
    {
        [
            'user' =>
                $driverUser,
        ] = $this->approvedDriver(
            password:
                'OldDriverPassword123!'
        );

        $newPassword =
            'NewDriverPassword123!';

        $this
            ->actingAs(
                $driverUser
            )
            ->patch(
                route(
                    'driver.profile.password.update'
                ),
                [
                    'current_password' =>
                        'OldDriverPassword123!',

                    'password' =>
                        $newPassword,

                    'password_confirmation' =>
                        $newPassword,
                ]
            )
            ->assertRedirect()
            ->assertSessionHas(
                'success'
            );

        $this->assertTrue(
            Hash::check(
                $newPassword,
                $driverUser
                    ->fresh()
                    ->password
            )
        );
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        [
            'user' =>
                $driverUser,
        ] = $this->approvedDriver(
            password:
                'OldDriverPassword123!'
        );

        $this
            ->actingAs(
                $driverUser
            )
            ->from(
                route(
                    'driver.profile.index'
                )
            )
            ->patch(
                route(
                    'driver.profile.password.update'
                ),
                [
                    'current_password' =>
                        'WrongPassword123!',

                    'password' =>
                        'NewDriverPassword123!',

                    'password_confirmation' =>
                        'NewDriverPassword123!',
                ]
            )
            ->assertRedirect(
                route(
                    'driver.profile.index'
                )
            )
            ->assertSessionHasErrors(
                'current_password'
            );

        $this->assertTrue(
            Hash::check(
                'OldDriverPassword123!',
                $driverUser
                    ->fresh()
                    ->password
            )
        );
    }

    public function test_new_password_must_respect_minimum_length(): void
    {
        [
            'user' =>
                $driverUser,
        ] = $this->approvedDriver(
            password:
                'OldDriverPassword123!'
        );

        $this
            ->actingAs(
                $driverUser
            )
            ->from(
                route(
                    'driver.profile.index'
                )
            )
            ->patch(
                route(
                    'driver.profile.password.update'
                ),
                [
                    'current_password' =>
                        'OldDriverPassword123!',

                    'password' =>
                        'Short123!',

                    'password_confirmation' =>
                        'Short123!',
                ]
            )
            ->assertRedirect(
                route(
                    'driver.profile.index'
                )
            )
            ->assertSessionHasErrors(
                'password'
            );
    }

    private function approvedDriver(
        string $password =
            'DriverPassword123!'
    ): array {
        $user =
            User::factory()
                ->driver()
                ->create([
                    'password' =>
                        Hash::make(
                            $password
                        ),
                ]);

        $driver =
            Driver::factory()
                ->approved()
                ->create([
                    'user_id' =>
                        $user->id,
                ]);

        return [
            'user' =>
                $user,

            'driver' =>
                $driver,
        ];
    }
}
