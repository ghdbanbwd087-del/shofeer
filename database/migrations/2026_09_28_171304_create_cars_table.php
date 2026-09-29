<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء سيارات السائقين.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
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
            | Vehicle Identity
            |--------------------------------------------------------------------------
            */

            $table->string(
                'make',
                100
            );

            $table->string(
                'model',
                100
            );

            $table
                ->unsignedSmallInteger(
                    'year'
                );

            $table
                ->string(
                    'plate_number',
                    50
                )
                ->unique();

            $table
                ->string(
                    'color',
                    50
                )
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Capacity
            |--------------------------------------------------------------------------
            */

            $table
                ->unsignedTinyInteger(
                    'seat_count'
                );

            /*
            |--------------------------------------------------------------------------
            | Vehicle Details
            |--------------------------------------------------------------------------
            */

            $table
                ->string(
                    'type',
                    50
                )
                ->nullable();

            $table
                ->json('features')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Vehicle Documents / Image
            |--------------------------------------------------------------------------
            */

            $table
                ->string('image')
                ->nullable();

            $table
                ->string(
                    'registration_image'
                )
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | State
            |--------------------------------------------------------------------------
            */

            $table
                ->boolean('is_active')
                ->default(true)
                ->index();

            $table->timestamps();

            $table->index([
                'driver_id',
                'is_active',
            ]);
        });
    }

    /**
     * حذف السيارات.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
