<?php

namespace App\Policies;

use App\Enums\PackageStatus;
use App\Models\Package;
use App\Models\User;

class PackagePolicy
{
    public function view(
        User $user,
        Package $package
    ): bool {
        return (string) $package->user_id
            === (string) $user->id;
    }

    public function update(
        User $user,
        Package $package
    ): bool {
        return $this->view(
            $user,
            $package
        )
            && $package->status ===
                PackageStatus::Received;
    }

    public function cancel(
        User $user,
        Package $package
    ): bool {
        return $this->view(
            $user,
            $package
        )
            && $package->status ===
                PackageStatus::Received;
    }
}
