<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Point extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'points',
        'balance_before',
        'balance_after',
        'description',
        'source_type',
        'source_id',
        'idempotency_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function isEarn(): bool
    {
        return $this->type === 'earn';
    }

    public function isSpend(): bool
    {
        return $this->type === 'spend';
    }
}
