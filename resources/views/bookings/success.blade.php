@extends('layouts.app')

@section('title', 'تم الحجز بنجاح')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

        <div
            class="rounded-3xl border border-emerald-200 bg-white p-7 shadow-xl shadow-slate-950/5 sm:p-10 dark:border-emerald-900 dark:bg-slate-900"
        >
            <div
                class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-4xl dark:bg-emerald-950/40"
            >
                ✅
            </div>

            <div class="mt-6 text-center">
                <h1 class="text-3xl font-black">
                    تم تأكيد حجزك
                </h1>

                <p class="mt-3 text-slate-500">
                    نتمنى لك رحلة آمنة ومريحة مع SHOFEER.
                </p>
            </div>

            {{-- Booking Card --}}
            <div
                class="mt-8 rounded-3xl bg-slate-50 p-6 dark:bg-slate-800/60"
            >
                <div
                    class="grid gap-5 sm:grid-cols-2"
                >
                    <div>
                        <p class="text-xs text-slate-400">
                            كود الحجز
                        </p>

                        <p
                            class="mt-1 font-black text-[#1E3A8A] dark:text-blue-300"
                            dir="ltr"
                        >
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
                            الرحلة
                        </p>

                        <p class="mt-1 font-black">
                            {{ $booking->trip->fromCity->name_ar }}
                            ←
                            {{ $booking->trip->toCity->name_ar }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            التاريخ
                        </p>

                        <p class="mt-1 font-black">
                            {{ $booking->trip->departure_at->format('Y-m-d H:i') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            المبلغ
                        </p>

                        <p class="mt-1 font-black">
                            {{ number_format((float) $booking->paid_amount, 2) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            نقطة التجمع
                        </p>

                        <p class="mt-1 font-black">
                            {{ $booking->trip->meeting_point }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Driver --}}
            <div
                class="mt-6 rounded-3xl border border-slate-200 p-6 dark:border-slate-700"
            >
                <h2 class="text-lg font-black">
                    السائق والسيارة
                </h2>

                <div
                    class="mt-5 grid gap-4 sm:grid-cols-2"
                >
                    <div>
                        <p class="text-xs text-slate-400">
                            السائق
                        </p>

                        <p class="mt-1 font-black">
                            {{ $booking->trip->driver->user->name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            السيارة
                        </p>

                        <p class="mt-1 font-black">
                            {{ $booking->trip->car->make }}
                            {{ $booking->trip->car->model }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">
                            اللوحة
                        </p>

                        <p class="mt-1 font-black">
                            {{ $booking->trip->car->plate_number }}
                        </p>
                    </div>

                    @if ($booking->trip->driver->user->phone)
                        <div>
                            <p class="text-xs text-slate-400">
                                واتساب
                            </p>

                            <a
                                href="https://wa.me/{{ preg_replace('/\D+/', '', $booking->trip->driver->user->phone) }}"
                                target="_blank"
                                rel="noopener"
                                class="mt-1 inline-block font-black text-emerald-600"
                                dir="ltr"
                            >
                                {{ $booking->trip->driver->user->phone }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Instructions --}}
            <div
                class="mt-6 rounded-3xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-900 dark:bg-blue-950/20"
            >
                <h2
                    class="font-black text-[#1E3A8A] dark:text-blue-300"
                >
                    تعليمات الرحلة
                </h2>

                <p
                    class="mt-3 leading-7 text-slate-600 dark:text-slate-300"
                >
                    يرجى الحضور إلى نقطة التجمع قبل موعد الانطلاق بـ15 دقيقة.
                </p>

                @if (
                    $booking->passenger_gender->value
                    === 'female'
                )
                    <p
                        class="mt-3 rounded-2xl bg-pink-100 p-4 font-bold text-pink-700 dark:bg-pink-950/30 dark:text-pink-300"
                    >
                        🌸 خصوصيتك مضمونة.
                    </p>
                @endif
            </div>

            {{-- Actions --}}
            <div
                class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
            >
                <button
                    id="calendar-button"
                    type="button"
                    class="rounded-xl border border-slate-200 px-4 py-3 font-bold dark:border-slate-700"
                >
                    📅 تقويم
                </button>

                <button
                    id="share-button"
                    type="button"
                    class="rounded-xl border border-slate-200 px-4 py-3 font-bold dark:border-slate-700"
                >
                    🔗 مشاركة
                </button>

                <button
                    type="button"
                    onclick="window.print()"
                    class="rounded-xl border border-slate-200 px-4 py-3 font-bold dark:border-slate-700"
                >
                    🖨️ طباعة
                </button>

                <a
                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode($booking->trip->meeting_point) }}"
                    target="_blank"
                    rel="noopener"
                    class="rounded-xl bg-[#1E3A8A] px-4 py-3 text-center font-bold text-white"
                >
                    📍 خريطة
                </a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const calendarButton =
        document.getElementById(
            'calendar-button'
        );

    const shareButton =
        document.getElementById(
            'share-button'
        );

    calendarButton?.addEventListener(
        'click',
        () => {
            const start =
                @json(
                    $booking
                        ->trip
                        ->departure_at
                        ->utc()
                        ->format('Ymd\THis\Z')
                );

            const title =
                @json(
                    'رحلة SHOFEER - '.
                    $booking
                        ->trip
                        ->fromCity
                        ->name_ar.
                    ' إلى '.
                    $booking
                        ->trip
                        ->toCity
                        ->name_ar
                );

            const location =
                @json(
                    $booking
                        ->trip
                        ->meeting_point
                );

            const content = [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'BEGIN:VEVENT',
                'DTSTART:' + start,
                'SUMMARY:' + title,
                'LOCATION:' + location,
                'END:VEVENT',
                'END:VCALENDAR',
            ].join('\r\n');

            const blob =
                new Blob(
                    [content],
                    {
                        type:
                            'text/calendar;charset=utf-8',
                    }
                );

            const url =
                URL.createObjectURL(blob);

            const link =
                document.createElement('a');

            link.href = url;
            link.download =
                'shofeer-trip.ics';

            link.click();

            URL.revokeObjectURL(url);
        }
    );

    shareButton?.addEventListener(
        'click',
        async () => {
            const data = {
                title:
                    'SHOFEER',

                text:
                    @json(
                        'حجزي في SHOFEER - '.
                        $booking->booking_code
                    ),

                url:
                    window.location.href,
            };

            if (navigator.share) {
                await navigator.share(data);

                return;
            }

            await navigator.clipboard
                .writeText(
                    window.location.href
                );
        }
    );
});
</script>
@endsection