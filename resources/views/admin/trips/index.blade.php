@extends('layouts.app')

@section('title', 'إدارة الرحلات')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-extrabold text-[#F59E0B]">
                    الإدارة
                </p>

                <h1 class="mt-2 text-3xl font-black">
                    الرحلات
                </h1>
            </div>

            <a
                href="{{ route('admin.trips.create') }}"
                class="rounded-2xl bg-[#1E3A8A] px-6 py-3.5 text-center font-extrabold text-white"
            >
                إضافة رحلة
            </a>
        </div>

        <div class="mt-8 overflow-x-auto rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-5 py-4 text-start">المسار</th>
                        <th class="px-5 py-4 text-start">السائق</th>
                        <th class="px-5 py-4 text-start">التاريخ</th>
                        <th class="px-5 py-4 text-start">السعر</th>
                        <th class="px-5 py-4 text-start">المقاعد</th>
                        <th class="px-5 py-4 text-start">الحالة</th>
                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($trips as $trip)
                        <tr>
                            <td class="px-5 py-5 font-bold">
                                {{ $trip->fromCity->name_ar }}
                                ←
                                {{ $trip->toCity->name_ar }}
                            </td>

                            <td class="px-5 py-5">
                                {{ $trip->driver->user->name }}
                            </td>

                            <td class="px-5 py-5">
                                {{ $trip->departure_at->format('Y-m-d H:i') }}
                            </td>

                            <td class="px-5 py-5">
                                {{ number_format((float) $trip->price, 2) }}
                            </td>

                            <td class="px-5 py-5">
                                {{ $trip->available_seats }}
                                /
                                {{ $trip->seat_count }}
                            </td>

                            <td class="px-5 py-5">
                                {{ $trip->status->label() }}
                            </td>

                            <td class="px-5 py-5">
                                <a
                                    href="{{ route('admin.trips.show', $trip) }}"
                                    class="font-bold text-[#1E3A8A] dark:text-blue-300"
                                >
                                    عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-14 text-center text-slate-500">
                                لا توجد رحلات.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-7">
            {{ $trips->links() }}
        </div>
    </div>
</section>
@endsection