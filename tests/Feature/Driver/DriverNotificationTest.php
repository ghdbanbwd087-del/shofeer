<?php

namespace Tests\Feature\Driver;

use App\Models\AppNotification;
use App\Models\Driver;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_approved_driver_can_view_notifications(): void
    {
        [
            'user' => $driverUser,
        ] = $this->approvedDriver();

        app(
            NotificationService::class
        )->sendToUser(
            user: $driverUser,
            title: 'رحلة جديدة',
            message: 'تم تعيين رحلة جديدة لك.',
        );

        $this
            ->actingAs($driverUser)
            ->get(
                route(
                    'driver.notifications.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'driver.notifications.index'
            )
            ->assertSee(
                'رحلة جديدة'
            )
            ->assertSee(
                'تم تعيين رحلة جديدة لك.'
            );
    }

    public function test_passenger_cannot_view_driver_notifications(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs($passenger)
            ->get(
                route(
                    'driver.notifications.index'
                )
            )
            ->assertForbidden();
    }

    public function test_driver_can_mark_own_notification_as_read(): void
    {
        [
            'user' => $driverUser,
        ] = $this->approvedDriver();

        $notification =
            app(
                NotificationService::class
            )->sendToUser(
                user: $driverUser,
                title: 'تنبيه',
                message: 'راجع التفاصيل.',
            );

        $this
            ->actingAs($driverUser)
            ->patch(
                route(
                    'driver.notifications.read',
                    $notification
                )
            )
            ->assertRedirect();

        $this->assertNotNull(
            $notification
                ->fresh()
                ->read_at
        );
    }

    public function test_driver_cannot_mark_another_users_notification_as_read(): void
    {
        [
            'user' => $driverUser,
        ] = $this->approvedDriver();

        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $notification =
            app(
                NotificationService::class
            )->sendToUser(
                user: $passenger,
                title: 'خاص',
                message: 'رسالة خاصة.',
            );

        $this
            ->actingAs($driverUser)
            ->patch(
                route(
                    'driver.notifications.read',
                    $notification
                )
            )
            ->assertSessionHasErrors(
                'notification'
            );

        $this->assertNull(
            $notification
                ->fresh()
                ->read_at
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
            'user' => $user,
            'driver' => $driver,
        ];
    }
}
