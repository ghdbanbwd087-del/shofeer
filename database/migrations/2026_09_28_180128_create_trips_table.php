<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول الرحلات الفعلية.
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /*
            |--------------------------------------------------------------------------
            | Driver / Car
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignUuid('driver_id')
                ->constrained('drivers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table
                ->foreignUuid('car_id')
                ->constrained('cars')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Source Request
            |--------------------------------------------------------------------------
            |
            | nullable لأن الإدارة تستطيع إنشاء رحلة مباشرة.
            |
            */

            $table
                ->foreignUuid(
                    'source_trip_request_id'
                )
                ->nullable()
                ->unique()
                ->constrained(
                    table: 'trip_requests'
                )
                ->nullOnDelete();

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
            | Schedule
            |--------------------------------------------------------------------------
            */

            $table->dateTime(
                'departure_at'
            );

            /*
            |--------------------------------------------------------------------------
            | Meeting Points
            |--------------------------------------------------------------------------
            */

            $table->string(
                'meeting_point',
                255
            );

            $table
                ->string(
                    'destination_point',
                    255
                )
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Price
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'price',
                12,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Seats
            |--------------------------------------------------------------------------
            */

            $table
                ->unsignedTinyInteger(
                    'seat_count'
                );

            $table
                ->unsignedTinyInteger(
                    'available_seats'
                );

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table
                ->enum(
                    'status',
                    [
                        'scheduled',
                        'boarding',
                        'in_progress',
                        'completed',
                        'cancelled',
                    ]
                )
                ->default('scheduled')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Publication
            |--------------------------------------------------------------------------
            */

            $table
                ->boolean('is_published')
                ->default(true)
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Notes
            |--------------------------------------------------------------------------
            */

            $table
                ->text('notes')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Created By Admin
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignUuid('created_by')
                ->nullable()
                ->constrained(
                    table: 'users'
                )
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Search Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'from_city_id',
                'to_city_id',
                'departure_at',
            ]);

            $table->index([
                'driver_id',
                'status',
            ]);

            $table->index([
                'status',
                'is_published',
                'departure_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
