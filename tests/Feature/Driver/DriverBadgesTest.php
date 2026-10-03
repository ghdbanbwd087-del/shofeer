<?php

namespace Tests\Feature\Driver;

use App\Enums\TripStatus;
use App\Models\Badge;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverBadgesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_approved_driver_can_view_driver_badges_page(): void
    {
        [
            'user' => $driverUser,
        ] = $this->approvedDriver();

        Badge::query()->create([
            'code' =>
                'driver-starter',

            'name' =>
                'سائق البداية',

            'description' =>
                'شارة سائق للاختبار.',

            'audience' =>
                'driver',

            'min_completed_trips' =>
                0,

            'benefits' => [
                'عمولة أقل حسب إعداد الإدارة.',
            ],

            'sort_order' =>
                10,

            'is_active' =>
                true,
        ]);

        $this
            ->actingAs(
                $driverUser
            )
            ->get(
                route(
                    'driver.badges.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'driver.badges.index'
            )
            ->assertSee(
                'شاراتي'
            )
            ->assertSee(
                'سائق البداية'
            )
            ->assertSee(
                'عمولة أقل'
            );
    }

    public function test_passenger_cannot_view_driver_badges_page(): void
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
                    'driver.badges.index'
                )
            )
            ->assertForbidden();
    }

    public function test_passenger_badges_do_not_appear_on_driver_page(): void
    {
        [
            'user' => $driverUser,
        ] = $this->approvedDriver();

        Badge::query()->create([
            'code' =>
                'passenger-only',

            'name' =>
                'شارة راكب فقط',

            'audience' =>
                'passenger',

            'min_completed_trips' =>
                0,

            'benefits' =>
                [],

            'sort_order' =>
                10,

            'is_active' =>
                true,
        ]);

        Badge::query()->create([
            'code' =>
                'driver-only',

            'name' =>
                'شارة سائق فقط',

            'audience' =>
                'driver',

            'min_completed_trips' =>
                0,

            'benefits' =>
                [],

            'sort_order' =>
                10,

            'is_active' =>
                true,
        ]);

        $this
            ->actingAs(
                $driverUser
            )
            ->get(
                route(
                    'driver.badges.index'
                )
            )
            ->assertOk()
            ->assertSee(
                'شارة سائق فقط'
            )
            ->assertDontSee(
                'شارة راكب فقط'
            );
    }

    public function test_completed_driver_trip_advances_driver_badge(): void
    {
        [
            'user' => $driverUser,
            'driver' => $driver,
        ] = $this->approvedDriver();

        Badge::query()->create([
            'code' =>
                'driver-zero',

            'name' =>
                'البداية',

            'audience' =>
                'driver',

            'min_completed_trips' =>
                0,

            'benefits' =>
                [],

            'sort_order' =>
                10,

            'is_active' =>
                true,
        ]);

        $earned =
            Badge::query()->create([
                'code' =>
                    'driver-one',

                'name' =>
                    'بعد رحلة',

                'audience' =>
                    'driver',

                'min_completed_trips' =>
                    1,

                'benefits' => [
                    'ميزة عمولة أقل.',
                ],

                'sort_order' =>
                    20,

                'is_active' =>
                    true,
            ]);

        $this->completedTrip(
            $driver
        );

        $this
            ->actingAs(
                $driverUser
            )
            ->get(
                route(
                    'driver.badges.index'
                )
            )
            ->assertOk()
            ->assertSee(
                'بعد رحلة'
            );

        $this->assertDatabaseHas(
            'user_badges',
            [
                'user_id' =>
                    $driverUser->id,

                'badge_id' =>
                    $earned->id,
            ]
        );
    }

    private function approvedDriver(): array
    {
        $user =
            User::factory()
                ->driver()
                ->create();

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

    private function completedTrip(
        Driver $driver
    ): Trip {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $car =
            Car::factory()
                ->create([
                    'driver_id' =>
                        $driver->id,

                    'seat_count' =>
                        8,
                ]);

        $from =
            City::factory()
                ->create();

        $to =
            City::factory()
                ->create();

        return Trip::query()
            ->create([
                'driver_id' =>
                    $driver->id,

                'car_id' =>
                    $car->id,

                'source_trip_request_id' =>
                    null,

                'from_city_id' =>
                    $from->id,

                'to_city_id' =>
                    $to->id,

                'departure_at' =>
                    now()->subDay(),

                'meeting_point' =>
                    'Meeting Point',

                'destination_point' =>
                    'Destination',

                'price' =>
                    500,

                'seat_count' =>
                    8,

                'available_seats' =>
                    8,

                'status' =>
                    TripStatus::Completed,

                'is_published' =>
                    true,

                'notes' =>
                    null,

                'created_by' =>
                    $admin->id,
            ]);
    }
}
