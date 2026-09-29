@extends('layouts.app')

@section('title', 'تفاصيل البضاعة - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                {{ $package->tracking_code }}
            </p>

            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                تفاصيل البضاعة
            </h1>
        </div>

        <a
            href="{{ route('dashboard.packages.index') }}"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700"
        >
            العودة إلى بضاعتي
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold text-slate-400">
                        الحالة
                    </p>
                    <p class="mt-1 font-bold text-blue-900">
                        {{ $package->status->label() }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-400">
                        تاريخ الإرسال المطلوب
                    </p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $package->requested_date?->format('Y-m-d') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-400">
                        المسار
                    </p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $package->from_city }}
                        ←
                        {{ $package->to_city }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-400">
                        التصنيف / الوزن / الحجم
                    </p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $package->category }}
                        —
                        {{ $package->weight_kg }} كجم
                        —
                        {{ $package->size }}
                    </p>
                </div>
            </div>

            <div class="mt-6 rounded-xl bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-400">
                    الوصف
                </p>
                <p class="mt-2 text-sm leading-7 text-slate-700">
                    {{ $package->description }}
                </p>
            </div>
        </section>

        <aside class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-bold text-slate-900">
                التتبع
            </h2>

            <a
                href="{{ route('packages.track', ['code' => $package->tracking_code]) }}"
                class="mt-4 block rounded-xl bg-blue-900 px-4 py-2 text-center text-sm font-bold text-white"
            >
                تتبع البضاعة
            </a>

            @if ($package->status === \App\Enums\PackageStatus::Received)
                <form
                    method="POST"
                    action="{{ route('dashboard.packages.cancel', $package) }}"
                    class="mt-3"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700"
                    >
                        إلغاء الطلب
                    </button>
                </form>
            @endif
        </aside>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">
                المرسل
            </h2>

            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-400">الاسم</dt>
                    <dd class="font-semibold text-slate-800">{{ $package->sender_name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">الجوال</dt>
                    <dd class="font-semibold text-slate-800">{{ $package->sender_phone }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">واتساب</dt>
                    <dd class="font-semibold text-slate-800">{{ $package->sender_whatsapp ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">المدينة</dt>
                    <dd class="font-semibold text-slate-800">{{ $package->sender_city }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">
                المستلم
            </h2>

            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-400">الاسم</dt>
                    <dd class="font-semibold text-slate-800">{{ $package->recipient_name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">الجوال</dt>
                    <dd class="font-semibold text-slate-800">{{ $package->recipient_phone }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">المدينة</dt>
                    <dd class="font-semibold text-slate-800">{{ $package->recipient_city }}</dd>
                </div>
            </dl>
        </section>
    </div>
</div>
@endsection
