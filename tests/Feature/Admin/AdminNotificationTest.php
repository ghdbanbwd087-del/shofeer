<?php

namespace Tests\Feature\Admin;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_view_notifications_page(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $this
            ->actingAs($admin)
            ->get(
                route(
                    'admin.notifications.index'
                )
            )
            ->assertOk()
            ->assertViewIs(
                'admin.notifications.index'
            )
            ->assertSee(
                'الإشعارات'
            );
    }

    public function test_passenger_cannot_view_admin_notifications_page(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $this
            ->actingAs($passenger)
            ->get(
                route(
                    'admin.notifications.index'
                )
            )
            ->assertForbidden();
    }

    public function test_admin_can_send_single_notification(): void
    {
        $admin =
            User::factory()
                ->admin()
                ->create();

        $driver =
            User::factory()
                ->driver()
                ->create();

        $this
            ->actingAs($admin)
            ->post(
                route(
                    'admin.notifications.store'
                ),
                [
                    'mode' =>
                        'single',

                    'user_id' =>
                        $driver->id,

                    'title' =>
                        'تنبيه رحلة',

                    'message' =>
                        'يرجى مراجعة الرحلة الجديدة.',
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $notification =
            AppNotification::query()
                ->firstOrFail();

        $this->assertSame(
            (string) $driver->id,
            (string) $notification->notifiable_id
        );

        $this->assertSame(
            'تنبيه رحلة',
            $notification->data['title']
        );
    }

    public function test_admin_can_broadcast_to_all_other_active_users(): void
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
            User::factory()
                ->driver()
                ->create();

        $inactive =
            User::factory()
                ->passenger()
                ->create([
                    'is_active' => false,
                ]);

        $this
            ->actingAs($admin)
            ->post(
                route(
                    'admin.notifications.store'
                ),
                [
                    'mode' =>
                        'broadcast',

                    'title' =>
                        'إشعار عام',

                    'message' =>
                        'هذه رسالة جماعية.',
                ]
            )
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseCount(
            'notifications',
            2
        );

        foreach (
            [
                $passenger,
                $driver,
            ] as $recipient
        ) {
            $this->assertDatabaseHas(
                'notifications',
                [
                    'notifiable_id' =>
                        $recipient->id,
                ]
            );
        }

        $this->assertDatabaseMissing(
            'notifications',
            [
                'notifiable_id' =>
                    $inactive->id,
            ]
        );

        $this->assertDatabaseMissing(
            'notifications',
            [
                'notifiable_id' =>
                    $admin->id,
            ]
        );
    }
}
