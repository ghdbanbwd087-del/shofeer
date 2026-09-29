<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'monthly_rewards',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table
                    ->foreignUuid(
                        'financial_reward_id'
                    )
                    ->constrained(
                        'financial_rewards'
                    )
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table
                    ->foreignUuid(
                        'user_id'
                    )
                    ->constrained(
                        'users'
                    )
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table
                    ->char(
                        'period_key',
                        7
                    );

                $table
                    ->unsignedInteger(
                        'completed_trips'
                    )
                    ->default(0);

                /*
                 * Snapshot of the reward amount at award time.
                 */
                $table->decimal(
                    'amount',
                    12,
                    2
                );

                $table
                    ->foreignUuid(
                        'balance_transaction_id'
                    )
                    ->nullable()
                    ->unique()
                    ->constrained(
                        'balance_transactions'
                    )
                    ->nullOnDelete();

                $table
                    ->string(
                        'idempotency_key',
                        191
                    )
                    ->unique();

                $table
                    ->dateTime(
                        'awarded_at'
                    )
                    ->nullable();

                $table->timestamps();

                /*
                 * Same reward can be granted to the same user only once
                 * for the same month.
                 */
                $table->unique(
                    [
                        'user_id',
                        'financial_reward_id',
                        'period_key',
                    ],
                    'monthly_rewards_user_reward_period_unique'
                );

                $table->index([
                    'period_key',
                    'awarded_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'monthly_rewards'
        );
    }
};
