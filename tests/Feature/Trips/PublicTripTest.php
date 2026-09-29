<?php

namespace Tests\Feature\Trips;

use App\Enums\TripStatus;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTripTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_upcoming_trip_is_visible_publicly(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $driverUser = User::factory()
            ->driver()
            ->create();

        $driver = Driver::factory()
            ->approved()
            ->create([
                'user_id' => $driverUser->id,
            ]);

        $car = Car::factory()->create([
            'driver_id' => $driver->id,
        ]);

        $from = City::factory()->create([
            'name_ar' => 'مدينة الانطلاق',
        ]);

        $to = City::factory()->create([
            'name_ar' => 'مدينة الوصول',
        ]);

        $trip = Trip::factory()->create([
            'driver_id' => $driver->id,

            'car_id' => $car->id,

            'from_city_id' => $from->id,

            'to_city_id' => $to->id,

            'departure_at' => now()->addDays(3),

            'meeting_point' => 'نقطة تجمع الرحلة المنشورة',

            'status' => TripStatus::Scheduled,

            'is_published' => true,

            'created_by' => $admin->id,
        ]);

        $response = $this->get(
            route('trips.index')
        );

        $response
            ->assertOk()
            ->assertViewIs(
                'trips.index'
            )
            ->assertViewHas(
                'trips',
                function ($trips) use ($trip) {
                    return $trips
                        ->getCollection()
                        ->contains(
                            'id',
                            $trip->id
                        );
                }
            )
            ->assertSee(
                'مدينة الانطلاق'
            )
            ->assertSee(
                'مدينة الوصول'
            );

        $this->get(
            route(
                'trips.show',
                $trip
            )
        )
            ->assertOk()
            ->assertViewIs(
                'trips.show'
            );
    }

    public function test_unpublished_trip_is_not_visible_in_public_listing(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $driverUser = User::factory()
            ->driver()
            ->create();

        $driver = Driver::factory()
            ->approved()
            ->create([
                'user_id' => $driverUser->id,
            ]);

        $car = Car::factory()->create([
            'driver_id' => $driver->id,
        ]);

        $from = City::factory()->create([
            'name_ar' => 'مدينة مخفية أ',
        ]);

        $to = City::factory()->create([
            'name_ar' => 'مدينة مخفية ب',
        ]);

        $trip = Trip::factory()->create([
            'driver_id' => $driver->id,

            'car_id' => $car->id,

            'from_city_id' => $from->id,

            'to_city_id' => $to->id,

            'departure_at' => now()->addDays(2),

            'meeting_point' => 'SECRET-UNPUBLISHED-TRIP',

            'status' => TripStatus::Scheduled,

            'is_published' => false,

            'created_by' => $admin->id,
        ]);

        $response = $this->get(
            route('trips.index')
        );

        $response
            ->assertOk()
            ->assertViewIs(
                'trips.index'
            )
            ->assertViewHas(
                'trips',
                function ($trips) use ($trip) {
                    return ! $trips
                        ->getCollection()
                        ->contains(
                            'id',
                            $trip->id
                        );
                }
            )
            ->assertSee(
                '0'
            );

        /*
         * ملاحظة:
         * أسماء المدن قد تظهر في فلتر البحث
         * حتى لو لم توجد لها رحلة منشورة.
         *
         * لذلك لا نستخدم assertDontSee
         * على اسم المدينة للتحقق من إخفاء الرحلة.
         */
    }

    public function test_unpublished_trip_details_return_404(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $driverUser = User::factory()
            ->driver()
            ->create();

        $driver = Driver::factory()
            ->approved()
            ->create([
                'user_id' => $driverUser->id,
            ]);

        $car = Car::factory()->create([
            'driver_id' => $driver->id,
        ]);

        $from = City::factory()->create();

        $to = City::factory()->create();

        $trip = Trip::factory()->create([
            'driver_id' => $driver->id,

            'car_id' => $car->id,

            'from_city_id' => $from->id,

            'to_city_id' => $to->id,

            'departure_at' => now()->addDays(2),

            'is_published' => false,

            'created_by' => $admin->id,
        ]);

        $this->get(
            route(
                'trips.show',
                $trip
            )
        )->assertNotFound();
    }
}
