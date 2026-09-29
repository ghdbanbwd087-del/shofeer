@extends('layouts.app')

@section('title', 'إضافة رحلة')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

        <a
            href="{{ route('admin.trips.index') }}"
            class="text-sm font-bold text-[#1E3A8A] dark:text-blue-300"
        >
            → العودة للرحلات
        </a>

        <h1 class="mt-5 text-3xl font-black">
            إضافة رحلة كاملة
        </h1>

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-700">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.trips.store') }}"
            class="mt-8 rounded-3xl border border-slate-200 bg-white p-7 dark:border-slate-800 dark:bg-slate-900"
        >
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold">
                        السائق
                    </label>

                    <select
                        id="driver_id"
                        name="driver_id"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="">
                            اختر السائق
                        </option>

                        @foreach ($drivers as $driver)
                            <option
                                value="{{ $driver->id }}"
                                @selected(old('driver_id') === $driver->id)
                            >
                                {{ $driver->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        السيارة
                    </label>

                    <select
                        name="car_id"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="">
                            اختر السيارة
                        </option>

                        @foreach ($drivers as $driver)
                            @foreach ($driver->cars as $car)
                                <option
                                    value="{{ $car->id }}"
                                    @selected(old('car_id') === $car->id)
                                >
                                    {{ $driver->user->name }}
                                    —
                                    {{ $car->make }}
                                    {{ $car->model }}
                                    —
                                    {{ $car->plate_number }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>

                    <p class="mt-2 text-xs text-slate-400">
                        النظام سيتحقق أن السيارة تخص السائق المختار.
                    </p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        من
                    </label>

                    <select
                        name="from_city_id"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="">اختر</option>

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
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="">اختر</option>

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
                        موعد الانطلاق
                    </label>

                    <input
                        type="datetime-local"
                        name="departure_at"
                        value="{{ old('departure_at') }}"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        السعر
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="price"
                        value="{{ old('price') }}"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        عدد المقاعد
                    </label>

                    <input
                        type="number"
                        min="1"
                        max="60"
                        name="seat_count"
                        value="{{ old('seat_count') }}"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        نقطة التجمع
                    </label>

                    <input
                        name="meeting_point"
                        value="{{ old('meeting_point') }}"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold">
                        نقطة الوصول
                    </label>

                    <input
                        name="destination_point"
                        value="{{ old('destination_point') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-bold">
                        ملاحظات
                    </label>

                    <textarea
                        name="notes"
                        rows="4"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >{{ old('notes') }}</textarea>
                </div>

                <label class="flex items-center gap-3 md:col-span-2">
                    <input
                        type="checkbox"
                        name="is_published"
                        value="1"
                        checked
                    >

                    نشر الرحلة مباشرة
                </label>
            </div>

            <button
                type="submit"
                class="mt-7 w-full rounded-2xl bg-[#1E3A8A] px-7 py-4 font-extrabold text-white"
            >
                إنشاء الرحلة
            </button>
        </form>
    </div>
</section>
@endsection