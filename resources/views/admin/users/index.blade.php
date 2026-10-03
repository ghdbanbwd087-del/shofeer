@extends('layouts.app')

@section('title', 'المستخدمون - SHOFEER')

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
                المستخدمون
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                جدول المستخدمين مع فلترة وإدارة حالة الحساب.
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

    @if ($errors->has('user'))
        <div class="mb-6 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            {{ $errors->first('user') }}
        </div>
    @endif

    <section class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">
                الكل
            </p>

            <p class="mt-2 text-2xl font-black text-slate-900">
                {{ $counts['all'] }}
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">
                النشطون
            </p>

            <p class="mt-2 text-2xl font-black text-emerald-700">
                {{ $counts['active'] }}
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">
                المعطلون
            </p>

            <p class="mt-2 text-2xl font-black text-rose-700">
                {{ $counts['inactive'] }}
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">
                الركاب
            </p>

            <p class="mt-2 text-2xl font-black text-blue-900">
                {{ $counts['passengers'] }}
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">
                السائقون
            </p>

            <p class="mt-2 text-2xl font-black text-blue-900">
                {{ $counts['drivers'] }}
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500">
                الإداريون
            </p>

            <p class="mt-2 text-2xl font-black text-blue-900">
                {{ $counts['admins'] }}
            </p>
        </article>
    </section>

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form
            method="GET"
            action="{{ route('admin.users.index') }}"
            class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_220px_auto]"
        >
            <div>
                <label
                    for="q"
                    class="mb-2 block text-xs font-semibold text-slate-500"
                >
                    بحث
                </label>

                <input
                    id="q"
                    name="q"
                    value="{{ $search }}"
                    placeholder="الاسم أو البريد الإلكتروني"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-blue-900"
                >
            </div>

            <div>
                <label
                    for="role"
                    class="mb-2 block text-xs font-semibold text-slate-500"
                >
                    الدور
                </label>

                <select
                    id="role"
                    name="role"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-blue-900"
                >
                    <option value="">
                        كل الأدوار
                    </option>

                    @foreach ($roles as $roleOption)
                        <option
                            value="{{ $roleOption->value }}"
                            @selected($role === $roleOption->value)
                        >
                            {{ $roleOption->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="status"
                    class="mb-2 block text-xs font-semibold text-slate-500"
                >
                    حالة الحساب
                </label>

                <select
                    id="status"
                    name="status"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-blue-900"
                >
                    <option value="">
                        الكل
                    </option>

                    <option
                        value="active"
                        @selected($status === 'active')
                    >
                        نشط
                    </option>

                    <option
                        value="inactive"
                        @selected($status === 'inactive')
                    >
                        معطل
                    </option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-bold text-white"
                >
                    فلترة
                </button>

                <a
                    href="{{ route('admin.users.index') }}"
                    class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600"
                >
                    مسح
                </a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if ($users->isEmpty())
            <div class="px-6 py-16 text-center text-sm text-slate-500">
                لا توجد نتائج مطابقة.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-right text-xs font-bold text-slate-500">
                            <th class="px-5 py-4">
                                المستخدم
                            </th>

                            <th class="px-5 py-4">
                                الدور
                            </th>

                            <th class="px-5 py-4">
                                الحساب
                            </th>

                            <th class="px-5 py-4">
                                الجوال
                            </th>

                            <th class="px-5 py-4">
                                الانضمام
                            </th>

                            <th class="px-5 py-4">
                                الإجراءات
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($users as $user)
                            <tr class="align-middle">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-900">
                                        {{ $user->name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $user->email ?? 'بدون بريد إلكتروني' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-900">
                                        {{ $user->role->label() }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($user->is_active)
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                            نشط
                                        </span>
                                    @else
                                        <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                                            معطل
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    @if ($user->phone_verified_at)
                                        <span class="text-xs font-bold text-emerald-700">
                                            موثق
                                        </span>
                                    @else
                                        <span class="text-xs font-semibold text-slate-400">
                                            غير موثق
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-xs text-slate-500">
                                    {{ $user->created_at?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    @if (auth()->id() === $user->id)
                                        <span class="text-xs font-semibold text-slate-400">
                                            حسابك الحالي
                                        </span>
                                    @else
                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.status.update', $user) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <input
                                                type="hidden"
                                                name="is_active"
                                                value="{{ $user->is_active ? 0 : 1 }}"
                                            >

                                            <button
                                                type="submit"
                                                class="rounded-xl px-4 py-2 text-xs font-bold
                                                    {{
                                                        $user->is_active
                                                            ? 'bg-rose-50 text-rose-700'
                                                            : 'bg-emerald-50 text-emerald-700'
                                                    }}"
                                            >
                                                {{ $user->is_active ? 'تعطيل الحساب' : 'تفعيل الحساب' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $users->links() }}
            </div>
        @endif
    </section>

    <div class="mt-5 rounded-xl bg-blue-50 px-4 py-3 text-xs leading-6 text-blue-900">
        حفاظًا على الخصوصية، لا تعرض هذه القائمة رقم الجوال الخام أو أي بيانات PII مشفرة.
        تغيير الدور والحذف غير مفعّلين في هذه المرحلة لأن المصدر لم يحدد هذه الإجراءات.
    </div>
</div>
@endsection
