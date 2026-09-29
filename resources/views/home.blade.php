@extends('layouts.app')

@section('title', 'الرئيسية')

@section(
    'description',
    'SHOFEER — منصتك الموثوقة للسفر بين اليمن والسعودية.'
)

@section('content')

{{-- Hero --}}
<section
    class="relative overflow-hidden bg-gradient-to-b from-blue-50 via-white to-white dark:from-blue-950/40 dark:via-slate-950 dark:to-slate-950"
>
    <div
        class="absolute -start-36 top-10 h-96 w-96 rounded-full bg-blue-300/20 blur-3xl"
    ></div>

    <div
        class="absolute -end-32 bottom-0 h-80 w-80 rounded-full bg-amber-300/20 blur-3xl"
    ></div>

    <div
        class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-24"
    >
        <div>
            <span
                class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white/80 px-4 py-2 text-sm font-bold text-[#1E3A8A] shadow-sm backdrop-blur dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300"
            >
                <span
                    class="h-2 w-2 rounded-full bg-[#F59E0B]"
                ></span>

                السفر بين اليمن والسعودية
            </span>

            <h1
                class="mt-7 text-4xl font-black leading-[1.25] tracking-tight text-slate-950 sm:text-5xl lg:text-6xl dark:text-white"
            >
                سافر بثقة…
                <span
                    class="block bg-gradient-to-l from-[#1E3A8A] to-blue-600 bg-clip-text text-transparent dark:from-blue-300 dark:to-blue-500"
                >
                    وصل بأمان
                </span>
            </h1>

            <p
                class="mt-6 max-w-xl text-lg leading-8 text-slate-600 dark:text-slate-300"
            >
                منصتك الموثوقة للسفر بين اليمن والسعودية.
                ابحث عن رحلتك، اختر مقعدك وادفع للمنصة.
            </p>

            {{-- Search --}}
            <form
                method="GET"
                action="{{ url('/trips') }}"
                class="mt-9 grid gap-3 rounded-3xl border border-slate-200/80 bg-white/90 p-4 shadow-xl shadow-slate-950/5 backdrop-blur sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-800 dark:bg-slate-900/90"
            >
                <div>
                    <label
                        for="from"
                        class="mb-1.5 block text-xs font-bold text-slate-500"
                    >
                        من
                    </label>

                    <select
                        id="from"
                        name="from"
                        class="w-full rounded-xl border-0 bg-slate-50 px-3 py-3 text-sm font-semibold outline-none ring-1 ring-slate-200 focus:ring-2 focus:ring-[#1E3A8A] dark:bg-slate-800 dark:ring-slate-700"
                    >
                        <option value="">
                            اختر المدينة
                        </option>

                        <option disabled>
                            المدن المفعّلة ستظهر بعد المرحلة 2
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        for="to"
                        class="mb-1.5 block text-xs font-bold text-slate-500"
                    >
                        إلى
                    </label>

                    <select
                        id="to"
                        name="to"
                        class="w-full rounded-xl border-0 bg-slate-50 px-3 py-3 text-sm font-semibold outline-none ring-1 ring-slate-200 focus:ring-2 focus:ring-[#1E3A8A] dark:bg-slate-800 dark:ring-slate-700"
                    >
                        <option value="">
                            اختر المدينة
                        </option>

                        <option disabled>
                            المدن المفعّلة ستظهر بعد المرحلة 2
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        for="date"
                        class="mb-1.5 block text-xs font-bold text-slate-500"
                    >
                        التاريخ
                    </label>

                    <input
                        id="date"
                        type="date"
                        name="date"
                        min="{{ now()->toDateString() }}"
                        class="w-full rounded-xl border-0 bg-slate-50 px-3 py-3 text-sm font-semibold outline-none ring-1 ring-slate-200 focus:ring-2 focus:ring-[#1E3A8A] dark:bg-slate-800 dark:ring-slate-700"
                    >
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-gradient-to-l from-[#1E3A8A] to-blue-700 px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-blue-900/20 transition duration-300 hover:-translate-y-0.5"
                    >
                        ابحث
                    </button>
                </div>
            </form>

            {{-- Trust Badges --}}
            <div
                class="mt-7 flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold text-slate-600 dark:text-slate-300"
            >
                <span class="flex items-center gap-2">
                    <span class="text-emerald-500">
                        ✓
                    </span>

                    سائقون موثقون
                </span>

                <span class="flex items-center gap-2">
                    <span class="text-emerald-500">
                        ✓
                    </span>

                    أسعار شفافة
                </span>

                <span class="flex items-center gap-2">
                    <span class="text-emerald-500">
                        ✓
                    </span>

                    تتبع مباشر
                </span>
            </div>
        </div>

        {{-- Hero Illustration --}}
        <div class="relative">
            <div
                class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#1E3A8A] via-blue-800 to-slate-950 p-6 shadow-2xl shadow-blue-950/20 sm:p-10"
            >
                <div
                    class="absolute -end-12 -top-12 h-48 w-48 rounded-full bg-[#F59E0B]/20 blur-2xl"
                ></div>

                <svg
                    viewBox="0 0 640 460"
                    class="relative w-full"
                    fill="none"
                    aria-label="سيارة على الطريق"
                >
                    <path
                        d="M238 460 300 190h40l62 270H238Z"
                        fill="#F8FAFC"
                        fill-opacity=".95"
                    />

                    <path
                        d="M313 420v-50"
                        stroke="#F59E0B"
                        stroke-width="8"
                        stroke-linecap="round"
                    />

                    <path
                        d="M320 324v-42"
                        stroke="#F59E0B"
                        stroke-width="7"
                        stroke-linecap="round"
                    />

                    <path
                        d="M326 240v-28"
                        stroke="#F59E0B"
                        stroke-width="6"
                        stroke-linecap="round"
                    />

                    <rect
                        x="214"
                        y="178"
                        width="210"
                        height="98"
                        rx="34"
                        fill="#F59E0B"
                    />

                    <path
                        d="m250 178 25-58h90l27 58H250Z"
                        fill="#E2E8F0"
                    />

                    <path
                        d="M286 132h68l17 38h-102l17-38Z"
                        fill="#1E3A8A"
                    />

                    <circle
                        cx="260"
                        cy="278"
                        r="28"
                        fill="#0F172A"
                    />

                    <circle
                        cx="378"
                        cy="278"
                        r="28"
                        fill="#0F172A"
                    />

                    <circle
                        cx="260"
                        cy="278"
                        r="11"
                        fill="#CBD5E1"
                    />

                    <circle
                        cx="378"
                        cy="278"
                        r="11"
                        fill="#CBD5E1"
                    />

                    <circle
                        cx="259"
                        cy="218"
                        r="11"
                        fill="#FFF7ED"
                    />

                    <circle
                        cx="378"
                        cy="218"
                        r="11"
                        fill="#FFF7ED"
                    />
                </svg>
            </div>

            <div
                class="absolute -bottom-5 start-5 rounded-2xl border border-white/50 bg-white/90 p-4 shadow-xl backdrop-blur dark:border-slate-700 dark:bg-slate-900/90"
            >
                <p
                    class="text-xs font-bold text-slate-400"
                >
                    SHOFEER
                </p>

                <p
                    class="mt-1 font-black text-[#1E3A8A] dark:text-blue-300"
                >
                    رحلتك تبدأ من هنا
                </p>
            </div>
        </div>
    </div>
</section>

{{-- How it Works --}}
<section class="py-20">
    <div
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        <div class="text-center">
            <p
                class="font-extrabold text-[#F59E0B]"
            >
                كيف تعمل؟
            </p>

            <h2
                class="mt-2 text-3xl font-black text-slate-950 sm:text-4xl dark:text-white"
            >
                ثلاث خطوات لرحلتك
            </h2>
        </div>

        <div
            class="mt-12 grid gap-5 md:grid-cols-3"
        >
            @foreach ([
                ['01', 'ابحث', 'حدد نقطة الانطلاق والوصول والتاريخ المناسب.'],
                ['02', 'اختر', 'اختر الرحلة والمقعد المناسب لك.'],
                ['03', 'ادفع', 'أكمل الحجز والدفع عبر المنصة.'],
            ] as $step)
                <article
                    class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <span
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 font-black text-[#1E3A8A] transition group-hover:bg-[#1E3A8A] group-hover:text-white dark:bg-blue-950/50 dark:text-blue-300"
                    >
                        {{ $step[0] }}
                    </span>

                    <h3
                        class="mt-6 text-xl font-black"
                    >
                        {{ $step[1] }}
                    </h3>

                    <p
                        class="mt-3 leading-7 text-slate-500 dark:text-slate-400"
                    >
                        {{ $step[2] }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Available Trips --}}
<section
    class="bg-slate-100/70 py-20 dark:bg-slate-900/40"
>
    <div
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div>
                <p
                    class="font-extrabold text-[#F59E0B]"
                >
                    الرحلات
                </p>

                <h2
                    class="mt-2 text-3xl font-black dark:text-white"
                >
                    الرحلات المتاحة
                </h2>
            </div>

            <a
                href="{{ url('/trips') }}"
                class="font-bold text-[#1E3A8A] dark:text-blue-300"
            >
                عرض كل الرحلات ←
            </a>
        </div>

        <div
            class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
        >
            @for ($trip = 1; $trip <= 6; $trip++)
                <article
                    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="h-40 animate-pulse bg-gradient-to-br from-blue-100 to-slate-100 dark:from-blue-950/50 dark:to-slate-800"
                    ></div>

                    <div class="p-5">
                        <div
                            class="h-4 w-24 rounded-full bg-slate-100 dark:bg-slate-800"
                        ></div>

                        <div
                            class="mt-4 h-6 w-3/4 rounded-full bg-slate-100 dark:bg-slate-800"
                        ></div>

                        <p
                            class="mt-5 text-sm leading-6 text-slate-400"
                        >
                            سيتم عرض الرحلات الحقيقية هنا بعد إكمال
                            Drivers + Trips في المرحلة 2.
                        </p>
                    </div>
                </article>
            @endfor
        </div>
    </div>
</section>

{{-- Why SHOFEER --}}
<section class="py-20">
    <div
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
    >
        <div class="text-center">
            <p
                class="font-extrabold text-[#F59E0B]"
            >
                لماذا شوفير؟
            </p>

            <h2
                class="mt-2 text-3xl font-black dark:text-white"
            >
                رحلة أكثر تنظيماً ووضوحاً
            </h2>
        </div>

        <div
            class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4"
        >
            @foreach ([
                ['سائقون موثقون', 'تراجع الإدارة بيانات ومستندات السائق قبل اعتماده.'],
                ['أسعار واضحة', 'يرى الراكب السعر المحدد للرحلة قبل إكمال الحجز.'],
                ['اختيار المقعد', 'يتمكن الراكب من اختيار مقعده قبل الدفع.'],
                ['تتبع مباشر', 'يدعم النظام تتبع الرحلات المنطلقة مباشرة.'],
            ] as $feature)
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                >
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 font-black text-amber-700 dark:bg-amber-950/40 dark:text-amber-300"
                    >
                        ✓
                    </span>

                    <h3
                        class="mt-5 text-lg font-black"
                    >
                        {{ $feature[0] }}
                    </h3>

                    <p
                        class="mt-3 text-sm leading-7 text-slate-500 dark:text-slate-400"
                    >
                        {{ $feature[1] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Stats --}}
<section
    class="bg-[#1E3A8A] py-16 text-white"
>
    <div
        class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-4 text-center sm:px-6 lg:grid-cols-4 lg:px-8"
    >
        @foreach ([
            'الرحلات',
            'السائقون',
            'المستخدمون',
            'الحجوزات',
        ] as $stat)
            <div>
                <p
                    class="text-4xl font-black text-[#F59E0B]"
                >
                    —
                </p>

                <p
                    class="mt-2 text-sm font-bold text-blue-100"
                >
                    {{ $stat }}
                </p>
            </div>
        @endforeach
    </div>
</section>

{{-- Testimonials --}}
<section class="py-20">
    <div
        class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8"
    >
        <p
            class="font-extrabold text-[#F59E0B]"
        >
            آراء المستخدمين
        </p>

        <h2
            class="mt-2 text-3xl font-black dark:text-white"
        >
            تجربتك هي ما يهمنا
        </h2>

        <div
            class="mt-10 rounded-[2rem] border border-slate-200 bg-white p-10 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-2xl text-[#1E3A8A] dark:bg-blue-950/50 dark:text-blue-300"
            >
                “
            </div>

            <p
                class="mx-auto mt-5 max-w-xl leading-8 text-slate-500 dark:text-slate-400"
            >
                ستظهر هنا تقييمات وآراء المستخدمين الفعلية بعد
                تشغيل نظام الرحلات والتقييمات.
            </p>
        </div>
    </div>
</section>

{{-- Live Map --}}
<section
    class="bg-slate-100/70 py-20 dark:bg-slate-900/40"
>
    <div
        class="mx-auto grid max-w-7xl items-center gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8"
    >
        <div>
            <p
                class="font-extrabold text-[#F59E0B]"
            >
                الخريطة المباشرة
            </p>

            <h2
                class="mt-2 text-3xl font-black dark:text-white"
            >
                تابع الرحلات المنطلقة
            </h2>

            <p
                class="mt-5 max-w-xl leading-8 text-slate-500 dark:text-slate-400"
            >
                عند تفعيل التتبع في المراحل القادمة ستتمكن
                من مشاهدة الرحلات النشطة على الخريطة دون عرض
                بيانات شخصية للعامة.
            </p>

            <a
                href="{{ url('/live') }}"
                class="mt-7 inline-flex rounded-2xl bg-[#1E3A8A] px-6 py-3.5 font-extrabold text-white shadow-lg shadow-blue-950/20 transition hover:-translate-y-0.5"
            >
                فتح الخريطة
            </a>
        </div>

        <div
            class="relative h-80 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="absolute inset-0 opacity-30"
                style="background-image: radial-gradient(#1E3A8A 1px, transparent 1px); background-size: 24px 24px;"
            ></div>

            <div
                class="absolute inset-0 flex items-center justify-center"
            >
                <div
                    class="rounded-2xl bg-white/90 px-6 py-4 text-center shadow-xl backdrop-blur dark:bg-slate-900/90"
                >
                    <div
                        class="mx-auto h-3 w-3 animate-pulse rounded-full bg-[#F59E0B]"
                    ></div>

                    <p
                        class="mt-3 font-extrabold text-[#1E3A8A] dark:text-blue-300"
                    >
                        Live Tracking
                    </p>

                    <p
                        class="mt-1 text-xs text-slate-400"
                    >
                        سيتم تفعيله لاحقاً
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="py-20">
    <div
        class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8"
    >
        <div class="text-center">
            <p
                class="font-extrabold text-[#F59E0B]"
            >
                الأسئلة الشائعة
            </p>

            <h2
                class="mt-2 text-3xl font-black dark:text-white"
            >
                لديك سؤال؟
            </h2>
        </div>

        <div class="mt-10 grid gap-3">
            @foreach ([
                [
                    'كيف أحجز رحلة؟',
                    'ابحث عن الرحلة المناسبة، اختر المقعد ثم أكمل بيانات الحجز والدفع.',
                ],
                [
                    'هل أستطيع اختيار مقعدي؟',
                    'نعم، يدعم SHOFEER اختيار المقعد قبل إكمال الدفع.',
                ],
                [
                    'ما طرق الدفع المخطط دعمها؟',
                    'التحويل البنكي وكريمي وفلوسك وجوالي وSTC Pay وفق إعدادات الإدارة.',
                ],
                [
                    'كم مقعداً أستطيع حجزه؟',
                    'يسمح النظام بحجز مقعدين كحد أقصى في الرحلة.',
                ],
                [
                    'هل يوجد تتبع للرحلات؟',
                    'نعم، التوثيق يتضمن نظام تتبع مباشر للرحلات المنطلقة.',
                ],
                [
                    'هل السائقون موثقون؟',
                    'يمر السائق بعملية تسجيل ومراجعة واعتماد من الإدارة قبل تشغيله على المنصة.',
                ],
            ] as $faq)
                <details
                    class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm open:shadow-md dark:border-slate-800 dark:bg-slate-900"
                >
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-4 font-extrabold"
                    >
                        {{ $faq[0] }}

                        <span
                            class="text-xl text-[#1E3A8A] transition group-open:rotate-45 dark:text-blue-300"
                        >
                            +
                        </span>
                    </summary>

                    <p
                        class="mt-4 leading-7 text-slate-500 dark:text-slate-400"
                    >
                        {{ $faq[1] }}
                    </p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="px-4 pb-20 sm:px-6 lg:px-8">
    <div
        class="relative mx-auto max-w-7xl overflow-hidden rounded-[2.5rem] bg-gradient-to-l from-[#1E3A8A] to-blue-700 px-6 py-14 text-center text-white shadow-2xl shadow-blue-950/20 sm:px-12"
    >
        <div
            class="absolute -start-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"
        ></div>

        <div class="relative">
            <h2
                class="text-3xl font-black sm:text-4xl"
            >
                جاهز لرحلتك القادمة؟
            </h2>

            <p
                class="mx-auto mt-4 max-w-2xl leading-8 text-blue-100"
            >
                أنشئ حسابك الآن واستعد لحجز رحلتك عبر SHOFEER.
            </p>

            <div
                class="mt-8 flex flex-col justify-center gap-3 sm:flex-row"
            >
                <a
                    href="{{ url('/trips') }}"
                    class="rounded-2xl bg-[#F59E0B] px-7 py-4 font-black text-slate-950 shadow-lg transition hover:-translate-y-0.5"
                >
                    احجز الآن
                </a>

                @guest
                    <a
                        href="{{ route('register') }}"
                        class="rounded-2xl border border-white/30 bg-white/10 px-7 py-4 font-black text-white backdrop-blur transition hover:bg-white/20"
                    >
                        إنشاء حساب
                    </a>
                @endguest
            </div>
        </div>
    </div>
</section>

@endsection