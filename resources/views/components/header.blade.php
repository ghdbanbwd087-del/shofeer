<header
    class="sticky top-0 z-50 border-b border-white/40 bg-white/80 shadow-sm shadow-slate-950/5 backdrop-blur-xl dark:border-slate-800/70 dark:bg-slate-950/80"
>
    <div
        class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8"
    >
        {{-- Logo --}}
        <a
            href="{{ route('home') }}"
            class="group flex shrink-0 items-center gap-3"
            aria-label="SHOFEER"
        >
            <span
                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-[#1E3A8A] to-[#3156b7] text-white shadow-lg shadow-blue-950/15 transition duration-300 group-hover:-translate-y-0.5 group-hover:shadow-xl"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 17h14M6.5 17v2m11-2v2M4 13l1.6-5.1A2 2 0 0 1 7.5 6.5h9a2 2 0 0 1 1.9 1.4L20 13M4 13h16v4H4v-4Z"
                    />

                    <circle
                        cx="7"
                        cy="14.8"
                        r=".8"
                        fill="currentColor"
                        stroke="none"
                    />

                    <circle
                        cx="17"
                        cy="14.8"
                        r=".8"
                        fill="currentColor"
                        stroke="none"
                    />
                </svg>
            </span>

            <span class="leading-none">
                <span
                    class="block text-xl font-extrabold tracking-tight text-[#1E3A8A] dark:text-blue-300"
                >
                    SHOFEER
                </span>

                <span
                    class="mt-1 block text-xs font-semibold text-slate-500 dark:text-slate-400"
                >
                    شوفير
                </span>
            </span>
        </a>

        {{-- Desktop Navigation --}}
        <nav
            class="hidden items-center gap-1 lg:flex"
            aria-label="التنقل الرئيسي"
        >
            <a
                href="{{ route('home') }}"
                class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-[#1E3A8A] dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-300"
            >
                الرئيسية
            </a>

            <a
                href="{{ url('/trips') }}"
                class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-[#1E3A8A] dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-300"
            >
                الرحلات
            </a>

            <a
                href="{{ url('/packages/create') }}"
                class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-[#1E3A8A] dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-300"
            >
                أرسل بضاعة
            </a>

            <a
                href="{{ url('/track') }}"
                class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-[#1E3A8A] dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-300"
            >
                تتبع
            </a>

            <a
                href="{{ url('/live') }}"
                class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-[#1E3A8A] dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-300"
            >
                الخريطة المباشرة
            </a>
        </nav>

        {{-- Desktop Actions --}}
        <div class="hidden items-center gap-2 lg:flex">
            @guest
                <a
                    href="{{ route('login') }}"
                    class="rounded-xl px-4 py-2.5 text-sm font-bold text-[#1E3A8A] transition hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-950/40"
                >
                    تسجيل دخول
                </a>

                <a
                    href="{{ route('register') }}"
                    class="rounded-xl bg-gradient-to-l from-[#F59E0B] to-amber-400 px-5 py-2.5 text-sm font-extrabold text-slate-950 shadow-lg shadow-amber-500/20 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl"
                >
                    سجل الآن
                </a>
            @else
                @php
                    $dashboardRoute = auth()
                        ->user()
                        ->role
                        ->dashboardRouteName();
                @endphp

                <a
                    href="{{ route($dashboardRoute) }}"
                    class="rounded-xl bg-[#1E3A8A] px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-950/15 transition hover:-translate-y-0.5 hover:bg-blue-800"
                >
                    لوحة التحكم
                </a>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-red-50 hover:text-red-600 dark:text-slate-300 dark:hover:bg-red-950/30 dark:hover:text-red-300"
                    >
                        خروج
                    </button>
                </form>
            @endguest
        </div>

        {{-- Mobile Menu --}}
        <details class="relative lg:hidden">
            <summary
                class="flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-[#1E3A8A] dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                aria-label="فتح القائمة"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        d="M4 7h16M4 12h16M4 17h16"
                    />
                </svg>
            </summary>

            <div
                class="absolute end-0 top-14 w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-2xl shadow-slate-950/10 dark:border-slate-700 dark:bg-slate-900"
            >
                <nav class="grid gap-1">
                    <a
                        href="{{ route('home') }}"
                        class="rounded-xl px-4 py-3 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800"
                    >
                        الرئيسية
                    </a>

                    <a
                        href="{{ url('/trips') }}"
                        class="rounded-xl px-4 py-3 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800"
                    >
                        الرحلات
                    </a>

                    <a
                        href="{{ url('/packages/create') }}"
                        class="rounded-xl px-4 py-3 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800"
                    >
                        أرسل بضاعة
                    </a>

                    <a
                        href="{{ url('/track') }}"
                        class="rounded-xl px-4 py-3 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800"
                    >
                        تتبع
                    </a>

                    <a
                        href="{{ url('/live') }}"
                        class="rounded-xl px-4 py-3 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800"
                    >
                        الخريطة المباشرة
                    </a>
                </nav>

                <div
                    class="my-3 border-t border-slate-100 dark:border-slate-800"
                ></div>

                @guest
                    <div class="grid grid-cols-2 gap-2">
                        <a
                            href="{{ route('login') }}"
                            class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-bold text-[#1E3A8A] dark:border-slate-700 dark:text-blue-300"
                        >
                            دخول
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="rounded-xl bg-gradient-to-l from-[#F59E0B] to-amber-400 px-4 py-3 text-center text-sm font-extrabold text-slate-950"
                        >
                            سجل الآن
                        </a>
                    </div>
                @else
                    @php
                        $mobileDashboardRoute = auth()
                            ->user()
                            ->role
                            ->dashboardRouteName();
                    @endphp

                    <a
                        href="{{ route($mobileDashboardRoute) }}"
                        class="block rounded-xl bg-[#1E3A8A] px-4 py-3 text-center text-sm font-bold text-white"
                    >
                        لوحة التحكم
                    </a>

                    <form
                        class="mt-2"
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="w-full rounded-xl border border-red-100 px-4 py-3 text-sm font-semibold text-red-600 dark:border-red-950 dark:text-red-300"
                        >
                            تسجيل الخروج
                        </button>
                    </form>
                @endguest
            </div>
        </details>
    </div>
</header>