<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'balance_transactions',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table
                    ->foreignUuid('balance_id')
                    ->constrained('balances')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table
                    ->foreignUuid('user_id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->enum(
                    'type',
                    [
                        'credit',
                        'debit',
                    ]
                );

                $table->decimal(
                    'amount',
                    14,
                    2
                );

                $table->decimal(
                    'balance_before',
                    14,
                    2
                );

                $table->decimal(
                    'balance_after',
                    14,
                    2
                );

                $table
                    ->string(
                        'description',
                        255
                    )
                    ->nullable();

                $table
                    ->string(
                        'source_type',
                        100
                    )
                    ->nullable();

                $table
                    ->uuid('source_id')
                    ->nullable();

                /*
                 * حماية Double Credit / Double Debit.
                 * يجب أن يكون المفتاح مبنياً على العملية التجارية.
                 */
                $table
                    ->string(
                        'idempotency_key',
                        191
                    )
                    ->unique();

                $table
                    ->json('metadata')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'user_id',
                    'created_at',
                ]);

                $table->index([
                    'balance_id',
                    'created_at',
                ]);

                $table->index([
                    'source_type',
                    'source_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'balance_transactions'
        );
    }
};
