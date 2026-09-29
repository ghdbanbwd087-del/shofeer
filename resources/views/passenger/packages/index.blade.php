@extends('layouts.app')

@section('title', 'بضاعتي - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                خدمة البضائع
            </p>

            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                بضاعتي
            </h1>
        </div>

        <a
            href="{{ route('packages.create') }}"
            class="rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-bold text-white"
        >
            أرسل بضاعة
        </a>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ([
            'active' => 'الحالية',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغاة',
        ] as $key => $label)
            <a
                href="{{ route('dashboard.packages.index', ['tab' => $key]) }}"
                class="rounded-xl px-4 py-2 text-sm font-semibold {{ $tab === $key ? 'bg-blue-900 text-white' : 'border border-slate-200 bg-white text-slate-700' }}"
            >
                {{ $label }}
                <span class="mr-1 opacity-70">
                    ({{ $counts[$key] }})
                </span>
            </a>
        @endforeach
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if ($packages->isEmpty())
            <div class="px-6 py-14 text-center text-sm text-slate-500">
                لا توجد بضائع في هذا التبويب.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-right text-xs font-semibold text-slate-500">
                            <th class="px-4 py-3">كود التتبع</th>
                            <th class="px-4 py-3">المسار</th>
                            <th class="px-4 py-3">التاريخ</th>
                            <th class="px-4 py-3">الحالة</th>
                            <th class="px-4 py-3">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($packages as $package)
                            <tr>
                                <td class="px-4 py-4 font-bold text-blue-900">
                                    {{ $package->tracking_code }}
                                </td>

                                <td class="px-4 py-4 text-slate-700">
                                    {{ $package->from_city }}
                                    ←
                                    {{ $package->to_city }}
                                </td>

                                <td class="px-4 py-4 text-slate-500">
                                    {{ $package->requested_date?->format('Y-m-d') }}
                                </td>

                                <td class="px-4 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                        {{ $package->status->label() }}
                                    </span>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a
                                            href="{{ route('dashboard.packages.show', $package) }}"
                                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700"
                                        >
                                            عرض
                                        </a>

                                        <a
                                            href="{{ route('packages.track', ['code' => $package->tracking_code]) }}"
                                            class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700"
                                        >
                                            تتبع
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-5">
                {{ $packages->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
