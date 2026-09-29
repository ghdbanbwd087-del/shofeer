@extends('layouts.app')

@section('title', 'أرسل بضاعة - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-semibold text-amber-600">
            خدمة البضائع
        </p>

        <h1 class="mt-1 text-3xl font-bold text-slate-900">
            أرسل بضاعة
        </h1>

        <p class="mt-2 text-sm text-slate-600">
            أدخل تفاصيل البضاعة والمرسل والمستلم ومسار الإرسال.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('packages.store') }}"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">
                بيانات البضاعة
            </h2>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                        الوصف
                    </label>

                    <textarea
                        name="description"
                        rows="4"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                        التصنيف
                    </label>

                    <input
                        type="text"
                        name="category"
                        value="{{ old('category') }}"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                        الوزن بالكيلو
                    </label>

                    <input
                        type="number"
                        name="weight_kg"
                        step="0.01"
                        min="0.01"
                        value="{{ old('weight_kg') }}"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                        الحجم
                    </label>

                    <input
                        type="text"
                        name="size"
                        value="{{ old('size') }}"
                        placeholder="مثال: 40×30×20 سم"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">
                        الصور
                    </label>

                    <input
                        type="file"
                        name="images[]"
                        accept=".jpg,.jpeg,.png,.webp"
                        multiple
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2"
                    >

                    <p class="mt-1 text-xs text-slate-500">
                        حتى 4 صور، 5MB للصورة.
                    </p>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">
                    بيانات المرسل
                </h2>

                <div class="mt-5 space-y-4">
                    <input
                        type="text"
                        name="sender_name"
                        value="{{ old('sender_name', $user->name) }}"
                        placeholder="اسم المرسل"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >

                    <input
                        type="text"
                        name="sender_phone"
                        value="{{ old('sender_phone') }}"
                        placeholder="رقم الجوال"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >

                    <input
                        type="text"
                        name="sender_whatsapp"
                        value="{{ old('sender_whatsapp') }}"
                        placeholder="واتساب - اختياري"
                        class="w-full rounded-xl border-slate-300"
                    >

                    <input
                        type="text"
                        name="sender_city"
                        value="{{ old('sender_city') }}"
                        placeholder="مدينة المرسل"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">
                    بيانات المستلم
                </h2>

                <div class="mt-5 space-y-4">
                    <input
                        type="text"
                        name="recipient_name"
                        value="{{ old('recipient_name') }}"
                        placeholder="اسم المستلم"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >

                    <input
                        type="text"
                        name="recipient_phone"
                        value="{{ old('recipient_phone') }}"
                        placeholder="رقم الجوال"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >

                    <input
                        type="text"
                        name="recipient_city"
                        value="{{ old('recipient_city') }}"
                        placeholder="مدينة المستلم"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >
                </div>
            </section>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">
                تفاصيل الرحلة
            </h2>

            <div class="mt-5 grid gap-4 md:grid-cols-3">
                <input
                    type="text"
                    name="from_city"
                    value="{{ old('from_city') }}"
                    placeholder="من"
                    class="w-full rounded-xl border-slate-300"
                    required
                >

                <input
                    type="text"
                    name="to_city"
                    value="{{ old('to_city') }}"
                    placeholder="إلى"
                    class="w-full rounded-xl border-slate-300"
                    required
                >

                <input
                    type="date"
                    name="requested_date"
                    value="{{ old('requested_date') }}"
                    class="w-full rounded-xl border-slate-300"
                    required
                >
            </div>
        </section>

        <div class="flex flex-wrap gap-3">
            <button
                type="submit"
                class="rounded-xl bg-blue-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-blue-800"
            >
                أرسل الطلب
            </button>

            <a
                href="{{ route('dashboard.packages.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700"
            >
                بضاعتي
            </a>
        </div>
    </form>
</div>
@endsection
