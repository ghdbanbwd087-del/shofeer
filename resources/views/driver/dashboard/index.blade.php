@extends('layouts.app')

@section('title', 'لوحة السائق - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                لوحة السائق
            </p>

            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                أهلاً {{ auth()->user()->name }}
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                تابع رحلاتك وركابك وتقييمك ومستحقاتك.
            </p>
        </div>

        <a
            href="{{ route('driver.live.index') }}"
            class="rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-bold text-white"
        >
            التتبع المباشر
        </a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                الرحلات
            </p>

            <p class="mt-3 text-3xl font-black text-slate-900">
                {{ $stats['trips'] }}
            </p>
        </section>

        <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
            <p class="text-sm font-semibold text-blue-700">
                الركاب
            </p>

            <p class="mt-3 text-3xl font-black text-blue-900">
                {{ $stats['passengers'] }}
            </p>
        </section>

        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm font-semibold text-amber-700">
                التقييم
            </p>

            <p class="mt-3 text-3xl font-black text-amber-900">
                {{ number_format($stats['rating'], 2) }}
                <span class="text-xl">★</span>
            </p>
        </section>

        <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-sm font-semibold text-emerald-700">
                صافي المستحقات
            </p>

            <p class="mt-3 text-3xl font-black text-emerald-900">
                {{ number_format($stats['earnings'], 2) }}
            </p>
        </section>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a
            href="{{ route('driver.earnings.index') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <p class="font-bold text-slate-900">مستحقاتي</p>
            <p class="mt-1 text-sm text-slate-500">صافي مستحقات الرحلات المكتملة</p>
        </a>

        <a
            href="{{ route('driver.live.index') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <p class="font-bold text-slate-900">التتبع المباشر</p>
            <p class="mt-1 text-sm text-slate-500">شارك موقعك أثناء الرحلة</p>
        </a>

        <a
            href="{{ route('driver.notifications.index') }}"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <p class="font-bold text-slate-900">إشعاراتي</p>
            <p class="mt-1 text-sm text-slate-500">راجع آخر التنبيهات والرسائل</p>
        </a>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="font-bold text-slate-900">حساب موثق</p>
            <p class="mt-1 text-sm text-slate-500">
                حالة السائق:
                {{ $driver->status->label() }}
            </p>
        </div>
    </div>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <h2 class="text-xl font-bold text-slate-900">
                الرحلات الحالية والقادمة
            </h2>
        </div>

        @if ($upcomingTrips->isEmpty())
            <div class="px-6 py-12 text-center text-sm text-slate-500">
                لا توجد رحلات حالية أو قادمة.
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($upcomingTrips as $trip)
                    <article class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-bold text-slate-900">
                                {{ $trip->departure_at?->format('Y-m-d H:i') }}
                            </p>

                            <p class="mt-1 text-sm text-slate-600">
                                {{ $trip->status->label() }}
                            </p>
                        </div>

                        @if (
                            in_array(
                                $trip->status,
                                [
                                    \App\Enums\TripStatus::Boarding,
                                    \App\Enums\TripStatus::InProgress,
                                ],
                                true
                            )
                        )
                            <a
                                href="{{ route('driver.live.index', ['trip' => $trip->id]) }}"
                                class="self-start rounded-xl bg-blue-900 px-4 py-2 text-xs font-bold text-white sm:self-auto"
                            >
                                فتح التتبع
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
