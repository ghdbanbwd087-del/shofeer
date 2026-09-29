<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جداول المستخدمين والمصادقة والجلسات.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | UUID
            |--------------------------------------------------------------------------
            */

            $table->uuid('id')->primary();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('name', 100);

            $table
                ->string('email')
                ->nullable()
                ->unique();

            $table
                ->string('google_id')
                ->nullable()
                ->unique();

            /*
             * مؤقتاً بدون PII Encryption.
             *
             * في المرحلة 7 سيُعاد تصميم تخزين الجوال
             * وإضافة phone_hash وphone_version.
             */
            $table
                ->string('phone', 32)
                ->nullable()
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Verification
            |--------------------------------------------------------------------------
            */

            $table
                ->timestamp('email_verified_at')
                ->nullable();

            $table
                ->timestamp('phone_verified_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Authentication
            |--------------------------------------------------------------------------
            */

            $table
                ->string('password')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Role
            |--------------------------------------------------------------------------
            */

            $table
                ->enum(
                    'role',
                    [
                        'passenger',
                        'driver',
                        'admin',
                    ]
                )
                ->default('passenger')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Account Status
            |--------------------------------------------------------------------------
            */

            $table
                ->boolean('is_active')
                ->default(true)
                ->index();

            $table->rememberToken();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Password Reset
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'password_reset_tokens',
            function (Blueprint $table) {
                $table
                    ->string('email')
                    ->primary();

                $table->string('token');

                $table
                    ->timestamp('created_at')
                    ->nullable();
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Sessions
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'sessions',
            function (Blueprint $table) {
                /*
                 * Session ID ليس Domain ID.
                 * Laravel نفسه يولده ويديره.
                 */
                $table
                    ->string('id')
                    ->primary();

                /*
                 * user_id يجب أن يطابق users.id UUID.
                 */
                $table
                    ->uuid('user_id')
                    ->nullable()
                    ->index();

                $table
                    ->string('ip_address', 45)
                    ->nullable();

                $table
                    ->text('user_agent')
                    ->nullable();

                $table->longText('payload');

                $table
                    ->integer('last_activity')
                    ->index();
            }
        );
    }

    /**
     * حذف الجداول.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');

        Schema::dropIfExists(
            'password_reset_tokens'
        );

        Schema::dropIfExists('users');
    }
};
