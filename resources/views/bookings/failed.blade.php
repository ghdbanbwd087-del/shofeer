@extends('layouts.app')

@section('title', 'تعذر تأكيد الحجز')

@section('content')
<section class="py-12">
    <div
        class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8"
    >
        <div
            class="rounded-3xl border border-red-200 bg-white p-8 text-center shadow-xl shadow-slate-950/5 dark:border-red-900 dark:bg-slate-900"
        >
            <div
                class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100 text-4xl dark:bg-red-950/40"
            >
                ❌
            </div>

            <h1 class="mt-6 text-3xl font-black">
                تعذر تأكيد الحجز
            </h1>

            <p
                class="mt-3 text-slate-500"
            >
                لم يتم اعتماد عملية الدفع.
            </p>

            <div
                class="mt-7 rounded-2xl border border-red-200 bg-red-50 p-5 text-start dark:border-red-900 dark:bg-red-950/20"
            >
                <p
                    class="text-xs font-bold text-red-500"
                >
                    السبب
                </p>

                <p
                    class="mt-2 font-bold leading-7 text-red-700 dark:text-red-300"
                >
                    {{ $reason }}
                </p>
            </div>

            <div
                class="mt-7 rounded-2xl bg-slate-50 p-5 text-start dark:bg-slate-800/60"
            >
                <p class="text-xs text-slate-400">
                    كود الحجز
                </p>

                <p
                    class="mt-1 font-black"
                    dir="ltr"
                >
                    {{ $booking->booking_code }}
                </p>

                <p class="mt-4 text-xs text-slate-400">
                    الرحلة
                </p>

                <p class="mt-1 font-black">
                    {{ $booking->trip->fromCity->name_ar }}
                    ←
                    {{ $booking->trip->toCity->name_ar }}
                </p>
            </div>

            <div
                class="mt-7 grid gap-3 sm:grid-cols-2"
            >
                <a
                    href="{{ route(
                        'trips.seats.show',
                        [
                            'trip' =>
                                $booking->trip,

                            'gender' =>
                                $booking
                                    ->passenger_gender
                                    ->value,
                        ]
                    ) }}"
                    class="rounded-2xl bg-[#F59E0B] px-6 py-4 font-black text-slate-950"
                >
                    حاول مرة أخرى
                </a>

                <a
                    href="/contact"
                    class="rounded-2xl border border-slate-200 px-6 py-4 font-bold dark:border-slate-700"
                >
                    تواصل معنا
                </a>
            </div>
        </div>
    </div>
</section>
@endsection