<!DOCTYPE html>
<html
    lang="ar"
    dir="rtl"
>
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'لوحة الراكب') | SHOFEER
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">

<div class="min-h-screen lg:flex">

    {{-- ========================================================= --}}
    {{-- Desktop Sidebar                                           --}}
    {{-- ========================================================= --}}

    <aside
        class="hidden w-72 shrink-0 border-l border-slate-200 bg-white lg:flex lg:flex-col"
    >
        <div
            class="border-b border-slate-100 px-6 py-6"
        >
            <a
                href="{{ route('home') }}"
                class="flex items-center gap-3"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#1E3A8A] text-lg font-black text-white shadow-sm"
                >
                    ش
                </div>

                <div>
                    <div
                        class="text-xl font-black text-[#1E3A8A]"
                    >
                        SHOFEER
                    </div>

                    <div
                        class="text-xs text-slate-500"
                    >
                        لوحة الراكب
                    </div>
                </div>
            </a>
        </div>

        <div
            class="border-b border-slate-100 px-6 py-5"
        >
            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-50 font-bold text-[#1E3A8A]"
                >
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>

                <div class="min-w-0">
                    <div
                        class="truncate font-bold text-slate-900"
                    >
                        {{ auth()->user()->name }}
                    </div>

                    <div
                        class="text-xs text-slate-500"
                    >
                        راكب
                    </div>
                </div>

            </div>
        </div>

        <nav
            class="flex-1 space-y-1 overflow-y-auto p-4"
        >

            {{-- Dashboard --}}

            <a
                href="{{ route('dashboard') }}"
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition',

                    'bg-[#1E3A8A] text-white shadow-sm' =>
                        request()->routeIs('dashboard'),

                    'text-slate-600 hover:bg-slate-100' =>
                        ! request()->routeIs('dashboard'),
                ])
            >
                <span>⌂</span>
                <span>الرئيسية</span>
            </a>

            {{-- Bookings --}}

            <a
                href="{{ route('dashboard.bookings.index') }}"
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition',

                    'bg-[#1E3A8A] text-white shadow-sm' =>
                        request()->routeIs(
                            'dashboard.bookings.*'
                        ),

                    'text-slate-600 hover:bg-slate-100' =>
                        ! request()->routeIs(
                            'dashboard.bookings.*'
                        ),
                ])
            >
                <span>🎫</span>
                <span>حجوزاتي</span>
            </a>

            {{-- Future Passenger Modules --}}

            @php
                $futureLinks = [
                    ['icon' => '🏅', 'label' => 'شاراتي'],
                    ['icon' => '💰', 'label' => 'رصيدي'],
                    ['icon' => '⭐', 'label' => 'نقاطي'],
                    ['icon' => '📦', 'label' => 'بضاعتي'],
                    ['icon' => '📍', 'label' => 'تتبع مباشر'],
                    ['icon' => '💬', 'label' => 'تقييماتي'],
                ];
            @endphp

            @foreach ($futureLinks as $item)
                <div
                    class="flex cursor-not-allowed items-center justify-between rounded-xl px-4 py-3 text-sm font-medium text-slate-400"
                >
                    <span
                        class="flex items-center gap-3"
                    >
                        <span>
                            {{ $item['icon'] }}
                        </span>

                        <span>
                            {{ $item['label'] }}
                        </span>
                    </span>

                    <span
                        class="rounded-full bg-slate-100 px-2 py-1 text-[10px]"
                    >
                        قريبًا
                    </span>
                </div>
            @endforeach

            {{-- Public Trips --}}

            <a
                href="{{ route('trips.index') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-100"
            >
                <span>🚌</span>
                <span>الرحلات</span>
            </a>

            {{-- Notifications Placeholder --}}

            <div
                class="flex cursor-not-allowed items-center justify-between rounded-xl px-4 py-3 text-sm text-slate-400"
            >
                <span class="flex items-center gap-3">
                    <span>🔔</span>
                    <span>الإشعارات</span>
                </span>

                <span
                    class="rounded-full bg-slate-100 px-2 py-1 text-[10px]"
                >
                    قريبًا
                </span>
            </div>

        </nav>

        {{-- Logout = sidebar item 11 --}}

        <div
            class="border-t border-slate-100 p-4"
        >
            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-red-600 transition hover:bg-red-50"
                >
                    <span>↪</span>
                    <span>تسجيل الخروج</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ========================================================= --}}
    {{-- Main                                                      --}}
    {{-- ========================================================= --}}

    <div class="min-w-0 flex-1">

        {{-- Mobile Header --}}

        <header
            class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur lg:hidden"
        >
            <div
                class="flex items-center justify-between"
            >
                <a
                    href="{{ route('dashboard') }}"
                    class="font-black text-[#1E3A8A]"
                >
                    SHOFEER
                </a>

                <details class="relative">
                    <summary
                        class="cursor-pointer list-none rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold"
                    >
                        القائمة
                    </summary>

                    <div
                        class="absolute left-0 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
                    >
                        <a
                            href="{{ route('dashboard') }}"
                            class="block rounded-xl px-4 py-3 text-sm hover:bg-slate-100"
                        >
                            الرئيسية
                        </a>

                        <a
                            href="{{ route('dashboard.bookings.index') }}"
                            class="block rounded-xl px-4 py-3 text-sm hover:bg-slate-100"
                        >
                            حجوزاتي
                        </a>

                        <a
                            href="{{ route('trips.index') }}"
                            class="block rounded-xl px-4 py-3 text-sm hover:bg-slate-100"
                        >
                            الرحلات
                        </a>

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="w-full rounded-xl px-4 py-3 text-right text-sm text-red-600 hover:bg-red-50"
                            >
                                تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </header>

        <main
            class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8"
        >

            @if (session('success'))
                <div
                    class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800"
                >
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div
                    class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800"
                >
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')

        </main>
    </div>

</div>

</body>
</html>