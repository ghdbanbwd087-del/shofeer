@extends('layouts.app')

@section('title', 'إدارة السائقين')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div>
            <p class="font-extrabold text-[#F59E0B]">
                الإدارة
            </p>

            <h1 class="mt-2 text-3xl font-black">
                السائقون
            </h1>
        </div>

        {{-- Status Tabs --}}
        <div class="mt-7 flex flex-wrap gap-2">
            <a
                href="{{ route('admin.drivers.index') }}"
                class="rounded-xl px-4 py-2 text-sm font-bold {{ request('status') ? 'bg-white text-slate-600 dark:bg-slate-900' : 'bg-[#1E3A8A] text-white' }}"
            >
                الكل
            </a>

            @foreach ($statuses as $status)
                <a
                    href="{{ route('admin.drivers.index', ['status' => $status->value]) }}"
                    class="rounded-xl px-4 py-2 text-sm font-bold {{ request('status') === $status->value ? 'bg-[#1E3A8A] text-white' : 'bg-white text-slate-600 dark:bg-slate-900 dark:text-slate-300' }}"
                >
                    {{ $status->label() }}
                </a>
            @endforeach
        </div>

        <div class="mt-7 overflow-x-auto rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-5 py-4 text-start">
                            السائق
                        </th>

                        <th class="px-5 py-4 text-start">
                            الخبرة
                        </th>

                        <th class="px-5 py-4 text-start">
                            التقييم
                        </th>

                        <th class="px-5 py-4 text-start">
                            الحالة
                        </th>

                        <th class="px-5 py-4 text-start">
                            إجراء
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($drivers as $driver)
                        <tr>
                            <td class="px-5 py-5">
                                <p class="font-black">
                                    {{ $driver->user->name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $driver->user->email ?: 'بدون بريد' }}
                                </p>
                            </td>

                            <td class="px-5 py-5">
                                {{ $driver->experience_years }}
                                سنوات
                            </td>

                            <td class="px-5 py-5">
                                ★ {{ $driver->rating }}
                            </td>

                            <td class="px-5 py-5">
                                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold dark:bg-slate-800">
                                    {{ $driver->status->label() }}
                                </span>
                            </td>

                            <td class="px-5 py-5">
                                <a
                                    href="{{ route('admin.drivers.show', $driver) }}"
                                    class="font-bold text-[#1E3A8A] dark:text-blue-300"
                                >
                                    مراجعة
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                class="px-5 py-14 text-center text-slate-500"
                            >
                                لا يوجد سائقون في هذه الحالة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-7">
            {{ $drivers->links() }}
        </div>
    </div>
</section>
@endsection