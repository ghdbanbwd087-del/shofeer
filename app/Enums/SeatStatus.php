<?php

namespace App\Enums;

enum SeatStatus: string
{
    case Available = 'available';

    case Held = 'held';

    case Booked = 'booked';

    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'متاح',

            self::Held => 'محجوز مؤقتاً',

            self::Booked => 'محجوز',

            self::Locked => 'مقفل',
        };
    }
}
