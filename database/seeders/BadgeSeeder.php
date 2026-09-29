<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * هذه قيم بداية قابلة للتعديل لاحقاً من لوحة الإدارة.
         * ملف الفكرة يحدد وجود الشارات والتقدم والمزايا،
         * لكنه لا يحدد أسماء الشارات أو حدود الرحلات.
         */
        $badges = [
            [
                'code' => 'starter',
                'name' => 'مسافر جديد',
                'description' => 'بداية رحلتك مع شوفير.',
                'icon' => 'sparkles',
                'min_completed_trips' => 0,
                'benefits' => [
                    'متابعة تقدمك نحو الشارة التالية.',
                ],
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'code' => 'active',
                'name' => 'مسافر نشط',
                'description' => 'تُمنح بعد إكمال 5 رحلات.',
                'icon' => 'badge',
                'min_completed_trips' => 5,
                'benefits' => [
                    'شارة مميزة في حسابك.',
                    'أولوية في العروض المخصصة مستقبلًا.',
                ],
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'code' => 'experienced',
                'name' => 'مسافر خبير',
                'description' => 'تُمنح بعد إكمال 15 رحلة.',
                'icon' => 'shield',
                'min_completed_trips' => 15,
                'benefits' => [
                    'شارة متقدمة في حسابك.',
                    'مزايا ولاء إضافية عند تفعيلها.',
                ],
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'code' => 'elite',
                'name' => 'مسافر مميز',
                'description' => 'تُمنح بعد إكمال 30 رحلة.',
                'icon' => 'star',
                'min_completed_trips' => 30,
                'benefits' => [
                    'أعلى شارة ركاب مبدئية.',
                    'أهلية لمزايا الولاء المميزة مستقبلًا.',
                ],
                'sort_order' => 40,
                'is_active' => true,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::query()->updateOrCreate(
                ['code' => $badge['code']],
                $badge
            );
        }
    }
}
