@extends('layouts.app')

@section('title', 'السيارات')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <p class="font-extrabold text-[#F59E0B]">
            الإدارة
        </p>

        <h1 class="mt-2 text-3xl font-black">
            السيارات
        </h1>

        <div class="mt-8 overflow-x-auto rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-5 py-4 text-start">
                            السيارة
                        </th>

                        <th class="px-5 py-4 text-start">
                            السائق
                        </th>

                        <th class="px-5 py-4 text-start">
                            اللوحة
                        </th>

                        <th class="px-5 py-4 text-start">
                            المقاعد
                        </th>

                        <th class="px-5 py-4 text-start">
                            الحالة
                        </th>

                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($cars as $car)
                        <tr>
                            <td class="px-5 py-5 font-bold">
                                {{ $car->make }}
                                {{ $car->model }}
                            </td>

                            <td class="px-5 py-5">
                                {{ $car->driver->user->name }}
                            </td>

                            <td class="px-5 py-5">
                                {{ $car->plate_number }}
                            </td>

                            <td class="px-5 py-5">
                                {{ $car->seat_count }}
                            </td>

                            <td class="px-5 py-5">
                                {{ $car->is_active ? 'مفعلة' : 'غير مفعلة' }}
                            </td>

                            <td class="px-5 py-5">
                                <a
                                    href="{{ route('admin.cars.show', $car) }}"
                                    class="font-bold text-[#1E3A8A] dark:text-blue-300"
                                >
                                    عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="p-12 text-center text-slate-500"
                            >
                                لا توجد سيارات.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-7">
            {{ $cars->links() }}
        </div>
    </div>
</section>
@endsection