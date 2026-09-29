<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'payments',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table
                    ->foreignUuid('booking_id')
                    ->unique()
                    ->constrained('bookings')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->enum(
                    'payment_method',
                    [
                        'bank_transfer',
                        'karimi',
                        'flousak',
                        'jawali',
                        'stc_pay',
                    ]
                );

                /*
                 * نسخة من رقم حساب المنصة
                 * المستخدم وقت تقديم الدفع.
                 */
                $table
                    ->string(
                        'destination_account',
                        150
                    )
                    ->nullable();

                $table->decimal(
                    'amount',
                    12,
                    2
                );

                $table->string(
                    'transaction_number',
                    100
                );

                /*
                 * إثبات الدفع محفوظ بشكل خاص
                 * وغير متاح كرابط عام.
                 */
                $table->string(
                    'proof_path',
                    500
                );

                $table
                    ->enum(
                        'status',
                        [
                            'pending',
                            'approved',
                            'rejected',
                        ]
                    )
                    ->default('pending')
                    ->index();

                $table
                    ->text('rejection_reason')
                    ->nullable();

                /*
                 * يمنع إرسال أكثر من عملية
                 * دفع لنفس الحجز.
                 *
                 * payment:booking:<booking-id>
                 */
                $table
                    ->string(
                        'idempotency_key',
                        100
                    )
                    ->unique();

                /*
                 * نستخدم DATETIME بدلاً من TIMESTAMP
                 * لتجنب مشكلة القيم الافتراضية في
                 * بعض إصدارات MySQL / MariaDB.
                 */
                $table->dateTime(
                    'submitted_at'
                );

                $table
                    ->dateTime(
                        'expires_at'
                    )
                    ->index();

                $table
                    ->foreignUuid('reviewed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->dateTime(
                        'reviewed_at'
                    )
                    ->nullable();

                /*
                 * Laravel timestamps.
                 *
                 * هذه Nullable تلقائياً ولذلك
                 * لا تسبب مشكلة expires_at.
                 */
                $table->timestamps();

                /*
                 * رقم العملية لا يتكرر داخل
                 * نفس وسيلة الدفع.
                 */
                $table->unique([
                    'payment_method',
                    'transaction_number',
                ]);

                $table->index([
                    'status',
                    'submitted_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'payments'
        );
    }
};
