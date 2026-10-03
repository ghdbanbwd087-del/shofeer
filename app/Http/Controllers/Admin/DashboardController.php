<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\DriverStatus;
use App\Enums\PaymentReviewStatus;
use App\Enums\RefundStatus;
use App\Enums\TripRequestStatus;
use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\Refund;
use App\Models\Trip;
use App\Models\TripRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view(
            'admin.dashboard.index',
            [
                'stats' =>
                    $this->stats(),

                'charts' =>
                    $this->charts(),

                'activities' =>
                    $this->activities(),

                'alerts' =>
                    $this->alerts(),

                'navigation' =>
                    $this->navigation(),
            ]
        );
    }

    private function stats(): array
    {
        return [
            'users' => [
                'label' =>
                    'المستخدمون',

                'value' =>
                    User::query()
                        ->count(),
            ],

            'approved_drivers' => [
                'label' =>
                    'السائقون الموثقون',

                'value' =>
                    Driver::query()
                        ->where(
                            'status',
                            DriverStatus::Approved
                                ->value
                        )
                        ->count(),
            ],

            'active_trips' => [
                'label' =>
                    'الرحلات الحالية والقادمة',

                'value' =>
                    Trip::query()
                        ->whereIn(
                            'status',
                            [
                                TripStatus::Scheduled
                                    ->value,

                                TripStatus::Boarding
                                    ->value,

                                TripStatus::InProgress
                                    ->value,
                            ]
                        )
                        ->count(),
            ],

            'confirmed_bookings' => [
                'label' =>
                    'الحجوزات المؤكدة',

                'value' =>
                    Booking::query()
                        ->where(
                            'status',
                            BookingStatus::Confirmed
                                ->value
                        )
                        ->count(),
            ],

            'pending_payments' => [
                'label' =>
                    'دفعات بانتظار المراجعة',

                'value' =>
                    Payment::query()
                        ->where(
                            'status',
                            PaymentReviewStatus::Pending
                                ->value
                        )
                        ->count(),
            ],

            'approved_payments_amount' => [
                'label' =>
                    'إجمالي الدفعات المعتمدة',

                'value' =>
                    round(
                        (float) Payment::query()
                            ->where(
                                'status',
                                PaymentReviewStatus::Approved
                                    ->value
                            )
                            ->sum(
                                'amount'
                            ),
                        2
                    ),

                'format' =>
                    'money',
            ],

            'packages' => [
                'label' =>
                    'طلبات البضاعة',

                'value' =>
                    Package::query()
                        ->count(),
            ],

            'average_rating' => [
                'label' =>
                    'متوسط التقييم',

                'value' =>
                    round(
                        (float) (
                            Rating::query()
                                ->avg(
                                    'score'
                                )
                            ?? 0
                        ),
                        2
                    ),

                'format' =>
                    'rating',
            ],
        ];
    }

    private function charts(): array
    {
        return [
            [
                'title' =>
                    'المستخدمون الجدد',

                'subtitle' =>
                    'آخر 7 أيام',

                'points' =>
                    $this->dailyTrend(
                        query:
                            User::query(),

                        dateColumn:
                            'created_at'
                    ),
            ],

            [
                'title' =>
                    'الحجوزات الجديدة',

                'subtitle' =>
                    'آخر 7 أيام',

                'points' =>
                    $this->dailyTrend(
                        query:
                            Booking::query(),

                        dateColumn:
                            'created_at'
                    ),
            ],

            [
                'title' =>
                    'الرحلات المنشأة',

                'subtitle' =>
                    'آخر 7 أيام',

                'points' =>
                    $this->dailyTrend(
                        query:
                            Trip::query(),

                        dateColumn:
                            'created_at'
                    ),
            ],

            [
                'title' =>
                    'الدفعات المعتمدة',

                'subtitle' =>
                    'قيمة الدفعات خلال آخر 7 أيام',

                'points' =>
                    $this->dailyTrend(
                        query:
                            Payment::query()
                                ->where(
                                    'status',
                                    PaymentReviewStatus::Approved
                                        ->value
                                ),

                        dateColumn:
                            'reviewed_at',

                        aggregateColumn:
                            'amount'
                    ),

                'format' =>
                    'money',
            ],
        ];
    }

    private function dailyTrend(
        Builder $query,
        string $dateColumn,
        ?string $aggregateColumn = null
    ): array {
        $points =
            [];

        $start =
            now()
                ->startOfDay()
                ->subDays(6);

        for (
            $dayOffset = 0;
            $dayOffset < 7;
            $dayOffset++
        ) {
            $day =
                $start
                    ->copy()
                    ->addDays(
                        $dayOffset
                    );

            $dayQuery =
                clone $query;

            $dayQuery
                ->whereBetween(
                    $dateColumn,
                    [
                        $day
                            ->copy()
                            ->startOfDay(),

                        $day
                            ->copy()
                            ->endOfDay(),
                    ]
                );

            $value =
                $aggregateColumn === null
                    ? $dayQuery
                        ->count()
                    : round(
                        (float) $dayQuery
                            ->sum(
                                $aggregateColumn
                            ),
                        2
                    );

            $points[] = [
                'label' =>
                    $day
                        ->format(
                            'm/d'
                        ),

                'value' =>
                    $value,
            ];
        }

        return $points;
    }

    private function activities(): Collection
    {
        $activities =
            collect();

        User::query()
            ->latest()
            ->limit(3)
            ->get()
            ->each(
                function (
                    User $user
                ) use (
                    $activities
                ): void {
                    $role =
                        $user->role
                        instanceof \BackedEnum
                            ? $user
                                ->role
                                ->value
                            : (string) $user
                                ->role;

                    $activities->push([
                        'type' =>
                            'user',

                        'title' =>
                            'مستخدم جديد',

                        'description' =>
                            $user->name.
                            ' — '.
                            $role,

                        'at' =>
                            $user->created_at,
                    ]);
                }
            );

        Booking::query()
            ->latest()
            ->limit(3)
            ->get()
            ->each(
                function (
                    Booking $booking
                ) use (
                    $activities
                ): void {
                    $status =
                        $booking->status
                        instanceof \BackedEnum
                            ? $booking
                                ->status
                                ->value
                            : (string) $booking
                                ->status;

                    $activities->push([
                        'type' =>
                            'booking',

                        'title' =>
                            'حجز',

                        'description' =>
                            $booking
                                ->booking_code.
                            ' — '.
                            $status,

                        'at' =>
                            $booking->created_at,
                    ]);
                }
            );

        Payment::query()
            ->latest()
            ->limit(3)
            ->get()
            ->each(
                function (
                    Payment $payment
                ) use (
                    $activities
                ): void {
                    $status =
                        $payment->status
                        instanceof \BackedEnum
                            ? $payment
                                ->status
                                ->value
                            : (string) $payment
                                ->status;

                    $activities->push([
                        'type' =>
                            'payment',

                        'title' =>
                            'دفعة',

                        'description' =>
                            number_format(
                                (float) $payment->amount,
                                2
                            ).
                            ' — '.
                            $status,

                        'at' =>
                            $payment->created_at,
                    ]);
                }
            );

        Trip::query()
            ->latest()
            ->limit(3)
            ->get()
            ->each(
                function (
                    Trip $trip
                ) use (
                    $activities
                ): void {
                    $status =
                        $trip->status
                        instanceof \BackedEnum
                            ? $trip
                                ->status
                                ->value
                            : (string) $trip
                                ->status;

                    $activities->push([
                        'type' =>
                            'trip',

                        'title' =>
                            'رحلة',

                        'description' =>
                            $status.
                            ' — '.
                            (
                                $trip
                                    ->departure_at
                                    ?->format(
                                        'Y-m-d H:i'
                                    )
                                ?? 'بدون موعد'
                            ),

                        'at' =>
                            $trip->created_at,
                    ]);
                }
            );

        return $activities
            ->filter(
                fn (
                    array $activity
                ): bool =>
                    $activity['at']
                    instanceof Carbon
            )
            ->sortByDesc(
                fn (
                    array $activity
                ): int =>
                    $activity['at']
                        ->timestamp
            )
            ->take(10)
            ->values();
    }

    private function alerts(): Collection
    {
        return collect([
            [
                'label' =>
                    'سائقون بانتظار المراجعة',

                'count' =>
                    Driver::query()
                        ->where(
                            'status',
                            DriverStatus::Pending
                                ->value
                        )
                        ->count(),

                'route' =>
                    'admin.drivers.index',
            ],

            [
                'label' =>
                    'طلبات رحلات معلقة',

                'count' =>
                    TripRequest::query()
                        ->where(
                            'status',
                            TripRequestStatus::Pending
                                ->value
                        )
                        ->count(),

                'route' =>
                    'admin.trip-requests.index',
            ],

            [
                'label' =>
                    'دفعات معلقة',

                'count' =>
                    Payment::query()
                        ->where(
                            'status',
                            PaymentReviewStatus::Pending
                                ->value
                        )
                        ->count(),

                'route' =>
                    'admin.payments.index',
            ],

            [
                'label' =>
                    'طلبات استرداد معلقة',

                'count' =>
                    Refund::query()
                        ->where(
                            'status',
                            RefundStatus::Pending
                                ->value
                        )
                        ->count(),

                'route' =>
                    'admin.refunds.index',
            ],
        ]);
    }

    private function navigation(): array
    {
        return [
            [
                'label' =>
                    'الرئيسية',

                'route' =>
                    'admin.dashboard',
            ],
            [
                'label' =>
                    'المستخدمون',

                'route' =>
                    'admin.users.index',
            ],
            [
                'label' =>
                    'السائقون',

                'route' =>
                    'admin.drivers.index',
            ],
            [
                'label' =>
                    'السيارات',

                'route' =>
                    'admin.cars.index',
            ],
            [
                'label' =>
                    'طلبات الرحلات',

                'route' =>
                    'admin.trip-requests.index',
            ],
            [
                'label' =>
                    'الرحلات',

                'route' =>
                    'admin.trips.index',
            ],
            [
                'label' =>
                    'المقاعد',

                'route' =>
                    'admin.seats.index',
            ],
            [
                'label' =>
                    'الحجوزات',

                'route' =>
                    'admin.bookings.index',
            ],
            [
                'label' =>
                    'الدفعات',

                'route' =>
                    'admin.payments.index',
            ],
            [
                'label' =>
                    'البضاعة',

                'route' =>
                    'admin.packages.index',
            ],
            [
                'label' =>
                    'طلبات المساعدة',

                'route' =>
                    'admin.help-requests.index',
            ],
            [
                'label' =>
                    'الخريطة المباشرة',

                'route' =>
                    'admin.live.index',
            ],
            [
                'label' =>
                    'الشارات والجوائز',

                'route' =>
                    'admin.badges.index',
            ],
            [
                'label' =>
                    'الجوائز المالية',

                'route' =>
                    'admin.rewards.index',
            ],
            [
                'label' =>
                    'التقارير',

                'route' =>
                    'admin.reports.index',
            ],
            [
                'label' =>
                    'سجل واتساب',

                'route' =>
                    'admin.whatsapp-logs.index',
            ],
            [
                'label' =>
                    'الإشعارات',

                'route' =>
                    'admin.notifications.index',
            ],
            [
                'label' =>
                    'المحتوى',

                'route' =>
                    'admin.content.index',
            ],
            [
                'label' =>
                    'سجل النشاطات',

                'route' =>
                    'admin.activity-log.index',
            ],
            [
                'label' =>
                    'الإعدادات',

                'route' =>
                    'admin.settings.index',
            ],
            [
                'label' =>
                    'الشعار والألوان',

                'route' =>
                    'admin.branding.index',
            ],
            [
                'label' =>
                    'تسجيل الخروج',

                'route' =>
                    'logout',

                'method' =>
                    'POST',
            ],
        ];
    }
}
