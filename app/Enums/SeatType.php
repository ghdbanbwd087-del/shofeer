<?php

namespace App\Enums;

enum SeatType: string
{
    case Standard = 'standard';

    case Female = 'female';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'عادي',

            self::Female => 'للنساء',
        };
    }
}
