@extends('layouts.app')

@section('title', 'تفاصيل السيارة')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

        <a
            href="{{ route('admin.cars.index') }}"
            class="text-sm font-bold text-[#1E3A8A] dark:text-blue-300"
        >
            → العودة للسيارات
        </a>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h1 class="text-3xl font-black">
                {{ $car->make }}
                {{ $car->model }}
            </h1>

            <p class="mt-2 text-slate-500">
                السائق:
                <a
                    href="{{ route('admin.drivers.show', $car->driver) }}"
                    class="font-bold text-[#1E3A8A]"
                >
                    {{ $car->driver->user->name }}
                </a>
            </p>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-sm text-slate-400">
                        سنة الصنع
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $car->year }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        اللوحة
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $car->plate_number }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        اللون
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $car->color ?: '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        النوع
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $car->type ?: '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        المقاعد
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $car->seat_count }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">
                        الحالة
                    </p>

                    <p class="mt-1 font-bold">
                        {{ $car->is_active ? 'مفعلة' : 'غير مفعلة' }}
                    </p>
                </div>
            </div>

            @if ($car->features)
                <div class="mt-8">
                    <p class="text-sm text-slate-400">
                        المميزات
                    </p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($car->features as $feature)
                            <span class="rounded-full bg-blue-50 px-3 py-1.5 text-sm font-bold text-[#1E3A8A] dark:bg-blue-950/40 dark:text-blue-300">
                                {{ $feature }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection