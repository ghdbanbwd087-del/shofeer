@props([
    'seats',
    'gender' => null,
    'suggestedSeatNumbers' => [],
    'adjacentFemaleSeatNumbers' => [],
])

<div
    class="mx-auto max-w-md rounded-[2rem] border border-slate-200 bg-slate-50 p-5 sm:p-7 dark:border-slate-700 dark:bg-slate-800/60"
>
    {{-- Front Of Vehicle --}}
    <div
        class="mb-7 flex items-center justify-between rounded-2xl bg-slate-200 px-5 py-4 dark:bg-slate-700"
    >
        <span class="text-sm font-black">
            مقدمة السيارة
        </span>

        <div
            class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-lg shadow-sm dark:bg-slate-800"
        >
            🚐
        </div>

        <span
            class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-bold text-white dark:bg-slate-950"
        >
            السائق
        </span>
    </div>

    {{-- Seats --}}
    <div class="grid gap-4">
        @foreach ($seats->chunk(2) as $row)
            <div
                class="grid grid-cols-[1fr_46px_1fr] items-center gap-3"
            >
                @foreach ($row as $index => $seat)
                    @php
                        $status =
                            $seat->status->value;

                        $type =
                            $seat->seat_type->value;

                        $isSuggested =
                            in_array(
                                $seat->seat_number,
                                $suggestedSeatNumbers,
                                true
                            );

                        $isAdjacentToFemale =
                            in_array(
                                $seat->seat_number,
                                $adjacentFemaleSeatNumbers,
                                true
                            );

                        $isHeldByMe =
                            $status === 'held'
                            && $seat->held_by_user_id === auth()->id();

                        $isHeldByOther =
                            $status === 'held'
                            && $seat->held_by_user_id !== auth()->id();

                        $isUnavailable =
                            in_array(
                                $status,
                                [
                                    'booked',
                                    'locked',
                                ],
                                true
                            )
                            || $isHeldByOther;

                        $isFemaleOnlyForMale =
                            $gender?->value === 'male'
                            && $type === 'female';

                        $isDisabled =
                            $isUnavailable
                            || $isFemaleOnlyForMale;

                        if ($status === 'locked') {
                            $seatClasses =
                                'border-slate-500 bg-slate-700 text-white';
                        } elseif (
                            $status === 'booked'
                            || $isHeldByOther
                        ) {
                            $seatClasses =
                                'border-red-300 bg-red-100 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300';
                        } elseif ($isHeldByMe) {
                            $seatClasses =
                                'border-amber-400 bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300';
                        } elseif ($type === 'female') {
                            $seatClasses =
                                'border-pink-300 bg-pink-100 text-pink-700 dark:border-pink-900 dark:bg-pink-950/40 dark:text-pink-300';
                        } else {
                            $seatClasses =
                                'border-emerald-300 bg-emerald-100 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300';
                        }

                        if ($isSuggested) {
                            $seatClasses .=
                                ' ring-4 ring-amber-300/70';
                        }
                    @endphp

                    <div
                        class="{{ $index === 1 ? 'col-start-3' : '' }}"
                    >
                        <input
                            id="seat_{{ $seat->seat_number }}"
                            type="radio"
                            name="seat_number"
                            value="{{ $seat->seat_number }}"
                            class="peer sr-only"
                            @disabled($isDisabled)
                            @checked(
                                (string) old('seat_number')
                                ===
                                (string) $seat->seat_number
                            )
                        >

                        <label
                            for="seat_{{ $seat->seat_number }}"
                            class="
                                relative flex min-h-24 flex-col items-center justify-center rounded-2xl border-2 p-3 text-center transition
                                {{ $seatClasses }}
                                {{ $isDisabled ? 'cursor-not-allowed opacity-70' : 'cursor-pointer hover:-translate-y-1 hover:shadow-lg peer-checked:ring-4 peer-checked:ring-[#1E3A8A]/30' }}
                            "
                        >
                            @if ($isSuggested)
                                <span
                                    class="absolute -end-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full bg-[#F59E0B] text-xs shadow"
                                    title="مقعد مقترح"
                                >
                                    ⭐
                                </span>
                            @endif

                            <span class="text-xl font-black">
                                {{ $seat->seat_number }}
                            </span>

                            <span
                                class="mt-1 text-[11px] font-bold"
                            >
                                @if ($status === 'locked')
                                    مقفل
                                @elseif ($status === 'booked')
                                    محجوز
                                @elseif ($isHeldByOther)
                                    محجوز مؤقتاً
                                @elseif ($isHeldByMe)
                                    محجوز لك
                                @elseif ($type === 'female')
                                    للنساء
                                @else
                                    متاح
                                @endif
                            </span>

                            @if (
                                $isAdjacentToFemale
                                && $gender?->value === 'male'
                                && ! $isDisabled
                            )
                                <span
                                    class="mt-1 text-[10px] font-black text-pink-600 dark:text-pink-300"
                                >
                                    بجوار راكبة
                                </span>
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    {{-- Legend --}}
    <div
        class="mt-8 grid grid-cols-2 gap-3 text-xs sm:grid-cols-3"
    >
        <div class="flex items-center gap-2">
            <span
                class="h-4 w-4 rounded bg-emerald-400"
            ></span>

            متاح
        </div>

        <div class="flex items-center gap-2">
            <span
                class="h-4 w-4 rounded bg-red-400"
            ></span>

            محجوز
        </div>

        <div class="flex items-center gap-2">
            <span
                class="h-4 w-4 rounded bg-pink-400"
            ></span>

            للنساء
        </div>

        <div class="flex items-center gap-2">
            <span
                class="h-4 w-4 rounded bg-slate-700"
            ></span>

            مقفل
        </div>

        <div class="flex items-center gap-2">
            <span>
                ⭐
            </span>

            مقترح
        </div>
    </div>
</div>