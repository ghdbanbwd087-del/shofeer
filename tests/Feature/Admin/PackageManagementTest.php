<?php

namespace Tests\Feature\Admin;

use App\Enums\PackageStatus;
use App\Models\Driver;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_view_packages_page(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $this
            ->actingAs($admin)
            ->get(
                route(
                    'admin.packages.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'admin.packages.index'
            )
            ->assertSee(
                'إدارة البضائع'
            );
    }

    public function test_passenger_cannot_view_admin_packages_page(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs($passenger)
            ->get(
                route(
                    'admin.packages.index'
                )
            )
            ->assertForbidden();
    }

    public function test_admin_can_assign_approved_driver(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $driver =
            Driver::factory()
                ->approved()
                ->create();

        $package =
            Package::query()->create(
                $this->packageData(
                    $passenger
                )
            );

        $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.packages.assign',
                    $package
                ),
                [
                    'driver_id' =>
                        $driver->id,
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(
            'packages',
            [
                'id' => $package->id,
                'assigned_driver_id' =>
                    $driver->id,
                'status' =>
                    PackageStatus::Assigned->value,
            ]
        );
    }

    public function test_admin_can_move_package_to_in_transit_then_delivered(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $driver =
            Driver::factory()
                ->approved()
                ->create();

        $package =
            Package::query()->create([
                ...$this->packageData(
                    $passenger
                ),
                'assigned_driver_id' =>
                    $driver->id,
                'status' =>
                    PackageStatus::Assigned,
                'assigned_at' =>
                    now(),
            ]);

        $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.packages.status',
                    $package
                ),
                [
                    'status' =>
                        'in_transit',
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $package->refresh();

        $this->assertSame(
            PackageStatus::InTransit,
            $package->status
        );

        $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.packages.status',
                    $package
                ),
                [
                    'status' =>
                        'delivered',
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $package->refresh();

        $this->assertSame(
            PackageStatus::Delivered,
            $package->status
        );

        $this->assertNotNull(
            $package->delivered_at
        );
    }

    public function test_cannot_mark_received_package_delivered_directly(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $package =
            Package::query()->create(
                $this->packageData(
                    $passenger
                )
            );

        $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.packages.status',
                    $package
                ),
                [
                    'status' =>
                        'delivered',
                ]
            )
            ->assertSessionHasErrors(
                'status'
            );

        $this->assertDatabaseHas(
            'packages',
            [
                'id' => $package->id,
                'status' =>
                    PackageStatus::Received->value,
            ]
        );
    }

    private function packageData(
        User $user
    ): array {
        return [
            'user_id' => $user->id,
            'tracking_code' => 'SHF-ADMIN12345',
            'description' => 'صندوق',
            'category' => 'عام',
            'weight_kg' => 3,
            'size' => '30x20x20',
            'image_paths' => null,
            'sender_name' => 'Sender',
            'sender_phone' => '+967771111111',
            'sender_whatsapp' => '+967771111111',
            'sender_city' => 'صنعاء',
            'recipient_name' => 'Receiver',
            'recipient_phone' => '+966501111111',
            'recipient_city' => 'جدة',
            'from_city' => 'صنعاء',
            'to_city' => 'جدة',
            'requested_date' =>
                now()
                    ->addDay()
                    ->format(
                        'Y-m-d'
                    ),
            'assigned_driver_id' => null,
            'trip_id' => null,
            'status' =>
                PackageStatus::Received,
        ];
    }
}
