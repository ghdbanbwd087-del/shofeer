<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء مقاعد كل رحلة.
     *
     * المقعد هو نقطة القفل الأساسية لمنع Race Condition.
     */
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table
                ->foreignUuid('trip_id')
                ->constrained('trips')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedSmallInteger(
                'seat_number'
            );

            /*
            |--------------------------------------------------------------------------
            | Seat Type
            |--------------------------------------------------------------------------
            |
            | standard = مقعد عادي
            | female   = مخصص للنساء
            |
            */
            $table
                ->enum(
                    'seat_type',
                    [
                        'standard',
                        'female',
                    ]
                )
                ->default('standard');

            /*
            |--------------------------------------------------------------------------
            | Seat Status
            |--------------------------------------------------------------------------
            |
            | available = متاح
            | held      = محجوز مؤقتاً لمدة 15 دقيقة
            | booked    = محجوز نهائياً
            | locked    = مقفل من الإدارة
            |
            */
            $table
                ->enum(
                    'status',
                    [
                        'available',
                        'held',
                        'booked',
                        'locked',
                    ]
                )
                ->default('available')
                ->index();

            /*
             * المستخدم صاحب الحجز المؤقت.
             */
            $table
                ->foreignUuid('held_by_user_id')
                ->nullable()
                ->constrained(
                    table: 'users'
                )
                ->nullOnDelete();

            /*
             * انتهاء مدة الـ 15 دقيقة.
             */
            $table
                ->timestamp('hold_expires_at')
                ->nullable()
                ->index();

            /*
             * رقم المقعد المجاور.
             *
             * سنستخدمه لقواعد الخصوصية بين
             * الراكب والراكبة.
             */
            $table
                ->unsignedSmallInteger(
                    'adjacent_seat_number'
                )
                ->nullable();

            $table->timestamps();

            /*
             * هذا الـ Unique Constraint مهم جداً.
             *
             * لا يمكن وجود مقعدين بنفس الرقم
             * داخل نفس الرحلة.
             */
            $table->unique([
                'trip_id',
                'seat_number',
            ]);

            $table->index([
                'trip_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
