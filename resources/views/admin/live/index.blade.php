@extends('layouts.app')

@section('title', 'الخريطة المباشرة - SHOFEER')

@section('content')
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<div dir="rtl" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-semibold text-amber-600">
            لوحة الإدارة
        </p>

        <h1 class="mt-1 text-3xl font-bold text-slate-900">
            الخريطة المباشرة
        </h1>

        <p class="mt-2 text-sm text-slate-600">
            متابعة الرحلات الجارية وحالة تحديث GPS لكل سائق.
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                الرحلات الجارية
            </p>

            <p
                id="stat-total"
                class="mt-3 text-3xl font-black text-slate-900"
            >
                {{ $stats['total'] }}
            </p>
        </section>

        <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-sm font-semibold text-emerald-700">
                مواقع مباشرة
            </p>

            <p
                id="stat-live"
                class="mt-3 text-3xl font-black text-emerald-800"
            >
                {{ $stats['live'] }}
            </p>
        </section>

        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm font-semibold text-amber-700">
                مواقع قديمة
            </p>

            <p
                id="stat-stale"
                class="mt-3 text-3xl font-black text-amber-800"
            >
                {{ $stats['stale'] }}
            </p>
        </section>

        <section class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
            <p class="text-sm font-semibold text-rose-700">
                بدون موقع
            </p>

            <p
                id="stat-missing"
                class="mt-3 text-3xl font-black text-rose-800"
            >
                {{ $stats['missing'] }}
            </p>
        </section>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div
                id="admin-live-map"
                class="h-[560px] w-full bg-slate-100"
            ></div>
        </section>

        <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        التنبيهات
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        محدثة تلقائيًا كل 15 ثانية.
                    </p>
                </div>

                <span
                    id="alerts-count"
                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700"
                >
                    {{ $stats['stale'] + $stats['missing'] }}
                </span>
            </div>

            <div
                id="alerts-list"
                class="mt-5 space-y-3"
            >
                @forelse (
                    $rows->whereIn(
                        'tracking_state',
                        [
                            'stale',
                            'missing',
                        ]
                    )
                    as $row
                )
                    <article
                        class="rounded-xl border p-4 {{ $row['tracking_state'] === 'missing' ? 'border-rose-200 bg-rose-50' : 'border-amber-200 bg-amber-50' }}"
                    >
                        <p class="font-bold text-slate-900">
                            {{ $row['driver_name'] }}
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ $row['from_city'] }}
                            ←
                            {{ $row['to_city'] }}
                        </p>

                        <p class="mt-2 text-xs font-semibold {{ $row['tracking_state'] === 'missing' ? 'text-rose-700' : 'text-amber-700' }}">
                            {{ $row['alert_message'] }}
                        </p>

                        @if ($row['recorded_at'])
                            <p class="mt-1 text-xs text-slate-500">
                                آخر تحديث:
                                {{ \Illuminate\Support\Carbon::parse($row['recorded_at'])->format('Y-m-d H:i:s') }}
                            </p>
                        @endif
                    </article>
                @empty
                    <div class="rounded-xl bg-emerald-50 px-4 py-6 text-center text-sm font-semibold text-emerald-700">
                        لا توجد تنبيهات حالية.
                    </div>
                @endforelse
            </div>
        </aside>
    </div>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
            <h2 class="text-xl font-bold text-slate-900">
                الرحلات الجارية
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-right text-xs font-semibold text-slate-500">
                        <th class="px-4 py-3">السائق</th>
                        <th class="px-4 py-3">المسار</th>
                        <th class="px-4 py-3">حالة الرحلة</th>
                        <th class="px-4 py-3">GPS</th>
                        <th class="px-4 py-3">السرعة</th>
                        <th class="px-4 py-3">آخر تحديث</th>
                    </tr>
                </thead>

                <tbody
                    id="live-trips-body"
                    class="divide-y divide-slate-100"
                >
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-4 py-4 font-semibold text-slate-900">
                                {{ $row['driver_name'] }}
                            </td>

                            <td class="px-4 py-4 text-slate-700">
                                {{ $row['from_city'] }}
                                ←
                                {{ $row['to_city'] }}
                            </td>

                            <td class="px-4 py-4 text-slate-700">
                                {{ $row['trip_status_label'] }}
                            </td>

                            <td class="px-4 py-4">
                                @if ($row['tracking_state'] === 'live')
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                        مباشر
                                    </span>
                                @elseif ($row['tracking_state'] === 'stale')
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                        قديم
                                    </span>
                                @else
                                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                                        غير متاح
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-slate-700">
                                {{ $row['speed_kmh'] !== null ? number_format($row['speed_kmh'], 1).' كم/س' : '—' }}
                            </td>

                            <td class="px-4 py-4 text-slate-500">
                                {{ $row['recorded_at'] ? \Illuminate\Support\Carbon::parse($row['recorded_at'])->format('Y-m-d H:i:s') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="px-4 py-12 text-center text-slate-500"
                            >
                                لا توجد رحلات جارية الآن.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(() => {
    const endpoint = @json(route('admin.live.data'));
    const initialRows = @json($rows->values());

    const map = L.map('admin-live-map').setView(
        [15.5, 44.0],
        5
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }
    ).addTo(map);

    const markers = new Map();

    function stateLabel(state) {
        if (state === 'live') {
            return 'مباشر';
        }

        if (state === 'stale') {
            return 'قديم';
        }

        return 'غير متاح';
    }

    function markerClass(state) {
        if (state === 'live') {
            return '🟢';
        }

        if (state === 'stale') {
            return '🟠';
        }

        return '🔴';
    }

    function syncMarkers(rows) {
        const seen = new Set();

        rows.forEach((row) => {
            if (
                row.latitude === null
                || row.longitude === null
            ) {
                return;
            }

            seen.add(row.trip_id);

            const popup = `
                <div dir="rtl">
                    <strong>${escapeHtml(row.driver_name)}</strong><br>
                    ${escapeHtml(row.from_city)} ← ${escapeHtml(row.to_city)}<br>
                    ${escapeHtml(row.trip_status_label)}<br>
                    ${markerClass(row.tracking_state)} ${stateLabel(row.tracking_state)}
                </div>
            `;

            if (markers.has(row.trip_id)) {
                markers
                    .get(row.trip_id)
                    .setLatLng([
                        row.latitude,
                        row.longitude,
                    ])
                    .setPopupContent(popup);

                return;
            }

            const marker =
                L.marker([
                    row.latitude,
                    row.longitude,
                ])
                    .addTo(map)
                    .bindPopup(popup);

            markers.set(
                row.trip_id,
                marker
            );
        });

        [...markers.keys()].forEach((tripId) => {
            if (!seen.has(tripId)) {
                map.removeLayer(
                    markers.get(tripId)
                );

                markers.delete(
                    tripId
                );
            }
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function alertHtml(row) {
        const missing =
            row.tracking_state === 'missing';

        const shell =
            missing
                ? 'border-rose-200 bg-rose-50'
                : 'border-amber-200 bg-amber-50';

        const text =
            missing
                ? 'text-rose-700'
                : 'text-amber-700';

        return `
            <article class="rounded-xl border p-4 ${shell}">
                <p class="font-bold text-slate-900">
                    ${escapeHtml(row.driver_name)}
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    ${escapeHtml(row.from_city)}
                    ←
                    ${escapeHtml(row.to_city)}
                </p>

                <p class="mt-2 text-xs font-semibold ${text}">
                    ${escapeHtml(row.alert_message)}
                </p>

                ${
                    row.recorded_at
                        ? `<p class="mt-1 text-xs text-slate-500">
                            آخر تحديث:
                            ${new Date(row.recorded_at).toLocaleString('ar')}
                        </p>`
                        : ''
                }
            </article>
        `;
    }

    function rowHtml(row) {
        const gps =
            row.tracking_state === 'live'
                ? '<span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">مباشر</span>'
                : row.tracking_state === 'stale'
                    ? '<span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">قديم</span>'
                    : '<span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">غير متاح</span>';

        return `
            <tr>
                <td class="px-4 py-4 font-semibold text-slate-900">
                    ${escapeHtml(row.driver_name)}
                </td>

                <td class="px-4 py-4 text-slate-700">
                    ${escapeHtml(row.from_city)}
                    ←
                    ${escapeHtml(row.to_city)}
                </td>

                <td class="px-4 py-4 text-slate-700">
                    ${escapeHtml(row.trip_status_label)}
                </td>

                <td class="px-4 py-4">
                    ${gps}
                </td>

                <td class="px-4 py-4 text-slate-700">
                    ${
                        row.speed_kmh !== null
                            ? Number(row.speed_kmh).toFixed(1) + ' كم/س'
                            : '—'
                    }
                </td>

                <td class="px-4 py-4 text-slate-500">
                    ${
                        row.recorded_at
                            ? new Date(row.recorded_at).toLocaleString('ar')
                            : '—'
                    }
                </td>
            </tr>
        `;
    }

    function render(data) {
        document.getElementById('stat-total').textContent =
            data.stats.total;

        document.getElementById('stat-live').textContent =
            data.stats.live;

        document.getElementById('stat-stale').textContent =
            data.stats.stale;

        document.getElementById('stat-missing').textContent =
            data.stats.missing;

        const alerts =
            data.trips.filter(
                (row) =>
                    row.tracking_state === 'stale'
                    || row.tracking_state === 'missing'
            );

        document.getElementById('alerts-count').textContent =
            alerts.length;

        document.getElementById('alerts-list').innerHTML =
            alerts.length
                ? alerts.map(alertHtml).join('')
                : `
                    <div class="rounded-xl bg-emerald-50 px-4 py-6 text-center text-sm font-semibold text-emerald-700">
                        لا توجد تنبيهات حالية.
                    </div>
                `;

        document.getElementById('live-trips-body').innerHTML =
            data.trips.length
                ? data.trips.map(rowHtml).join('')
                : `
                    <tr>
                        <td
                            colspan="6"
                            class="px-4 py-12 text-center text-slate-500"
                        >
                            لا توجد رحلات جارية الآن.
                        </td>
                    </tr>
                `;

        syncMarkers(
            data.trips
        );
    }

    async function refresh() {
        try {
            const response =
                await fetch(
                    endpoint,
                    {
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    }
                );

            if (!response.ok) {
                return;
            }

            render(
                await response.json()
            );
        } catch (error) {
            // Keep the last rendered state.
        }
    }

    syncMarkers(
        initialRows
    );

    window.setInterval(
        refresh,
        15000
    );
})();
</script>
@endsection
