<footer
    class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950"
>
    <div
        class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8"
    >
        <div
            class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4"
        >
            {{-- Brand --}}
            <div>
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center gap-3"
                >
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#1E3A8A] text-white"
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
                        </svg>
                    </span>

                    <span>
                        <span
                            class="block text-xl font-black text-[#1E3A8A] dark:text-blue-300"
                        >
                            SHOFEER
                        </span>

                        <span
                            class="block text-xs text-slate-500"
                        >
                            شوفير
                        </span>
                    </span>
                </a>

                <p
                    class="mt-5 max-w-xs text-sm leading-7 text-slate-500 dark:text-slate-400"
                >
                    منصة لإدارة وحجز الرحلات بين اليمن والسعودية
                    بطريقة منظمة وآمنة وسهلة.
                </p>
            </div>

            {{-- Quick Links --}}
            <div>
                <h2
                    class="font-extrabold text-slate-900 dark:text-white"
                >
                    روابط سريعة
                </h2>

                <div
                    class="mt-5 grid gap-3 text-sm text-slate-500 dark:text-slate-400"
                >
                    <a
                        href="{{ route('home') }}"
                        class="transition hover:text-[#1E3A8A] dark:hover:text-blue-300"
                    >
                        الرئيسية
                    </a>

                    <a
                        href="{{ url('/trips') }}"
                        class="transition hover:text-[#1E3A8A] dark:hover:text-blue-300"
                    >
                        الرحلات
                    </a>

                    <a
                        href="{{ url('/track') }}"
                        class="transition hover:text-[#1E3A8A] dark:hover:text-blue-300"
                    >
                        تتبع
                    </a>

                    <a
                        href="{{ url('/live') }}"
                        class="transition hover:text-[#1E3A8A] dark:hover:text-blue-300"
                    >
                        الخريطة المباشرة
                    </a>
                </div>
            </div>

            {{-- Platform --}}
            <div>
                <h2
                    class="font-extrabold text-slate-900 dark:text-white"
                >
                    المنصة
                </h2>

                <div
                    class="mt-5 grid gap-3 text-sm text-slate-500 dark:text-slate-400"
                >
                    <a
                        href="{{ url('/about') }}"
                        class="transition hover:text-[#1E3A8A] dark:hover:text-blue-300"
                    >
                        عن المنصة
                    </a>

                    <a
                        href="{{ url('/faq') }}"
                        class="transition hover:text-[#1E3A8A] dark:hover:text-blue-300"
                    >
                        الأسئلة الشائعة
                    </a>

                    <a
                        href="{{ url('/contact') }}"
                        class="transition hover:text-[#1E3A8A] dark:hover:text-blue-300"
                    >
                        تواصل معنا
                    </a>
                </div>
            </div>

            {{-- Legal --}}
            <div>
                <h2
                    class="font-extrabold text-slate-900 dark:text-white"
                >
                    قانوني
                </h2>

                <div
                    class="mt-5 grid gap-3 text-sm text-slate-500 dark:text-slate-400"
                >
                    <a
                        href="{{ url('/terms') }}"
                        class="transition hover:text-[#1E3A8A] dark:hover:text-blue-300"
                    >
                        الشروط والأحكام
                    </a>

                    <a
                        href="{{ url('/privacy') }}"
                        class="transition hover:text-[#1E3A8A] dark:hover:text-blue-300"
                    >
                        سياسة الخصوصية
                    </a>
                </div>
            </div>
        </div>

        <div
            class="mt-12 flex flex-col gap-4 border-t border-slate-100 pt-7 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:text-slate-400"
        >
            <p>
                © {{ now()->year }} SHOFEER — جميع الحقوق محفوظة.
            </p>

            <p>
                سافر بثقة… وصل بأمان
            </p>
        </div>
    </div>
</footer>