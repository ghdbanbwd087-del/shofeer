<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyReward extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'financial_reward_id',
        'user_id',
        'period_key',
        'completed_trips',
        'amount',
        'balance_transaction_id',
        'idempotency_key',
        'awarded_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_trips' => 'integer',
            'amount' => 'decimal:2',
            'awarded_at' => 'datetime',
        ];
    }

    public function financialReward(): BelongsTo
    {
        return $this->belongsTo(
            FinancialReward::class
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function balanceTransaction(): BelongsTo
    {
        return $this->belongsTo(
            BalanceTransaction::class
        );
    }
}
