<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلبات الاسترداد - SHOFEER</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-amber-600">SHOFEER Admin</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-900">طلبات الاسترداد</h1>
                <p class="mt-2 text-sm text-slate-500">مراجعة طلبات استرداد الحجوزات المدفوعة وتنفيذها أو رفضها.</p>
            </div>

            <a
                href="{{ route('admin.payments.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-900"
            >
                الدفعات
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            @php
                $tabs = [
                    'pending' => 'قيد المراجعة',
                    'processed' => 'تم الاسترداد',
                    'rejected' => 'مرفوض',
                ];
            @endphp

            @foreach ($tabs as $key => $label)
                <a
                    href="{{ route('admin.refunds.index', ['status' => $key]) }}"
                    class="rounded-2xl border px-5 py-4 shadow-sm transition {{ $status === $key ? 'border-blue-900 bg-blue-900 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-blue-200' }}"
                >
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-semibold">{{ $label }}</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $status === $key ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {{ (int) ($counts[$key] ?? 0) }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-right text-xs font-bold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-4">الحجز</th>
                            <th class="px-5 py-4">الراكب</th>
                            <th class="px-5 py-4">الرحلة</th>
                            <th class="px-5 py-4">المبلغ</th>
                            <th class="px-5 py-4">السبب</th>
                            <th class="px-5 py-4">الحالة</th>
                            <th class="px-5 py-4">الإجراء</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($refunds as $refund)
                            @php
                                $booking = $refund->booking;
                                $trip = $booking?->trip;
                                $fromCity = $trip?->fromCity;
                                $toCity = $trip?->toCity;
                                $fromName = $fromCity?->name_ar ?? $fromCity?->name ?? '—';
                                $toName = $toCity?->name_ar ?? $toCity?->name ?? '—';
                                $statusValue = $refund->status instanceof \BackedEnum
                                    ? $refund->status->value
                                    : (string) $refund->status;
                            @endphp

                            <tr class="align-top">
                                <td class="px-5 py-5">
                                    <div class="font-bold text-blue-900">{{ $booking?->booking_code ?? '—' }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ $refund->id }}</div>
                                </td>

                                <td class="px-5 py-5">
                                    <div class="font-semibold text-slate-800">{{ $booking?->user?->name ?? $refund->requester?->name ?? '—' }}</div>
                                </td>

                                <td class="px-5 py-5">
                                    <div class="font-medium text-slate-700">{{ $fromName }} ← {{ $toName }}</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $trip?->departure_at ? $trip->departure_at->format('Y-m-d H:i') : '—' }}
                                    </div>
                                </td>

                                <td class="px-5 py-5">
                                    <div class="font-bold text-slate-900">{{ number_format((float) $refund->amount, 2) }}</div>
                                </td>

                                <td class="max-w-xs px-5 py-5 text-slate-600">
                                    {{ $refund->reason ?: '—' }}

                                    @if ($refund->rejection_reason)
                                        <div class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">
                                            {{ $refund->rejection_reason }}
                                        </div>
                                    @endif

                                    @if ($refund->admin_reference)
                                        <div class="mt-2 text-xs text-emerald-700">
                                            المرجع: {{ $refund->admin_reference }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-5">
                                    @if ($statusValue === 'pending')
                                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">قيد المراجعة</span>
                                    @elseif ($statusValue === 'processed')
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">تم الاسترداد</span>
                                    @else
                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">مرفوض</span>
                                    @endif
                                </td>

                                <td class="min-w-72 px-5 py-5">
                                    @if ($statusValue === 'pending')
                                        <div class="space-y-3">
                                            <form method="POST" action="{{ route('admin.refunds.process', $refund) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')

                                                <input
                                                    type="text"
                                                    name="admin_reference"
                                                    required
                                                    maxlength="191"
                                                    placeholder="مرجع التحويل العكسي"
                                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                >

                                                <button
                                                    type="submit"
                                                    class="w-full rounded-xl bg-blue-900 px-4 py-2.5 font-bold text-white transition hover:bg-blue-800"
                                                >
                                                    تنفيذ الاسترداد
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.refunds.reject', $refund) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')

                                                <textarea
                                                    name="rejection_reason"
                                                    required
                                                    minlength="5"
                                                    maxlength="1000"
                                                    rows="2"
                                                    placeholder="سبب الرفض"
                                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100"
                                                ></textarea>

                                                <button
                                                    type="submit"
                                                    class="w-full rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 font-bold text-red-700 transition hover:bg-red-100"
                                                >
                                                    رفض الطلب
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">تمت مراجعة الطلب</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center text-slate-500">
                                    لا توجد طلبات في هذا التبويب.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $refunds->links() }}
        </div>
    </main>
</body>
</html>
