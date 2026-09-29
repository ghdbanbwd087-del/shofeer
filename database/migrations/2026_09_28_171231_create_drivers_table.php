<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء ملفات السائقين.
     */
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignUuid('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            |
            | التوثيق النهائي يطلب تشفير national_id والرخصة
            | وإضافة hashes.
            |
            | لكن PII Encryption مؤجل إلى المرحلة 7،
            | لذلك نخزنهما مؤقتاً كنص عادي.
            |
            */

            $table
                ->string('national_id', 100)
                ->unique();

            $table
                ->string('license_number', 100)
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Documents
            |--------------------------------------------------------------------------
            */

            $table->string(
                'id_image_front'
            );

            $table->string(
                'id_image_back'
            );

            $table->string(
                'license_image'
            );

            /*
            |--------------------------------------------------------------------------
            | Driver Information
            |--------------------------------------------------------------------------
            */

            $table->date(
                'license_expiry'
            );

            $table
                ->unsignedTinyInteger(
                    'experience_years'
                )
                ->default(0);

            $table
                ->text('bio')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Verification Status
            |--------------------------------------------------------------------------
            */

            $table
                ->enum(
                    'status',
                    [
                        'pending',
                        'approved',
                        'rejected',
                        'suspended',
                    ]
                )
                ->default('pending')
                ->index();

            $table
                ->text('rejection_reason')
                ->nullable();

            $table
                ->timestamp('verified_at')
                ->nullable();

            $table
                ->foreignUuid('verified_by')
                ->nullable()
                ->constrained(
                    table: 'users'
                )
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Driver Statistics
            |--------------------------------------------------------------------------
            */

            $table
                ->decimal(
                    'rating',
                    3,
                    2
                )
                ->default(0);

            $table
                ->unsignedInteger(
                    'total_trips'
                )
                ->default(0);

            $table
                ->boolean('is_online')
                ->default(false)
                ->index();

            $table->timestamps();

            $table->index([
                'status',
                'is_online',
            ]);
        });
    }

    /**
     * حذف جدول السائقين.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
