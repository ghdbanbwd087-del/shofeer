@extends('layouts.app')

@section('title', 'لوحة التحكم - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                لوحة الراكب
            </p>

            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                أهلاً {{ $user->name }}
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                تابع حجوزاتك ورحلاتك وإشعاراتك من مكان واحد.
            </p>
        </div>

        <a
            href="{{ route('trips.index') }}"
            class="rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-blue-800"
        >
            ابحث عن رحلة
        </a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                كل الحجوزات
            </p>
            <p class="mt-3 text-3xl font-black text-slate-900">
                {{ $stats['total_bookings'] }}
            </p>
        </section>

        <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
            <p class="text-sm font-semibold text-blue-700">
                الحجوزات القادمة
            </p>
            <p class="mt-3 text-3xl font-black text-blue-900">
                {{ $stats['upcoming_bookings'] }}
            </p>
        </section>

        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm font-semibold text-amber-700">
                بانتظار الدفع
            </p>
            <p class="mt-3 text-3xl font-black text-amber-900">
                {{ $stats['pending_payments'] }}
            </p>
        </section>

        <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-sm font-semibold text-emerald-700">
                الرحلات المكتملة
            </p>
            <p class="mt-3 text-3xl font-black text-emerald-900">
                {{ $stats['completed_trips'] }}
            </p>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                إجمالي المدفوع
            </p>
            <p class="mt-3 text-2xl font-black text-slate-900">
                {{ number_format($stats['total_paid'], 2) }}
            </p>
        </section>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a
            href="{{ route('dashboard.bookings.index') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <p class="font-bold text-slate-900">حجوزاتي</p>
            <p class="mt-1 text-sm text-slate-500">القادمة والسابقة والملغاة</p>
        </a>

        <a
            href="{{ route('dashboard.badges') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <p class="font-bold text-slate-900">شاراتي</p>
            <p class="mt-1 text-sm text-slate-500">تقدمك ومزاياك</p>
        </a>

        <a
            href="{{ route('dashboard.points') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <p class="font-bold text-slate-900">نقاطي</p>
            <p class="mt-1 text-sm text-slate-500">رصيد النقاط وطريقة استخدامها</p>
        </a>

        <a
            href="{{ route('dashboard.live.index') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <p class="font-bold text-slate-900">التتبع المباشر</p>
            <p class="mt-1 text-sm text-slate-500">موقع السائق أثناء الرحلة</p>
        </a>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        حجوزاتي القادمة
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        آخر الحجوزات النشطة ذات الرحلات القادمة.
                    </p>
                </div>

                <a
                    href="{{ route('dashboard.bookings.index') }}"
                    class="text-sm font-bold text-blue-900"
                >
                    عرض الكل
                </a>
            </div>

            @if ($upcomingBookings->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-slate-500">
                    لا توجد حجوزات قادمة حاليًا.
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($upcomingBookings as $booking)
                        <article class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-bold text-blue-900">
                                    {{ $booking->booking_code }}
                                </p>

                                <p class="mt-1 text-sm text-slate-700">
                                    {{ $booking->trip?->departure_at?->format('Y-m-d H:i') ?? '—' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    المقعد:
                                    {{ $booking->seat_number }}
                                    —
                                    {{ $booking->status->label() }}
                                </p>
                            </div>

                            <a
                                href="{{ route('dashboard.bookings.show', $booking) }}"
                                class="self-start rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 sm:self-auto"
                            >
                                عرض
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            آخر إشعاراتي
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            آخر 5 إشعارات داخل المنصة.
                        </p>
                    </div>

                    @if ($unreadNotificationsCount > 0)
                        <span class="rounded-full bg-blue-900 px-3 py-1 text-xs font-bold text-white">
                            {{ $unreadNotificationsCount }} جديد
                        </span>
                    @endif
                </div>
            </div>

            @if ($latestNotifications->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-slate-500">
                    لا توجد إشعارات حتى الآن.
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($latestNotifications as $notification)
                        @php
                            $notificationData =
                                is_array($notification->data)
                                    ? $notification->data
                                    : [];
                        @endphp

                        <article class="p-5 {{ $notification->read_at === null ? 'bg-blue-50/40' : '' }}">
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->read_at === null ? 'bg-blue-900' : 'bg-slate-300' }}"></span>

                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900">
                                        {{ $notificationData['title'] ?? 'إشعار' }}
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-slate-600">
                                        {{ $notificationData['message'] ?? '' }}
                                    </p>

                                    <p class="mt-2 text-xs text-slate-400">
                                        {{ $notification->created_at?->format('Y-m-d H:i') }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a
            href="{{ route('dashboard.balance') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            <p class="font-bold text-slate-900">رصيدي</p>
            <p class="mt-1 text-sm text-slate-500">الرصيد وسجل الجوائز</p>
        </a>

        <a
            href="{{ route('dashboard.packages.index') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            <p class="font-bold text-slate-900">بضاعتي</p>
            <p class="mt-1 text-sm text-slate-500">متابعة طلبات البضائع</p>
        </a>

        <a
            href="{{ route('dashboard.ratings.index') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            <p class="font-bold text-slate-900">تقييماتي</p>
            <p class="mt-1 text-sm text-slate-500">قيّم رحلاتك المكتملة</p>
        </a>

        <a
            href="{{ route('packages.create') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            <p class="font-bold text-slate-900">أرسل بضاعة</p>
            <p class="mt-1 text-sm text-slate-500">إنشاء طلب بضاعة جديد</p>
        </a>
    </div>
</div>
@endsection
