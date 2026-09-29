<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BalanceService
{
    public function balanceFor(User $user): Balance
    {
        return Balance::query()->firstOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'amount' => 0,
            ]
        );
    }

    public function credit(
        User $user,
        float $amount,
        string $idempotencyKey,
        ?string $description = null,
        ?string $sourceType = null,
        ?string $sourceId = null,
        array $metadata = []
    ): BalanceTransaction {
        return $this->apply(
            user: $user,
            type: 'credit',
            amount: $amount,
            idempotencyKey: $idempotencyKey,
            description: $description,
            sourceType: $sourceType,
            sourceId: $sourceId,
            metadata: $metadata,
        );
    }

    public function debit(
        User $user,
        float $amount,
        string $idempotencyKey,
        ?string $description = null,
        ?string $sourceType = null,
        ?string $sourceId = null,
        array $metadata = []
    ): BalanceTransaction {
        return $this->apply(
            user: $user,
            type: 'debit',
            amount: $amount,
            idempotencyKey: $idempotencyKey,
            description: $description,
            sourceType: $sourceType,
            sourceId: $sourceId,
            metadata: $metadata,
        );
    }

    private function apply(
        User $user,
        string $type,
        float $amount,
        string $idempotencyKey,
        ?string $description,
        ?string $sourceType,
        ?string $sourceId,
        array $metadata
    ): BalanceTransaction {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'قيمة حركة الرصيد يجب أن تكون أكبر من صفر.',
            ]);
        }

        if (! in_array($type, ['credit', 'debit'], true)) {
            throw ValidationException::withMessages([
                'type' => 'نوع حركة الرصيد غير صالح.',
            ]);
        }

        if (trim($idempotencyKey) === '') {
            throw ValidationException::withMessages([
                'idempotency_key' => 'مفتاح منع التكرار مطلوب.',
            ]);
        }

        /*
         * ننشئ سجل الرصيد قبل القفل.
         * يوجد UNIQUE على user_id لضمان سجل واحد لكل مستخدم.
         */
        $this->balanceFor($user);

        return DB::transaction(
            function () use (
                $user,
                $type,
                $amount,
                $idempotencyKey,
                $description,
                $sourceType,
                $sourceId,
                $metadata
            ): BalanceTransaction {
                /*
                 * Idempotency:
                 * إعادة نفس العملية ترجع نفس الحركة بدون تغيير الرصيد.
                 */
                $existing =
                    BalanceTransaction::query()
                        ->where(
                            'idempotency_key',
                            $idempotencyKey
                        )
                        ->first();

                if ($existing !== null) {
                    if (
                        (string) $existing->user_id
                        !== (string) $user->id
                    ) {
                        throw ValidationException::withMessages([
                            'idempotency_key' => 'مفتاح العملية مستخدم مسبقاً.',
                        ]);
                    }

                    return $existing;
                }

                $balance =
                    Balance::query()
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                $before =
                    round(
                        (float) $balance->amount,
                        2
                    );

                if (
                    $type === 'debit'
                    && $before < $amount
                ) {
                    throw ValidationException::withMessages([
                        'balance' => 'الرصيد غير كافٍ لإتمام العملية.',
                    ]);
                }

                $after =
                    $type === 'credit'
                        ? round($before + $amount, 2)
                        : round($before - $amount, 2);

                $balance->update([
                    'amount' => $after,
                ]);

                return BalanceTransaction::query()
                    ->create([
                        'balance_id' => $balance->id,
                        'user_id' => $user->id,
                        'type' => $type,
                        'amount' => $amount,
                        'balance_before' => $before,
                        'balance_after' => $after,
                        'description' => $description,
                        'source_type' => $sourceType,
                        'source_id' => $sourceId,
                        'idempotency_key' => $idempotencyKey,
                        'metadata' => $metadata ?: null,
                    ]);
            },
            3
        );
    }
}
