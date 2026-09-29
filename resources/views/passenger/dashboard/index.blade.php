@extends('passenger.layouts.dashboard')

@section('title', 'الرئيسية')

@section('content')

{{-- ============================================================= --}}
{{-- Welcome                                                       --}}
{{-- ============================================================= --}}

<section
    class="mb-8 overflow-hidden rounded-3xl bg-gradient-to-l from-[#1E3A8A] to-blue-700 p-6 text-white shadow-lg sm:p-8"
>
    <div
        class="flex flex-col justify-between gap-6 md:flex-row md:items-center"
    >
        <div>
            <div
                class="mb-2 text-sm text-blue-100"
            >
                أهلاً بعودتك
            </div>

            <h1
                class="text-2xl font-black sm:text-3xl"
            >
                مرحباً، {{ $user->name }}
            </h1>

            <p
                class="mt-3 max-w-2xl text-sm leading-7 text-blue-100"
            >
                تابع حجوزاتك ورحلاتك القادمة من مكان واحد.
            </p>
        </div>

        <a
            href="{{ route('trips.index') }}"
            class="inline-flex items-center justify-center rounded-2xl bg-[#F59E0B] px-5 py-3 text-sm font-black text-slate-950 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            احجز رحلة جديدة
        </a>
    </div>
</section>

{{-- ============================================================= --}}
{{-- Statistics                                                    --}}
{{-- ============================================================= --}}

<section
    class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-5"
>

    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >
        <div
            class="text-sm font-medium text-slate-500"
        >
            كل الحجوزات
        </div>

        <div
            class="mt-3 text-3xl font-black text-[#1E3A8A]"
        >
            {{ $stats['total_bookings'] }}
        </div>
    </div>

    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >
        <div
            class="text-sm font-medium text-slate-500"
        >
            حجوزات قادمة
        </div>

        <div
            class="mt-3 text-3xl font-black text-emerald-600"
        >
            {{ $stats['upcoming_bookings'] }}
        </div>
    </div>

    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >
        <div
            class="text-sm font-medium text-slate-500"
        >
            قيد التحقق
        </div>

        <div
            class="mt-3 text-3xl font-black text-amber-600"
        >
            {{ $stats['pending_payments'] }}
        </div>
    </div>

    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >
        <div
            class="text-sm font-medium text-slate-500"
        >
            رحلات سابقة
        </div>

        <div
            class="mt-3 text-3xl font-black text-violet-600"
        >
            {{ $stats['completed_trips'] }}
        </div>
    </div>

    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
    >
        <div
            class="text-sm font-medium text-slate-500"
        >
            إجمالي المدفوع
        </div>

        <div
            class="mt-3 text-2xl font-black text-slate-900"
        >
            {{ number_format($stats['total_paid'], 2) }}
            <span
                class="text-xs font-medium text-slate-500"
            >
                ريال
            </span>
        </div>
    </div>

</section>

{{-- ============================================================= --}}
{{-- Badges / Trips / Points                                       --}}
{{-- ============================================================= --}}

<section
    class="mb-8 grid gap-4 md:grid-cols-3"
>

    <div
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <div
            class="mb-4 flex items-center justify-between"
        >
            <div
                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-xl"
            >
                🏅
            </div>

            <span
                class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-500"
            >
                قريبًا
            </span>
        </div>

        <h2 class="font-black">
            شاراتك
        </h2>

        <p
            class="mt-2 text-sm leading-6 text-slate-500"
        >
            سيظهر هنا تقدمك والشارة الحالية والشارة القادمة.
        </p>
    </div>

    <div
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <div
            class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-xl"
        >
            🚌
        </div>

        <h2 class="font-black">
            رحلاتك
        </h2>

        <div
            class="mt-2 text-2xl font-black text-[#1E3A8A]"
        >
            {{ $stats['total_bookings'] }}
        </div>

        <p
            class="mt-1 text-sm text-slate-500"
        >
            إجمالي الحجوزات المسجلة
        </p>
    </div>

    <div
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <div
            class="mb-4 flex items-center justify-between"
        >
            <div
                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-xl"
            >
                ⭐
            </div>

            <span
                class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-500"
            >
                قريبًا
            </span>
        </div>

        <h2 class="font-black">
            نقاطك
        </h2>

        <p
            class="mt-2 text-sm leading-6 text-slate-500"
        >
            نظام النقاط سيتم ربطه في المرحلة الخاصة بالمكافآت.
        </p>
    </div>

</section>

{{-- ============================================================= --}}
{{-- Upcoming Bookings + Notifications                             --}}
{{-- ============================================================= --}}

<section
    class="grid gap-6 xl:grid-cols-[1.7fr_1fr]"
>

    {{-- Upcoming bookings --}}

    <div
        class="rounded-3xl border border-slate-200 bg-white shadow-sm"
    >
        <div
            class="flex items-center justify-between border-b border-slate-100 px-6 py-5"
        >
            <div>
                <h2
                    class="text-lg font-black"
                >
                    حجوزاتي القادمة
                </h2>

                <p
                    class="mt-1 text-xs text-slate-500"
                >
                    آخر الحجوزات والرحلات القادمة
                </p>
            </div>

            <a
                href="{{ route('dashboard.bookings.index') }}"
                class="text-sm font-bold text-[#1E3A8A] hover:underline"
            >
                عرض الكل
            </a>
        </div>

        <div class="divide-y divide-slate-100">

            @forelse ($upcomingBookings as $booking)

                @php
                    $statusValue =
                        $booking->status instanceof \BackedEnum
                            ? $booking->status->value
                            : $booking->status;

                    $statusData = match ($statusValue) {
                        'confirmed' => [
                            'label' => 'مؤكد',
                            'class' => 'bg-emerald-50 text-emerald-700',
                        ],

                        'pending_payment' => [
                            'label' => 'قيد التحقق',
                            'class' => 'bg-amber-50 text-amber-700',
                        ],

                        'held' => [
                            'label' => 'بانتظار الدفع',
                            'class' => 'bg-blue-50 text-blue-700',
                        ],

                        default => [
                            'label' => $statusValue,
                            'class' => 'bg-slate-100 text-slate-600',
                        ],
                    };

                    $detailsRoute = match ($statusValue) {
                        'confirmed' =>
                            route(
                                'booking.success',
                                $booking
                            ),

                        'pending_payment' =>
                            route(
                                'booking.pending',
                                $booking
                            ),

                        default =>
                            route(
                                'booking.pay',
                                $booking
                            ),
                    };
                @endphp

                <div
                    class="p-6 transition hover:bg-slate-50"
                >
                    <div
                        class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                    >
                        <div>

                            <div
                                class="flex flex-wrap items-center gap-2"
                            >
                                <span
                                    class="font-black text-[#1E3A8A]"
                                >
                                    {{ $booking->booking_code }}
                                </span>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-bold {{ $statusData['class'] }}"
                                >
                                    {{ $statusData['label'] }}
                                </span>
                            </div>

                            <div
                                class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-500"
                            >
                                <span>
                                    المقعد:
                                    <strong
                                        class="text-slate-800"
                                    >
                                        {{ $booking->seat_number }}
                                    </strong>
                                </span>

                                @if ($booking->trip)
                                    <span>
                                        الموعد:
                                        <strong
                                            class="text-slate-800"
                                        >
                                            {{ \Illuminate\Support\Carbon::parse($booking->trip->departure_at)->format('Y/m/d H:i') }}
                                        </strong>
                                    </span>
                                @endif
                            </div>

                        </div>

                        <a
                            href="{{ $detailsRoute }}"
                            class="inline-flex items-center justify-center rounded-xl border border-[#1E3A8A] px-4 py-2 text-sm font-bold text-[#1E3A8A] transition hover:bg-[#1E3A8A] hover:text-white"
                        >
                            عرض الحجز
                        </a>
                    </div>
                </div>

            @empty

                <div
                    class="px-6 py-14 text-center"
                >
                    <div class="text-4xl">
                        🚌
                    </div>

                    <h3
                        class="mt-4 font-black text-slate-800"
                    >
                        لا توجد حجوزات قادمة
                    </h3>

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        ابحث عن رحلة مناسبة وابدأ حجزك.
                    </p>

                    <a
                        href="{{ route('trips.index') }}"
                        class="mt-5 inline-flex rounded-xl bg-[#1E3A8A] px-5 py-3 text-sm font-bold text-white"
                    >
                        استعرض الرحلات
                    </a>
                </div>

            @endforelse

        </div>
    </div>

    {{-- Notifications --}}

    <div
        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
    >
        <div
            class="flex items-center justify-between"
        >
            <h2
                class="text-lg font-black"
            >
                آخر إشعاراتي
            </h2>

            <span
                class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-500"
            >
                قريبًا
            </span>
        </div>

        <div
            class="mt-8 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center"
        >
            <div class="text-4xl">
                🔔
            </div>

            <div
                class="mt-4 font-bold"
            >
                مركز الإشعارات
            </div>

            <p
                class="mt-2 text-sm leading-6 text-slate-500"
            >
                سنربطه بخدمة الإشعارات عند بناء
                NotificationService.
            </p>
        </div>
    </div>

</section>

@endsection