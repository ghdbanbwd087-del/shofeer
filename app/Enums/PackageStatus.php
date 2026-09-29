<?php

namespace App\Enums;

enum PackageStatus: string
{
    case Received = 'received';
    case Assigned = 'assigned';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'مستلمة',
            self::Assigned => 'تم التعيين',
            self::InTransit => 'في الطريق',
            self::Delivered => 'تم التسليم',
            self::Cancelled => 'ملغاة',
        };
    }

    public function isActive(): bool
    {
        return in_array(
            $this,
            [
                self::Received,
                self::Assigned,
                self::InTransit,
            ],
            true
        );
    }
}
