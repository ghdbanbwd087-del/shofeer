<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'user_badges',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table
                    ->foreignUuid('user_id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table
                    ->foreignUuid('badge_id')
                    ->constrained('badges')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->dateTime('awarded_at');

                $table->timestamps();

                /*
                 * حماية من Badge Duplication.
                 */
                $table->unique([
                    'user_id',
                    'badge_id',
                ]);

                $table->index([
                    'user_id',
                    'awarded_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
