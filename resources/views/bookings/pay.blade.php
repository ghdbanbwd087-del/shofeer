@extends('layouts.app')

@section('title', 'الدفع')

@section('content')
<section class="py-10">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        {{-- Progress --}}
        <div class="mx-auto mb-10 max-w-3xl">
            <div class="grid grid-cols-4">
                @foreach ([
                    1 => ['المقعد', true],
                    2 => ['البيانات', true],
                    3 => ['الدفع', false],
                    4 => ['التأكيد', false],
                ] as $number => [$label, $done])

                    <div class="text-center">
                        <div
                            class="mx-auto flex h-10 w-10 items-center justify-center rounded-full font-black {{ $done ? 'bg-emerald-500 text-white' : ($number === 3 ? 'bg-[#F59E0B] text-slate-950' : 'bg-slate-200 text-slate-500 dark:bg-slate-800') }}"
                        >
                            {{ $done ? '✓' : $number }}
                        </div>

                        <p class="mt-2 text-xs font-bold">
                            {{ $label }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid gap-7 lg:grid-cols-[minmax(0,7fr)_minmax(300px,3fr)]">

            <form
                method="POST"
                action="{{ route('booking.pay.submit', $booking) }}"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf

                <input
                    type="hidden"
                    name="passenger_gender"
                    value="{{ $booking->passenger_gender->value }}"
                >

                {{-- Timer --}}
                <section class="rounded-3xl bg-slate-950 p-6 text-white">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-black">
                                ⏱️ الوقت المتبقي لحجز المقعد
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                أكمل الدفع قبل انتهاء المهلة.
                            </p>
                        </div>

                        <p
                            id="hold-countdown"
                            data-countdown-until="{{ $seat->hold_expires_at->toIso8601String() }}"
                            class="text-4xl font-black"
                            dir="ltr"
                        >
                            15:00
                        </p>
                    </div>
                </section>

                {{-- Passenger --}}
                <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-xl font-black">
                        بيانات الراكب
                    </h2>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                الاسم
                            </label>

                            <input
                                name="passenger_name"
                                value="{{ old('passenger_name', $booking->passenger_name) }}"
                                required
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                الجوال
                            </label>

                            <input
                                name="passenger_phone"
                                value="{{ old('passenger_phone') }}"
                                required
                                dir="ltr"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-end dark:border-slate-700 dark:bg-slate-800"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                رقم الهوية
                            </label>

                            <input
                                name="passenger_id"
                                value="{{ old('passenger_id') }}"
                                required
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold">
                                الجنس
                            </label>

                            <div class="rounded-2xl bg-slate-100 px-4 py-3.5 font-bold dark:bg-slate-800">
                                {{ $booking->passenger_gender->label() }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="mb-2 block text-sm font-bold">
                            ملاحظات
                        </label>

                        <textarea
                            name="passenger_notes"
                            rows="3"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                        >{{ old('passenger_notes') }}</textarea>
                    </div>
                </section>

                {{-- WhatsApp --}}
                <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-xl font-black">
                        قناة التواصل
                    </h2>

                    <label class="mt-5 flex items-center gap-3">
                        <input
                            id="whatsapp_same"
                            type="checkbox"
                            name="whatsapp_same"
                            value="1"
                            @checked(old('whatsapp_same'))
                        >

                        <span class="font-bold">
                            رقم واتساب هو نفس رقم الجوال
                        </span>
                    </label>

                    <div
                        id="whatsapp_other_wrap"
                        class="mt-5"
                    >
                        <label class="mb-2 block text-sm font-bold">
                            رقم واتساب
                        </label>

                        <input
                            name="passenger_whatsapp"
                            value="{{ old('passenger_whatsapp') }}"
                            dir="ltr"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-end dark:border-slate-700 dark:bg-slate-800"
                        >
                    </div>
                </section>

                {{-- Payment Method --}}
                <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-xl font-black">
                        طريقة الدفع
                    </h2>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach ($paymentMethods as $value => $method)
                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="{{ $value }}"
                                    class="peer sr-only"
                                    @checked(old('payment_method') === $value)
                                    required
                                >

                                <div class="rounded-2xl border-2 border-slate-200 p-5 transition peer-checked:border-[#1E3A8A] peer-checked:bg-blue-50 dark:border-slate-700 dark:peer-checked:bg-blue-950/20">
                                    <p class="font-black">
                                        {{ $method['label'] }}
                                    </p>

                                    <p class="mt-2 text-xs text-slate-500">
                                        الحساب:
                                        {{ $method['account_number'] }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- Payment Details --}}
                <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-xl font-black">
                        تفاصيل الدفع
                    </h2>

                    <div class="mt-6 rounded-2xl bg-slate-50 p-5 dark:bg-slate-800">
                        <p class="text-sm text-slate-500">
                            المبلغ المطلوب
                        </p>

                        <p class="mt-1 text-3xl font-black text-[#1E3A8A] dark:text-blue-300">
                            {{ number_format((float) $booking->price, 2) }}
                        </p>
                    </div>

                    <div class="mt-5">
                        <label class="mb-2 block text-sm font-bold">
                            رقم العملية
                        </label>

                        <input
                            name="transaction_number"
                            value="{{ old('transaction_number') }}"
                            required
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                        >
                    </div>

                    <div class="mt-5">
                        <label class="mb-2 block text-sm font-bold">
                            إشعار / إثبات الدفع
                        </label>

                        <input
                            type="file"
                            name="payment_proof"
                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                            required
                            class="block w-full text-sm"
                        >

                        <p class="mt-2 text-xs text-slate-400">
                            JPG / PNG / WEBP / PDF — بحد أقصى 5MB.
                        </p>
                    </div>
                </section>

                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <input
                        type="checkbox"
                        name="terms"
                        value="1"
                        required
                    >

                    <span class="text-sm leading-7">
                        أوافق على الشروط والأحكام وأؤكد صحة بيانات الدفع.
                    </span>
                </label>

                <button
                    type="submit"
                    class="w-full rounded-2xl bg-gradient-to-l from-[#F59E0B] to-amber-400 px-7 py-4 font-black text-slate-950 shadow-lg shadow-amber-500/20"
                >
                    تأكيد وإرسال الدفع
                </button>
            </form>

            {{-- Summary --}}
            <aside>
                <div class="sticky top-28 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-950/5 dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-xs text-slate-400">
                        كود الحجز
                    </p>

                    <p class="mt-1 font-black text-[#1E3A8A]" dir="ltr">
                        {{ $booking->booking_code }}
                    </p>

                    <div class="my-5 border-t border-slate-100 dark:border-slate-800"></div>

                    <div class="grid gap-4 text-sm">
                        <div class="flex justify-between gap-3">
                            <span class="text-slate-500">الرحلة</span>

                            <strong>
                                {{ $booking->trip->fromCity->name_ar }}
                                ←
                                {{ $booking->trip->toCity->name_ar }}
                            </strong>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500">المقعد</span>
                            <strong>{{ $booking->seat_number }}</strong>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500">المبلغ</span>

                            <strong>
                                {{ number_format((float) $booking->price, 2) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const countdown =
        document.getElementById('hold-countdown');

    const same =
        document.getElementById('whatsapp_same');

    const otherWrap =
        document.getElementById('whatsapp_other_wrap');

    const updateWhatsapp = () => {
        if (!same || !otherWrap) {
            return;
        }

        otherWrap.classList.toggle(
            'hidden',
            same.checked
        );
    };

    updateWhatsapp();

    same?.addEventListener(
        'change',
        updateWhatsapp
    );

    if (!countdown) {
        return;
    }

    const expiresAt = new Date(
        countdown.dataset.countdownUntil
    ).getTime();

    const tick = () => {
        const remaining =
            expiresAt - Date.now();

        if (remaining <= 0) {
            countdown.textContent =
                '00:00';

            window.location.reload();

            return;
        }

        const totalSeconds =
            Math.floor(
                remaining / 1000
            );

        const minutes =
            Math.floor(
                totalSeconds / 60
            );

        const seconds =
            totalSeconds % 60;

        countdown.textContent =
            String(minutes)
                .padStart(2, '0')
            + ':'
            + String(seconds)
                .padStart(2, '0');
    };

    tick();

    setInterval(
        tick,
        1000
    );
});
</script>
@endsection