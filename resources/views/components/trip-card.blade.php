@props([
    'trip',
])

<article
    class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
>
    <div
        class="relative flex h-44 items-center justify-center overflow-hidden bg-gradient-to-br from-blue-100 via-slate-100 to-amber-50 dark:from-blue-950/50 dark:via-slate-900 dark:to-slate-800"
    >
        @if ($trip->car->image)
            <img
                src="{{ Storage::disk('local')->url($trip->car->image) }}"
                alt="{{ $trip->car->make }} {{ $trip->car->model }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            >
        @else
            <svg
                class="h-24 w-24 text-[#1E3A8A]/20 dark:text-blue-300/20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M5 17h14M6.5 17v2m11-2v2M4 13l1.6-5.1A2 2 0 0 1 7.5 6.5h9a2 2 0 0 1 1.9 1.4L20 13M4 13h16v4H4v-4Z"
                />
            </svg>
        @endif

        <span
            class="absolute end-4 top-4 rounded-full bg-white/90 px-3 py-1.5 text-xs font-extrabold text-emerald-700 shadow-sm backdrop-blur dark:bg-slate-900/90 dark:text-emerald-300"
        >
            متاح
        </span>
    </div>

    <div class="p-6">
        <div
            class="flex items-center justify-between gap-4"
        >
            <div>
                <p
                    class="text-xs font-bold text-slate-400"
                >
                    السائق
                </p>

                <p
                    class="mt-1 font-extrabold text-slate-900 dark:text-white"
                >
                    {{ $trip->driver->user->name }}
                </p>
            </div>

            <div
                class="rounded-xl bg-amber-50 px-3 py-2 text-sm font-bold text-amber-700 dark:bg-amber-950/30 dark:text-amber-300"
            >
                ★ {{ $trip->driver->rating }}
            </div>
        </div>

        <div
            class="my-5 border-t border-slate-100 dark:border-slate-800"
        ></div>

        <div
            class="flex items-center justify-between gap-3"
        >
            <div>
                <p
                    class="text-xs text-slate-400"
                >
                    من
                </p>

                <p class="mt-1 font-black">
                    {{ $trip->fromCity->name_ar }}
                </p>
            </div>

            <div
                class="flex flex-1 items-center gap-2"
            >
                <div
                    class="h-px flex-1 bg-slate-200 dark:bg-slate-700"
                ></div>

                <span
                    class="text-[#F59E0B]"
                >
                    ←
                </span>

                <div
                    class="h-px flex-1 bg-slate-200 dark:bg-slate-700"
                ></div>
            </div>

            <div class="text-end">
                <p
                    class="text-xs text-slate-400"
                >
                    إلى
                </p>

                <p class="mt-1 font-black">
                    {{ $trip->toCity->name_ar }}
                </p>
            </div>
        </div>

        <div
            class="mt-5 grid grid-cols-2 gap-3 rounded-2xl bg-slate-50 p-4 text-sm dark:bg-slate-800/60"
        >
            <div>
                <p class="text-slate-400">
                    الموعد
                </p>

                <p class="mt-1 font-bold">
                    {{ $trip->departure_at->format('Y-m-d') }}
                </p>
            </div>

            <div>
                <p class="text-slate-400">
                    الوقت
                </p>

                <p class="mt-1 font-bold">
                    {{ $trip->departure_at->format('H:i') }}
                </p>
            </div>

            <div>
                <p class="text-slate-400">
                    المقاعد
                </p>

                <p class="mt-1 font-bold">
                    {{ $trip->available_seats }}
                    متاح
                </p>
            </div>

            <div>
                <p class="text-slate-400">
                    السيارة
                </p>

                <p class="mt-1 font-bold">
                    {{ $trip->car->make }}
                    {{ $trip->car->model }}
                </p>
            </div>
        </div>

        <div
            class="mt-5 flex items-end justify-between gap-4"
        >
            <div>
                <p
                    class="text-xs font-semibold text-slate-400"
                >
                    السعر
                </p>

                <p
                    class="mt-1 text-2xl font-black text-[#1E3A8A] dark:text-blue-300"
                >
                    {{ number_format((float) $trip->price, 2) }}
                </p>
            </div>

            <a
                href="{{ route('trips.show', $trip) }}"
                class="rounded-xl bg-[#1E3A8A] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-blue-950/15 transition hover:-translate-y-0.5 hover:bg-blue-800"
            >
                التفاصيل
            </a>
        </div>
    </div>
</article>