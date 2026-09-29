<?php

namespace App\Enums;

enum PaymentReviewStatus: string
{
    case Pending = 'pending';

    case Approved = 'approved';

    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد التحقق',

            self::Approved => 'مؤكد',

            self::Rejected => 'مرفوض',
        };
    }
}
