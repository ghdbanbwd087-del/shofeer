@extends('layouts.app')

@section('title', 'السائقون - SHOFEER')

@section('content')
<div
    dir="rtl"
    class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                لوحة الإدارة
            </p>

            <h1 class="mt-1 text-3xl font-black text-slate-900">
                السائقون
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                مراجعة طلبات السائقين ومتابعة حالات التوثيق.
            </p>
        </div>

        <a
            href="{{ route('admin.dashboard') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm"
        >
            العودة للرئيسية
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="mb-6 overflow-x-auto">
        <div class="inline-flex min-w-full gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm sm:min-w-0">
            @foreach ($tabs as $tab)
                @php
                    $active =
                        $status === $tab->value;

                    $count =
                        (int) (
                            $counts[
                                $tab->value
                            ]
                            ?? 0
                        );
                @endphp

                <a
                    href="{{ route('admin.drivers.index', ['status' => $tab->value]) }}"
                    class="flex min-w-[150px] items-center justify-between gap-3 rounded-xl px-4 py-3 text-sm font-bold transition
                        {{
                            $active
                                ? 'bg-blue-900 text-white'
                                : 'text-slate-600 hover:bg-slate-50'
                        }}"
                >
                    <span>
                        {{ $tab->label() }}
                    </span>

                    <span
                        class="rounded-full px-2 py-0.5 text-xs
                        {{
                            $active
                                ? 'bg-white/15 text-white'
                                : 'bg-slate-100 text-slate-500'
                        }}"
                    >
                        {{ $count }}
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form
            method="GET"
            action="{{ route('admin.drivers.index') }}"
            class="flex flex-col gap-3 sm:flex-row"
        >
            <input
                type="hidden"
                name="status"
                value="{{ $status }}"
            >

            <input
                name="q"
                value="{{ $search }}"
                placeholder="بحث بالاسم أو البريد الإلكتروني"
                class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-blue-900"
            >

            <button
                type="submit"
                class="rounded-xl bg-blue-900 px-6 py-3 text-sm font-bold text-white"
            >
                بحث
            </button>

            <a
                href="{{ route('admin.drivers.index', ['status' => $status]) }}"
                class="rounded-xl border border-slate-200 px-6 py-3 text-center text-sm font-bold text-slate-600"
            >
                مسح
            </a>
        </form>
    </section>

    @if ($drivers->isEmpty())
        <section class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
            <p class="font-bold text-slate-900">
                لا توجد سجلات في هذا التبويب.
            </p>

            <p class="mt-2 text-sm text-slate-500">
                غيّر التبويب أو عبارة البحث لعرض نتائج أخرى.
            </p>
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-right text-xs font-bold text-slate-500">
                            <th class="px-5 py-4">
                                السائق
                            </th>

                            <th class="px-5 py-4">
                                الحالة
                            </th>

                            <th class="px-5 py-4">
                                المستندات
                            </th>

                            <th class="px-5 py-4">
                                الخبرة
                            </th>

                            <th class="px-5 py-4">
                                التقييم
                            </th>

                            <th class="px-5 py-4">
                                السيارات
                            </th>

                            <th class="px-5 py-4">
                                الإجراء
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($drivers as $driver)
                            @php
                                $documentsComplete =
                                    filled($driver->id_image_front)
                                    && filled($driver->id_image_back)
                                    && filled($driver->license_image);
                            @endphp

                            <tr class="align-middle">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-900">
                                        {{ $driver->user?->name ?? '—' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $driver->user?->email ?? 'بدون بريد إلكتروني' }}
                                    </p>

                                    <p class="mt-1 text-[11px] text-slate-400">
                                        تاريخ الطلب:
                                        {{ $driver->created_at?->format('Y-m-d') ?? '—' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    @php
                                        $statusClasses =
                                            match ($driver->status) {
                                                \App\Enums\DriverStatus::Approved =>
                                                    'bg-emerald-50 text-emerald-700',

                                                \App\Enums\DriverStatus::Rejected =>
                                                    'bg-rose-50 text-rose-700',

                                                \App\Enums\DriverStatus::Suspended =>
                                                    'bg-slate-100 text-slate-700',

                                                default =>
                                                    'bg-amber-50 text-amber-800',
                                            };
                                    @endphp

                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                                        {{ $driver->status->label() }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($documentsComplete)
                                        <span class="text-xs font-bold text-emerald-700">
                                            مكتملة
                                        </span>
                                    @else
                                        <span class="text-xs font-bold text-amber-700">
                                            غير مكتملة
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    {{ $driver->experience_years ?? 0 }}
                                    سنة
                                </td>

                                <td class="px-5 py-4">
                                    <span class="font-bold text-slate-900">
                                        {{ number_format((float) $driver->rating, 2) }}
                                    </span>
                                    <span class="text-amber-500">
                                        ★
                                    </span>
                                </td>

                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    {{ $driver->cars->count() }}
                                </td>

                                <td class="px-5 py-4">
                                    <a
                                        href="{{ route('admin.drivers.show', $driver) }}"
                                        class="inline-flex rounded-xl px-4 py-2 text-xs font-bold
                                            {{
                                                $driver->status === \App\Enums\DriverStatus::Pending
                                                    ? 'bg-blue-900 text-white'
                                                    : 'bg-blue-50 text-blue-900'
                                            }}"
                                    >
                                        {{
                                            $driver->status === \App\Enums\DriverStatus::Pending
                                                ? 'مراجعة الطلب'
                                                : 'عرض الملف'
                                        }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $drivers->links() }}
            </div>
        </section>
    @endif

    <div class="mt-5 rounded-xl bg-blue-50 px-4 py-3 text-xs leading-6 text-blue-900">
        أرقام الهوية والرخصة لا تظهر في هذه القائمة. مراجعة المستندات والإجراءات التفصيلية تتم من صفحة ملف السائق الحالية.
    </div>
</div>
@endsection
