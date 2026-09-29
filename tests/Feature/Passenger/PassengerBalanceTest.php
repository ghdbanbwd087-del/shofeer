<?php

namespace Tests\Feature\Passenger;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassengerBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_balance_page(): void
    {
        $this
            ->get(route('dashboard.balance'))
            ->assertRedirect(route('login'));
    }

    public function test_passenger_can_view_balance_page(): void
    {
        $passenger = User::factory()->create([
            'role' => UserRole::Passenger,
        ]);

        $this
            ->actingAs($passenger)
            ->get(route('dashboard.balance'))
            ->assertOk()
            ->assertViewIs('passenger.balance.index')
            ->assertSee('رصيدي')
            ->assertSee('الرصيد الحالي');

        $this->assertDatabaseHas(
            'balances',
            [
                'user_id' => $passenger->id,
                'amount' => 0,
            ]
        );
    }

    public function test_admin_cannot_view_passenger_balance_page(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this
            ->actingAs($admin)
            ->get(route('dashboard.balance'))
            ->assertForbidden();
    }

    public function test_credit_is_idempotent(): void
    {
        $passenger = User::factory()->create([
            'role' => UserRole::Passenger,
        ]);

        $service = app(BalanceService::class);

        $first = $service->credit(
            user: $passenger,
            amount: 100,
            idempotencyKey: 'reward:test:'.$passenger->id,
            description: 'مكافأة اختبار'
        );

        $second = $service->credit(
            user: $passenger,
            amount: 100,
            idempotencyKey: 'reward:test:'.$passenger->id,
            description: 'مكافأة اختبار'
        );

        $this->assertSame(
            $first->id,
            $second->id
        );

        $this->assertDatabaseCount(
            'balance_transactions',
            1
        );

        $this->assertDatabaseHas(
            'balances',
            [
                'user_id' => $passenger->id,
                'amount' => 100,
            ]
        );
    }

    public function test_debit_cannot_exceed_available_balance(): void
    {
        $passenger = User::factory()->create([
            'role' => UserRole::Passenger,
        ]);

        $service = app(BalanceService::class);

        $service->credit(
            user: $passenger,
            amount: 50,
            idempotencyKey: 'reward:seed:'.$passenger->id,
            description: 'رصيد أولي'
        );

        $this->expectException(
            \Illuminate\Validation\ValidationException::class
        );

        $service->debit(
            user: $passenger,
            amount: 100,
            idempotencyKey: 'spend:test:'.$passenger->id,
            description: 'خصم اختبار'
        );
    }
}
