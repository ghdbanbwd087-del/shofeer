<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Up
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create(
            'refunds',
            function (Blueprint $table): void {
                $table->uuid('id')
                    ->primary();

                /*
                |--------------------------------------------------------------------------
                | Relations
                |--------------------------------------------------------------------------
                */

                $table->foreignUuid('payment_id')
                    ->constrained('payments')
                    ->cascadeOnDelete();

                $table->foreignUuid('booking_id')
                    ->constrained('bookings')
                    ->cascadeOnDelete();

                $table->foreignUuid('requested_by')
                    ->constrained('users')
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Refund Data
                |--------------------------------------------------------------------------
                */

                $table->decimal(
                    'amount',
                    12,
                    2
                );

                $table->text('reason')
                    ->nullable();

                $table->enum(
                    'status',
                    [
                        'pending',
                        'processed',
                        'rejected',
                    ]
                )->default(
                    'pending'
                );

                /*
                |--------------------------------------------------------------------------
                | Idempotency
                |--------------------------------------------------------------------------
                |
                | يمنع تنفيذ نفس Refund مرتين.
                |
                */

                $table->string(
                    'refund_idempotency_key',
                    191
                )->unique();

                /*
                |--------------------------------------------------------------------------
                | Admin Review
                |--------------------------------------------------------------------------
                */

                $table->foreignUuid('reviewed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->text(
                    'rejection_reason'
                )->nullable();

                $table->string(
                    'admin_reference',
                    191
                )->nullable();

                $table->dateTime(
                    'reviewed_at'
                )->nullable();

                $table->dateTime(
                    'processed_at'
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Query Indexes
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'payment_id',
                    'status',
                ]);

                $table->index([
                    'booking_id',
                    'status',
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | One Pending Refund Per Payment
        |--------------------------------------------------------------------------
        |
        | SQLite:
        | Partial unique index.
        |
        | MySQL:
        | Generated column لأن UNIQUE يسمح بأكثر من NULL.
        |
        */

        $driver =
            DB::connection()
                ->getDriverName();

        if (
            $driver === 'sqlite'
        ) {
            DB::statement(
                "
                CREATE UNIQUE INDEX
                    refunds_one_pending_per_payment_unique
                ON refunds(payment_id)
                WHERE status = 'pending'
                "
            );

            return;
        }

        if (
            $driver === 'mysql'
        ) {
            DB::statement(
                "
                ALTER TABLE refunds
                ADD COLUMN pending_payment_id CHAR(36)
                GENERATED ALWAYS AS (
                    CASE
                        WHEN status = 'pending'
                        THEN payment_id
                        ELSE NULL
                    END
                ) STORED
                "
            );

            DB::statement(
                '
                CREATE UNIQUE INDEX
                    refunds_one_pending_per_payment_unique
                ON refunds(pending_payment_id)
                '
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Down
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::dropIfExists(
            'refunds'
        );
    }
};
