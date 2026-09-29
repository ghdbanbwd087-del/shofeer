@extends('layouts.app')

@section('title', 'تفاصيل الرحلة')

@section('content')
<section class="py-10">
    <div
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        <nav
            class="mb-7 text-sm text-slate-500"
        >
            <a
                href="{{ route('home') }}"
                class="hover:text-[#1E3A8A]"
            >
                الرئيسية
            </a>

            <span class="mx-2">
                /
            </span>

            <a
                href="{{ route('trips.index') }}"
                class="hover:text-[#1E3A8A]"
            >
                الرحلات
            </a>

            <span class="mx-2">
                /
            </span>

            <span>
                التفاصيل
            </span>
        </nav>

        <div
            class="grid gap-8 lg:grid-cols-[minmax(0,7fr)_minmax(300px,3fr)]"
        >
            <div class="space-y-6">
                {{-- Route --}}
                <section
                    class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <span
                        class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-extrabold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300"
                    >
                        رحلة متاحة
                    </span>

                    <div
                        class="mt-6 flex items-center justify-between gap-5"
                    >
                        <div>
                            <p
                                class="text-sm text-slate-400"
                            >
                                من
                            </p>

                            <h1
                                class="mt-1 text-3xl font-black"
                            >
                                {{ $trip->fromCity->name_ar }}
                            </h1>
                        </div>

                        <div
                            class="flex flex-1 items-center gap-3"
                        >
                            <div
                                class="h-px flex-1 bg-slate-200"
                            ></div>

                            <span
                                class="text-2xl text-[#F59E0B]"
                            >
                                ←
                            </span>

                            <div
                                class="h-px flex-1 bg-slate-200"
                            ></div>
                        </div>

                        <div class="text-end">
                            <p
                                class="text-sm text-slate-400"
                            >
                                إلى
                            </p>

                            <h2
                                class="mt-1 text-3xl font-black"
                            >
                                {{ $trip->toCity->name_ar }}
                            </h2>
                        </div>
                    </div>

                    <div
                        class="mt-8 grid gap-4 rounded-2xl bg-slate-50 p-5 sm:grid-cols-2 dark:bg-slate-800/50"
                    >
                        <div>
                            <p class="text-sm text-slate-400">
                                التاريخ
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $trip->departure_at->format('Y-m-d') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-400">
                                الوقت
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $trip->departure_at->format('H:i') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-400">
                                نقطة التجمع
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $trip->meeting_point }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-400">
                                نقطة الوصول
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $trip->destination_point ?: '—' }}
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Car --}}
                <section
                    class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-xl font-black"
                    >
                        معلومات السيارة
                    </h2>

                    <div
                        class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <div>
                            <p class="text-sm text-slate-400">
                                الشركة
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $trip->car->make }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-400">
                                الموديل
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $trip->car->model }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-400">
                                السنة
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $trip->car->year }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-400">
                                اللوحة
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $trip->car->plate_number }}
                            </p>
                        </div>
                    </div>

                    @if ($trip->car->features)
                        <div
                            class="mt-6 flex flex-wrap gap-2"
                        >
                            @foreach ($trip->car->features as $feature)
                                <span
                                    class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-[#1E3A8A] dark:bg-blue-950/40 dark:text-blue-300"
                                >
                                    {{ $feature }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Driver --}}
                <section
                    class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-xl font-black"
                    >
                        السائق
                    </h2>

                    <div
                        class="mt-6 flex items-center gap-4"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#1E3A8A] text-xl font-black text-white"
                        >
                            {{ mb_substr($trip->driver->user->name, 0, 1) }}
                        </div>

                        <div>
                            <div
                                class="flex flex-wrap items-center gap-2"
                            >
                                <p class="font-black">
                                    {{ $trip->driver->user->name }}
                                </p>

                                @if ($trip->driver->isApproved())
                                    <span
                                        class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300"
                                    >
                                        ✓ موثق
                                    </span>
                                @endif
                            </div>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                ★ {{ $trip->driver->rating }}
                                ·
                                {{ $trip->driver->total_trips }}
                                رحلة
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Booking Card --}}
            <aside>
                <div
                    class="sticky top-28 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-950/5 dark:border-slate-800 dark:bg-slate-900"
                >
                    <p
                        class="text-sm text-slate-400"
                    >
                        سعر المقعد
                    </p>

                    <p
                        class="mt-2 text-4xl font-black text-[#1E3A8A] dark:text-blue-300"
                    >
                        {{ number_format((float) $trip->price, 2) }}
                    </p>

                    <div
                        class="mt-6 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"
                    >
                        <div
                            class="flex justify-between gap-4"
                        >
                            <span class="text-slate-500">
                                المقاعد المتاحة
                            </span>

                            <strong>
                                {{ $trip->available_seats }}
                            </strong>
                        </div>

                        <div
                            class="mt-3 flex justify-between gap-4"
                        >
                            <span class="text-slate-500">
                                إجمالي المقاعد
                            </span>

                            <strong>
                                {{ $trip->seat_count }}
                            </strong>
                        </div>
                    </div>

                    @if ($trip->canAcceptBookings())
                        <a
                            href="{{ url('/trips/'.$trip->id.'/seats') }}"
                            class="mt-6 block w-full rounded-2xl bg-gradient-to-l from-[#F59E0B] to-amber-400 px-6 py-4 text-center font-black text-slate-950 shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5"
                        >
                            احجز مقعدك
                        </a>
                    @else
                        <button
                            disabled
                            class="mt-6 w-full cursor-not-allowed rounded-2xl bg-slate-200 px-6 py-4 font-black text-slate-500 dark:bg-slate-800"
                        >
                            الحجز غير متاح
                        </button>
                    @endif

                    <div
                        class="mt-5 grid gap-2 text-sm text-slate-500"
                    >
                        <p>✓ اختيار المقعد</p>
                        <p>✓ دفع آمن عبر المنصة</p>
                        <p>✓ إدارة مركزية للحجز</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection