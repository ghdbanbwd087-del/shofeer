@extends('layouts.app')

@section('title', 'مستحقاتي - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                لوحة السائق
            </p>

            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                مستحقاتي
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                صافي مستحقاتك من الرحلات المكتملة.
            </p>
        </div>

        <a
            href="{{ route('driver.dashboard') }}"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700"
        >
            العودة للرئيسية
        </a>
    </div>

    <section class="rounded-2xl bg-blue-900 p-6 text-white shadow-sm">
        <p class="text-sm font-semibold text-blue-100">
            صافي المستحقات
        </p>

        <p class="mt-3 text-4xl font-black">
            {{ number_format($totalNet, 2) }}
        </p>
    </section>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <h2 class="text-xl font-bold text-slate-900">
                جدول الرحلات
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                يظهر عدد الركاب وصافي مستحقات كل رحلة مكتملة.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-right text-xs font-semibold text-slate-500">
                        <th class="px-4 py-3">التاريخ</th>
                        <th class="px-4 py-3">المسار</th>
                        <th class="px-4 py-3">الركاب</th>
                        <th class="px-4 py-3">صافي المستحق</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($trips as $trip)
                        <tr>
                            <td class="px-4 py-4 font-semibold text-slate-900">
                                {{ $trip->departure_at?->format('Y-m-d H:i') }}
                            </td>

                            <td class="px-4 py-4 text-slate-700">
                                {{ $trip->fromCity?->name ?? '—' }}
                                ←
                                {{ $trip->toCity?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-4 text-slate-700">
                                {{ $trip->passengers_count }}
                            </td>

                            <td class="px-4 py-4 font-bold text-emerald-700">
                                {{ number_format($trip->net_earnings, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="4"
                                class="px-4 py-12 text-center text-slate-500"
                            >
                                لا توجد رحلات مكتملة لها مستحقات حتى الآن.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($trips->hasPages())
            <div class="p-5">
                {{ $trips->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
