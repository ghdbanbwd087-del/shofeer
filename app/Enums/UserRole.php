<?php

namespace App\Enums;

/**
 * أدوار المستخدمين في SHOFEER.
 */
enum UserRole: string
{
    case Passenger = 'passenger';

    case Driver = 'driver';

    case Admin = 'admin';

    /**
     * الاسم العربي للدور.
     */
    public function label(): string
    {
        return match ($this) {
            self::Passenger => 'راكب',
            self::Driver => 'سائق',
            self::Admin => 'إدارة',
        };
    }

    /**
     * الصفحة الرئيسية المناسبة لكل دور.
     */
    public function dashboardRouteName(): string
    {
        return match ($this) {
            self::Passenger => 'dashboard',

            self::Driver => 'driver.dashboard',

            self::Admin => 'admin.dashboard',
        };
    }
}
