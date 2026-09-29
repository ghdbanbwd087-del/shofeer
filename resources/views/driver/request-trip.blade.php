@extends('layouts.app')

@section('title', 'اطلب رحلة')

@section('content')
<section class="py-12">
    <div
        class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8"
    >
        <p
            class="font-extrabold text-[#F59E0B]"
        >
            السائق
        </p>

        <h1
            class="mt-2 text-3xl font-black"
        >
            اطلب رحلة
        </h1>

        <p
            class="mt-3 text-slate-500"
        >
            أرسل المسار والموعد المقترح إلى الإدارة للمراجعة.
        </p>

        @if ($errors->any())
            <div
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-700"
            >
                @foreach ($errors->all() as $error)
                    <p>
                        • {{ $error }}
                    </p>
                @endforeach
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('driver.request-trip.store') }}"
            class="mt-8 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            @csrf

            <div
                class="grid gap-5 md:grid-cols-2"
            >
                <div>
                    <label class="mb-2 block text-sm font-bold">
                        من
                    </label>

                    <select
                        name="from_city_id"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="">
                            اختر مدينة الانطلاق
                        </option>

                        @foreach ($cities as $city)
                            <option
                                value="{{ $city->id }}"
                                @selected(old('from_city_id') === $city->id)
                            >
                                {{ $city->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        إلى
                    </label>

                    <select
                        name="to_city_id"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="">
                            اختر مدينة الوصول
                        </option>

                        @foreach ($cities as $city)
                            <option
                                value="{{ $city->id }}"
                                @selected(old('to_city_id') === $city->id)
                            >
                                {{ $city->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        التاريخ
                    </label>

                    <input
                        type="date"
                        name="travel_date"
                        value="{{ old('travel_date') }}"
                        min="{{ now()->toDateString() }}"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        الوقت
                    </label>

                    <input
                        type="time"
                        name="departure_time"
                        value="{{ old('departure_time') }}"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        المقاعد
                    </label>

                    <input
                        type="number"
                        name="requested_seats"
                        value="{{ old('requested_seats') }}"
                        min="1"
                        max="60"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>
            </div>

            <div class="mt-5">
                <label class="mb-2 block text-sm font-bold">
                    ملاحظات
                </label>

                <textarea
                    name="notes"
                    rows="4"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                >{{ old('notes') }}</textarea>
            </div>

            <button
                type="submit"
                class="mt-7 rounded-2xl bg-[#1E3A8A] px-7 py-4 font-extrabold text-white"
            >
                إرسال للإدارة
            </button>
        </form>

        {{-- Recent Requests --}}
        <div class="mt-12">
            <h2
                class="text-xl font-black"
            >
                آخر طلباتي
            </h2>

            <div
                class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
            >
                @forelse ($recentRequests as $tripRequest)
                    <div
                        class="flex flex-col gap-3 border-b border-slate-100 p-5 last:border-0 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                    >
                        <div>
                            <p class="font-black">
                                {{ $tripRequest->fromCity->name_ar }}
                                ←
                                {{ $tripRequest->toCity->name_ar }}
                            </p>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                {{ $tripRequest->travel_date->format('Y-m-d') }}
                                ·
                                {{ $tripRequest->departure_time }}
                            </p>
                        </div>

                        <span
                            class="text-sm font-bold"
                        >
                            {{ $tripRequest->status->label() }}
                        </span>
                    </div>
                @empty
                    <p
                        class="p-7 text-center text-slate-500"
                    >
                        لم ترسل أي طلب رحلة بعد.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection