<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /*
    |--------------------------------------------------------------------------
    | View Booking
    |--------------------------------------------------------------------------
    */

    public function view(
        User $user,
        Booking $booking
    ): bool {
        return (string) $booking->user_id
            === (string) $user->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Update Booking
    |--------------------------------------------------------------------------
    */

    public function update(
        User $user,
        Booking $booking
    ): bool {
        return $this->view(
            $user,
            $booking
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel Booking
    |--------------------------------------------------------------------------
    |
    | في هذه المرحلة نسمح بالإلغاء المباشر فقط
    | قبل اعتماد الدفع نهائياً.
    |
    */

    public function cancel(
        User $user,
        Booking $booking
    ): bool {
        if (
            ! $this->view(
                $user,
                $booking
            )
        ) {
            return false;
        }

        $status =
            $booking->status instanceof \BackedEnum
                ? $booking->status->value
                : $booking->status;

        return in_array(
            $status,
            [
                BookingStatus::Held->value,
                BookingStatus::PendingPayment->value,
            ],
            true
        );
    }
}
