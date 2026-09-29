<?php

namespace App\Enums;

/**
 * حالات الرحلة الفعلية.
 */
enum TripStatus: string
{
    case Scheduled = 'scheduled';

    case Boarding = 'boarding';

    case InProgress = 'in_progress';

    case Completed = 'completed';

    case Cancelled = 'cancelled';

    /**
     * الاسم العربي.
     */
    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'مجدولة',

            self::Boarding => 'استقبال الركاب',

            self::InProgress => 'في الطريق',

            self::Completed => 'مكتملة',

            self::Cancelled => 'ملغاة',
        };
    }

    /**
     * هل الرحلة متاحة للحجز مبدئياً؟
     */
    public function canAcceptBookings(): bool
    {
        return $this === self::Scheduled;
    }
}
