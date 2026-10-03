<?php

namespace Tests\Feature\Passenger;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassengerDashboardNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_dashboard_shows_latest_notifications_for_passenger(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        app(
            NotificationService::class
        )->sendToUser(
            user: $passenger,
            title: 'تأكيد الرحلة',
            message: 'تم تأكيد رحلتك بنجاح.',
        );

        $this
            ->actingAs($passenger)
            ->get(
                route('dashboard')
            )
            ->assertOk()
            ->assertViewIs(
                'passenger.dashboard.index'
            )
            ->assertSee(
                'آخر إشعاراتي'
            )
            ->assertSee(
                'تأكيد الرحلة'
            )
            ->assertSee(
                'تم تأكيد رحلتك بنجاح.'
            );
    }

    public function test_dashboard_does_not_show_another_users_notification(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $other =
            User::factory()
                ->passenger()
                ->create();

        app(
            NotificationService::class
        )->sendToUser(
            user: $other,
            title: 'رسالة خاصة',
            message: 'هذه الرسالة لمستخدم آخر.',
        );

        $this
            ->actingAs($passenger)
            ->get(
                route('dashboard')
            )
            ->assertOk()
            ->assertDontSee(
                'رسالة خاصة'
            )
            ->assertDontSee(
                'هذه الرسالة لمستخدم آخر.'
            );
    }

    public function test_dashboard_shows_unread_notifications_count(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $service =
            app(
                NotificationService::class
            );

        $service->sendToUser(
            user: $passenger,
            title: 'الأول',
            message: 'إشعار أول.',
        );

        $service->sendToUser(
            user: $passenger,
            title: 'الثاني',
            message: 'إشعار ثان.',
        );

        $this
            ->actingAs($passenger)
            ->get(
                route('dashboard')
            )
            ->assertOk()
            ->assertSee(
                '2 جديد'
            );
    }

    public function test_dashboard_limits_latest_notifications_to_five(): void
    {
        $passenger =
            User::factory()
                ->passenger()
                ->create();

        $service =
            app(
                NotificationService::class
            );

        for ($i = 1; $i <= 6; $i++) {
            $service->sendToUser(
                user: $passenger,
                title: 'إشعار '.$i,
                message: 'رسالة '.$i,
            );
        }

        $response =
            $this
                ->actingAs($passenger)
                ->get(
                    route('dashboard')
                );

        $response
            ->assertOk()
            ->assertSee(
                'إشعار 6'
            )
            ->assertDontSee(
                'إشعار 1'
            );
    }
}
