<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'audit_logs',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table->foreignUuid('actor_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('event', 120)->index();

                $table->string('auditable_type', 191);
                $table->uuid('auditable_id');

                $table->json('metadata')->nullable();
                $table->dateTime('created_at')->index();

                $table->index([
                    'auditable_type',
                    'auditable_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
