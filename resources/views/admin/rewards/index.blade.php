@extends('layouts.app')

@section('title', 'الجوائز المالية - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-semibold text-amber-600">
            لوحة الإدارة
        </p>

        <h1 class="mt-1 text-3xl font-bold text-slate-900">
            الجوائز المالية
        </h1>

        <p class="mt-2 text-sm text-slate-600">
            إعداد قواعد الجوائز الشهرية وتشغيلها بأمان بدون تكرار الصرف.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <h2 class="text-xl font-bold text-slate-900">
                قواعد الجوائز
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                لا توجد مبالغ افتراضية؛ الإدارة تحدد القواعد المالية صراحة.
            </p>

            @if ($rewards->isEmpty())
                <div class="mt-6 rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                    لا توجد قواعد جوائز بعد.
                </div>
            @else
                <div class="mt-6 space-y-5">
                    @foreach ($rewards as $reward)
                        <form
                            method="POST"
                            action="{{ route('admin.rewards.update', $reward) }}"
                            class="rounded-2xl border border-slate-200 p-5"
                        >
                            @csrf
                            @method('PATCH')

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                                        الرمز
                                    </label>

                                    <input
                                        type="text"
                                        name="code"
                                        value="{{ $reward->code }}"
                                        class="w-full rounded-xl border-slate-300"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                                        الاسم
                                    </label>

                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ $reward->name }}"
                                        class="w-full rounded-xl border-slate-300"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                                        مبلغ الجائزة
                                    </label>

                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        name="amount"
                                        value="{{ $reward->amount }}"
                                        class="w-full rounded-xl border-slate-300"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                                        الحد الأدنى للرحلات المكتملة بالشهر
                                    </label>

                                    <input
                                        type="number"
                                        min="0"
                                        name="min_completed_trips"
                                        value="{{ $reward->min_completed_trips }}"
                                        class="w-full rounded-xl border-slate-300"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                                        الترتيب
                                    </label>

                                    <input
                                        type="number"
                                        min="0"
                                        name="sort_order"
                                        value="{{ $reward->sort_order }}"
                                        class="w-full rounded-xl border-slate-300"
                                    >
                                </div>

                                <div class="flex items-end">
                                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            @checked($reward->is_active)
                                            class="rounded border-slate-300"
                                        >
                                        مفعلة
                                    </label>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                                        الوصف
                                    </label>

                                    <textarea
                                        name="description"
                                        rows="2"
                                        class="w-full rounded-xl border-slate-300"
                                    >{{ $reward->description }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button
                                    type="submit"
                                    class="rounded-xl bg-blue-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800"
                                >
                                    حفظ التغييرات
                                </button>
                            </div>
                        </form>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">
                    قاعدة جائزة جديدة
                </h2>

                <form
                    method="POST"
                    action="{{ route('admin.rewards.store') }}"
                    class="mt-5 space-y-4"
                >
                    @csrf

                    <input
                        type="text"
                        name="code"
                        placeholder="monthly_10_trips"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >

                    <input
                        type="text"
                        name="name"
                        placeholder="اسم الجائزة"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >

                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="amount"
                        placeholder="المبلغ"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >

                    <input
                        type="number"
                        min="0"
                        name="min_completed_trips"
                        placeholder="الحد الأدنى للرحلات"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >

                    <input
                        type="number"
                        min="0"
                        name="sort_order"
                        value="0"
                        class="w-full rounded-xl border-slate-300"
                    >

                    <textarea
                        name="description"
                        rows="3"
                        placeholder="وصف اختياري"
                        class="w-full rounded-xl border-slate-300"
                    ></textarea>

                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            checked
                            class="rounded border-slate-300"
                        >
                        مفعلة
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-blue-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800"
                    >
                        إنشاء القاعدة
                    </button>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">
                    تشغيل الجوائز الشهرية
                </h2>

                <form
                    method="POST"
                    action="{{ route('admin.rewards.run-monthly') }}"
                    class="mt-5 space-y-4"
                >
                    @csrf

                    <input
                        type="month"
                        name="period"
                        value="{{ $period }}"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-amber-500 px-4 py-2 text-sm font-bold text-slate-950 transition hover:bg-amber-400"
                    >
                        تشغيل جوائز الشهر
                    </button>
                </form>

                <p class="mt-3 text-xs leading-5 text-slate-500">
                    إعادة تشغيل نفس الشهر آمنة؛ السجلات المصروفة سابقًا لن تتكرر.
                </p>
            </section>
        </aside>
    </div>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">
                    الجوائز المصروفة
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    الشهر: {{ $period }}
                </p>
            </div>

            <form
                method="GET"
                action="{{ route('admin.rewards.index') }}"
                class="flex gap-2"
            >
                <input
                    type="month"
                    name="period"
                    value="{{ $period }}"
                    class="rounded-xl border-slate-300"
                >

                <button
                    type="submit"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700"
                >
                    عرض
                </button>
            </form>
        </div>

        @if ($monthlyRewards->isEmpty())
            <div class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                لم تُصرف جوائز لهذا الشهر.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-right text-xs font-semibold text-slate-500">
                            <th class="px-3 py-3">المستخدم</th>
                            <th class="px-3 py-3">الجائزة</th>
                            <th class="px-3 py-3">الرحلات</th>
                            <th class="px-3 py-3">المبلغ</th>
                            <th class="px-3 py-3">وقت الصرف</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($monthlyRewards as $monthlyReward)
                            <tr>
                                <td class="px-3 py-4 font-semibold text-slate-900">
                                    {{ $monthlyReward->user?->name }}
                                </td>

                                <td class="px-3 py-4 text-slate-700">
                                    {{ $monthlyReward->financialReward?->name }}
                                </td>

                                <td class="px-3 py-4 text-slate-700">
                                    {{ $monthlyReward->completed_trips }}
                                </td>

                                <td class="px-3 py-4 font-bold text-emerald-600">
                                    {{ number_format((float) $monthlyReward->amount, 2) }}
                                </td>

                                <td class="px-3 py-4 text-slate-500">
                                    {{ $monthlyReward->awarded_at?->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $monthlyRewards->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
