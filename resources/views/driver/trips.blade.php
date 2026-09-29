@extends('layouts.app')

@section('title', 'رحلاتي')

@section('content')
<section class="py-12">
    <div
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        <p
            class="font-extrabold text-[#F59E0B]"
        >
            السائق
        </p>

        <h1
            class="mt-2 text-3xl font-black"
        >
            رحلاتي
        </h1>

        <div
            class="mt-8 overflow-x-auto rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <table class="min-w-full">
                <thead
                    class="bg-slate-50 text-start text-sm dark:bg-slate-800"
                >
                    <tr>
                        <th class="px-5 py-4 text-start">
                            المسار
                        </th>

                        <th class="px-5 py-4 text-start">
                            الموعد
                        </th>

                        <th class="px-5 py-4 text-start">
                            السيارة
                        </th>

                        <th class="px-5 py-4 text-start">
                            المقاعد
                        </th>

                        <th class="px-5 py-4 text-start">
                            الحالة
                        </th>
                    </tr>
                </thead>

                <tbody
                    class="divide-y divide-slate-100 dark:divide-slate-800"
                >
                    @forelse ($trips as $trip)
                        <tr>
                            <td class="px-5 py-5">
                                <p class="font-bold">
                                    {{ $trip->fromCity->name_ar }}
                                    ←
                                    {{ $trip->toCity->name_ar }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    {{ $trip->meeting_point }}
                                </p>
                            </td>

                            <td class="px-5 py-5">
                                {{ $trip->departure_at->format('Y-m-d H:i') }}
                            </td>

                            <td class="px-5 py-5">
                                {{ $trip->car->make }}
                                {{ $trip->car->model }}
                            </td>

                            <td class="px-5 py-5">
                                {{ $trip->available_seats }}
                                /
                                {{ $trip->seat_count }}
                            </td>

                            <td class="px-5 py-5">
                                <span
                                    class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-[#1E3A8A] dark:bg-blue-950/40 dark:text-blue-300"
                                >
                                    {{ $trip->status->label() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                class="px-5 py-14 text-center text-slate-500"
                            >
                                لا توجد رحلات حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection