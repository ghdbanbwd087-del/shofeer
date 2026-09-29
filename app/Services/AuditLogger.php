<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function log(
        ?User $actor,
        string $event,
        Model $auditable,
        array $metadata = []
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_user_id' => $actor?->id,
            'event' => $event,
            'auditable_type' => $auditable::class,
            'auditable_id' => (string) $auditable->getKey(),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
