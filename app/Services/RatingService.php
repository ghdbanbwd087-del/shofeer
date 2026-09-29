<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Rating;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RatingService
{
    /**
     * @param array{score:int, comment?:?string} $data
     */
    public function createForBooking(
        Booking $booking,
        User $user,
        array $data
    ): Rating {
        return DB::transaction(
            function () use (
                $booking,
                $user,
                $data
            ): Rating {
                $lockedBooking =
                    Booking::query()
                        ->whereKey(
                            $booking->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    (string) $lockedBooking->user_id
                    !== (string) $user->id
                ) {
                    throw ValidationException::withMessages([
                        'rating' => 'لا يمكنك تقييم حجز لا يخصك.',
                    ]);
                }

                if (
                    $lockedBooking->status
                    !== BookingStatus::Confirmed
                ) {
                    throw ValidationException::withMessages([
                        'rating' => 'يمكن تقييم الحجوزات المؤكدة فقط.',
                    ]);
                }

                $trip =
                    Trip::query()
                        ->whereKey(
                            $lockedBooking->trip_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    $trip->departure_at === null
                    || ! $trip->departure_at->isPast()
                ) {
                    throw ValidationException::withMessages([
                        'rating' => 'لا يمكن تقييم الرحلة قبل انتهائها.',
                    ]);
                }

                if ($trip->driver_id === null) {
                    throw ValidationException::withMessages([
                        'rating' => 'لا يوجد سائق مرتبط بهذه الرحلة.',
                    ]);
                }

                $existing =
                    Rating::query()
                        ->where(
                            'booking_id',
                            $lockedBooking->id
                        )
                        ->lockForUpdate()
                        ->first();

                if ($existing !== null) {
                    return $existing;
                }

                $driver =
                    Driver::query()
                        ->whereKey(
                            $trip->driver_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                $rating =
                    Rating::query()->create([
                        'booking_id' =>
                            $lockedBooking->id,

                        'trip_id' =>
                            $trip->id,

                        'driver_id' =>
                            $driver->id,

                        'user_id' =>
                            $user->id,

                        'score' =>
                            (int) $data['score'],

                        'comment' =>
                            filled(
                                $data['comment'] ?? null
                            )
                                ? trim(
                                    (string) $data['comment']
                                )
                                : null,
                    ]);

                $this->recalculateDriverRating(
                    $driver
                );

                return $rating->fresh([
                    'booking',
                    'trip',
                    'driver.user',
                ]);
            },
            3
        );
    }

    /**
     * @param array{score:int, comment?:?string} $data
     */
    public function update(
        Rating $rating,
        User $user,
        array $data
    ): Rating {
        return DB::transaction(
            function () use (
                $rating,
                $user,
                $data
            ): Rating {
                $lockedRating =
                    Rating::query()
                        ->whereKey(
                            $rating->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    (string) $lockedRating->user_id
                    !== (string) $user->id
                ) {
                    throw ValidationException::withMessages([
                        'rating' => 'لا يمكنك تعديل تقييم لا يخصك.',
                    ]);
                }

                $driver =
                    Driver::query()
                        ->whereKey(
                            $lockedRating->driver_id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                $lockedRating->update([
                    'score' =>
                        (int) $data['score'],

                    'comment' =>
                        filled(
                            $data['comment'] ?? null
                        )
                            ? trim(
                                (string) $data['comment']
                            )
                            : null,
                ]);

                $this->recalculateDriverRating(
                    $driver
                );

                return $lockedRating->fresh([
                    'booking',
                    'trip',
                    'driver.user',
                ]);
            },
            3
        );
    }

    private function recalculateDriverRating(
        Driver $driver
    ): void {
        $average =
            Rating::query()
                ->where(
                    'driver_id',
                    $driver->id
                )
                ->avg(
                    'score'
                );

        $driver->forceFill([
            'rating' =>
                $average === null
                    ? 0
                    : round(
                        (float) $average,
                        2
                    ),
        ])->save();
    }
}
