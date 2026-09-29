@extends('layouts.app')

@section('title', 'مراجعة السائق')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

        <a
            href="{{ route('admin.drivers.index') }}"
            class="text-sm font-bold text-[#1E3A8A] dark:text-blue-300"
        >
            → العودة للسائقين
        </a>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <p class="text-sm text-slate-400">
                        السائق
                    </p>

                    <h1 class="mt-1 text-3xl font-black">
                        {{ $driver->user->name }}
                    </h1>
                </div>

                <span class="rounded-full bg-blue-50 px-4 py-2 text-sm font-bold text-[#1E3A8A] dark:bg-blue-950/40 dark:text-blue-300">
                    {{ $driver->status->label() }}
                </span>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-sm text-slate-400">
                        رقم الهوية
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $driver->national_id }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        رقم الرخصة
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $driver->license_number }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        انتهاء الرخصة
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $driver->license_expiry->format('Y-m-d') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        سنوات الخبرة
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $driver->experience_years }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        التقييم
                    </p>

                    <p class="mt-1 font-bold">
                        ★ {{ $driver->rating }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        الرحلات
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $driver->total_trips }}
                    </p>
                </div>
            </div>

            @if ($driver->bio)
                <div class="mt-7 rounded-2xl bg-slate-50 p-5 dark:bg-slate-800">
                    <p class="text-sm text-slate-400">
                        نبذة
                    </p>

                    <p class="mt-2 leading-7">
                        {{ $driver->bio }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Documents --}}
        <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xl font-black">
                المستندات
            </h2>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <a
                    href="{{ route('admin.drivers.document', [$driver, 'id-front']) }}"
                    target="_blank"
                    class="rounded-2xl border border-slate-200 px-5 py-4 text-center font-bold text-[#1E3A8A] hover:bg-blue-50 dark:border-slate-700 dark:text-blue-300"
                >
                    الهوية — أمام
                </a>

                <a
                    href="{{ route('admin.drivers.document', [$driver, 'id-back']) }}"
                    target="_blank"
                    class="rounded-2xl border border-slate-200 px-5 py-4 text-center font-bold text-[#1E3A8A] hover:bg-blue-50 dark:border-slate-700 dark:text-blue-300"
                >
                    الهوية — خلف
                </a>

                <a
                    href="{{ route('admin.drivers.document', [$driver, 'license']) }}"
                    target="_blank"
                    class="rounded-2xl border border-slate-200 px-5 py-4 text-center font-bold text-[#1E3A8A] hover:bg-blue-50 dark:border-slate-700 dark:text-blue-300"
                >
                    الرخصة
                </a>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-6 grid gap-5 lg:grid-cols-2">

            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-900 dark:bg-emerald-950/20">
                <h2 class="font-black text-emerald-800 dark:text-emerald-300">
                    اعتماد السائق
                </h2>

                <form
                    method="POST"
                    action="{{ route('admin.drivers.approve', $driver) }}"
                    class="mt-5"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-emerald-600 px-6 py-4 font-extrabold text-white"
                    >
                        اعتماد
                    </button>
                </form>
            </div>

            <div class="rounded-3xl border border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-950/20">
                <h2 class="font-black text-red-800 dark:text-red-300">
                    رفض / إيقاف
                </h2>

                <form
                    method="POST"
                    action="{{ route('admin.drivers.reject', $driver) }}"
                    class="mt-5"
                >
                    @csrf
                    @method('PATCH')

                    <textarea
                        name="rejection_reason"
                        rows="3"
                        required
                        placeholder="سبب الرفض"
                        class="w-full rounded-2xl border border-red-200 bg-white px-4 py-3 dark:border-red-900 dark:bg-slate-900"
                    ></textarea>

                    <button
                        type="submit"
                        class="mt-3 w-full rounded-2xl bg-red-600 px-6 py-3 font-bold text-white"
                    >
                        رفض الطلب
                    </button>
                </form>

                @if ($driver->isApproved())
                    <form
                        method="POST"
                        action="{{ route('admin.drivers.suspend', $driver) }}"
                        class="mt-5"
                    >
                        @csrf
                        @method('PATCH')

                        <textarea
                            name="rejection_reason"
                            rows="3"
                            required
                            placeholder="سبب الإيقاف"
                            class="w-full rounded-2xl border border-orange-200 bg-white px-4 py-3 dark:bg-slate-900"
                        ></textarea>

                        <button
                            type="submit"
                            class="mt-3 w-full rounded-2xl bg-orange-600 px-6 py-3 font-bold text-white"
                        >
                            إيقاف السائق
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Cars --}}
        <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-7 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-xl font-black">
                سيارات السائق
            </h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @forelse ($driver->cars as $car)
                    <a
                        href="{{ route('admin.cars.show', $car) }}"
                        class="rounded-2xl bg-slate-50 p-5 transition hover:bg-blue-50 dark:bg-slate-800"
                    >
                        <p class="font-black">
                            {{ $car->make }}
                            {{ $car->model }}
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ $car->plate_number }}
                            ·
                            {{ $car->seat_count }}
                            مقعد
                        </p>
                    </a>
                @empty
                    <p class="text-slate-500">
                        لا توجد سيارات.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection