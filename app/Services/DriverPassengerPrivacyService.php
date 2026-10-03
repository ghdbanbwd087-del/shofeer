<?php

namespace App\Services;

use App\Models\Trip;

class DriverPassengerPrivacyService
{
    public function firstName(
        ?string $fullName
    ): string {
        $name =
            trim(
                (string) $fullName
            );

        if (
            $name === ''
        ) {
            return 'راكب';
        }

        $parts =
            preg_split(
                '/\s+/u',
                $name
            );

        return (string) (
            $parts[0]
            ?? 'راكب'
        );
    }

    public function canRevealWhatsApp(
        Trip $trip
    ): bool {
        if (
            $trip->departure_at === null
        ) {
            return false;
        }

        $departureAt =
            $trip
                ->departure_at
                ->copy();

        $windowStartsAt =
            $departureAt
                ->copy()
                ->subMinutes(30);

        $now =
            now();

        return $now->greaterThanOrEqualTo(
            $windowStartsAt
        )
            && $now->lessThanOrEqualTo(
                $departureAt
            );
    }

    public function whatsappUrl(
        ?string $number
    ): ?string {
        $digits =
            preg_replace(
                '/\D+/',
                '',
                (string) $number
            );

        if (
            $digits === ''
        ) {
            return null;
        }

        return 'https://wa.me/'.
            $digits;
    }
}
