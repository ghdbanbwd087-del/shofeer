<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'badges',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table
                    ->string('code', 50)
                    ->unique();

                $table->string('name', 100);

                $table
                    ->text('description')
                    ->nullable();

                $table
                    ->string('icon', 100)
                    ->nullable();

                $table
                    ->unsignedInteger('min_completed_trips')
                    ->default(0)
                    ->index();

                $table
                    ->json('benefits')
                    ->nullable();

                $table
                    ->unsignedSmallInteger('sort_order')
                    ->default(0);

                $table
                    ->boolean('is_active')
                    ->default(true)
                    ->index();

                $table->timestamps();

                $table->index([
                    'is_active',
                    'min_completed_trips',
                    'sort_order',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
