@extends('layouts.app')

@section('title', 'إضافة سيارة')

@section('content')
<section class="py-12">
    <div
        class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8"
    >
        <a
            href="{{ route('driver.cars.index') }}"
            class="text-sm font-bold text-[#1E3A8A] dark:text-blue-300"
        >
            → العودة إلى السيارات
        </a>

        <h1
            class="mt-5 text-3xl font-black"
        >
            إضافة سيارة
        </h1>

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
            action="{{ route('driver.cars.store') }}"
            enctype="multipart/form-data"
            class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
        >
            @csrf

            <div
                class="grid gap-5 sm:grid-cols-2"
            >
                <div>
                    <label class="mb-2 block text-sm font-bold">
                        الشركة
                    </label>

                    <input
                        name="make"
                        value="{{ old('make') }}"
                        required
                        placeholder="Toyota"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        الموديل
                    </label>

                    <input
                        name="model"
                        value="{{ old('model') }}"
                        required
                        placeholder="Hiace"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        سنة الصنع
                    </label>

                    <input
                        type="number"
                        name="year"
                        value="{{ old('year') }}"
                        min="1990"
                        max="{{ now()->year + 1 }}"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        رقم اللوحة
                    </label>

                    <input
                        name="plate_number"
                        value="{{ old('plate_number') }}"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        اللون
                    </label>

                    <input
                        name="color"
                        value="{{ old('color') }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        عدد المقاعد
                    </label>

                    <input
                        type="number"
                        name="seat_count"
                        value="{{ old('seat_count') }}"
                        min="1"
                        max="60"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        نوع السيارة
                    </label>

                    <select
                        name="type"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="">
                            اختر
                        </option>

                        <option value="van">
                            Van
                        </option>

                        <option value="bus">
                            Bus
                        </option>

                        <option value="vip">
                            VIP
                        </option>
                    </select>
                </div>
            </div>

            <div class="mt-6">
                <p class="mb-3 text-sm font-bold">
                    المميزات
                </p>

                <div
                    class="flex flex-wrap gap-4"
                >
                    @foreach ([
                        'مكيف',
                        'USB',
                        'Wi-Fi',
                        'مقاعد مريحة',
                    ] as $feature)
                        <label
                            class="flex items-center gap-2"
                        >
                            <input
                                type="checkbox"
                                name="features[]"
                                value="{{ $feature }}"
                            >

                            <span>
                                {{ $feature }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div
                class="mt-6 grid gap-5 sm:grid-cols-2"
            >
                <div>
                    <label class="mb-2 block text-sm font-bold">
                        صورة السيارة
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept="image/*"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold">
                        صورة الاستمارة
                    </label>

                    <input
                        type="file"
                        name="registration_image"
                        accept="image/*"
                    >
                </div>
            </div>

            <button
                type="submit"
                class="mt-8 w-full rounded-2xl bg-[#1E3A8A] px-6 py-4 font-extrabold text-white"
            >
                حفظ السيارة
            </button>
        </form>
    </div>
</section>
@endsection