@extends('layouts.app')

@section('title', 'ركابي - SHOFEER')

@section('content')
<div
    dir="rtl"
    class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                لوحة السائق
            </p>

            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                ركابي
            </h1>

            <p class="mt-2 text-sm leading-7 text-slate-600">
                تظهر لك أقل بيانات ممكنة:
                الاسم الأول ورقم المقعد فقط.
                واتساب متاح للطوارئ خلال آخر 30 دقيقة قبل الانطلاق.
            </p>
        </div>

        <a
            href="{{ route('driver.dashboard') }}"
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

    @if (session('error'))
        <div class="mb-6 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 rounded-2xl border border-blue-100 bg-blue-50 p-5">
        <h2 class="font-bold text-blue-950">
            حماية خصوصية الركاب
        </h2>

        <p class="mt-2 text-sm leading-7 text-blue-900">
            لا تظهر هذه الصفحة هوية الراكب، رقمه الأساسي،
            سعر الحجز، المبلغ المدفوع أو العمولة.
            واتساب يظهر فقط في نافذة الـ30 دقيقة المحددة للطوارئ.
        </p>
    </div>

    @if ($tripRows->isEmpty())
        <section class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
            <p class="font-bold text-slate-900">
                لا توجد رحلات حالية أو قادمة.
            </p>

            <p class="mt-2 text-sm text-slate-500">
                ستظهر قوائم الركاب هنا عند وجود حجوزات مؤكدة.
            </p>
        </section>
    @else
        <div class="space-y-6">
            @foreach ($tripRows as $row)
                @php
                    $trip =
                        $row['trip'];

                    $passengers =
                        $row['passengers'];
                @endphp

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-slate-100 p-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold text-slate-900">
                                    {{ $trip->fromCity?->name ?? '—' }}
                                    ←
                                    {{ $trip->toCity?->name ?? '—' }}
                                </h2>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                    {{ $trip->status->label() }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-slate-500">
                                الانطلاق:
                                {{ $trip->departure_at?->format('Y-m-d H:i') ?? '—' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-xl bg-blue-50 px-4 py-2 text-sm font-bold text-blue-900">
                                {{ $row['passengers_count'] }}
                                راكب
                            </span>

                            @if ($row['whatsapp_window_open'])
                                <span class="rounded-xl bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">
                                    نافذة واتساب مفتوحة
                                </span>
                            @else
                                <span class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-500">
                                    واتساب مخفي
                                </span>
                            @endif
                        </div>
                    </div>

                    @if ($passengers->isEmpty())
                        <div class="px-6 py-10 text-center text-sm text-slate-500">
                            لا توجد حجوزات مؤكدة لهذه الرحلة.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-right text-xs font-semibold text-slate-500">
                                        <th class="px-5 py-3">
                                            الاسم الأول
                                        </th>

                                        <th class="px-5 py-3">
                                            المقعد
                                        </th>

                                        <th class="px-5 py-3">
                                            واتساب للطوارئ
                                        </th>

                                        <th class="px-5 py-3">
                                            المساعدة
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($passengers as $passenger)
                                        <tr>
                                            <td class="px-5 py-4 font-bold text-slate-900">
                                                {{ $passenger['first_name'] }}
                                            </td>

                                            <td class="px-5 py-4">
                                                <span class="inline-flex min-w-10 justify-center rounded-lg bg-slate-100 px-3 py-1.5 font-bold text-slate-700">
                                                    {{ $passenger['seat_number'] }}
                                                </span>
                                            </td>

                                            <td class="px-5 py-4">
                                                @if ($passenger['whatsapp_available'])
                                                    <a
                                                        href="{{ $passenger['whatsapp_url'] }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white"
                                                    >
                                                        فتح واتساب
                                                    </a>
                                                @elseif ($row['whatsapp_window_open'])
                                                    <span class="text-xs font-semibold text-slate-400">
                                                        لم يحدد الراكب واتساب
                                                    </span>
                                                @else
                                                    <span class="text-xs font-semibold text-slate-400">
                                                        متاح فقط آخر 30 دقيقة
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-5 py-4">
                                                <form
                                                    method="POST"
                                                    action="{{ route('driver.passengers.contact-admin', $passenger['booking_id']) }}"
                                                >
                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-bold text-blue-900"
                                                    >
                                                        اتصل بالإدارة
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    @endif
</div>
@endsection
