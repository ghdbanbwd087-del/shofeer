<?php

namespace App\Enums;

/**
 * حالات اعتماد السائق.
 */
enum DriverStatus: string
{
    case Pending = 'pending';

    case Approved = 'approved';

    case Rejected = 'rejected';

    case Suspended = 'suspended';

    /**
     * الاسم العربي للحالة.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',
            self::Approved => 'موثق',
            self::Rejected => 'مرفوض',
            self::Suspended => 'موقوف',
        };
    }

    /**
     * هل السائق معتمد؟
     */
    public function isApproved(): bool
    {
        return $this === self::Approved;
    }
}
