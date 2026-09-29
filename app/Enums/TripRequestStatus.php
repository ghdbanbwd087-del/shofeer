<?php

namespace App\Enums;

/**
 * حالة طلب الرحلة الذي أرسله السائق.
 */
enum TripRequestStatus: string
{
    case Pending = 'pending';

    case Approved = 'approved';

    case Rejected = 'rejected';

    /**
     * الاسم العربي.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',

            self::Approved => 'تمت الموافقة',

            self::Rejected => 'مرفوض',
        };
    }

    /**
     * هل الطلب ما زال قابلاً للمراجعة؟
     */
    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}
