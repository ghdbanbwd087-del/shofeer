<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'packages',
            function (Blueprint $table): void {
                $table->uuid('id')->primary();

                $table
                    ->foreignUuid('user_id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table
                    ->string(
                        'tracking_code',
                        30
                    )
                    ->unique();

                /*
                 * Package details.
                 */
                $table->text(
                    'description'
                );

                $table->string(
                    'category',
                    100
                );

                $table->decimal(
                    'weight_kg',
                    8,
                    2
                );

                $table->string(
                    'size',
                    100
                );

                $table
                    ->json(
                        'image_paths'
                    )
                    ->nullable();

                /*
                 * Sender.
                 *
                 * Full PII encryption remains part of the advanced
                 * security stage, matching the current project approach.
                 */
                $table->string(
                    'sender_name',
                    100
                );

                $table->string(
                    'sender_phone',
                    32
                );

                $table
                    ->string(
                        'sender_whatsapp',
                        32
                    )
                    ->nullable();

                $table->string(
                    'sender_city',
                    100
                );

                /*
                 * Recipient.
                 */
                $table->string(
                    'recipient_name',
                    100
                );

                $table->string(
                    'recipient_phone',
                    32
                );

                $table->string(
                    'recipient_city',
                    100
                );

                /*
                 * Requested route.
                 */
                $table->string(
                    'from_city',
                    100
                );

                $table->string(
                    'to_city',
                    100
                );

                $table->date(
                    'requested_date'
                );

                /*
                 * Assignment is completed in Stage 5.5B.
                 */
                $table
                    ->foreignUuid(
                        'assigned_driver_id'
                    )
                    ->nullable()
                    ->constrained(
                        'drivers'
                    )
                    ->nullOnDelete();

                $table
                    ->foreignUuid(
                        'trip_id'
                    )
                    ->nullable()
                    ->constrained(
                        'trips'
                    )
                    ->nullOnDelete();

                $table->enum(
                    'status',
                    [
                        'received',
                        'assigned',
                        'in_transit',
                        'delivered',
                        'cancelled',
                    ]
                )
                    ->default(
                        'received'
                    )
                    ->index();

                $table
                    ->text(
                        'cancel_reason'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'assigned_at'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'in_transit_at'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'delivered_at'
                    )
                    ->nullable();

                $table
                    ->dateTime(
                        'cancelled_at'
                    )
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'user_id',
                    'status',
                    'created_at',
                ]);

                $table->index([
                    'from_city',
                    'to_city',
                    'requested_date',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'packages'
        );
    }
};
