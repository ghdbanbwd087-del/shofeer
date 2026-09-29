<?php

namespace App\Services;

use App\Models\Point;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PointService
{
    public function currentBalance(User $user): int
    {
        return (int) (
            Point::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->latest(
                    'created_at'
                )
                ->latest(
                    'id'
                )
                ->value(
                    'balance_after'
                )
            ?? 0
        );
    }

    public function earn(
        User $user,
        int $points,
        string $idempotencyKey,
        ?string $description = null,
        ?string $sourceType = null,
        ?string $sourceId = null,
        array $metadata = []
    ): Point {
        return $this->apply(
            user: $user,
            type: 'earn',
            points: $points,
            idempotencyKey: $idempotencyKey,
            description: $description,
            sourceType: $sourceType,
            sourceId: $sourceId,
            metadata: $metadata,
        );
    }

    public function spend(
        User $user,
        int $points,
        string $idempotencyKey,
        ?string $description = null,
        ?string $sourceType = null,
        ?string $sourceId = null,
        array $metadata = []
    ): Point {
        return $this->apply(
            user: $user,
            type: 'spend',
            points: $points,
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
        int $points,
        string $idempotencyKey,
        ?string $description,
        ?string $sourceType,
        ?string $sourceId,
        array $metadata
    ): Point {
        if ($points <= 0) {
            throw ValidationException::withMessages([
                'points' => 'عدد النقاط يجب أن يكون أكبر من صفر.',
            ]);
        }

        if (! in_array($type, ['earn', 'spend'], true)) {
            throw ValidationException::withMessages([
                'type' => 'نوع حركة النقاط غير صالح.',
            ]);
        }

        if (trim($idempotencyKey) === '') {
            throw ValidationException::withMessages([
                'idempotency_key' => 'مفتاح منع التكرار مطلوب.',
            ]);
        }

        return DB::transaction(
            function () use (
                $user,
                $type,
                $points,
                $idempotencyKey,
                $description,
                $sourceType,
                $sourceId,
                $metadata
            ): Point {
                /*
                 * Lock user row so all point operations for the same user
                 * are serialized and cannot race each other.
                 */
                User::query()
                    ->whereKey(
                        $user->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $existing =
                    Point::query()
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

                $before =
                    (int) (
                        Point::query()
                            ->where(
                                'user_id',
                                $user->id
                            )
                            ->latest(
                                'created_at'
                            )
                            ->latest(
                                'id'
                            )
                            ->value(
                                'balance_after'
                            )
                        ?? 0
                    );

                if (
                    $type === 'spend'
                    && $before < $points
                ) {
                    throw ValidationException::withMessages([
                        'points' => 'رصيد النقاط غير كافٍ لإتمام العملية.',
                    ]);
                }

                $after =
                    $type === 'earn'
                        ? $before + $points
                        : $before - $points;

                return Point::query()
                    ->create([
                        'user_id' => $user->id,
                        'type' => $type,
                        'points' => $points,
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
