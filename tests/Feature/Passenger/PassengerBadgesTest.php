<?php

namespace Tests\Feature\Passenger;

use App\Enums\UserRole;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassengerBadgesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_passenger_badges_page(): void
    {
        $this
            ->get(route('dashboard.badges'))
            ->assertRedirect(route('login'));
    }

    public function test_passenger_can_view_badges_page_and_get_eligible_badge(): void
    {
        $passenger = User::factory()->create([
            'role' => UserRole::Passenger,
        ]);

        $starter = Badge::query()->create([
            'code' => 'starter',
            'name' => 'مسافر جديد',
            'description' => 'بداية رحلتك مع شوفير.',
            'min_completed_trips' => 0,
            'benefits' => [
                'متابعة تقدمك.',
            ],
            'sort_order' => 10,
            'is_active' => true,
        ]);

        Badge::query()->create([
            'code' => 'active',
            'name' => 'مسافر نشط',
            'description' => 'بعد خمس رحلات.',
            'min_completed_trips' => 5,
            'benefits' => [],
            'sort_order' => 20,
            'is_active' => true,
        ]);

        $this
            ->actingAs($passenger)
            ->get(route('dashboard.badges'))
            ->assertOk()
            ->assertViewIs('passenger.badges.index')
            ->assertSee('شاراتي')
            ->assertSee('مسافر جديد')
            ->assertSee('مسافر نشط');

        $this->assertDatabaseHas(
            'user_badges',
            [
                'user_id' => $passenger->id,
                'badge_id' => $starter->id,
            ]
        );
    }

    public function test_admin_cannot_view_passenger_badges_page(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this
            ->actingAs($admin)
            ->get(route('dashboard.badges'))
            ->assertForbidden();
    }

    public function test_same_badge_is_not_awarded_twice(): void
    {
        $passenger = User::factory()->create([
            'role' => UserRole::Passenger,
        ]);

        $badge = Badge::query()->create([
            'code' => 'starter',
            'name' => 'مسافر جديد',
            'min_completed_trips' => 0,
            'benefits' => [],
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $this
            ->actingAs($passenger)
            ->get(route('dashboard.badges'))
            ->assertOk();

        $this
            ->actingAs($passenger)
            ->get(route('dashboard.badges'))
            ->assertOk();

        $this->assertDatabaseCount(
            'user_badges',
            1
        );

        $this->assertDatabaseHas(
            'user_badges',
            [
                'user_id' => $passenger->id,
                'badge_id' => $badge->id,
            ]
        );
    }
}
