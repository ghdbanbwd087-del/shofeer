@extends('passenger.layouts.dashboard')

@section('title', 'تفاصيل الحجز')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Booking Status
    |--------------------------------------------------------------------------
    */

    $statusValue =
        $booking->status instanceof \BackedEnum
            ? $booking->status->value
            : (string) $booking->status;

    /*
    |--------------------------------------------------------------------------
    | Payment Status
    |--------------------------------------------------------------------------
    */

    $paymentStatusValue =
        $booking->payment_status instanceof \BackedEnum
            ? $booking->payment_status->value
            : (string) $booking->payment_status;

    /*
    |--------------------------------------------------------------------------
    | Refund Status
    |--------------------------------------------------------------------------
    */

    $refundStatus =
        $latestRefund
            ? (
                $latestRefund->status instanceof \BackedEnum
                    ? $latestRefund->status->value
                    : (string) $latestRefund->status
            )
            : null;

    /*
    |--------------------------------------------------------------------------
    | Status UI
    |--------------------------------------------------------------------------
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
    |--------------------------------------------------------------------------
    | Can Cancel
    |--------------------------------------------------------------------------
    */

    $tripIsFuture =
        $booking->trip
        && \Illuminate\Support\Carbon::parse(
            $booking->trip->departure_at
        )->isFuture();

    $canCancel =
        $tripIsFuture
        && in_array(
            $statusValue,
            [
                'held',
                'pending_payment',
            ],
            true
        );

    /*
    |--------------------------------------------------------------------------
    | Can Request Refund
    |--------------------------------------------------------------------------
    */

    $canRequestRefund =
        $tripIsFuture
        && $statusValue === 'confirmed'
        && $paymentStatusValue === 'paid'
        && ! $latestRefund;
@endphp

{{-- ============================================================= --}}
{{-- Header                                                        --}}
{{-- ============================================================= --}}

<div
    class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
>
    <div>
        <a
            href="{{ route('dashboard.bookings.index') }}"
            class="text-sm font-bold text-[#1E3A8A] hover:underline"
        >
            ← العودة إلى حجوزاتي
        </a>

        <h1
            class="mt-3 text-3xl font-black text-slate-900"
        >
            تفاصيل الحجز
        </h1>
    </div>

    <span
        class="w-fit rounded-full px-4 py-2 text-sm font-bold {{ $statusData['class'] }}"
    >
        {{ $statusData['label'] }}
    </span>
</div>

{{-- ============================================================= --}}
{{-- Booking Code                                                  --}}
{{-- ============================================================= --}}

<section
    class="mb-6 overflow-hidden rounded-3xl bg-gradient-to-l from-[#1E3A8A] to-blue-700 p-6 text-white shadow-lg"
>
    <div
        class="text-sm text-blue-100"
    >
        كود الحجز
    </div>

    <div
        class="mt-2 text-3xl font-black tracking-wider"
    >
        {{ $booking->booking_code }}
    </div>
</section>

<div
    class="grid gap-6 xl:grid-cols-[1.5fr_1fr]"
>

    {{-- ========================================================= --}}
    {{-- Main Details                                              --}}
    {{-- ========================================================= --}}

    <div class="space-y-6">

        {{-- ===================================================== --}}
        {{-- Booking                                               --}}
        {{-- ===================================================== --}}

        <section
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <h2
                class="text-lg font-black"
            >
                بيانات الحجز
            </h2>

            <div
                class="mt-6 grid gap-5 sm:grid-cols-2"
            >

                <div>
                    <div
                        class="text-xs font-bold text-slate-400"
                    >
                        رقم المقعد
                    </div>

                    <div
                        class="mt-1 font-black"
                    >
                        {{ $booking->seat_number }}
                    </div>
                </div>

                <div>
                    <div
                        class="text-xs font-bold text-slate-400"
                    >
                        اسم الراكب
                    </div>

                    <div
                        class="mt-1 font-black"
                    >
                        {{ $booking->passenger_name ?: $user->name }}
                    </div>
                </div>

                <div>
                    <div
                        class="text-xs font-bold text-slate-400"
                    >
                        سعر الحجز
                    </div>

                    <div
                        class="mt-1 font-black"
                    >
                        {{ number_format(
                            (float) $booking->price,
                            2
                        ) }}
                        ريال
                    </div>
                </div>

                <div>
                    <div
                        class="text-xs font-bold text-slate-400"
                    >
                        المدفوع
                    </div>

                    <div
                        class="mt-1 font-black"
                    >
                        {{ number_format(
                            (float) $booking->paid_amount,
                            2
                        ) }}
                        ريال
                    </div>
                </div>

                <div>
                    <div
                        class="text-xs font-bold text-slate-400"
                    >
                        حالة الدفع
                    </div>

                    <div
                        class="mt-1 font-black"
                    >
                        {{ match ($paymentStatusValue) {
                            'paid' => 'مدفوع',
                            'pending' => 'قيد التحقق',
                            'failed' => 'فشل',
                            'refunded' => 'مسترد',
                            default => 'غير مدفوع',
                        } }}
                    </div>
                </div>

                <div>
                    <div
                        class="text-xs font-bold text-slate-400"
                    >
                        تاريخ الحجز
                    </div>

                    <div
                        class="mt-1 font-black"
                    >
                        {{ $booking->created_at?->format(
                            'Y/m/d H:i'
                        ) }}
                    </div>
                </div>

            </div>
        </section>

        {{-- ===================================================== --}}
        {{-- Trip                                                  --}}
        {{-- ===================================================== --}}

        <section
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <h2
                class="text-lg font-black"
            >
                بيانات الرحلة
            </h2>

            @if ($booking->trip)

                <div
                    class="mt-6 grid gap-5 sm:grid-cols-2"
                >

                    <div>
                        <div
                            class="text-xs font-bold text-slate-400"
                        >
                            موعد الانطلاق
                        </div>

                        <div
                            class="mt-1 font-black"
                        >
                            {{ \Illuminate\Support\Carbon::parse(
                                $booking->trip->departure_at
                            )->format('Y/m/d H:i') }}
                        </div>
                    </div>

                    <div>
                        <div
                            class="text-xs font-bold text-slate-400"
                        >
                            نقطة التجمع
                        </div>

                        <div
                            class="mt-1 font-black"
                        >
                            {{ $booking->trip->meeting_point ?: '—' }}
                        </div>
                    </div>

                </div>

                <a
                    href="{{ route(
                        'trips.show',
                        $booking->trip
                    ) }}"
                    class="mt-6 inline-flex rounded-xl border border-[#1E3A8A] px-4 py-2 text-sm font-bold text-[#1E3A8A] transition hover:bg-[#1E3A8A] hover:text-white"
                >
                    عرض الرحلة
                </a>

            @else

                <p
                    class="mt-5 text-sm text-slate-500"
                >
                    بيانات الرحلة غير متوفرة.
                </p>

            @endif
        </section>

        {{-- ===================================================== --}}
        {{-- Cancelled                                             --}}
        {{-- ===================================================== --}}

        @if (
            $statusValue === 'cancelled'
            || $statusValue === 'expired'
        )

            <section
                class="rounded-3xl border border-red-100 bg-red-50 p-6"
            >
                <h2
                    class="font-black text-red-800"
                >
                    سبب الإلغاء
                </h2>

                <p
                    class="mt-3 text-sm leading-7 text-red-700"
                >
                    {{ $booking->cancel_reason ?: 'لم يتم تسجيل سبب.' }}
                </p>

                @if ($booking->cancelled_at)

                    <div
                        class="mt-3 text-xs text-red-500"
                    >
                        {{ $booking->cancelled_at->format(
                            'Y/m/d H:i'
                        ) }}
                    </div>

                @endif
            </section>

        @endif

    </div>

    {{-- ========================================================= --}}
    {{-- Actions                                                   --}}
    {{-- ========================================================= --}}

    <aside class="space-y-5">

        {{-- ===================================================== --}}
        {{-- Held                                                  --}}
        {{-- ===================================================== --}}

        @if ($statusValue === 'held')

            <a
                href="{{ route(
                    'booking.pay',
                    $booking
                ) }}"
                class="flex w-full items-center justify-center rounded-2xl bg-[#1E3A8A] px-5 py-4 font-black text-white"
            >
                متابعة للدفع
            </a>

        @endif

        {{-- ===================================================== --}}
        {{-- Pending Payment                                       --}}
        {{-- ===================================================== --}}

        @if ($statusValue === 'pending_payment')

            <a
                href="{{ route(
                    'booking.pending',
                    $booking
                ) }}"
                class="flex w-full items-center justify-center rounded-2xl bg-[#F59E0B] px-5 py-4 font-black text-slate-950"
            >
                متابعة التحقق
            </a>

        @endif

        {{-- ===================================================== --}}
        {{-- Confirmed                                             --}}
        {{-- ===================================================== --}}

        @if ($statusValue === 'confirmed')

            <a
                href="{{ route(
                    'booking.success',
                    $booking
                ) }}"
                class="flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 py-4 font-black text-white"
            >
                بطاقة الحجز
            </a>

        @endif

        {{-- ===================================================== --}}
        {{-- Cancellation                                         --}}
        {{-- ===================================================== --}}

        @if ($canCancel)

            <section
                id="cancel"
                class="rounded-3xl border border-red-200 bg-white p-5 shadow-sm"
            >
                <h2
                    class="font-black text-red-700"
                >
                    إلغاء الحجز
                </h2>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    سيتم تحرير المقعد فورًا بعد الإلغاء.
                </p>

                @if ($errors->has('booking'))

                    <div
                        class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700"
                    >
                        {{ $errors->first('booking') }}
                    </div>

                @endif

                <form
                    method="POST"
                    action="{{ route(
                        'dashboard.bookings.cancel',
                        $booking
                    ) }}"
                    class="mt-5"
                >
                    @csrf
                    @method('PATCH')

                    <label
                        for="cancel_reason"
                        class="mb-2 block text-sm font-bold"
                    >
                        سبب الإلغاء
                    </label>

                    <textarea
                        id="cancel_reason"
                        name="cancel_reason"
                        rows="4"
                        maxlength="500"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-red-400"
                        placeholder="سبب الإلغاء..."
                    >{{ old('cancel_reason') }}</textarea>

                    @error('cancel_reason')

                        <div
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ $message }}
                        </div>

                    @enderror

                    <button
                        type="submit"
                        onclick="return confirm('هل أنت متأكد من إلغاء الحجز؟')"
                        class="mt-4 w-full rounded-2xl bg-red-600 px-5 py-3 font-black text-white hover:bg-red-700"
                    >
                        تأكيد إلغاء الحجز
                    </button>
                </form>
            </section>

        @endif

        {{-- ===================================================== --}}
        {{-- New Refund Request                                    --}}
        {{-- ===================================================== --}}

        @if ($canRequestRefund)

            <section
                class="rounded-3xl border border-amber-200 bg-white p-5 shadow-sm"
            >
                <h2
                    class="font-black text-slate-900"
                >
                    طلب استرداد
                </h2>

                <p
                    class="mt-2 text-sm leading-7 text-slate-500"
                >
                    سيُرسل طلبك إلى الإدارة للمراجعة.
                    لن يتم إلغاء الحجز أو تحرير المقعد
                    حتى تعتمد الإدارة الاسترداد.
                </p>

                @if ($errors->has('refund'))

                    <div
                        class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700"
                    >
                        {{ $errors->first('refund') }}
                    </div>

                @endif

                <form
                    method="POST"
                    action="{{ route(
                        'dashboard.bookings.refund',
                        $booking
                    ) }}"
                    class="mt-5"
                >
                    @csrf

                    <label
                        for="reason"
                        class="mb-2 block text-sm font-bold"
                    >
                        سبب طلب الاسترداد
                    </label>

                    <textarea
                        id="reason"
                        name="reason"
                        rows="4"
                        maxlength="1000"
                        required
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#F59E0B]"
                        placeholder="اكتب سبب طلب الاسترداد..."
                    >{{ old('reason') }}</textarea>

                    @error('reason')

                        <div
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ $message }}
                        </div>

                    @enderror

                    <button
                        type="submit"
                        class="mt-4 w-full rounded-2xl bg-[#F59E0B] px-5 py-3 font-black text-slate-950"
                    >
                        إرسال طلب الاسترداد
                    </button>
                </form>
            </section>

        @endif

        {{-- ===================================================== --}}
        {{-- Refund Pending                                        --}}
        {{-- ===================================================== --}}

        @if ($refundStatus === 'pending')

            <section
                class="rounded-3xl border border-amber-200 bg-amber-50 p-5"
            >
                <div
                    class="font-black text-amber-800"
                >
                    طلب الاسترداد قيد المراجعة
                </div>

                <div
                    class="mt-3 text-sm text-amber-700"
                >
                    المبلغ المطلوب:
                    <strong>
                        {{ number_format(
                            (float) $latestRefund->amount,
                            2
                        ) }}
                        ريال
                    </strong>
                </div>

                @if ($latestRefund->reason)

                    <div
                        class="mt-3 text-sm leading-7 text-amber-700"
                    >
                        السبب:
                        {{ $latestRefund->reason }}
                    </div>

                @endif
            </section>

        @endif

        {{-- ===================================================== --}}
        {{-- Refund Processed                                      --}}
        {{-- ===================================================== --}}

        @if ($refundStatus === 'processed')

            <section
                class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5"
            >
                <div
                    class="font-black text-emerald-800"
                >
                    تم تنفيذ الاسترداد
                </div>

                <div
                    class="mt-3 text-sm text-emerald-700"
                >
                    المبلغ:
                    {{ number_format(
                        (float) $latestRefund->amount,
                        2
                    ) }}
                    ريال
                </div>
            </section>

        @endif

        {{-- ===================================================== --}}
        {{-- Refund Rejected                                       --}}
        {{-- ===================================================== --}}

        @if ($refundStatus === 'rejected')

            <section
                class="rounded-3xl border border-red-200 bg-red-50 p-5"
            >
                <div
                    class="font-black text-red-800"
                >
                    تم رفض طلب الاسترداد
                </div>

                @if ($latestRefund->rejection_reason)

                    <p
                        class="mt-3 text-sm leading-7 text-red-700"
                    >
                        {{ $latestRefund->rejection_reason }}
                    </p>

                @endif
            </section>

        @endif

    </aside>

</div>

@endsection