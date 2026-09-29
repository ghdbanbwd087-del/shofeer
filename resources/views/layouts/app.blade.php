<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    class="scroll-smooth scheme-light dark:scheme-dark"
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

    <meta
        name="theme-color"
        content="#1E3A8A"
    >

    <meta
        name="description"
        content="@yield('description', 'SHOFEER — منصتك للسفر والحجز بين اليمن والسعودية.')"
    >

    <title>
        @hasSection('title')
            @yield('title') | SHOFEER
        @else
            SHOFEER — شوفير
        @endif
    </title>

    {{-- Cairo للعربية وInter للإنجليزية --}}
    <link
        rel="preconnect"
        href="https://fonts.bunny.net"
    >

    <link
        href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,800|inter:400,500,600,700,800"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('head')
</head>

<body
    class="min-h-screen bg-slate-50 text-slate-900 antialiased selection:bg-[#F59E0B]/30 selection:text-[#1E3A8A] dark:bg-slate-950 dark:text-slate-100"
    style="font-family: {{ app()->getLocale() === 'ar' ? "'Cairo', sans-serif" : "'Inter', sans-serif" }};"
>
    <div class="flex min-h-screen flex-col">

        @include('components.header')

        {{-- رسائل النجاح العامة --}}
        @if (session('success'))
            <div
                class="fixed start-1/2 top-24 z-[100] w-[calc(100%-2rem)] max-w-lg -translate-x-1/2"
                role="status"
            >
                <div
                    class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-white/95 p-4 shadow-xl shadow-emerald-950/5 backdrop-blur-xl dark:border-emerald-900 dark:bg-slate-900/95"
                >
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p
                            class="font-bold text-emerald-900 dark:text-emerald-200"
                        >
                            تم بنجاح
                        </p>

                        <p
                            class="mt-1 text-sm leading-6 text-emerald-700 dark:text-emerald-300"
                        >
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <main class="flex-1">
            @yield('content')
        </main>

        @include('components.footer')

    </div>

    @stack('scripts')
</body>
</html>