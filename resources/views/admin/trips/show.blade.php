@extends('layouts.app')

@section('title', 'تفاصيل الرحلة')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

        <a
            href="{{ route('admin.trips.index') }}"
            class="text-sm font-bold text-[#1E3A8A]"
        >
            → العودة
        </a>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-8 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">

                <div>
                    <p class="text-sm text-slate-400">
                        المسار
                    </p>

                    <h1 class="mt-1 text-3xl font-black">
                        {{ $trip->fromCity->name_ar }}
                        ←
                        {{ $trip->toCity->name_ar }}
                    </h1>
                </div>

                <span class="h-fit rounded-full bg-blue-50 px-4 py-2 font-bold text-[#1E3A8A]">
                    {{ $trip->status->label() }}
                </span>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-sm text-slate-400">السائق</p>
                    <p class="mt-1 font-bold">{{ $trip->driver->user->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">السيارة</p>
                    <p class="mt-1 font-bold">
                        {{ $trip->car->make }}
                        {{ $trip->car->model }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">الانطلاق</p>
                    <p class="mt-1 font-bold">
                        {{ $trip->departure_at->format('Y-m-d H:i') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">السعر</p>
                    <p class="mt-1 font-bold">
                        {{ number_format((float) $trip->price, 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">المقاعد</p>
                    <p class="mt-1 font-bold">
                        {{ $trip->available_seats }}
                        /
                        {{ $trip->seat_count }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-400">منشورة</p>
                    <p class="mt-1 font-bold">
                        {{ $trip->is_published ? 'نعم' : 'لا' }}
                    </p>
                </div>
            </div>

            <div class="mt-8 rounded-2xl bg-slate-50 p-5 dark:bg-slate-800">
                <p>
                    <strong>نقطة التجمع:</strong>
                    {{ $trip->meeting_point }}
                </p>

                <p class="mt-3">
                    <strong>نقطة الوصول:</strong>
                    {{ $trip->destination_point ?: '—' }}
                </p>
            </div>

            @if (
                ! in_array(
                    $trip->status->value,
                    ['completed', 'cancelled'],
                    true
                )
            )
                <form
                    method="POST"
                    action="{{ route('admin.trips.cancel', $trip) }}"
                    class="mt-7"
                    onsubmit="return confirm('تأكيد إلغاء الرحلة؟');"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="rounded-2xl bg-red-600 px-6 py-3 font-bold text-white"
                    >
                        إلغاء الرحلة
                    </button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection