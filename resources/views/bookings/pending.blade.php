@extends('layouts.app')

@section('title', 'بانتظار التحقق')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

        <div class="rounded-3xl border border-slate-200 bg-white p-7 text-center shadow-xl shadow-slate-950/5 sm:p-10 dark:border-slate-800 dark:bg-slate-900">

            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-amber-100 text-4xl dark:bg-amber-950/30">
                ⏳
            </div>

            <h1 class="mt-6 text-3xl font-black">
                جاري التحقق من الدفع...
            </h1>

            <p class="mx-auto mt-3 max-w-xl leading-7 text-slate-500">
                تم استلام إثبات الدفع وسيتم مراجعته من الإدارة.
            </p>

            <div class="mt-8 rounded-3xl bg-slate-950 p-7 text-white">
                <p class="text-sm font-bold text-slate-400">
                    الوقت المتبقي للتحقق
                </p>

                <p
                    id="payment-countdown"
                    data-countdown-until="{{ $booking->payment->expires_at->toIso8601String() }}"
                    class="mt-3 text-5xl font-black"
                    dir="ltr"
                >
                    60:00
                </p>
            </div>

            <div class="mt-8 grid gap-4 rounded-2xl bg-slate-50 p-6 text-start sm:grid-cols-2 dark:bg-slate-800/60">
                <div>
                    <p class="text-xs text-slate-400">
                        كود الحجز
                    </p>

                    <p class="mt-1 font-black" dir="ltr">
                        {{ $booking->booking_code }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">
                        المقعد
                    </p>

                    <p class="mt-1 font-black">
                        {{ $booking->seat_number }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">
                        المبلغ
                    </p>

                    <p class="mt-1 font-black">
                        {{ number_format((float) $booking->payment->amount, 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">
                        طريقة الدفع
                    </p>

                    <p class="mt-1 font-black">
                        {{ $booking->payment->payment_method->label() }}
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-xs text-slate-400">
                        رقم العملية
                    </p>

                    <p class="mt-1 font-black">
                        {{ $booking->payment->transaction_number }}
                    </p>
                </div>
            </div>

            <a
                href="{{ route('booking.pending', $booking) }}"
                class="mt-7 inline-flex rounded-2xl bg-[#1E3A8A] px-7 py-3.5 font-extrabold text-white"
            >
                تحديث الحالة
            </a>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const countdown =
        document.getElementById(
            'payment-countdown'
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