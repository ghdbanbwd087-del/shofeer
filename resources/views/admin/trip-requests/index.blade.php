@extends('layouts.app')

@section('title', 'طلبات الرحلات')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div>
            <p class="font-extrabold text-[#F59E0B]">
                الإدارة
            </p>

            <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white">
                طلبات الرحلات
            </h1>

            <p class="mt-3 text-slate-500 dark:text-slate-400">
                راجع طلبات الرحلات المرسلة من السائقين، ثم وافق عليها أو ارفضها.
            </p>
        </div>

        {{-- Status Filters --}}
        <div class="mt-7 flex flex-wrap gap-2">
            @foreach ([
                '' => 'الكل',
                'pending' => 'قيد المراجعة',
                'approved' => 'تمت الموافقة',
                'rejected' => 'مرفوض',
            ] as $value => $label)

                <a
                    href="{{ route(
                        'admin.trip-requests.index',
                        $value ? ['status' => $value] : []
                    ) }}"
                    class="
                        rounded-xl px-4 py-2 text-sm font-bold transition
                        {{
                            request('status', '') === $value
                                ? 'bg-[#1E3A8A] text-white shadow-md shadow-blue-950/10'
                                : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'
                        }}
                    "
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300"
            >
                <p class="font-black">
                    توجد أخطاء يجب مراجعتها:
                </p>

                <ul class="mt-3 grid gap-2">
                    @foreach ($errors->all() as $error)
                        <li>
                            • {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Requests --}}
        <div class="mt-8 space-y-5">
            @forelse ($tripRequests as $tripRequest)

                <article
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                >
                    {{-- Request Header --}}
                    <div
                        class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="font-black text-slate-950 dark:text-white">
                                    {{ $tripRequest->driver->user->name }}
                                </p>

                                @if ($tripRequest->driver->isApproved())
                                    <span
                                        class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300"
                                    >
                                        ✓ سائق موثق
                                    </span>
                                @endif
                            </div>

                            <h2
                                class="mt-3 text-xl font-black text-[#1E3A8A] dark:text-blue-300"
                            >
                                {{ $tripRequest->fromCity->name_ar }}
                                <span class="mx-2 text-[#F59E0B]">
                                    ←
                                </span>
                                {{ $tripRequest->toCity->name_ar }}
                            </h2>

                            <div
                                class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                <span>
                                    التاريخ:
                                    <strong class="text-slate-700 dark:text-slate-200">
                                        {{ $tripRequest->travel_date->format('Y-m-d') }}
                                    </strong>
                                </span>

                                <span>
                                    الوقت:
                                    <strong class="text-slate-700 dark:text-slate-200">
                                        {{ $tripRequest->departure_time }}
                                    </strong>
                                </span>

                                <span>
                                    المقاعد:
                                    <strong class="text-slate-700 dark:text-slate-200">
                                        {{ $tripRequest->requested_seats }}
                                    </strong>
                                </span>
                            </div>
                        </div>

                        <div>
                            @php
                                $statusClass = match ($tripRequest->status->value) {
                                    'approved' =>
                                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300',

                                    'rejected' =>
                                        'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-300',

                                    default =>
                                        'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300',
                                };
                            @endphp

                            <span
                                class="rounded-full px-3 py-1.5 text-sm font-bold {{ $statusClass }}"
                            >
                                {{ $tripRequest->status->label() }}
                            </span>
                        </div>
                    </div>

                    {{-- Notes --}}
                    @if ($tripRequest->notes)
                        <div
                            class="mt-5 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"
                        >
                            <p class="text-xs font-bold text-slate-400">
                                ملاحظات السائق
                            </p>

                            <p
                                class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300"
                            >
                                {{ $tripRequest->notes }}
                            </p>
                        </div>
                    @endif

                    {{-- Rejection Reason --}}
                    @if ($tripRequest->rejection_reason)
                        <div
                            class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/20"
                        >
                            <p class="text-xs font-bold text-red-500">
                                سبب الرفض
                            </p>

                            <p
                                class="mt-2 text-sm leading-7 text-red-700 dark:text-red-300"
                            >
                                {{ $tripRequest->rejection_reason }}
                            </p>
                        </div>
                    @endif

                    {{-- Reviewed Information --}}
                    @if ($tripRequest->reviewed_at)
                        <div
                            class="mt-5 text-xs text-slate-400"
                        >
                            تمت المراجعة:

                            {{ $tripRequest->reviewed_at->format('Y-m-d H:i') }}

                            @if ($tripRequest->reviewer)
                                بواسطة
                                {{ $tripRequest->reviewer->name }}
                            @endif
                        </div>
                    @endif

                    {{-- Pending Actions --}}
                    @if ($tripRequest->isPending())

                        {{-- Approve --}}
                        <details
                            class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 dark:border-emerald-900 dark:bg-emerald-950/10"
                        >
                            <summary
                                class="cursor-pointer font-black text-emerald-700 dark:text-emerald-300"
                            >
                                الموافقة وإنشاء الرحلة
                            </summary>

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.trip-requests.approve',
                                    $tripRequest
                                ) }}"
                                class="mt-6 grid gap-5 md:grid-cols-2"
                            >
                                @csrf
                                @method('PATCH')

                                {{-- Car --}}
                                <div>
                                    <label
                                        for="car_{{ $tripRequest->id }}"
                                        class="mb-2 block text-sm font-bold"
                                    >
                                        السيارة
                                    </label>

                                    <select
                                        id="car_{{ $tripRequest->id }}"
                                        name="car_id"
                                        required
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                                    >
                                        <option value="">
                                            اختر السيارة
                                        </option>

                                        @foreach (
                                            $tripRequest
                                                ->driver
                                                ->cars
                                                ->where(
                                                    'is_active',
                                                    true
                                                )
                                            as $car
                                        )
                                            <option value="{{ $car->id }}">
                                                {{ $car->make }}
                                                {{ $car->model }}
                                                —
                                                {{ $car->plate_number }}
                                                —
                                                {{ $car->seat_count }}
                                                مقعد
                                            </option>
                                        @endforeach
                                    </select>

                                    @if (
                                        $tripRequest
                                            ->driver
                                            ->cars
                                            ->where(
                                                'is_active',
                                                true
                                            )
                                            ->isEmpty()
                                    )
                                        <p
                                            class="mt-2 text-xs font-bold text-red-600 dark:text-red-400"
                                        >
                                            لا توجد سيارة مفعلة لهذا السائق.
                                        </p>
                                    @endif
                                </div>

                                {{-- Price --}}
                                <div>
                                    <label
                                        for="price_{{ $tripRequest->id }}"
                                        class="mb-2 block text-sm font-bold"
                                    >
                                        سعر المقعد
                                    </label>

                                    <input
                                        id="price_{{ $tripRequest->id }}"
                                        type="number"
                                        name="price"
                                        step="0.01"
                                        min="0.01"
                                        required
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                                    >
                                </div>

                                {{-- Meeting Point --}}
                                <div>
                                    <label
                                        for="meeting_point_{{ $tripRequest->id }}"
                                        class="mb-2 block text-sm font-bold"
                                    >
                                        نقطة التجمع
                                    </label>

                                    <input
                                        id="meeting_point_{{ $tripRequest->id }}"
                                        type="text"
                                        name="meeting_point"
                                        required
                                        maxlength="255"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                                    >
                                </div>

                                {{-- Destination Point --}}
                                <div>
                                    <label
                                        for="destination_point_{{ $tripRequest->id }}"
                                        class="mb-2 block text-sm font-bold"
                                    >
                                        نقطة الوصول
                                    </label>

                                    <input
                                        id="destination_point_{{ $tripRequest->id }}"
                                        type="text"
                                        name="destination_point"
                                        maxlength="255"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                                    >
                                </div>

                                {{-- Admin Notes --}}
                                <div class="md:col-span-2">
                                    <label
                                        for="notes_{{ $tripRequest->id }}"
                                        class="mb-2 block text-sm font-bold"
                                    >
                                        ملاحظات الإدارة
                                    </label>

                                    <textarea
                                        id="notes_{{ $tripRequest->id }}"
                                        name="notes"
                                        rows="3"
                                        maxlength="2000"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-[#1E3A8A] dark:border-slate-700 dark:bg-slate-800"
                                    ></textarea>
                                </div>

                                {{-- Publish --}}
                                <div class="md:col-span-2">
                                    <label
                                        class="flex items-center gap-3"
                                    >
                                        <input
                                            type="checkbox"
                                            name="is_published"
                                            value="1"
                                            checked
                                            class="h-4 w-4"
                                        >

                                        <span class="text-sm font-bold">
                                            نشر الرحلة مباشرة بعد الموافقة
                                        </span>
                                    </label>
                                </div>

                                <div class="md:col-span-2">
                                    <button
                                        type="submit"
                                        @disabled(
                                            $tripRequest
                                                ->driver
                                                ->cars
                                                ->where(
                                                    'is_active',
                                                    true
                                                )
                                                ->isEmpty()
                                        )
                                        class="w-full rounded-xl bg-emerald-600 px-5 py-3.5 font-extrabold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        موافقة وإنشاء الرحلة
                                    </button>
                                </div>
                            </form>
                        </details>

                        {{-- Reject --}}
                        <details
                            class="mt-3 rounded-2xl border border-red-200 bg-red-50/40 p-5 dark:border-red-900 dark:bg-red-950/10"
                        >
                            <summary
                                class="cursor-pointer font-black text-red-700 dark:text-red-300"
                            >
                                رفض الطلب
                            </summary>

                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.trip-requests.reject',
                                    $tripRequest
                                ) }}"
                                class="mt-5"
                            >
                                @csrf
                                @method('PATCH')

                                <label
                                    for="rejection_reason_{{ $tripRequest->id }}"
                                    class="mb-2 block text-sm font-bold"
                                >
                                    سبب الرفض
                                </label>

                                <textarea
                                    id="rejection_reason_{{ $tripRequest->id }}"
                                    name="rejection_reason"
                                    required
                                    minlength="5"
                                    maxlength="2000"
                                    rows="3"
                                    placeholder="اكتب سبب رفض الطلب..."
                                    class="w-full rounded-xl border border-red-200 bg-white px-4 py-3 outline-none focus:border-red-500 dark:border-red-900 dark:bg-slate-800"
                                ></textarea>

                                <button
                                    type="submit"
                                    class="mt-3 rounded-xl bg-red-600 px-6 py-3 font-bold text-white transition hover:bg-red-700"
                                >
                                    رفض الطلب
                                </button>
                            </form>
                        </details>
                    @endif
                </article>

            @empty
                <div
                    class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900"
                >
                    <div class="text-4xl">
                        🚌
                    </div>

                    <h2 class="mt-4 text-xl font-black">
                        لا توجد طلبات رحلات
                    </h2>

                    <p class="mt-2 text-slate-500">
                        لا توجد طلبات مطابقة للحالة المحددة.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($tripRequests->hasPages())
            <div class="mt-8">
                {{ $tripRequests->links() }}
            </div>
        @endif
    </div>
</section>
@endsection