@extends('layouts.app')

@section('title', 'توثيق السائق')

@section('content')
<section class="py-12">
    <div
        class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8"
    >
        <div>
            <p
                class="font-extrabold text-[#F59E0B]"
            >
                حساب السائق
            </p>

            <h1
                class="mt-2 text-3xl font-black"
            >
                طلب توثيق السائق
            </h1>

            <p
                class="mt-3 text-slate-500 dark:text-slate-400"
            >
                أدخل بيانات الهوية ورخصة القيادة لإرسالها للإدارة للمراجعة.
            </p>
        </div>

        @if ($driver)
            @php
                $statusClasses = match ($driver->status->value) {
                    'approved' =>
                        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300',

                    'rejected' =>
                        'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300',

                    'suspended' =>
                        'border-orange-200 bg-orange-50 text-orange-700 dark:border-orange-900 dark:bg-orange-950/30 dark:text-orange-300',

                    default =>
                        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300',
                };
            @endphp

            <div
                class="mt-7 rounded-2xl border p-5 {{ $statusClasses }}"
            >
                <p class="font-black">
                    حالة الطلب:
                    {{ $driver->status->label() }}
                </p>

                @if ($driver->rejection_reason)
                    <p class="mt-2 text-sm leading-7">
                        {{ $driver->rejection_reason }}
                    </p>
                @endif
            </div>
        @endif

        @if ($errors->any())
            <div
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300"
            >
                <ul class="grid gap-2">
                    @foreach ($errors->all() as $error)
                        <li>
                            • {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('driver.register.store') }}"
            enctype="multipart/form-data"
            class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
        >
            @csrf

            <div
                class="grid gap-6 md:grid-cols-2"
            >
                <div>
                    <label
                        for="national_id"
                        class="mb-2 block text-sm font-bold"
                    >
                        رقم الهوية
                    </label>

                    <input
                        id="national_id"
                        name="national_id"
                        type="text"
                        value="{{ old('national_id', $driver?->national_id) }}"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label
                        for="license_number"
                        class="mb-2 block text-sm font-bold"
                    >
                        رقم الرخصة
                    </label>

                    <input
                        id="license_number"
                        name="license_number"
                        type="text"
                        value="{{ old('license_number', $driver?->license_number) }}"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label
                        for="license_expiry"
                        class="mb-2 block text-sm font-bold"
                    >
                        انتهاء الرخصة
                    </label>

                    <input
                        id="license_expiry"
                        name="license_expiry"
                        type="date"
                        min="{{ now()->addDay()->toDateString() }}"
                        value="{{ old('license_expiry', $driver?->license_expiry?->format('Y-m-d')) }}"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>

                <div>
                    <label
                        for="experience_years"
                        class="mb-2 block text-sm font-bold"
                    >
                        سنوات الخبرة
                    </label>

                    <input
                        id="experience_years"
                        name="experience_years"
                        type="number"
                        min="0"
                        max="70"
                        value="{{ old('experience_years', $driver?->experience_years) }}"
                        required
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                    >
                </div>
            </div>

            <div class="mt-6">
                <label
                    for="bio"
                    class="mb-2 block text-sm font-bold"
                >
                    نبذة
                </label>

                <textarea
                    id="bio"
                    name="bio"
                    rows="4"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-800"
                >{{ old('bio', $driver?->bio) }}</textarea>
            </div>

            <div
                class="mt-6 grid gap-5 md:grid-cols-3"
            >
                <div>
                    <label
                        class="mb-2 block text-sm font-bold"
                    >
                        صورة الهوية الأمامية
                    </label>

                    <input
                        type="file"
                        name="id_image_front"
                        accept="image/*"
                        @required(!$driver)
                        class="block w-full text-sm"
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-bold"
                    >
                        صورة الهوية الخلفية
                    </label>

                    <input
                        type="file"
                        name="id_image_back"
                        accept="image/*"
                        @required(!$driver)
                        class="block w-full text-sm"
                    >
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-bold"
                    >
                        صورة الرخصة
                    </label>

                    <input
                        type="file"
                        name="license_image"
                        accept="image/*"
                        @required(!$driver)
                        class="block w-full text-sm"
                    >
                </div>
            </div>

            <div
                class="mt-8 flex justify-end"
            >
                <button
                    type="submit"
                    class="rounded-2xl bg-[#1E3A8A] px-7 py-4 font-extrabold text-white shadow-lg shadow-blue-950/20 transition hover:-translate-y-0.5"
                >
                    {{ $driver ? 'إعادة إرسال الطلب' : 'إرسال طلب التوثيق' }}
                </button>
            </div>
        </form>
    </div>
</section>
@endsection