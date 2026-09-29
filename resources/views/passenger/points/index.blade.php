@extends('layouts.app')

@section('title', 'نقاطي - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                برنامج الولاء
            </p>

            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                نقاطي
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                تابع رصيد نقاطك وسجل عمليات الكسب والاستخدام.
            </p>
        </div>

        <a
            href="{{ route('dashboard') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-900"
        >
            العودة للوحة التحكم
        </a>
    </div>

    <section class="overflow-hidden rounded-2xl bg-blue-900 p-6 text-white shadow-sm">
        <p class="text-sm font-semibold text-blue-100">
            النقاط الحالية
        </p>

        <div class="mt-3 flex items-end gap-3">
            <span class="text-5xl font-bold">
                {{ number_format($currentPoints) }}
            </span>

            <span class="pb-1 text-sm font-semibold text-blue-100">
                نقطة
            </span>
        </div>

        <p class="mt-4 max-w-2xl text-sm leading-7 text-blue-100">
            قيمة النقاط وقواعد تحويلها إلى خصم أو رصيد ستُربط لاحقًا
            بعد اعتماد سياسة المكافآت رسميًا.
        </p>
    </section>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-xl font-bold text-emerald-700">
                    +
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        كيف تكسب النقاط؟
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        مصادر الكسب ستُدار من برنامج المكافآت.
                    </p>
                </div>
            </div>

            <ul class="mt-5 space-y-3 text-sm leading-6 text-slate-600">
                <li class="rounded-xl bg-slate-50 p-4">
                    يمكن ربط النقاط بالرحلات المكتملة بعد تحديد عدد النقاط لكل رحلة.
                </li>

                <li class="rounded-xl bg-slate-50 p-4">
                    يمكن منح نقاط من الحملات والعروض والجوائز الشهرية عند تفعيلها.
                </li>

                <li class="rounded-xl bg-slate-50 p-4">
                    كل عملية كسب تُسجل مرة واحدة بمفتاح منع تكرار مستقل.
                </li>
            </ul>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-xl font-bold text-amber-700">
                    −
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        كيف تستخدم النقاط؟
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        الاستخدام المالي غير مفعّل بعد.
                    </p>
                </div>
            </div>

            <ul class="mt-5 space-y-3 text-sm leading-6 text-slate-600">
                <li class="rounded-xl bg-slate-50 p-4">
                    يمكن لاحقًا ربط النقاط بخصم على الحجز بعد تحديد نسبة التحويل.
                </li>

                <li class="rounded-xl bg-slate-50 p-4">
                    يمكن ربطها بمزايا أو عروض محددة بدل تحويلها مباشرة إلى مال.
                </li>

                <li class="rounded-xl bg-slate-50 p-4">
                    النظام يمنع إنفاق نقاط أكثر من الرصيد المتاح.
                </li>
            </ul>
        </section>
    </div>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-xl font-bold text-slate-900">
                سجل النقاط
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                جميع عمليات الكسب والاستخدام محفوظة في سجل مستقل.
            </p>
        </div>

        @if ($transactions->isEmpty())
            <div class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                لا توجد حركات نقاط حتى الآن.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-right text-xs font-semibold text-slate-500">
                            <th class="px-3 py-3">النوع</th>
                            <th class="px-3 py-3">الوصف</th>
                            <th class="px-3 py-3">النقاط</th>
                            <th class="px-3 py-3">الرصيد بعد العملية</th>
                            <th class="px-3 py-3">التاريخ</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td class="px-3 py-4">
                                    @if ($transaction->type === 'earn')
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                            كسب
                                        </span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                            استخدام
                                        </span>
                                    @endif
                                </td>

                                <td class="px-3 py-4 text-slate-700">
                                    {{ $transaction->description ?: 'حركة نقاط' }}
                                </td>

                                <td class="px-3 py-4 font-bold {{ $transaction->type === 'earn' ? 'text-emerald-600' : 'text-amber-700' }}">
                                    {{ $transaction->type === 'earn' ? '+' : '-' }}
                                    {{ number_format($transaction->points) }}
                                </td>

                                <td class="px-3 py-4 font-semibold text-slate-900">
                                    {{ number_format($transaction->balance_after) }}
                                </td>

                                <td class="px-3 py-4 text-slate-500">
                                    {{ $transaction->created_at?->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $transactions->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
