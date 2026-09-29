@extends('layouts.app')

@section('title', 'تقييماتي - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                تجربتك تهمنا
            </p>

            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                تقييماتي
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                قيّم الرحلات المكتملة وراجع تقييماتك السابقة.
            </p>
        </div>

        <a
            href="{{ route('dashboard.bookings.index', ['tab' => 'past']) }}"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700"
        >
            حجوزاتي السابقة
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <section class="rounded-2xl bg-blue-900 p-6 text-white shadow-sm">
            <p class="text-sm font-semibold text-blue-100">
                عدد تقييماتك
            </p>

            <div class="mt-3 text-4xl font-bold">
                {{ $ratings->total() }}
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                متوسط تقييماتك
            </p>

            <div class="mt-3 flex items-end gap-2">
                <span class="text-4xl font-bold text-amber-500">
                    {{ $averageScore !== null ? number_format($averageScore, 2) : '—' }}
                </span>

                @if ($averageScore !== null)
                    <span class="pb-1 text-lg text-amber-500">
                        ★
                    </span>
                @endif
            </div>
        </section>
    </div>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-xl font-bold text-slate-900">
                رحلات بانتظار تقييمك
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                تظهر هنا الحجوزات المؤكدة بعد انتهاء موعد الرحلة فقط.
            </p>
        </div>

        @if ($eligibleBookings->isEmpty())
            <div class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                لا توجد رحلات تحتاج إلى تقييم الآن.
            </div>
        @else
            <div class="space-y-5">
                @foreach ($eligibleBookings as $booking)
                    <form
                        method="POST"
                        action="{{ route('dashboard.bookings.rating.store', $booking) }}"
                        class="rounded-2xl border border-slate-200 p-5"
                    >
                        @csrf

                        <div class="grid gap-5 lg:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold text-slate-400">
                                    الحجز
                                </p>

                                <p class="mt-1 font-bold text-blue-900">
                                    {{ $booking->booking_code }}
                                </p>

                                <p class="mt-2 text-sm text-slate-600">
                                    السائق:
                                    {{ $booking->trip?->driver?->user?->name ?? '—' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $booking->trip?->departure_at?->format('Y-m-d H:i') }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    التقييم
                                </label>

                                <select
                                    name="score"
                                    class="w-full rounded-xl border-slate-300"
                                    required
                                >
                                    <option value="">اختر</option>
                                    <option value="5">★★★★★ ممتاز</option>
                                    <option value="4">★★★★☆ جيد جدًا</option>
                                    <option value="3">★★★☆☆ جيد</option>
                                    <option value="2">★★☆☆☆ مقبول</option>
                                    <option value="1">★☆☆☆☆ ضعيف</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    تعليق اختياري
                                </label>

                                <textarea
                                    name="comment"
                                    rows="3"
                                    maxlength="1000"
                                    class="w-full rounded-xl border-slate-300"
                                    placeholder="اكتب ملاحظتك عن الرحلة..."
                                ></textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button
                                type="submit"
                                class="rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-bold text-white"
                            >
                                حفظ التقييم
                            </button>
                        </div>
                    </form>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-xl font-bold text-slate-900">
                تقييماتي السابقة
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                يمكنك تعديل تقييمك السابق من هنا.
            </p>
        </div>

        @if ($ratings->isEmpty())
            <div class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                لم تضف أي تقييم بعد.
            </div>
        @else
            <div class="space-y-5">
                @foreach ($ratings as $rating)
                    <form
                        method="POST"
                        action="{{ route('dashboard.ratings.update', $rating) }}"
                        class="rounded-2xl border border-slate-200 p-5"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-5 lg:grid-cols-3">
                            <div>
                                <p class="font-bold text-slate-900">
                                    {{ $rating->driver?->user?->name ?? 'السائق' }}
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    الحجز:
                                    {{ $rating->booking?->booking_code }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $rating->trip?->departure_at?->format('Y-m-d H:i') }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    التقييم
                                </label>

                                <select
                                    name="score"
                                    class="w-full rounded-xl border-slate-300"
                                    required
                                >
                                    @for ($score = 5; $score >= 1; $score--)
                                        <option
                                            value="{{ $score }}"
                                            @selected($rating->score === $score)
                                        >
                                            {{ str_repeat('★', $score) }}
                                            {{ str_repeat('☆', 5 - $score) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    تعليقك
                                </label>

                                <textarea
                                    name="comment"
                                    rows="3"
                                    maxlength="1000"
                                    class="w-full rounded-xl border-slate-300"
                                >{{ $rating->comment }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button
                                type="submit"
                                class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-2.5 text-sm font-bold text-blue-900"
                            >
                                تحديث التقييم
                            </button>
                        </div>
                    </form>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $ratings->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
