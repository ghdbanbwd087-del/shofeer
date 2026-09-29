<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Verification Window
    |--------------------------------------------------------------------------
    */

    'verification_minutes' => 60,

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | أرقام الحسابات هنا placeholders محلية.
    | لاحقاً تنتقل إلى إعدادات الإدارة.
    |
    */

    'methods' => [

        'bank_transfer' => [
            'label' => 'تحويل بنكي',

            'account_number' => env(
                'PAYMENT_BANK_ACCOUNT',
                'يُحدد من الإدارة'
            ),
        ],

        'karimi' => [
            'label' => 'كريمي',

            'account_number' => env(
                'PAYMENT_KARIMI_ACCOUNT',
                'يُحدد من الإدارة'
            ),
        ],

        'flousak' => [
            'label' => 'فلوسك',

            'account_number' => env(
                'PAYMENT_FLOUSAK_ACCOUNT',
                'يُحدد من الإدارة'
            ),
        ],

        'jawali' => [
            'label' => 'جوالي',

            'account_number' => env(
                'PAYMENT_JAWALI_ACCOUNT',
                'يُحدد من الإدارة'
            ),
        ],

        'stc_pay' => [
            'label' => 'STC Pay',

            'account_number' => env(
                'PAYMENT_STC_ACCOUNT',
                'يُحدد من الإدارة'
            ),
        ],
    ],
];
