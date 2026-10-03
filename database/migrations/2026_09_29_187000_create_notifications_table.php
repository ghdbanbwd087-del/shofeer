<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'notifications',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table->string(
                    'type',
                    191
                );

                $table->string(
                    'notifiable_type',
                    191
                );

                $table->uuid(
                    'notifiable_id'
                );

                $table->json('data');

                $table
                    ->dateTime('read_at')
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'notifiable_type',
                        'notifiable_id',
                        'created_at',
                    ],
                    'notifications_notifiable_created_index'
                );

                $table->index([
                    'read_at',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'notifications'
        );
    }
};
