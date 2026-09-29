<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'points',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table
                    ->foreignUuid('user_id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->enum(
                    'type',
                    [
                        'earn',
                        'spend',
                    ]
                );

                $table->unsignedInteger(
                    'points'
                );

                $table->unsignedBigInteger(
                    'balance_before'
                );

                $table->unsignedBigInteger(
                    'balance_after'
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
                    ->string(
                        'source_id',
                        100
                    )
                    ->nullable();

                /*
                 * Prevent duplicate earn/spend operations.
                 * Key must be derived from the business operation.
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
                    'source_type',
                    'source_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'points'
        );
    }
};
