@extends('layouts.app')

@section('title', 'إدارة البضائع - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-semibold text-amber-600">
            لوحة الإدارة
        </p>

        <h1 class="mt-1 text-3xl font-bold text-slate-900">
            إدارة البضائع
        </h1>

        <p class="mt-2 text-sm text-slate-600">
            تعيين السائقين ومتابعة انتقال البضاعة حتى التسليم.
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

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ([
            'all' => 'الكل',
            'received' => 'مستلمة',
            'assigned' => 'تم التعيين',
            'in_transit' => 'في الطريق',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغاة',
        ] as $key => $label)
            <a
                href="{{ route('admin.packages.index', ['status' => $key]) }}"
                class="rounded-xl px-4 py-2 text-sm font-semibold {{ $status === $key ? 'bg-blue-900 text-white' : 'border border-slate-200 bg-white text-slate-700' }}"
            >
                {{ $label }}
                <span class="mr-1 opacity-70">
                    ({{ $counts[$key] }})
                </span>
            </a>
        @endforeach
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if ($packages->isEmpty())
            <div class="px-6 py-14 text-center text-sm text-slate-500">
                لا توجد بضائع في هذه الحالة.
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($packages as $package)
                    <article class="p-6">
                        <div class="grid gap-6 xl:grid-cols-4">
                            <div>
                                <p class="text-xs font-semibold text-slate-400">
                                    كود التتبع
                                </p>

                                <p class="mt-1 font-bold text-blue-900">
                                    {{ $package->tracking_code }}
                                </p>

                                <p class="mt-2 text-sm text-slate-600">
                                    {{ $package->from_city }}
                                    ←
                                    {{ $package->to_city }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $package->requested_date?->format('Y-m-d') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-slate-400">
                                    صاحب الطلب
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ $package->user?->name }}
                                </p>

                                <p class="mt-3 text-xs font-semibold text-slate-400">
                                    الحالة
                                </p>

                                <span class="mt-1 inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                    {{ $package->status->label() }}
                                </span>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-slate-400">
                                    السائق الحالي
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ $package->driver?->user?->name ?? 'غير معين' }}
                                </p>

                                @if ($package->trip)
                                    <p class="mt-2 text-xs text-slate-500">
                                        الرحلة:
                                        {{ $package->trip->departure_at?->format('Y-m-d H:i') }}
                                    </p>
                                @endif
                            </div>

                            <div class="space-y-3">
                                @if (in_array($package->status, [
                                    \App\Enums\PackageStatus::Received,
                                    \App\Enums\PackageStatus::Assigned,
                                ], true))
                                    <form
                                        method="POST"
                                        action="{{ route('admin.packages.assign', $package) }}"
                                        class="space-y-2 rounded-xl bg-slate-50 p-3"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <select
                                            name="driver_id"
                                            class="w-full rounded-xl border-slate-300 text-sm"
                                            required
                                        >
                                            <option value="">
                                                اختر السائق
                                            </option>

                                            @foreach ($drivers as $driver)
                                                <option
                                                    value="{{ $driver->id }}"
                                                    @selected((string) $package->assigned_driver_id === (string) $driver->id)
                                                >
                                                    {{ $driver->user?->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select
                                            name="trip_id"
                                            class="w-full rounded-xl border-slate-300 text-sm"
                                        >
                                            <option value="">
                                                بدون رحلة محددة
                                            </option>

                                            @foreach ($trips as $trip)
                                                <option
                                                    value="{{ $trip->id }}"
                                                    @selected((string) $package->trip_id === (string) $trip->id)
                                                >
                                                    {{ $trip->driver?->user?->name }}
                                                    —
                                                    {{ $trip->departure_at?->format('Y-m-d H:i') }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button
                                            type="submit"
                                            class="w-full rounded-xl bg-blue-900 px-3 py-2 text-xs font-bold text-white"
                                        >
                                            تعيين / تحديث التعيين
                                        </button>
                                    </form>
                                @endif

                                @if ($package->status === \App\Enums\PackageStatus::Assigned)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.packages.status', $package) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input
                                            type="hidden"
                                            name="status"
                                            value="in_transit"
                                        >

                                        <button
                                            type="submit"
                                            class="w-full rounded-xl bg-amber-500 px-3 py-2 text-xs font-bold text-slate-950"
                                        >
                                            بدء النقل
                                        </button>
                                    </form>
                                @endif

                                @if ($package->status === \App\Enums\PackageStatus::InTransit)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.packages.status', $package) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input
                                            type="hidden"
                                            name="status"
                                            value="delivered"
                                        >

                                        <button
                                            type="submit"
                                            class="w-full rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white"
                                        >
                                            تسجيل التسليم
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="p-5">
                {{ $packages->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
