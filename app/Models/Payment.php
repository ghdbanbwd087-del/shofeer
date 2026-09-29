<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentReviewStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'payment_method',
        'destination_account',
        'amount',
        'transaction_number',
        'proof_path',
        'status',
        'rejection_reason',
        'idempotency_key',
        'submitted_at',
        'expires_at',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'proof_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,

            'amount' => 'decimal:2',

            'status' => PaymentReviewStatus::class,

            'submitted_at' => 'datetime',

            'expires_at' => 'datetime',

            'reviewed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(
            Booking::class
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function isPending(): bool
    {
        return $this->status ===
            PaymentReviewStatus::Pending;
    }
}
