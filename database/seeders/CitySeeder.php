<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * المدن الأولية المستخدمة في التطوير.
     *
     * القائمة هنا Seed أولي تقني وليست قائمة نهائية
     * لمدن المنصة.
     */
    public function run(): void
    {
        $cities = [
            [
                'name_ar' => 'صنعاء',
                'name_en' => 'Sanaa',
                'country_code' => 'YE',
                'sort_order' => 10,
            ],

            [
                'name_ar' => 'عدن',
                'name_en' => 'Aden',
                'country_code' => 'YE',
                'sort_order' => 20,
            ],

            [
                'name_ar' => 'تعز',
                'name_en' => 'Taiz',
                'country_code' => 'YE',
                'sort_order' => 30,
            ],

            [
                'name_ar' => 'الرياض',
                'name_en' => 'Riyadh',
                'country_code' => 'SA',
                'sort_order' => 40,
            ],

            [
                'name_ar' => 'جدة',
                'name_en' => 'Jeddah',
                'country_code' => 'SA',
                'sort_order' => 50,
            ],

            [
                'name_ar' => 'مكة المكرمة',
                'name_en' => 'Makkah',
                'country_code' => 'SA',
                'sort_order' => 60,
            ],
        ];

        foreach ($cities as $city) {
            City::query()->updateOrCreate(
                [
                    'name_ar' => $city['name_ar'],

                    'country_code' => $city['country_code'],
                ],
                [
                    ...$city,

                    'is_active' => true,
                ]
            );
        }
    }
}
