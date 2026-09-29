<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';

    case Pending = 'pending';

    case Paid = 'paid';

    case Failed = 'failed';

    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'غير مدفوع',

            self::Pending => 'قيد التحقق',

            self::Paid => 'مدفوع',

            self::Failed => 'فشل الدفع',

            self::Refunded => 'مسترد',
        };
    }
}
