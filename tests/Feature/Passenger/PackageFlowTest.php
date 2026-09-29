<?php

namespace Tests\Feature\Passenger;

use App\Enums\PackageStatus;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_passenger_can_open_package_create_page(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs($passenger)
            ->get(
                route('packages.create')
            )
            ->assertOk()
            ->assertViewIs(
                'packages.create'
            )
            ->assertSee(
                'أرسل بضاعة'
            );
    }

    public function test_passenger_can_create_package(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $response =
            $this
                ->actingAs($passenger)
                ->post(
                    route('packages.store'),
                    [
                        'description' => 'صندوق ملابس',
                        'category' => 'ملابس',
                        'weight_kg' => 5.5,
                        'size' => '40x30x20',
                        'sender_name' => 'Sender',
                        'sender_phone' => '+967771234567',
                        'sender_whatsapp' => '+967771234567',
                        'sender_city' => 'صنعاء',
                        'recipient_name' => 'Receiver',
                        'recipient_phone' => '+966501234567',
                        'recipient_city' => 'جدة',
                        'from_city' => 'صنعاء',
                        'to_city' => 'جدة',
                        'requested_date' => now()->addDay()->format('Y-m-d'),
                    ]
                );

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $package =
            Package::query()
                ->firstOrFail();

        $this->assertSame(
            (string) $passenger->id,
            (string) $package->user_id
        );

        $this->assertSame(
            PackageStatus::Received,
            $package->status
        );

        $this->assertStringStartsWith(
            'SHF-',
            $package->tracking_code
        );
    }

    public function test_passenger_can_view_my_packages_page(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        Package::query()->create(
            $this->packageData(
                $passenger
            )
        );

        $this
            ->actingAs($passenger)
            ->get(
                route(
                    'dashboard.packages.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'passenger.packages.index'
            )
            ->assertSee(
                'بضاعتي'
            )
            ->assertSee(
                'SHF-TEST123456'
            );
    }

    public function test_other_passenger_cannot_view_package_details(): void
    {
        $owner =
            User::factory()
                ->passenger()
                ->create();

        $other =
            User::factory()
                ->passenger()
                ->create();

        $package =
            Package::query()->create(
                $this->packageData(
                    $owner
                )
            );

        $this
            ->actingAs($other)
            ->get(
                route(
                    'dashboard.packages.show',
                    $package
                )
            )
            ->assertForbidden();
    }

    public function test_public_tracking_does_not_show_private_phone_numbers(): void
    {
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
            ->get(
                route(
                    'packages.track',
                    [
                        'code' =>
                            $package->tracking_code,
                    ]
                )
            )
            ->assertOk()
            ->assertSee(
                $package->tracking_code
            )
            ->assertSee(
                'مستلمة'
            )
            ->assertDontSee(
                '+967771234567'
            )
            ->assertDontSee(
                '+966501234567'
            );
    }

    public function test_owner_can_cancel_received_package(): void
    {
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
            ->actingAs($passenger)
            ->patch(
                route(
                    'dashboard.packages.cancel',
                    $package
                )
            )
            ->assertRedirect();

        $this->assertDatabaseHas(
            'packages',
            [
                'id' => $package->id,
                'status' =>
                    PackageStatus::Cancelled->value,
            ]
        );
    }

    private function packageData(
        User $user
    ): array {
        return [
            'user_id' => $user->id,
            'tracking_code' => 'SHF-TEST123456',
            'description' => 'صندوق ملابس',
            'category' => 'ملابس',
            'weight_kg' => 5.5,
            'size' => '40x30x20',
            'image_paths' => null,
            'sender_name' => 'Sender',
            'sender_phone' => '+967771234567',
            'sender_whatsapp' => '+967771234567',
            'sender_city' => 'صنعاء',
            'recipient_name' => 'Receiver',
            'recipient_phone' => '+966501234567',
            'recipient_city' => 'جدة',
            'from_city' => 'صنعاء',
            'to_city' => 'جدة',
            'requested_date' => now()->addDay()->format('Y-m-d'),
            'assigned_driver_id' => null,
            'trip_id' => null,
            'status' => PackageStatus::Received,
        ];
    }
}
