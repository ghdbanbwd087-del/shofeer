<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';

    case Karimi = 'karimi';

    case Flousak = 'flousak';

    case Jawali = 'jawali';

    case StcPay = 'stc_pay';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'تحويل بنكي',

            self::Karimi => 'كريمي',

            self::Flousak => 'فلوسك',

            self::Jawali => 'جوالي',

            self::StcPay => 'STC Pay',
        };
    }
}
