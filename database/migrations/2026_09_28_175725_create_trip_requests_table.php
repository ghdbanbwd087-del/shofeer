<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول طلبات الرحلات التي يرسلها السائقون.
     */
    public function up(): void
    {
        Schema::create('trip_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /*
            |--------------------------------------------------------------------------
            | Driver
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignUuid('driver_id')
                ->constrained('drivers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Route
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignUuid('from_city_id')
                ->constrained(
                    table: 'cities'
                )
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table
                ->foreignUuid('to_city_id')
                ->constrained(
                    table: 'cities'
                )
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Requested Schedule
            |--------------------------------------------------------------------------
            */

            $table->date('travel_date');

            $table->time('departure_time');

            /*
            |--------------------------------------------------------------------------
            | Requested Capacity
            |--------------------------------------------------------------------------
            */

            $table
                ->unsignedTinyInteger(
                    'requested_seats'
                );

            /*
            |--------------------------------------------------------------------------
            | Driver Notes
            |--------------------------------------------------------------------------
            */

            $table
                ->text('notes')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Review Status
            |--------------------------------------------------------------------------
            */

            $table
                ->enum(
                    'status',
                    [
                        'pending',
                        'approved',
                        'rejected',
                    ]
                )
                ->default('pending')
                ->index();

            $table
                ->text('rejection_reason')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Admin Review
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignUuid('reviewed_by')
                ->nullable()
                ->constrained(
                    table: 'users'
                )
                ->nullOnDelete();

            $table
                ->timestamp('reviewed_at')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'driver_id',
                'status',
            ]);

            $table->index([
                'from_city_id',
                'to_city_id',
                'travel_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'trip_requests'
        );
    }
};
