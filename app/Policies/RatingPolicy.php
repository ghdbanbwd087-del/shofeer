<?php

namespace App\Policies;

use App\Models\Rating;
use App\Models\User;

class RatingPolicy
{
    public function view(
        User $user,
        Rating $rating
    ): bool {
        return $user->isAdmin()
            || (string) $rating->user_id
                === (string) $user->id;
    }

    public function update(
        User $user,
        Rating $rating
    ): bool {
        return $user->isPassenger()
            && (string) $rating->user_id
                === (string) $user->id;
    }
}
