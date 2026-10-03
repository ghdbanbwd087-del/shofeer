@extends('layouts.app')

@section('title', 'التتبع المباشر - SHOFEER')

@section('content')
<div
    dir="rtl"
    class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
    data-live-page
>
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">رحلتك الآن</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">التتبع المباشر</h1>
            <p class="mt-2 text-sm text-slate-600">
                موقع السائق وآخر تحديث متاح لرحلتك الجارية.
            </p>
        </div>

        <a
            href="{{ route('dashboard') }}"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700"
        >
            العودة للوحة التحكم
        </a>
    </div>

    @if ($bookings->isEmpty())
        <section class="rounded-2xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
            <div class="text-4xl">📍</div>

            <h2 class="mt-4 text-xl font-bold text-slate-900">
                لا توجد رحلة جارية الآن
            </h2>

            <p class="mx-auto mt-2 max-w-xl text-sm leading-7 text-slate-500">
                يظهر التتبع عندما يكون لديك حجز مؤكد وحالة الرحلة
                استقبال الركاب أو في الطريق.
            </p>
        </section>
    @else
        @if ($bookings->count() > 1)
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <label class="mb-2 block text-sm font-semibold text-slate-700">
                    اختر الرحلة
                </label>

                <select
                    class="w-full rounded-xl border-slate-300 md:max-w-md"
                    onchange="window.location.href=this.value"
                >
                    @foreach ($bookings as $booking)
                        <option
                            value="{{ route('dashboard.live.index', ['booking' => $booking->id]) }}"
                            @selected($selectedBooking?->id === $booking->id)
                        >
                            {{ $booking->booking_code }}
                            —
                            {{ $booking->trip?->departure_at?->format('Y-m-d H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                <div
                    id="live-map-wrap"
                    class="relative min-h-[430px] bg-slate-100"
                >
                    @if ($latestLocation)
                        @php
                            $lat = (float) $latestLocation->latitude;
                            $lng = (float) $latestLocation->longitude;
                            $delta = 0.02;

                            $mapUrl = 'https://www.openstreetmap.org/export/embed.html?'.
                                http_build_query([
                                    'bbox' => ($lng - $delta).','.
                                        ($lat - $delta).','.
                                        ($lng + $delta).','.
                                        ($lat + $delta),
                                    'layer' => 'mapnik',
                                    'marker' => $lat.','.$lng,
                                ]);
                        @endphp

                        <iframe
                            id="live-map"
                            src="{{ $mapUrl }}"
                            class="h-[430px] w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer"
                            title="موقع السائق"
                        ></iframe>
                    @else
                        <div
                            id="live-map-empty"
                            class="flex min-h-[430px] items-center justify-center p-8 text-center"
                        >
                            <div>
                                <div class="text-5xl">🛰️</div>

                                <h2 class="mt-4 text-xl font-bold text-slate-900">
                                    بانتظار موقع السائق
                                </h2>

                                <p class="mt-2 text-sm leading-7 text-slate-500">
                                    لم يصل تحديث موقع لهذه الرحلة حتى الآن.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <aside class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-semibold text-slate-400">الحجز</p>
                    <p class="mt-1 text-lg font-bold text-blue-900">
                        {{ $selectedBooking?->booking_code }}
                    </p>

                    <p class="mt-4 text-xs font-semibold text-slate-400">السائق</p>
                    <p class="mt-1 font-semibold text-slate-900">
                        {{ $selectedBooking?->trip?->driver?->user?->name ?? '—' }}
                    </p>

                    <p class="mt-4 text-xs font-semibold text-slate-400">حالة الرحلة</p>
                    <p class="mt-1 font-semibold text-slate-900">
                        {{ $selectedBooking?->trip?->status?->label() ?? '—' }}
                    </p>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-bold text-slate-900">حالة الموقع</h2>

                        <span
                            id="live-fresh-badge"
                            class="rounded-full px-3 py-1 text-xs font-bold {{ $locationIsFresh ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}"
                        >
                            {{ $latestLocation ? ($locationIsFresh ? 'مباشر' : 'قديم') : 'بانتظار الموقع' }}
                        </span>
                    </div>

                    <dl class="mt-5 space-y-4 text-sm">
                        <div>
                            <dt class="text-slate-400">آخر تحديث</dt>
                            <dd id="live-recorded-at" class="mt-1 font-semibold text-slate-800">
                                {{ $latestLocation?->recorded_at?->format('Y-m-d H:i:s') ?? '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-400">السرعة</dt>
                            <dd id="live-speed" class="mt-1 font-semibold text-slate-800">
                                {{ $latestLocation?->speed_kmh !== null ? number_format((float) $latestLocation->speed_kmh, 1).' كم/س' : '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-400">دقة GPS</dt>
                            <dd id="live-accuracy" class="mt-1 font-semibold text-slate-800">
                                {{ $latestLocation?->accuracy_m !== null ? number_format((float) $latestLocation->accuracy_m, 0).' متر' : '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-400">الوقت المتوقع</dt>
                            <dd id="live-eta" class="mt-1 font-semibold text-slate-800">
                                {{ $latestLocation?->eta_at?->format('Y-m-d H:i') ?? 'غير متاح بعد' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                <p class="text-xs leading-6 text-slate-500">
                    يتم تحديث بيانات الموقع كل 15 ثانية. لا يتم عرض أي
                    موقع لسائق إلا إذا كان مرتبطًا بحجزك المؤكد الجاري.
                </p>
            </aside>
        </div>
    @endif
</div>

@if ($selectedBooking)
<script>
(() => {
    const bookingId = @json($selectedBooking->id);
    const dataUrl = @json(route('dashboard.live.data'));

    const map = document.getElementById('live-map');
    const badge = document.getElementById('live-fresh-badge');
    const recordedAt = document.getElementById('live-recorded-at');
    const speed = document.getElementById('live-speed');
    const accuracy = document.getElementById('live-accuracy');
    const eta = document.getElementById('live-eta');

    function mapUrl(latitude, longitude) {
        const delta = 0.02;
        const params = new URLSearchParams({
            bbox: [
                longitude - delta,
                latitude - delta,
                longitude + delta,
                latitude + delta,
            ].join(','),
            layer: 'mapnik',
            marker: [latitude, longitude].join(','),
        });

        return 'https://www.openstreetmap.org/export/embed.html?' + params.toString();
    }

    async function refreshLocation() {
        try {
            const url = new URL(dataUrl, window.location.origin);
            url.searchParams.set('booking', bookingId);

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (!data.available) {
                return;
            }

            if (map) {
                const nextMapUrl = mapUrl(
                    Number(data.latitude),
                    Number(data.longitude)
                );

                if (map.src !== nextMapUrl) {
                    map.src = nextMapUrl;
                }
            }

            if (recordedAt) {
                recordedAt.textContent =
                    data.recorded_at
                        ? new Date(data.recorded_at).toLocaleString('ar')
                        : '—';
            }

            if (speed) {
                speed.textContent =
                    data.speed_kmh !== null
                        ? Number(data.speed_kmh).toFixed(1) + ' كم/س'
                        : '—';
            }

            if (accuracy) {
                accuracy.textContent =
                    data.accuracy_m !== null
                        ? Math.round(Number(data.accuracy_m)) + ' متر'
                        : '—';
            }

            if (eta) {
                eta.textContent =
                    data.eta_at
                        ? new Date(data.eta_at).toLocaleString('ar')
                        : 'غير متاح بعد';
            }

            if (badge) {
                badge.textContent =
                    data.fresh
                        ? 'مباشر'
                        : 'قديم';

                badge.className =
                    'rounded-full px-3 py-1 text-xs font-bold ' +
                    (
                        data.fresh
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-amber-50 text-amber-700'
                    );
            }
        } catch (error) {
            // Keep last known state.
        }
    }

    window.setInterval(refreshLocation, 15000);
})();
</script>
@endif
@endsection
