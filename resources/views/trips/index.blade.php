@extends('layouts.app')

@section('title', 'الرحلات')

@section(
    'description',
    'ابحث عن الرحلات المتاحة بين اليمن والسعودية عبر SHOFEER.'
)

@section('content')
<section
    class="border-b border-slate-200 bg-gradient-to-b from-blue-50 to-white py-12 dark:border-slate-800 dark:from-blue-950/30 dark:to-slate-950"
>
    <div
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        <nav
            class="text-sm text-slate-500"
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

            <span>
                الرحلات
            </span>
        </nav>

        <h1
            class="mt-5 text-3xl font-black text-slate-950 sm:text-4xl dark:text-white"
        >
            الرحلات المتاحة
        </h1>

        <p
            class="mt-3 text-slate-500 dark:text-slate-400"
        >
            وجدنا
            <strong>
                {{ $trips->total() }}
            </strong>
            رحلة مطابقة.
        </p>
    </div>
</section>

<section class="py-10">
    <div
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        {{-- Filters --}}
        <form
            method="GET"
            action="{{ route('trips.index') }}"
            class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="grid gap-4 md:grid-cols-2 lg:grid-cols-4"
            >
                <div>
                    <label
                        class="mb-2 block text-sm font-bold"
                    >
                        من
                    </label>

                    <select
                        name="from"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="">
                            كل المدن
                        </option>

                        @foreach ($cities as $city)
                            <option
                                value="{{ $city->id }}"
                                @selected(request('from') === $city->id)
                            >
                                {{ $city->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-bold"
                    >
                        إلى
                    </label>

                    <select
                        name="to"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="">
                            كل المدن
                        </option>

                        @foreach ($cities as $city)
                            <option
                                value="{{ $city->id }}"
                                @selected(request('to') === $city->id)
                            >
                                {{ $city->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-bold"
                    >
                        التاريخ
                    </label>

                    <input
                        type="date"
                        name="date"
                        value="{{ request('date') }}"
                        min="{{ now()->toDateString() }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-bold"
                    >
                        عدد المقاعد
                    </label>

                    <input
                        type="number"
                        name="seats"
                        min="1"
                        max="60"
                        value="{{ request('seats') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>
            </div>

            <div
                class="mt-4 grid gap-4 md:grid-cols-3"
            >
                <div>
                    <label
                        class="mb-2 block text-sm font-bold"
                    >
                        نوع السيارة
                    </label>

                    <select
                        name="type"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="">
                            الكل
                        </option>

                        <option
                            value="van"
                            @selected(request('type') === 'van')
                        >
                            Van
                        </option>

                        <option
                            value="bus"
                            @selected(request('type') === 'bus')
                        >
                            Bus
                        </option>

                        <option
                            value="vip"
                            @selected(request('type') === 'vip')
                        >
                            VIP
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-bold"
                    >
                        الترتيب
                    </label>

                    <select
                        name="sort"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option
                            value="soonest"
                            @selected(request('sort') === 'soonest')
                        >
                            الأقرب موعداً
                        </option>

                        <option
                            value="latest"
                            @selected(request('sort') === 'latest')
                        >
                            الأحدث
                        </option>

                        <option
                            value="cheapest"
                            @selected(request('sort') === 'cheapest')
                        >
                            الأرخص
                        </option>

                        <option
                            value="highest"
                            @selected(request('sort') === 'highest')
                        >
                            الأعلى تقييماً
                        </option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="flex-1 rounded-xl bg-[#1E3A8A] px-5 py-3 font-extrabold text-white"
                    >
                        بحث
                    </button>

                    <a
                        href="{{ route('trips.index') }}"
                        class="rounded-xl border border-slate-200 px-5 py-3 font-bold dark:border-slate-700"
                    >
                        مسح
                    </a>
                </div>
            </div>
        </form>

        {{-- Results --}}
        @if ($trips->count())
            <div
                class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                @foreach ($trips as $trip)
                    <x-trip-card
                        :trip="$trip"
                    />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $trips->links() }}
            </div>
        @else
            <div
                class="mt-8 rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-[#1E3A8A] dark:bg-blue-950/40 dark:text-blue-300"
                >
                    🔎
                </div>

                <h2
                    class="mt-5 text-xl font-black"
                >
                    لا توجد رحلات مطابقة
                </h2>

                <p
                    class="mt-2 text-slate-500"
                >
                    جرّب تغيير المدن أو التاريخ أو عدد المقاعد.
                </p>
            </div>
        @endif
    </div>
</section>
@endsection