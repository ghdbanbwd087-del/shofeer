<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Held = 'held';

    case PendingPayment = 'pending_payment';

    case Confirmed = 'confirmed';

    case Cancelled = 'cancelled';

    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Held => 'محجوز مؤقتاً',

            self::PendingPayment => 'بانتظار الدفع',

            self::Confirmed => 'مؤكد',

            self::Cancelled => 'ملغي',

            self::Expired => 'منتهي',
        };
    }

    public function isActive(): bool
    {
        return in_array(
            $this,
            [
                self::Held,
                self::PendingPayment,
                self::Confirmed,
            ],
            true
        );
    }
}
