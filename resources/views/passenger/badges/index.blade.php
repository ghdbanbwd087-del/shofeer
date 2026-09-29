@extends('layouts.app')

@section('title', 'شاراتي - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">برنامج الولاء</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">شاراتي</h1>
            <p class="mt-2 text-sm text-slate-600">
                تابع شارتك الحالية وتقدمك نحو المستوى التالي.
            </p>
        </div>

        <a
            href="{{ route('dashboard') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-900"
        >
            العودة للوحة التحكم
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">الشارة الحالية</p>

                    <h2 class="mt-2 text-2xl font-bold text-blue-900">
                        {{ $currentBadge?->name ?? 'لا توجد شارة حالية' }}
                    </h2>

                    @if ($currentBadge?->description)
                        <p class="mt-2 text-sm leading-7 text-slate-600">
                            {{ $currentBadge->description }}
                        </p>
                    @endif
                </div>

                <div class="rounded-2xl bg-blue-50 px-5 py-4 text-center">
                    <div class="text-3xl font-bold text-blue-900">
                        {{ $completedTrips }}
                    </div>
                    <div class="mt-1 text-xs font-semibold text-slate-500">
                        رحلة مكتملة
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="font-semibold text-slate-700">
                        التقدم
                    </span>
                    <span class="font-bold text-blue-900">
                        {{ $progressPercent }}%
                    </span>
                </div>

                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div
                        class="h-full rounded-full bg-blue-900 transition-all duration-500"
                        style="width: {{ $progressPercent }}%"
                    ></div>
                </div>

                @if ($nextBadge)
                    <div class="mt-4 rounded-xl bg-amber-50 p-4">
                        <p class="text-sm font-semibold text-amber-800">
                            الشارة القادمة: {{ $nextBadge->name }}
                        </p>
                        <p class="mt-1 text-sm text-amber-700">
                            متبقي {{ $tripsToNext }} رحلة للوصول إليها.
                        </p>
                    </div>
                @elseif ($currentBadge)
                    <div class="mt-4 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
                        وصلت إلى أعلى شارة مفعلة حاليًا.
                    </div>
                @else
                    <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                        لا توجد شارات مفعلة حاليًا.
                    </div>
                @endif
            </div>
        </section>

        <aside class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">المزايا الحالية</h2>

            @php
                $benefits = $currentBadge?->benefits ?? [];
            @endphp

            @if (count($benefits))
                <ul class="mt-4 space-y-3">
                    @foreach ($benefits as $benefit)
                        <li class="flex gap-3 text-sm leading-6 text-slate-600">
                            <span class="mt-1 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700">
                                ✓
                            </span>
                            <span>{{ $benefit }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-4 text-sm leading-6 text-slate-500">
                    لا توجد مزايا مضافة لهذه الشارة حتى الآن.
                </p>
            @endif
        </aside>
    </div>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-xl font-bold text-slate-900">الشارات التي حصلت عليها</h2>
            <p class="mt-1 text-sm text-slate-500">
                تُحفظ كل شارة مرة واحدة فقط في حسابك.
            </p>
        </div>

        @if ($earnedBadges->isEmpty())
            <div class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                لم تحصل على شارة بعد.
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($earnedBadges as $userBadge)
                    <article class="rounded-2xl border border-slate-200 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-bold text-blue-900">
                                    {{ $userBadge->badge?->name ?? 'شارة' }}
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ $userBadge->badge?->description }}
                                </p>
                            </div>

                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                مكتسبة
                            </span>
                        </div>

                        <p class="mt-4 text-xs text-slate-400">
                            حصلت عليها:
                            {{ $userBadge->awarded_at?->format('Y-m-d') }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
