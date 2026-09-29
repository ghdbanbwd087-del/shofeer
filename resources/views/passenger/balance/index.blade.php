@extends('layouts.app')

@section('title', 'رصيدي - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">المكافآت والرصيد</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">رصيدي</h1>
            <p class="mt-2 text-sm text-slate-600">
                تابع رصيدك وحركات الإضافة والاستخدام في مكان واحد.
            </p>
        </div>

        <a
            href="{{ route('dashboard') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-900"
        >
            العودة للوحة التحكم
        </a>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <section class="rounded-2xl bg-blue-900 p-6 text-white shadow-sm md:col-span-2">
            <p class="text-sm font-semibold text-blue-100">
                الرصيد الحالي
            </p>

            <div class="mt-3 flex items-end gap-2">
                <span class="text-4xl font-bold">
                    {{ number_format((float) $balance->amount, 2) }}
                </span>
                <span class="pb-1 text-sm font-semibold text-blue-100">
                    رصيد
                </span>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button
                    type="button"
                    disabled
                    title="سيتم تفعيل استخدام الرصيد عند ربطه بتدفق الدفع."
                    class="cursor-not-allowed rounded-xl bg-white/20 px-4 py-2 text-sm font-semibold text-white opacity-70"
                >
                    استخدام الرصيد
                </button>

                <button
                    type="button"
                    disabled
                    title="سيتم تفعيل الاسترداد بعد تحديد قواعد السحب والاسترداد."
                    class="cursor-not-allowed rounded-xl border border-white/30 px-4 py-2 text-sm font-semibold text-white opacity-70"
                >
                    استرداد
                </button>
            </div>

            <p class="mt-4 text-xs leading-5 text-blue-100">
                أزرار الاستخدام والاسترداد ظاهرة حسب المواصفات، وسيتم ربطها ماليًا عند تحديد قواعد الدفع والسحب.
            </p>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                الرحلات المكتملة
            </p>

            <div class="mt-3 text-4xl font-bold text-blue-900">
                {{ $completedTrips }}
            </div>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                الرحلات المؤكدة التي تجاوز موعد انطلاقها.
            </p>
        </section>
    </div>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-xl font-bold text-slate-900">
                آخر الجوائز
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                آخر الحركات التي أضافت رصيدًا إلى حسابك.
            </p>
        </div>

        @if ($rewardTransactions->isEmpty())
            <div class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                لا توجد جوائز مالية حتى الآن.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($rewardTransactions as $transaction)
                    <div class="flex flex-col gap-2 rounded-xl border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-slate-900">
                                {{ $transaction->description ?: 'إضافة إلى الرصيد' }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $transaction->created_at?->format('Y-m-d H:i') }}
                            </p>
                        </div>

                        <div class="font-bold text-emerald-600">
                            +{{ number_format((float) $transaction->amount, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-xl font-bold text-slate-900">
                سجل الرصيد
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                جميع عمليات الإضافة والخصم محفوظة في سجل مستقل.
            </p>
        </div>

        @if ($transactions->isEmpty())
            <div class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                لا توجد حركات رصيد حتى الآن.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-right text-xs font-semibold text-slate-500">
                            <th class="px-3 py-3">النوع</th>
                            <th class="px-3 py-3">الوصف</th>
                            <th class="px-3 py-3">المبلغ</th>
                            <th class="px-3 py-3">الرصيد بعد العملية</th>
                            <th class="px-3 py-3">التاريخ</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td class="px-3 py-4">
                                    @if ($transaction->type === 'credit')
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                            إضافة
                                        </span>
                                    @else
                                        <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                                            خصم
                                        </span>
                                    @endif
                                </td>

                                <td class="px-3 py-4 text-slate-700">
                                    {{ $transaction->description ?: 'حركة رصيد' }}
                                </td>

                                <td class="px-3 py-4 font-bold {{ $transaction->type === 'credit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $transaction->type === 'credit' ? '+' : '-' }}
                                    {{ number_format((float) $transaction->amount, 2) }}
                                </td>

                                <td class="px-3 py-4 font-semibold text-slate-900">
                                    {{ number_format((float) $transaction->balance_after, 2) }}
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
