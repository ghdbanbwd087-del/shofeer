@extends('layouts.app')

@section('title', 'سياراتي')

@section('content')
<section class="py-12">
    <div
        class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8"
    >
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <p
                    class="font-extrabold text-[#F59E0B]"
                >
                    السائق
                </p>

                <h1
                    class="mt-2 text-3xl font-black"
                >
                    سياراتي
                </h1>
            </div>

            <a
                href="{{ route('driver.cars.create') }}"
                class="rounded-2xl bg-[#1E3A8A] px-6 py-3.5 text-center font-extrabold text-white"
            >
                إضافة سيارة
            </a>
        </div>

        @if ($cars->count())
            <div
                class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3"
            >
                @foreach ($cars as $car)
                    <article
                        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div>
                                <h2
                                    class="text-xl font-black"
                                >
                                    {{ $car->make }}
                                    {{ $car->model }}
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    {{ $car->year }}
                                    ·
                                    {{ $car->color }}
                                </p>
                            </div>

                            @if ($car->is_active)
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"
                                >
                                    مفعلة
                                </span>
                            @else
                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500"
                                >
                                    غير مفعلة
                                </span>
                            @endif
                        </div>

                        <div
                            class="mt-5 grid grid-cols-2 gap-3 rounded-2xl bg-slate-50 p-4 text-sm dark:bg-slate-800/50"
                        >
                            <div>
                                <span class="text-slate-400">
                                    اللوحة
                                </span>

                                <p class="mt-1 font-bold">
                                    {{ $car->plate_number }}
                                </p>
                            </div>

                            <div>
                                <span class="text-slate-400">
                                    المقاعد
                                </span>

                                <p class="mt-1 font-bold">
                                    {{ $car->seat_count }}
                                </p>
                            </div>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('driver.cars.destroy', $car) }}"
                            class="mt-5"
                            onsubmit="return confirm('هل تريد حذف السيارة؟');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="w-full rounded-xl border border-red-200 px-4 py-3 text-sm font-bold text-red-600 transition hover:bg-red-50 dark:border-red-900"
                            >
                                حذف السيارة
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        @else
            <div
                class="mt-8 rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center dark:border-slate-700 dark:bg-slate-900"
            >
                <p class="text-4xl">
                    🚐
                </p>

                <h2
                    class="mt-4 text-xl font-black"
                >
                    لا توجد سيارات بعد
                </h2>

                <a
                    href="{{ route('driver.cars.create') }}"
                    class="mt-6 inline-flex rounded-xl bg-[#1E3A8A] px-5 py-3 font-bold text-white"
                >
                    أضف سيارتك
                </a>
            </div>
        @endif
    </div>
</section>
@endsection