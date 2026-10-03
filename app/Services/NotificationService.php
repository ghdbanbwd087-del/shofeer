<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificationService
{
    public function sendToUser(
        User $user,
        string $title,
        string $message,
        ?User $sender = null
    ): AppNotification {
        return AppNotification::query()->create([
            'type' => 'shofeer.in_app',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => trim($title),
                'message' => trim($message),
                'sender_id' => $sender?->id,
                'sent_at' => now()->toIso8601String(),
            ],
            'read_at' => null,
        ]);
    }

    public function broadcast(
        User $sender,
        string $title,
        string $message
    ): int {
        $count = 0;

        User::query()
            ->where('is_active', true)
            ->whereKeyNot($sender->id)
            ->orderBy('id')
            ->chunkById(
                200,
                function ($users) use (
                    $sender,
                    $title,
                    $message,
                    &$count
                ): void {
                    DB::transaction(
                        function () use (
                            $users,
                            $sender,
                            $title,
                            $message,
                            &$count
                        ): void {
                            foreach ($users as $user) {
                                $this->sendToUser(
                                    user: $user,
                                    title: $title,
                                    message: $message,
                                    sender: $sender,
                                );

                                $count++;
                            }
                        }
                    );
                }
            );

        return $count;
    }

    /**
     * @return Collection<int, AppNotification>
     */
    public function latestForUser(
        User $user,
        int $limit = 10
    ): Collection {
        return AppNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function unreadCount(
        User $user
    ): int {
        return AppNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function markRead(
        AppNotification $notification,
        User $user
    ): AppNotification {
        $this->assertOwnership(
            notification: $notification,
            user: $user,
        );

        if ($notification->read_at === null) {
            $notification->forceFill([
                'read_at' => now(),
            ])->save();
        }

        return $notification->fresh();
    }

    public function markAllRead(
        User $user
    ): int {
        return AppNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function assertOwnership(
        AppNotification $notification,
        User $user
    ): void {
        if (
            $notification->notifiable_type !== User::class
            || (string) $notification->notifiable_id
                !== (string) $user->id
        ) {
            throw ValidationException::withMessages([
                'notification' => 'لا يمكنك الوصول إلى هذا الإشعار.',
            ]);
        }
    }
}
