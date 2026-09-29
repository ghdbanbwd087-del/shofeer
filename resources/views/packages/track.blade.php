@extends('layouts.app')

@section('title', 'تتبع البضاعة - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="text-center">
        <p class="text-sm font-semibold text-amber-600">
            خدمة البضائع
        </p>

        <h1 class="mt-2 text-3xl font-bold text-slate-900">
            تتبع البضاعة
        </h1>

        <p class="mt-2 text-sm text-slate-600">
            أدخل كود التتبع لمعرفة الحالة الحالية.
        </p>
    </div>

    <form
        method="GET"
        action="{{ route('packages.track') }}"
        class="mx-auto mt-8 flex max-w-xl gap-2"
    >
        <input
            type="text"
            name="code"
            value="{{ $code }}"
            placeholder="SHF-XXXXXXXXXX"
            class="min-w-0 flex-1 rounded-xl border-slate-300"
        >

        <button
            type="submit"
            class="rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-bold text-white"
        >
            تتبع
        </button>
    </form>

    @if ($code !== '' && $package === null)
        <div class="mx-auto mt-8 max-w-xl rounded-xl bg-rose-50 px-4 py-5 text-center text-sm font-semibold text-rose-700">
            لم يتم العثور على بضاعة بهذا الكود.
        </div>
    @endif

    @if ($package)
        @php
            $steps = [
                'received' => 'مستلمة',
                'assigned' => 'تم التعيين',
                'in_transit' => 'في الطريق',
                'delivered' => 'تم التسليم',
            ];

            $order = [
                'received' => 1,
                'assigned' => 2,
                'in_transit' => 3,
                'delivered' => 4,
            ];

            $current = $order[$package->status->value] ?? 0;
        @endphp

        <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400">
                        كود التتبع
                    </p>
                    <p class="mt-1 text-xl font-bold text-blue-900">
                        {{ $package->tracking_code }}
                    </p>
                </div>

                <span class="self-start rounded-full bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700">
                    {{ $package->status->label() }}
                </span>
            </div>

            @if ($package->status === \App\Enums\PackageStatus::Cancelled)
                <div class="mt-6 rounded-xl bg-rose-50 p-4 text-sm font-semibold text-rose-700">
                    تم إلغاء طلب هذه البضاعة.
                </div>
            @else
                <div class="mt-8 grid gap-3 sm:grid-cols-4">
                    @foreach ($steps as $key => $label)
                        <div class="rounded-xl border p-4 text-center {{ $current >= $order[$key] ? 'border-blue-200 bg-blue-50' : 'border-slate-200 bg-slate-50' }}">
                            <div class="text-sm font-bold {{ $current >= $order[$key] ? 'text-blue-900' : 'text-slate-400' }}">
                                {{ $label }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold text-slate-400">
                        المسار
                    </p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $package->from_city }}
                        ←
                        {{ $package->to_city }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold text-slate-400">
                        التاريخ المطلوب
                    </p>
                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $package->requested_date?->format('Y-m-d') }}
                    </p>
                </div>
            </div>

            <p class="mt-5 text-xs leading-6 text-slate-500">
                حفاظًا على الخصوصية لا تعرض صفحة التتبع العامة أسماء أو أرقام المرسل والمستلم.
            </p>
        </section>
    @endif
</div>
@endsection
