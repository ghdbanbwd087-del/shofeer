<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $payment->loadMissing('booking');

        return $user->isPassenger()
            && $payment->booking !== null
            && (string) $payment->booking->user_id === (string) $user->id;
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $user->isAdmin();
    }
}
