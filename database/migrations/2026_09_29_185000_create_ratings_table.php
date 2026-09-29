<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'ratings',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                /*
                 * One rating per booking.
                 */
                $table
                    ->foreignUuid('booking_id')
                    ->unique()
                    ->constrained('bookings')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table
                    ->foreignUuid('trip_id')
                    ->constrained('trips')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table
                    ->foreignUuid('driver_id')
                    ->constrained('drivers')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table
                    ->foreignUuid('user_id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unsignedTinyInteger(
                    'score'
                );

                $table
                    ->text('comment')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'user_id',
                    'created_at',
                ]);

                $table->index([
                    'driver_id',
                    'created_at',
                ]);

                $table->index([
                    'trip_id',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ratings'
        );
    }
};
