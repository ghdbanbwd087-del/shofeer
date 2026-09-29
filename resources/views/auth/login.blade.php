@extends('layouts.app')

@section('title', 'تسجيل الدخول')

@section(
    'description',
    'تسجيل الدخول إلى حسابك في SHOFEER.'
)

@section('content')
<section
    class="relative overflow-hidden px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20"
>
    <div
        class="pointer-events-none absolute -start-32 top-0 h-80 w-80 rounded-full bg-blue-300/20 blur-3xl"
    ></div>

    <div
        class="pointer-events-none absolute -end-24 bottom-0 h-72 w-72 rounded-full bg-amber-300/20 blur-3xl"
    ></div>

    <div
        class="relative mx-auto grid max-w-5xl overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-2xl shadow-slate-950/10 lg:grid-cols-2 dark:border-slate-800 dark:bg-slate-900"
    >
        {{-- Side Panel --}}
        <div
            class="relative hidden overflow-hidden bg-gradient-to-br from-[#1E3A8A] via-blue-800 to-blue-950 p-12 text-white lg:flex lg:flex-col lg:justify-between"
        >
            <div
                class="absolute -end-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-2xl"
            ></div>

            <div>
                <span
                    class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-bold backdrop-blur"
                >
                    مرحباً بعودتك
                </span>

                <h1
                    class="mt-8 text-4xl font-black leading-tight"
                >
                    رحلتك القادمة
                    <span class="text-[#F59E0B]">
                        أقرب مما تتوقع.
                    </span>
                </h1>

                <p
                    class="mt-5 max-w-md text-base leading-8 text-blue-100"
                >
                    سجّل دخولك للوصول إلى حجوزاتك ومتابعة
                    رحلاتك وإدارة حسابك.
                </p>
            </div>

            <div
                class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#F59E0B] text-slate-950"
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

                    <div>
                        <p class="font-extrabold">
                            دخول آمن
                        </p>

                        <p class="mt-1 text-sm text-blue-100">
                            جلسات محمية وإدارة صلاحيات حسب الدور.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="p-6 sm:p-10 lg:p-12">
            <div class="mx-auto max-w-md">
                <div>
                    <p
                        class="text-sm font-extrabold text-[#F59E0B]"
                    >
                        SHOFEER
                    </p>

                    <h2
                        class="mt-2 text-3xl font-black tracking-tight text-slate-950 dark:text-white"
                    >
                        تسجيل الدخول
                    </h2>

                    <p
                        class="mt-3 text-sm leading-7 text-slate-500 dark:text-slate-400"
                    >
                        أدخل رقم جوالك وكلمة المرور للمتابعة.
                    </p>
                </div>

                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div
                        class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300"
                    >
                        <ul class="grid gap-1.5">
                            @foreach ($errors->all() as $error)
                                <li class="flex gap-2">
                                    <span>•</span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Google --}}
                <a
                    href="{{ route('auth.google.redirect') }}"
                    class="mt-7 flex w-full items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-700 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            fill="#4285F4"
                            d="M21.6 12.2c0-.7-.1-1.5-.2-2.2H12v4.2h5.4a4.6 4.6 0 0 1-2 3v2.7h3.3c1.9-1.8 2.9-4.4 2.9-7.7Z"
                        />

                        <path
                            fill="#34A853"
                            d="M12 22c2.7 0 5-.9 6.7-2.4l-3.3-2.7c-.9.6-2.1 1-3.4 1-2.6 0-4.8-1.8-5.6-4.1H3v2.8A10 10 0 0 0 12 22Z"
                        />

                        <path
                            fill="#FBBC05"
                            d="M6.4 13.8a6 6 0 0 1 0-3.6V7.4H3a10 10 0 0 0 0 9.2l3.4-2.8Z"
                        />

                        <path
                            fill="#EA4335"
                            d="M12 6.1c1.5 0 2.8.5 3.9 1.5l2.9-2.9A9.8 9.8 0 0 0 3 7.4l3.4 2.8C7.2 7.8 9.4 6.1 12 6.1Z"
                        />
                    </svg>

                    متابعة عبر Google
                </a>

                <div
                    class="my-7 flex items-center gap-4"
                >
                    <div
                        class="h-px flex-1 bg-slate-200 dark:bg-slate-700"
                    ></div>

                    <span
                        class="text-xs font-semibold text-slate-400"
                    >
                        أو
                    </span>

                    <div
                        class="h-px flex-1 bg-slate-200 dark:bg-slate-700"
                    ></div>
                </div>

                <form
                    method="POST"
                    action="{{ route('login.store') }}"
                    class="space-y-5"
                >
                    @csrf

                    {{-- Phone --}}
                    <div>
                        <label
                            for="phone"
                            class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200"
                        >
                            رقم الجوال
                        </label>

                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            required
                            autofocus
                            placeholder="مثال: 771234567"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-start text-slate-900 outline-none transition duration-200 placeholder:text-slate-400 focus:border-[#1E3A8A] focus:bg-white focus:ring-4 focus:ring-blue-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-400 dark:focus:bg-slate-900"
                        >
                    </div>

                    {{-- Password --}}
                    <div>
                        <div
                            class="mb-2 flex items-center justify-between gap-3"
                        >
                            <label
                                for="password"
                                class="block text-sm font-bold text-slate-700 dark:text-slate-200"
                            >
                                كلمة المرور
                            </label>

                            <a
                                href="{{ url('/password/reset') }}"
                                class="text-xs font-bold text-[#1E3A8A] transition hover:text-blue-700 dark:text-blue-300"
                            >
                                نسيت كلمة المرور؟
                            </a>
                        </div>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-slate-900 outline-none transition duration-200 focus:border-[#1E3A8A] focus:bg-white focus:ring-4 focus:ring-blue-900/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-400 dark:focus:bg-slate-900"
                        >
                    </div>

                    {{-- Remember --}}
                    <label
                        class="flex cursor-pointer items-center gap-3"
                    >
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            @checked(old('remember'))
                            class="h-4 w-4 rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1E3A8A]"
                        >

                        <span
                            class="text-sm text-slate-600 dark:text-slate-300"
                        >
                            تذكرني
                        </span>
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-gradient-to-l from-[#1E3A8A] to-blue-700 px-5 py-4 text-sm font-extrabold text-white shadow-lg shadow-blue-900/20 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-900/20"
                    >
                        تسجيل الدخول
                    </button>
                </form>

                <p
                    class="mt-7 text-center text-sm text-slate-500 dark:text-slate-400"
                >
                    ليس لديك حساب؟

                    <a
                        href="{{ route('register') }}"
                        class="font-extrabold text-[#1E3A8A] hover:underline dark:text-blue-300"
                    >
                        سجل الآن
                    </a>
                </p>
            </div>
        </div>
    </div>
</section>
@endsection