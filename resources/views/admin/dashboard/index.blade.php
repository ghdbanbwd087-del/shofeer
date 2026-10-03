@extends('layouts.app')

@section('title', 'لوحة الإدارة - SHOFEER')

@section('content')
<div
    dir="rtl"
    class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8"
>
    <div class="grid gap-6 xl:grid-cols-[260px_minmax(0,1fr)]">
        <aside class="self-start rounded-2xl border border-slate-200 bg-white p-4 shadow-sm xl:sticky xl:top-6">
            <div class="border-b border-slate-100 px-2 pb-4">
                <p class="text-xs font-bold text-amber-600">
                    SHOFEER
                </p>

                <h2 class="mt-1 text-lg font-black text-slate-900">
                    لوحة الإدارة
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    22 عنصرًا في القائمة
                </p>
            </div>

            <nav class="mt-4 space-y-1">
                @foreach ($navigation as $item)
                    @php
                        $routeExists =
                            \Illuminate\Support\Facades\Route::has(
                                $item['route']
                            );

                        $isLogout =
                            ($item['method'] ?? 'GET')
                            === 'POST';
                    @endphp

                    @if ($isLogout && $routeExists)
                        <form
                            method="POST"
                            action="{{ route($item['route']) }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-right text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                            >
                                <span>
                                    {{ $item['label'] }}
                                </span>
                            </button>
                        </form>
                    @elseif ($routeExists)
                        <a
                            href="{{ route($item['route']) }}"
                            class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold transition
                                {{
                                    request()->routeIs($item['route'])
                                        ? 'bg-blue-900 text-white'
                                        : 'text-slate-700 hover:bg-slate-50'
                                }}"
                        >
                            <span>
                                {{ $item['label'] }}
                            </span>
                        </a>
                    @else
                        <div
                            class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm text-slate-400"
                            title="ستُنفذ في مرحلة لاحقة"
                        >
                            <span>
                                {{ $item['label'] }}
                            </span>

                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold">
                                لاحقًا
                            </span>
                        </div>
                    @endif
                @endforeach
            </nav>
        </aside>

        <main class="min-w-0">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-amber-600">
                        الإدارة المركزية
                    </p>

                    <h1 class="mt-1 text-3xl font-black text-slate-900">
                        الرئيسية
                    </h1>

                    <p class="mt-2 text-sm text-slate-600">
                        ملخص تشغيلي مباشر من بيانات SHOFEER الحالية.
                    </p>
                </div>

                @if (\Illuminate\Support\Facades\Route::has('admin.live.index'))
                    <a
                        href="{{ route('admin.live.index') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm"
                    >
                        الخريطة المباشرة
                    </a>
                @endif
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($stats as $key => $stat)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-500">
                            {{ $stat['label'] }}
                        </p>

                        <p class="mt-3 text-3xl font-black text-slate-900">
                            @if (($stat['format'] ?? null) === 'money')
                                {{ number_format((float) $stat['value'], 2) }}
                            @elseif (($stat['format'] ?? null) === 'rating')
                                {{ number_format((float) $stat['value'], 2) }}
                                <span class="text-lg text-amber-500">★</span>
                            @else
                                {{ number_format((float) $stat['value']) }}
                            @endif
                        </p>
                    </article>
                @endforeach
            </section>

            <section class="mt-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">
                            الرسوم البيانية
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            اتجاهات آخر 7 أيام من قاعدة البيانات.
                        </p>
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    @foreach ($charts as $chart)
                        @php
                            $maxValue =
                                max(
                                    1,
                                    collect(
                                        $chart['points']
                                    )->max(
                                        'value'
                                    )
                                );
                        @endphp

                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-bold text-slate-900">
                                        {{ $chart['title'] }}
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $chart['subtitle'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex h-52 items-end gap-2 sm:gap-3">
                                @foreach ($chart['points'] as $point)
                                    @php
                                        $height =
                                            max(
                                                4,
                                                (
                                                    (float) $point['value']
                                                    / (float) $maxValue
                                                )
                                                * 100
                                            );
                                    @endphp

                                    <div class="flex min-w-0 flex-1 flex-col items-center justify-end gap-2">
                                        <div class="text-center text-[10px] font-bold text-slate-500">
                                            @if (($chart['format'] ?? null) === 'money')
                                                {{ number_format((float) $point['value'], 0) }}
                                            @else
                                                {{ number_format((float) $point['value']) }}
                                            @endif
                                        </div>

                                        <div class="flex h-36 w-full items-end rounded-lg bg-slate-50 p-1">
                                            <div
                                                class="w-full rounded-md bg-blue-900 transition-all"
                                                style="height: {{ $height }}%;"
                                            ></div>
                                        </div>

                                        <span class="text-[10px] font-semibold text-slate-400">
                                            {{ $point['label'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 p-5">
                        <h2 class="text-xl font-black text-slate-900">
                            آخر النشاطات
                        </h2>

                        <p class="mt-1 text-xs leading-6 text-slate-500">
                            هذه قائمة تشغيلية من أحدث المستخدمين والحجوزات والدفعات والرحلات.
                            سجل Audit الكامل سيُنفذ في مرحلته المخصصة.
                        </p>
                    </div>

                    @if ($activities->isEmpty())
                        <div class="px-5 py-12 text-center text-sm text-slate-500">
                            لا توجد نشاطات حديثة.
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach ($activities as $activity)
                                <article class="flex items-start justify-between gap-4 p-5">
                                    <div>
                                        <p class="font-bold text-slate-900">
                                            {{ $activity['title'] }}
                                        </p>

                                        <p class="mt-1 text-sm text-slate-600">
                                            {{ $activity['description'] }}
                                        </p>
                                    </div>

                                    <time class="shrink-0 text-xs text-slate-400">
                                        {{ $activity['at']->format('m-d H:i') }}
                                    </time>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 p-5">
                        <h2 class="text-xl font-black text-slate-900">
                            التنبيهات
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            عناصر تتطلب مراجعة الإدارة.
                        </p>
                    </div>

                    @php
                        $activeAlerts =
                            $alerts->filter(
                                fn ($alert) =>
                                    $alert['count'] > 0
                            );
                    @endphp

                    @if ($activeAlerts->isEmpty())
                        <div class="p-5">
                            <div class="rounded-xl bg-emerald-50 px-4 py-4 text-sm font-bold text-emerald-700">
                                لا توجد عناصر معلقة تتطلب مراجعة الآن.
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach ($activeAlerts as $alert)
                                <div class="flex items-center justify-between gap-4 p-5">
                                    <div>
                                        <p class="font-bold text-slate-900">
                                            {{ $alert['label'] }}
                                        </p>

                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $alert['count'] }}
                                            عنصر
                                        </p>
                                    </div>

                                    @if (\Illuminate\Support\Facades\Route::has($alert['route']))
                                        <a
                                            href="{{ route($alert['route']) }}"
                                            class="rounded-xl bg-amber-50 px-4 py-2 text-xs font-bold text-amber-800"
                                        >
                                            مراجعة
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</div>
@endsection
