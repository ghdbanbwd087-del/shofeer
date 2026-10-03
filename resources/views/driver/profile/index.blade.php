@extends('layouts.app')

@section('title', 'ملفي - SHOFEER')

@section('content')
<div
    dir="rtl"
    class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8"
>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                لوحة السائق
            </p>

            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                ملفي
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                بيانات حسابك وملف السائق وإعدادات كلمة المرور.
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

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        بيانات الحساب
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        معلومات الحساب الأساسية.
                    </p>
                </div>

                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-900">
                    {{ $driver->status->label() }}
                </span>
            </div>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-semibold text-slate-500">
                        الاسم
                    </dt>

                    <dd class="mt-2 font-bold text-slate-900">
                        {{ $user->name }}
                    </dd>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-semibold text-slate-500">
                        البريد الإلكتروني
                    </dt>

                    <dd class="mt-2 break-all font-semibold text-slate-900">
                        {{ $user->email ?? 'غير مضاف' }}
                    </dd>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-semibold text-slate-500">
                        رقم الجوال
                    </dt>

                    <dd class="mt-2 font-semibold text-slate-900">
                        {{ $user->phone ?? 'غير مضاف' }}
                    </dd>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-semibold text-slate-500">
                        حالة التحقق من الجوال
                    </dt>

                    <dd class="mt-2 font-semibold text-slate-900">
                        {{ $user->phone_verified_at ? 'موثق' : 'غير موثق' }}
                    </dd>
                </div>
            </dl>
        </section>

        <aside class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">
                ملخص السائق
            </h2>

            <div class="mt-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <span class="text-sm text-slate-500">
                        التقييم
                    </span>

                    <strong class="text-slate-900">
                        {{ number_format((float) $driver->rating, 2) }} ★
                    </strong>
                </div>

                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <span class="text-sm text-slate-500">
                        إجمالي الرحلات
                    </span>

                    <strong class="text-slate-900">
                        {{ $driver->total_trips ?? 0 }}
                    </strong>
                </div>

                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <span class="text-sm text-slate-500">
                        سنوات الخبرة
                    </span>

                    <strong class="text-slate-900">
                        {{ $driver->experience_years ?? '—' }}
                    </strong>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">
                        حالة الاتصال
                    </span>

                    <strong class="{{ $driver->is_online ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $driver->is_online ? 'متصل' : 'غير متصل' }}
                    </strong>
                </div>
            </div>
        </aside>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900">
                بيانات التوثيق
            </h2>

            <p class="mt-2 text-sm leading-7 text-slate-500">
                لأسباب أمنية لا نعرض رقم الهوية أو رقم الرخصة كنص خام داخل الصفحة.
            </p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold text-slate-500">
                        الهوية
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{ $driver->national_id_hash ? 'محفوظة ومحمية' : 'غير مضافة' }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold text-slate-500">
                        الرخصة
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{ $driver->license_number_hash ? 'محفوظة ومحمية' : 'غير مضافة' }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold text-slate-500">
                        انتهاء الرخصة
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{ $driver->license_expiry?->format('Y-m-d') ?? '—' }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold text-slate-500">
                        المستندات
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{
                            $driver->id_image_front
                            && $driver->id_image_back
                            && $driver->license_image
                                ? 'مكتملة'
                                : 'غير مكتملة'
                        }}
                    </p>
                </div>
            </div>

            @if ($driver->bio)
                <div class="mt-4 rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold text-slate-500">
                        نبذة
                    </p>

                    <p class="mt-2 text-sm leading-7 text-slate-700">
                        {{ $driver->bio }}
                    </p>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900">
                تغيير كلمة المرور
            </h2>

            <p class="mt-2 text-sm leading-7 text-slate-500">
                يلزم إدخال كلمة المرور الحالية، ويجب ألا تقل الجديدة عن
                {{ config('security.password_min_length', 12) }}
                حرفًا.
            </p>

            <form
                method="POST"
                action="{{ route('driver.profile.password.update') }}"
                class="mt-6 space-y-5"
            >
                @csrf
                @method('PATCH')

                <div>
                    <label
                        for="current_password"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        كلمة المرور الحالية
                    </label>

                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-blue-900"
                    >

                    @error('current_password')
                        <p class="mt-2 text-xs font-semibold text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="password"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        كلمة المرور الجديدة
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-blue-900"
                    >

                    @error('password')
                        <p class="mt-2 text-xs font-semibold text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="password_confirmation"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        تأكيد كلمة المرور الجديدة
                    </label>

                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-blue-900"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-blue-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-800"
                >
                    حفظ كلمة المرور الجديدة
                </button>
            </form>
        </section>
    </div>
</div>
@endsection
