@extends('passenger.layouts.dashboard')

@section('title', 'حجوزاتي')

@section('content')

{{-- ============================================================= --}}
{{-- Page Header                                                   --}}
{{-- ============================================================= --}}

<div
    class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
>
    <div>
        <div
            class="text-sm font-bold text-[#F59E0B]"
        >
            حسابي
        </div>

        <h1
            class="mt-1 text-3xl font-black text-slate-900"
        >
            حجوزاتي
        </h1>

        <p
            class="mt-2 text-sm text-slate-500"
        >
            تابع حجوزاتك القادمة والسابقة والملغاة.
        </p>
    </div>

    <a
        href="{{ route('trips.index') }}"
        class="inline-flex items-center justify-center rounded-2xl bg-[#1E3A8A] px-5 py-3 text-sm font-black text-white transition hover:-translate-y-0.5 hover:shadow-md"
    >
        حجز رحلة جديدة
    </a>
</div>

{{-- ============================================================= --}}
{{-- Tabs                                                          --}}
{{-- ============================================================= --}}

<div class="mb-6 overflow-x-auto">
    <div
        class="inline-flex min-w-full gap-2 rounded-2xl border border-slate-200 bg-white p-2 sm:min-w-0"
    >
        {{-- Upcoming --}}

        <a
            href="{{ route(
                'dashboard.bookings.index',
                ['tab' => 'upcoming']
            ) }}"
            @class([
                'rounded-xl px-5 py-3 text-sm font-bold transition',
                'bg-[#1E3A8A] text-white' => $tab === 'upcoming',
                'text-slate-600 hover:bg-slate-100' => $tab !== 'upcoming',
            ])
        >
            قادمة
        </a>

        {{-- Past --}}

        <a
            href="{{ route(
                'dashboard.bookings.index',
                ['tab' => 'past']
            ) }}"
            @class([
                'rounded-xl px-5 py-3 text-sm font-bold transition',
                'bg-[#1E3A8A] text-white' => $tab === 'past',
                'text-slate-600 hover:bg-slate-100' => $tab !== 'past',
            ])
        >
            سابقة
        </a>

        {{-- Cancelled --}}

        <a
            href="{{ route(
                'dashboard.bookings.index',
                ['tab' => 'cancelled']
            ) }}"
            @class([
                'rounded-xl px-5 py-3 text-sm font-bold transition',
                'bg-[#1E3A8A] text-white' => $tab === 'cancelled',
                'text-slate-600 hover:bg-slate-100' => $tab !== 'cancelled',
            ])
        >
            ملغاة
        </a>
    </div>
</div>

{{-- ============================================================= --}}
{{-- Bookings                                                      --}}
{{-- ============================================================= --}}

<div
    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
>
    <div class="overflow-x-auto">
        <table class="min-w-full">

            {{-- ================================================= --}}
            {{-- Table Header                                      --}}
            {{-- ================================================= --}}

            <thead class="bg-slate-50">
                <tr
                    class="text-right text-xs font-bold text-slate-500"
                >
                    <th class="px-5 py-4">
                        كود الحجز
                    </th>

                    <th class="px-5 py-4">
                        الموعد
                    </th>

                    <th class="px-5 py-4">
                        المقعد
                    </th>

                    <th class="px-5 py-4">
                        المبلغ
                    </th>

                    <th class="px-5 py-4">
                        الحالة
                    </th>

                    <th class="px-5 py-4">
                        الإجراءات
                    </th>
                </tr>
            </thead>

            {{-- ================================================= --}}
            {{-- Table Body                                        --}}
            {{-- ================================================= --}}

            <tbody class="divide-y divide-slate-100">

                @forelse ($bookings as $booking)

                    @php
                        /*
                         * Normalize booking status.
                         */
                        $statusValue =
                            $booking->status instanceof \BackedEnum
                                ? $booking->status->value
                                : (string) $booking->status;

                        /*
                         * Status presentation.
                         */
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

                            'cancelled' => [
                                'label' => 'ملغي',
                                'class' => 'bg-red-50 text-red-700',
                            ],

                            'expired' => [
                                'label' => 'منتهي',
                                'class' => 'bg-slate-100 text-slate-600',
                            ],

                            default => [
                                'label' => $statusValue ?: 'غير معروف',
                                'class' => 'bg-slate-100 text-slate-600',
                            ],
                        };

                        /*
                         * Details page.
                         */
                        $viewRoute = route(
                            'dashboard.bookings.show',
                            $booking
                        );

                        /*
                         * Cancellation is currently available
                         * only before final payment approval.
                         */
                        $canCancel =
                            $tab === 'upcoming'
                            && in_array(
                                $statusValue,
                                [
                                    'held',
                                    'pending_payment',
                                ],
                                true
                            );

                        /*
                         * Rating will be activated later.
                         */
                        $canRate =
                            $tab === 'past'
                            && $statusValue === 'confirmed';
                    @endphp

                    <tr
                        class="text-sm transition hover:bg-slate-50"
                    >

                        {{-- Booking Code --}}

                        <td
                            class="whitespace-nowrap px-5 py-5"
                        >
                            <span
                                class="font-black text-[#1E3A8A]"
                            >
                                {{ $booking->booking_code }}
                            </span>
                        </td>

                        {{-- Trip Date --}}

                        <td
                            class="whitespace-nowrap px-5 py-5 text-slate-600"
                        >
                            @if ($booking->trip)
                                {{ \Illuminate\Support\Carbon::parse(
                                    $booking->trip->departure_at
                                )->format('Y/m/d H:i') }}
                            @else
                                —
                            @endif
                        </td>

                        {{-- Seat --}}

                        <td
                            class="whitespace-nowrap px-5 py-5 font-bold"
                        >
                            {{ $booking->seat_number }}
                        </td>

                        {{-- Price --}}

                        <td
                            class="whitespace-nowrap px-5 py-5"
                        >
                            {{ number_format(
                                (float) $booking->price,
                                2
                            ) }}

                            <span
                                class="text-xs text-slate-400"
                            >
                                ريال
                            </span>
                        </td>

                        {{-- Status --}}

                        <td
                            class="whitespace-nowrap px-5 py-5"
                        >
                            <span
                                class="rounded-full px-3 py-1.5 text-xs font-bold {{ $statusData['class'] }}"
                            >
                                {{ $statusData['label'] }}
                            </span>
                        </td>

                        {{-- Actions --}}

                        <td
                            class="whitespace-nowrap px-5 py-5"
                        >
                            <div
                                class="flex items-center gap-2"
                            >

                                {{-- View --}}

                                <a
                                    href="{{ $viewRoute }}"
                                    class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-[#1E3A8A] hover:text-[#1E3A8A]"
                                >
                                    عرض
                                </a>

                                {{-- Cancel --}}

                                @if ($canCancel)
                                    <a
                                        href="{{ $viewRoute }}#cancel"
                                        class="rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-100"
                                    >
                                        إلغاء
                                    </a>
                                @endif

                                {{-- Rating Placeholder --}}

                                @if ($canRate)
                                    <button
                                        type="button"
                                        disabled
                                        title="سيتم تفعيل التقييم في مرحلة التقييمات"
                                        class="cursor-not-allowed rounded-xl bg-amber-50 px-3 py-2 text-xs font-bold text-amber-500 opacity-70"
                                    >
                                        تقييم
                                    </button>
                                @endif

                            </div>
                        </td>
                    </tr>

                @empty

                    {{-- ========================================= --}}
                    {{-- Empty State                               --}}
                    {{-- ========================================= --}}

                    <tr>
                        <td
                            colspan="6"
                            class="px-6 py-16 text-center"
                        >
                            <div class="text-5xl">
                                🎫
                            </div>

                            <h2
                                class="mt-4 text-lg font-black text-slate-900"
                            >
                                لا توجد حجوزات هنا
                            </h2>

                            <p
                                class="mt-2 text-sm text-slate-500"
                            >
                                لم نجد حجوزات ضمن هذا التصنيف.
                            </p>

                            <a
                                href="{{ route('trips.index') }}"
                                class="mt-5 inline-flex rounded-xl bg-[#1E3A8A] px-5 py-3 text-sm font-bold text-white"
                            >
                                تصفح الرحلات
                            </a>
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>
    </div>

    {{-- ========================================================= --}}
    {{-- Pagination                                                --}}
    {{-- ========================================================= --}}

    @if ($bookings->hasPages())
        <div
            class="border-t border-slate-100 px-5 py-4"
        >
            {{ $bookings->links() }}
        </div>
    @endif

</div>

@endsection