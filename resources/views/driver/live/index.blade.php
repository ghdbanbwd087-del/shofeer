@extends('layouts.app')

@section('title', 'التتبع المباشر للسائق - SHOFEER')

@section('content')
<div
    dir="rtl"
    class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8"
>
    <div class="mb-8">
        <p class="text-sm font-semibold text-amber-600">
            لوحة السائق
        </p>

        <h1 class="mt-1 text-3xl font-bold text-slate-900">
            التتبع المباشر
        </h1>

        <p class="mt-2 text-sm text-slate-600">
            فعّل مشاركة موقعك أثناء الرحلة الجارية ليظهر للركاب
            المرتبطين بالحجز فقط.
        </p>
    </div>

    @if ($trips->isEmpty())
        <section class="rounded-2xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
            <div class="text-5xl">🚗</div>

            <h2 class="mt-4 text-xl font-bold text-slate-900">
                لا توجد رحلة جارية
            </h2>

            <p class="mx-auto mt-2 max-w-lg text-sm leading-7 text-slate-500">
                تظهر مشاركة الموقع عندما تصبح إحدى رحلاتك في حالة
                استقبال الركاب أو في الطريق.
            </p>
        </section>
    @else
        <div class="grid gap-6 lg:grid-cols-3">
            <section class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    @if ($trips->count() > 1)
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            الرحلة الحالية
                        </label>

                        <select
                            class="w-full rounded-xl border-slate-300"
                            onchange="window.location.href=this.value"
                        >
                            @foreach ($trips as $trip)
                                <option
                                    value="{{ route('driver.live.index', ['trip' => $trip->id]) }}"
                                    @selected($selectedTrip?->id === $trip->id)
                                >
                                    {{ $trip->departure_at?->format('Y-m-d H:i') }}
                                    —
                                    {{ $trip->status->label() }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <p class="text-xs font-semibold text-slate-400">
                            الرحلة الجارية
                        </p>

                        <p class="mt-1 text-lg font-bold text-blue-900">
                            {{ $selectedTrip?->departure_at?->format('Y-m-d H:i') }}
                            —
                            {{ $selectedTrip?->status?->label() }}
                        </p>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">
                                مشاركة GPS
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                سيُرسل الموقع كل 15 ثانية تقريبًا أثناء التفعيل.
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                id="start-tracking"
                                class="rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-bold text-white"
                            >
                                بدء المشاركة
                            </button>

                            <button
                                type="button"
                                id="stop-tracking"
                                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700"
                                disabled
                            >
                                إيقاف
                            </button>
                        </div>
                    </div>

                    <div
                        id="tracking-message"
                        class="mt-5 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600"
                    >
                        الموقع غير مفعّل.
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-400">
                                خط العرض
                            </p>

                            <p
                                id="driver-latitude"
                                class="mt-1 font-semibold text-slate-900"
                            >
                                {{ $latestLocation?->latitude ?? '—' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-400">
                                خط الطول
                            </p>

                            <p
                                id="driver-longitude"
                                class="mt-1 font-semibold text-slate-900"
                            >
                                {{ $latestLocation?->longitude ?? '—' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-400">
                                الدقة
                            </p>

                            <p
                                id="driver-accuracy"
                                class="mt-1 font-semibold text-slate-900"
                            >
                                {{ $latestLocation?->accuracy_m !== null ? number_format((float) $latestLocation->accuracy_m, 0).' متر' : '—' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-400">
                                السرعة
                            </p>

                            <p
                                id="driver-speed"
                                class="mt-1 font-semibold text-slate-900"
                            >
                                {{ $latestLocation?->speed_kmh !== null ? number_format((float) $latestLocation->speed_kmh, 1).' كم/س' : '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="font-bold text-slate-900">
                        آخر تحديث محفوظ
                    </h2>

                    <p
                        id="driver-recorded-at"
                        class="mt-3 text-sm font-semibold text-slate-700"
                    >
                        {{ $latestLocation?->recorded_at?->format('Y-m-d H:i:s') ?? 'لا يوجد' }}
                    </p>
                </section>

                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                    <h2 class="font-bold text-amber-900">
                        الخصوصية
                    </h2>

                    <p class="mt-2 text-sm leading-7 text-amber-800">
                        لا تظهر بيانات موقعك في لوحة الراكب إلا للركاب
                        أصحاب الحجوزات المؤكدة على الرحلة الجارية.
                    </p>
                </section>
            </aside>
        </div>
    @endif
</div>

@if ($selectedTrip)
<script>
(() => {
    const tripId = @json($selectedTrip->id);
    const endpoint = @json(route('driver.live.location.store'));
    const csrf = @json(csrf_token());

    const startButton = document.getElementById('start-tracking');
    const stopButton = document.getElementById('stop-tracking');
    const message = document.getElementById('tracking-message');

    const latitudeOutput = document.getElementById('driver-latitude');
    const longitudeOutput = document.getElementById('driver-longitude');
    const accuracyOutput = document.getElementById('driver-accuracy');
    const speedOutput = document.getElementById('driver-speed');
    const recordedAtOutput = document.getElementById('driver-recorded-at');

    let watchId = null;
    let lastSentAt = 0;

    function setMessage(text, type = 'normal') {
        if (!message) {
            return;
        }

        message.textContent = text;

        message.className =
            'mt-5 rounded-xl px-4 py-3 text-sm ' +
            (
                type === 'success'
                    ? 'bg-emerald-50 text-emerald-700'
                    : type === 'error'
                        ? 'bg-rose-50 text-rose-700'
                        : 'bg-slate-50 text-slate-600'
            );
    }

    async function sendPosition(position) {
        const now = Date.now();

        if (now - lastSentAt < 15000) {
            return;
        }

        lastSentAt = now;

        const speedKmh =
            position.coords.speed !== null
                ? position.coords.speed * 3.6
                : null;

        const payload = {
            trip_id: tripId,
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy_m: position.coords.accuracy,
            speed_kmh: speedKmh,
            heading: position.coords.heading,
            recorded_at: new Date(position.timestamp).toISOString(),
        };

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                const firstError =
                    data?.errors
                        ? Object.values(data.errors).flat()[0]
                        : null;

                setMessage(
                    firstError || data?.message || 'تعذر تحديث الموقع.',
                    'error'
                );

                return;
            }

            const location = data.location;

            if (latitudeOutput) {
                latitudeOutput.textContent =
                    Number(location.latitude).toFixed(6);
            }

            if (longitudeOutput) {
                longitudeOutput.textContent =
                    Number(location.longitude).toFixed(6);
            }

            if (accuracyOutput) {
                accuracyOutput.textContent =
                    location.accuracy_m !== null
                        ? Math.round(Number(location.accuracy_m)) + ' متر'
                        : '—';
            }

            if (speedOutput) {
                speedOutput.textContent =
                    location.speed_kmh !== null
                        ? Number(location.speed_kmh).toFixed(1) + ' كم/س'
                        : '—';
            }

            if (recordedAtOutput) {
                recordedAtOutput.textContent =
                    location.recorded_at
                        ? new Date(location.recorded_at).toLocaleString('ar')
                        : '—';
            }

            setMessage(
                'تم تحديث موقعك وإرساله بأمان.',
                'success'
            );
        } catch (error) {
            setMessage(
                'تعذر الاتصال بالخادم لتحديث الموقع.',
                'error'
            );
        }
    }

    function handleError(error) {
        const messages = {
            1: 'تم رفض إذن الوصول إلى الموقع.',
            2: 'الموقع غير متاح حاليًا.',
            3: 'انتهت مهلة تحديد الموقع.',
        };

        setMessage(
            messages[error.code] || 'تعذر تحديد موقعك.',
            'error'
        );
    }

    function startTracking() {
        if (!navigator.geolocation) {
            setMessage(
                'المتصفح لا يدعم تحديد الموقع الجغرافي.',
                'error'
            );

            return;
        }

        if (watchId !== null) {
            return;
        }

        setMessage(
            'جاري طلب موقعك...'
        );

        watchId =
            navigator.geolocation.watchPosition(
                sendPosition,
                handleError,
                {
                    enableHighAccuracy: true,
                    maximumAge: 10000,
                    timeout: 15000,
                }
            );

        startButton.disabled = true;
        stopButton.disabled = false;
    }

    function stopTracking() {
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }

        startButton.disabled = false;
        stopButton.disabled = true;

        setMessage(
            'تم إيقاف مشاركة الموقع.'
        );
    }

    startButton?.addEventListener(
        'click',
        startTracking
    );

    stopButton?.addEventListener(
        'click',
        stopTracking
    );

    window.addEventListener(
        'beforeunload',
        stopTracking
    );
})();
</script>
@endif
@endsection
