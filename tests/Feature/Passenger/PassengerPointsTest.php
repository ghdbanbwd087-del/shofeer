<?php

namespace Tests\Feature\Passenger;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PassengerPointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_points_page(): void
    {
        $this
            ->get(
                route('dashboard.points')
            )
            ->assertRedirect(
                route('login')
            );
    }

    public function test_passenger_can_view_points_page(): void
    {
        $passenger =
            User::factory()->create([
                'role' => UserRole::Passenger,
            ]);

        $this
            ->actingAs($passenger)
            ->get(
                route('dashboard.points')
            )
            ->assertOk()
            ->assertViewIs(
                'passenger.points.index'
            )
            ->assertSee(
                'نقاطي'
            )
            ->assertSee(
                'النقاط الحالية'
            );
    }

    public function test_admin_cannot_view_passenger_points_page(): void
    {
        $admin =
            User::factory()->create([
                'role' => UserRole::Admin,
            ]);

        $this
            ->actingAs($admin)
            ->get(
                route('dashboard.points')
            )
            ->assertForbidden();
    }

    public function test_earning_points_is_idempotent(): void
    {
        $passenger =
            User::factory()->create([
                'role' => UserRole::Passenger,
            ]);

        $service =
            app(
                PointService::class
            );

        $first =
            $service->earn(
                user: $passenger,
                points: 50,
                idempotencyKey: 'points:test:'.$passenger->id,
                description: 'نقاط اختبار'
            );

        $second =
            $service->earn(
                user: $passenger,
                points: 50,
                idempotencyKey: 'points:test:'.$passenger->id,
                description: 'نقاط اختبار'
            );

        $this->assertSame(
            $first->id,
            $second->id
        );

        $this->assertDatabaseCount(
            'points',
            1
        );

        $this->assertSame(
            50,
            $service->currentBalance(
                $passenger
            )
        );
    }

    public function test_spending_cannot_exceed_current_points(): void
    {
        $passenger =
            User::factory()->create([
                'role' => UserRole::Passenger,
            ]);

        $service =
            app(
                PointService::class
            );

        $service->earn(
            user: $passenger,
            points: 20,
            idempotencyKey: 'points:seed:'.$passenger->id,
            description: 'نقاط أولية'
        );

        $this->expectException(
            ValidationException::class
        );

        $service->spend(
            user: $passenger,
            points: 30,
            idempotencyKey: 'points:spend:'.$passenger->id,
            description: 'استخدام نقاط'
        );
    }
}
