<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'driver_locations',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table
                    ->foreignUuid('driver_id')
                    ->constrained('drivers')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table
                    ->foreignUuid('trip_id')
                    ->nullable()
                    ->constrained('trips')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);

                $table
                    ->decimal('accuracy_m', 10, 2)
                    ->nullable();

                $table
                    ->decimal('speed_kmh', 8, 2)
                    ->nullable();

                $table
                    ->unsignedSmallInteger('heading')
                    ->nullable();

                $table
                    ->dateTime('eta_at')
                    ->nullable();

                $table->dateTime('recorded_at');

                $table->timestamps();

                $table->index([
                    'driver_id',
                    'recorded_at',
                ]);

                $table->index([
                    'trip_id',
                    'recorded_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_locations');
    }
};
