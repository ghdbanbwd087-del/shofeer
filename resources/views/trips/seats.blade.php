@extends('layouts.app')

@section('title', 'اختيار المقعد')

@section('content')
<section class="py-10">
    <div
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        {{-- Progress --}}
        <div
            class="mx-auto mb-10 max-w-3xl"
        >
            <div
                class="grid grid-cols-4 items-center"
            >
                @foreach ([
                    1 => 'المقعد',
                    2 => 'البيانات',
                    3 => 'الدفع',
                    4 => 'التأكيد',
                ] as $number => $label)
                    <div class="text-center">
                        <div
                            class="mx-auto flex h-10 w-10 items-center justify-center rounded-full font-black {{ $number === 1 ? 'bg-[#1E3A8A] text-white' : 'bg-slate-200 text-slate-500 dark:bg-slate-800' }}"
                        >
                            {{ $number }}
                        </div>

                        <p
                            class="mt-2 text-xs font-bold {{ $number === 1 ? 'text-[#1E3A8A] dark:text-blue-300' : 'text-slate-400' }}"
                        >
                            {{ $label }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Header --}}
        <div>
            <a
                href="{{ route('trips.show', $trip) }}"
                class="text-sm font-bold text-[#1E3A8A] dark:text-blue-300"
            >
                → العودة إلى تفاصيل الرحلة
            </a>

            <h1
                class="mt-5 text-3xl font-black text-slate-950 dark:text-white"
            >
                اختر مقعدك
            </h1>

            <p
                class="mt-3 text-slate-500 dark:text-slate-400"
            >
                {{ $trip->fromCity->name_ar }}
                <span class="mx-2 text-[#F59E0B]">
                    ←
                </span>
                {{ $trip->toCity->name_ar }}
            </p>
        </div>

        {{-- Existing Holds --}}
        @if ($currentHoldBookings->isNotEmpty())
            <div
                class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/20"
            >
                <p
                    class="font-black text-amber-800 dark:text-amber-300"
                >
                    لديك مقعد محجوز مؤقتاً في هذه الرحلة.
                </p>

                <div
                    class="mt-3 flex flex-wrap gap-2"
                >
                    @foreach ($currentHoldBookings as $currentBooking)
                        <a
                            href="{{ route('booking.pay', $currentBooking) }}"
                            class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-black text-slate-950"
                        >
                            المقعد
                            {{ $currentBooking->seat_number }}
                            —
                            متابعة
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Errors --}}
        @if ($errors->any())
            <div
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300"
            >
                <p class="font-black">
                    تعذر إكمال الحجز:
                </p>

                <ul class="mt-3 grid gap-2">
                    @foreach ($errors->all() as $error)
                        <li>
                            • {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div
            class="mt-8 grid gap-7 lg:grid-cols-[minmax(0,7fr)_minmax(300px,3fr)]"
        >
            <div class="space-y-6">

                {{-- Gender --}}
                <section
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="text-xl font-black"
                    >
                        1. جنس الراكب
                    </h2>

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        نستخدم هذه المعلومة لترتيب المقاعد المناسبة والمحافظة على الخصوصية.
                    </p>

                    <div
                        class="mt-5 grid gap-4 sm:grid-cols-2"
                    >
                        <a
                            href="{{ route(
                                'trips.seats.show',
                                [
                                    'trip' => $trip,
                                    'gender' => 'male',
                                ]
                            ) }}"
                            class="rounded-2xl border-2 p-5 text-center transition {{ $gender?->value === 'male' ? 'border-[#1E3A8A] bg-blue-50 dark:bg-blue-950/20' : 'border-slate-200 hover:border-blue-300 dark:border-slate-700' }}"
                        >
                            <span class="text-3xl">
                                👨
                            </span>

                            <p class="mt-2 font-black">
                                ذكر
                            </p>
                        </a>

                        <a
                            href="{{ route(
                                'trips.seats.show',
                                [
                                    'trip' => $trip,
                                    'gender' => 'female',
                                ]
                            ) }}"
                            class="rounded-2xl border-2 p-5 text-center transition {{ $gender?->value === 'female' ? 'border-pink-400 bg-pink-50 dark:bg-pink-950/20' : 'border-slate-200 hover:border-pink-300 dark:border-slate-700' }}"
                        >
                            <span class="text-3xl">
                                👩
                            </span>

                            <p class="mt-2 font-black">
                                أنثى
                            </p>
                        </a>
                    </div>
                </section>

                @if ($gender)
                    <form
                        method="POST"
                        action="{{ route('trips.seats.store', $trip) }}"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="passenger_gender"
                            value="{{ $gender->value }}"
                        >

                        {{-- Seat Map --}}
                        <section
                            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <h2
                                        class="text-xl font-black"
                                    >
                                        2. اختر المقعد
                                    </h2>

                                    <p
                                        class="mt-1 text-sm text-slate-500"
                                    >
                                        المقاعد المميزة بالنجمة هي اقتراحات مناسبة لك.
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-bold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300"
                                >
                                    {{ $availableSeatsCount }}
                                    متاح
                                </span>
                            </div>

                            @if (
                                $gender->value === 'female'
                                && count($suggestedSeatNumbers)
                            )
                                <div
                                    class="mb-6 rounded-2xl border border-pink-200 bg-pink-50 p-4 text-sm text-pink-700 dark:border-pink-900 dark:bg-pink-950/20 dark:text-pink-300"
                                >
                                    🌸 اقترحنا لك حتى 3 مقاعد بناءً على:
                                    المنطقة الخلفية، منطقة النساء، أو القرب من راكبة أخرى.
                                </div>
                            @endif

                            <x-seat-map
                                :seats="$seats"
                                :gender="$gender"
                                :suggested-seat-numbers="$suggestedSeatNumbers"
                                :adjacent-female-seat-numbers="$adjacentFemaleSeatNumbers"
                            />
                        </section>

                        {{-- Family Relation --}}
                        @if ($gender->value === 'male')
                            <section
                                class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                            >
                                <h2
                                    class="text-lg font-black"
                                >
                                    صلة القرابة
                                </h2>

                                <p
                                    class="mt-2 text-sm leading-7 text-slate-500"
                                >
                                    اترك الحقل فارغاً عادةً. إذا اخترت مقعداً بجانب راكبة من أقاربك، حدد صلة القرابة.
                                </p>

                                <select
                                    name="family_relation"
                                    class="mt-4 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <option value="">
                                        لا توجد
                                    </option>

                                    <option
                                        value="husband"
                                        @selected(old('family_relation') === 'husband')
                                    >
                                        زوج
                                    </option>

                                    <option
                                        value="brother"
                                        @selected(old('family_relation') === 'brother')
                                    >
                                        أخ
                                    </option>

                                    <option
                                        value="father"
                                        @selected(old('family_relation') === 'father')
                                    >
                                        أب
                                    </option>

                                    <option
                                        value="son"
                                        @selected(old('family_relation') === 'son')
                                    >
                                        ابن
                                    </option>

                                    <option
                                        value="paternal_uncle"
                                        @selected(old('family_relation') === 'paternal_uncle')
                                    >
                                        عم
                                    </option>

                                    <option
                                        value="maternal_uncle"
                                        @selected(old('family_relation') === 'maternal_uncle')
                                    >
                                        خال
                                    </option>
                                </select>
                            </section>
                        @endif

                        {{-- Hold Warning --}}
                        <div
                            class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/20"
                        >
                            <div class="flex gap-3">
                                <span class="text-2xl">
                                    ⏱️
                                </span>

                                <div>
                                    <p
                                        class="font-black text-amber-800 dark:text-amber-300"
                                    >
                                        لديك 15 دقيقة
                                    </p>

                                    <p
                                        class="mt-1 text-sm leading-7 text-amber-700 dark:text-amber-400"
                                    >
                                        بعد الضغط على متابعة سيتم حجز المقعد لك مؤقتاً لمدة 15 دقيقة لإكمال بيانات الدفع.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between"
                        >
                            <a
                                href="{{ route('trips.show', $trip) }}"
                                class="rounded-2xl border border-slate-200 px-6 py-4 text-center font-bold dark:border-slate-700"
                            >
                                رجوع
                            </a>

                            <button
                                type="submit"
                                class="rounded-2xl bg-gradient-to-l from-[#F59E0B] to-amber-400 px-8 py-4 font-black text-slate-950 shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5"
                            >
                                متابعة للدفع
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- Trip Summary --}}
            <aside>
                <div
                    class="sticky top-28 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-950/5 dark:border-slate-800 dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-bold text-slate-400"
                    >
                        الرحلة
                    </p>

                    <h2
                        class="mt-2 text-xl font-black"
                    >
                        {{ $trip->fromCity->name_ar }}
                        ←
                        {{ $trip->toCity->name_ar }}
                    </h2>

                    <div
                        class="mt-5 grid gap-4 rounded-2xl bg-slate-50 p-4 text-sm dark:bg-slate-800/60"
                    >
                        <div>
                            <span class="text-slate-400">
                                التاريخ
                            </span>

                            <p class="mt-1 font-bold">
                                {{ $trip->departure_at->format('Y-m-d') }}
                            </p>
                        </div>

                        <div>
                            <span class="text-slate-400">
                                الوقت
                            </span>

                            <p class="mt-1 font-bold">
                                {{ $trip->departure_at->format('H:i') }}
                            </p>
                        </div>

                        <div>
                            <span class="text-slate-400">
                                السيارة
                            </span>

                            <p class="mt-1 font-bold">
                                {{ $trip->car->make }}
                                {{ $trip->car->model }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 border-t border-slate-100 pt-5 dark:border-slate-800"
                    >
                        <p class="text-sm text-slate-400">
                            سعر المقعد
                        </p>

                        <p
                            class="mt-1 text-3xl font-black text-[#1E3A8A] dark:text-blue-300"
                        >
                            {{ number_format((float) $trip->price, 2) }}
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection