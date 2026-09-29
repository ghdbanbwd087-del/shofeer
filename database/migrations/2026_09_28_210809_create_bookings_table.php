<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول الحجوزات.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table
                ->foreignUuid('trip_id')
                ->constrained('trips')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table
                ->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
             * كود الحجز المرئي للمستخدم.
             */
            $table
                ->string(
                    'booking_code',
                    30
                )
                ->unique();

            $table->unsignedSmallInteger(
                'seat_number'
            );

            /*
            |--------------------------------------------------------------------------
            | Passenger
            |--------------------------------------------------------------------------
            */

            $table->string(
                'passenger_name',
                100
            );

            $table->enum(
                'passenger_gender',
                [
                    'male',
                    'female',
                ]
            );

            /*
             * التشفير الكامل لهذه البيانات
             * مؤجل إلى مرحلة Advanced Security.
             */
            $table
                ->string(
                    'passenger_phone',
                    32
                )
                ->nullable();

            $table
                ->char(
                    'passenger_phone_hash',
                    64
                )
                ->nullable()
                ->index();

            $table
                ->string(
                    'passenger_whatsapp',
                    32
                )
                ->nullable();

            $table
                ->string(
                    'passenger_id',
                    100
                )
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Financial Snapshot
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'price',
                12,
                2
            );

            $table
                ->decimal(
                    'commission',
                    12,
                    2
                )
                ->default(0);

            $table
                ->decimal(
                    'paid_amount',
                    12,
                    2
                )
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            $table
                ->enum(
                    'payment_status',
                    [
                        'unpaid',
                        'pending',
                        'paid',
                        'failed',
                        'refunded',
                    ]
                )
                ->default('unpaid')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Booking Status
            |--------------------------------------------------------------------------
            */

            $table
                ->enum(
                    'status',
                    [
                        'held',
                        'pending_payment',
                        'confirmed',
                        'cancelled',
                        'expired',
                    ]
                )
                ->default('held')
                ->index();

            $table
                ->text('cancel_reason')
                ->nullable();

            /*
             * يمنع تكرار نفس عملية الحجز.
             */
            $table
                ->string(
                    'idempotency_key',
                    64
                )
                ->unique();

            $table
                ->timestamp('confirmed_at')
                ->nullable();

            $table
                ->timestamp('cancelled_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'trip_id',
                'seat_number',
            ]);

            $table->index([
                'user_id',
                'status',
            ]);

            $table->index([
                'trip_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'bookings'
        );
    }
};
