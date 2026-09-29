<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Badge;
use App\Models\Booking;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BadgeService
{
    /**
     * @return array{
     *     completedTrips:int,
     *     currentBadge:?Badge,
     *     nextBadge:?Badge,
     *     earnedBadges:Collection<int, UserBadge>,
     *     progressPercent:int,
     *     tripsToNext:int
     * }
     */
    public function summaryFor(User $user): array
    {
        $completedTrips = $this->completedTripsFor($user);

        $badges = Badge::query()
            ->active()
            ->orderBy('min_completed_trips')
            ->orderBy('sort_order')
            ->get();

        $this->syncEarnedBadges(
            user: $user,
            badges: $badges,
            completedTrips: $completedTrips,
        );

        $currentBadge = $badges
            ->filter(
                fn (Badge $badge): bool =>
                    $badge->min_completed_trips <= $completedTrips
            )
            ->last();

        $nextBadge = $badges
            ->first(
                fn (Badge $badge): bool =>
                    $badge->min_completed_trips > $completedTrips
            );

        $earnedBadges = UserBadge::query()
            ->where('user_id', $user->id)
            ->with('badge')
            ->latest('awarded_at')
            ->get();

        $progressPercent = $this->progressPercent(
            completedTrips: $completedTrips,
            currentBadge: $currentBadge,
            nextBadge: $nextBadge,
        );

        $tripsToNext = $nextBadge
            ? max(
                0,
                $nextBadge->min_completed_trips - $completedTrips
            )
            : 0;

        return [
            'completedTrips' => $completedTrips,
            'currentBadge' => $currentBadge,
            'nextBadge' => $nextBadge,
            'earnedBadges' => $earnedBadges,
            'progressPercent' => $progressPercent,
            'tripsToNext' => $tripsToNext,
        ];
    }

    private function completedTripsFor(User $user): int
    {
        return Booking::query()
            ->where('user_id', $user->id)
            ->where('status', BookingStatus::Confirmed->value)
            ->whereHas(
                'trip',
                fn ($query) =>
                    $query->where('departure_at', '<', now())
            )
            ->distinct()
            ->count('trip_id');
    }

    /**
     * @param Collection<int, Badge> $badges
     */
    private function syncEarnedBadges(
        User $user,
        Collection $badges,
        int $completedTrips
    ): void {
        $now = now();

        foreach ($badges as $badge) {
            if ($badge->min_completed_trips > $completedTrips) {
                continue;
            }

            UserBadge::query()->insertOrIgnore([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'awarded_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function progressPercent(
        int $completedTrips,
        ?Badge $currentBadge,
        ?Badge $nextBadge
    ): int {
        if ($nextBadge === null) {
            return $currentBadge === null ? 0 : 100;
        }

        $start = $currentBadge?->min_completed_trips ?? 0;
        $end = $nextBadge->min_completed_trips;

        if ($end <= $start) {
            return 100;
        }

        $progress = (($completedTrips - $start) / ($end - $start)) * 100;

        return (int) max(
            0,
            min(100, round($progress))
        );
    }
}
