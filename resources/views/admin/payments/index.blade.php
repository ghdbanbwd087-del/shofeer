@extends('layouts.app')

@section('title', 'الدفعات')

@section('content')
<section class="py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div>
            <p class="font-black text-[#F59E0B]">
                الإدارة
            </p>

            <h1 class="mt-2 text-3xl font-black">
                الدفعات
            </h1>

            <p class="mt-3 text-slate-500">
                راجع إثباتات الدفع ثم أكد أو ارفض العملية.
            </p>
        </div>

        {{-- Tabs --}}
        <div class="mt-7 flex flex-wrap gap-3">
            @foreach ([
                'pending' => 'قيد التحقق',
                'approved' => 'مؤكدة',
                'rejected' => 'مرفوضة',
            ] as $value => $label)

                <a
                    href="{{ route(
                        'admin.payments.index',
                        ['status' => $value]
                    ) }}"
                    class="rounded-2xl px-5 py-3 text-sm font-black transition {{
                        $status === $value
                            ? 'bg-[#1E3A8A] text-white shadow-lg shadow-blue-950/10'
                            : 'border border-slate-200 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'
                    }}"
                >
                    {{ $label }}

                    <span
                        class="ms-2 rounded-full bg-black/10 px-2 py-0.5 text-xs"
                    >
                        {{ $counts[$value] ?? 0 }}
                    </span>
                </a>
            @endforeach
        </div>

        @if ($errors->any())
            <div
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-700 dark:border-red-900 dark:bg-red-950/20 dark:text-red-300"
            >
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div
                class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/20 dark:text-emerald-300"
            >
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-8 space-y-5">
            @forelse ($payments as $payment)

                <article
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"
                    >
                        <div>
                            <div
                                class="flex flex-wrap items-center gap-3"
                            >
                                <p class="text-lg font-black">
                                    {{ $payment->booking->passenger_name }}
                                </p>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-black {{
                                        $payment->status->value === 'approved'
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : (
                                                $payment->status->value === 'rejected'
                                                    ? 'bg-red-100 text-red-700'
                                                    : 'bg-amber-100 text-amber-700'
                                            )
                                    }}"
                                >
                                    {{ $payment->status->label() }}
                                </span>
                            </div>

                            <p
                                class="mt-2 text-sm text-slate-500"
                                dir="ltr"
                            >
                                {{ $payment->booking->booking_code }}
                            </p>
                        </div>

                        <div class="text-start lg:text-end">
                            <p class="text-xs text-slate-400">
                                المبلغ
                            </p>

                            <p
                                class="mt-1 text-2xl font-black text-[#1E3A8A] dark:text-blue-300"
                            >
                                {{ number_format((float) $payment->amount, 2) }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 grid gap-4 rounded-2xl bg-slate-50 p-5 text-sm sm:grid-cols-2 lg:grid-cols-4 dark:bg-slate-800/60"
                    >
                        <div>
                            <p class="text-xs text-slate-400">
                                الرحلة
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $payment->booking->trip->fromCity->name_ar }}
                                ←
                                {{ $payment->booking->trip->toCity->name_ar }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                المقعد
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $payment->booking->seat_number }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                الطريقة
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $payment->payment_method->label() }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                رقم العملية
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $payment->transaction_number }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                أُرسل في
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $payment->submitted_at->format('Y-m-d H:i') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                انتهاء المراجعة
                            </p>

                            <p class="mt-1 font-bold">
                                {{ $payment->expires_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 flex flex-wrap gap-3"
                    >
                        <a
                            href="{{ route(
                                'admin.payments.proof',
                                $payment
                            ) }}"
                            target="_blank"
                            rel="noopener"
                            class="rounded-xl border border-[#1E3A8A] px-5 py-3 text-sm font-black text-[#1E3A8A] dark:text-blue-300"
                        >
                            عرض إثبات الدفع
                        </a>
                    </div>

                    @if (
                        $payment->status->value
                        === 'pending'
                    )
                        <div
                            class="mt-6 grid gap-4 lg:grid-cols-2"
                        >
                            <form
                                method="POST"
                                action="{{ route(
                                    'admin.payments.approve',
                                    $payment
                                ) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-emerald-600 px-5 py-3.5 font-black text-white transition hover:bg-emerald-700"
                                >
                                    ✓ تأكيد الدفع
                                </button>
                            </form>

                            <details
                                class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/20"
                            >
                                <summary
                                    class="cursor-pointer font-black text-red-700 dark:text-red-300"
                                >
                                    رفض الدفع
                                </summary>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.payments.reject',
                                        $payment
                                    ) }}"
                                    class="mt-4"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <textarea
                                        name="rejection_reason"
                                        rows="3"
                                        required
                                        minlength="5"
                                        maxlength="2000"
                                        placeholder="سبب رفض الدفع..."
                                        class="w-full rounded-xl border border-red-200 bg-white px-4 py-3 dark:border-red-900 dark:bg-slate-900"
                                    ></textarea>

                                    <button
                                        type="submit"
                                        class="mt-3 w-full rounded-xl bg-red-600 px-5 py-3 font-black text-white"
                                    >
                                        تأكيد الرفض
                                    </button>
                                </form>
                            </details>
                        </div>
                    @endif

                    @if ($payment->rejection_reason)
                        <div
                            class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/20 dark:text-red-300"
                        >
                            <strong>
                                سبب الرفض:
                            </strong>

                            {{ $payment->rejection_reason }}
                        </div>
                    @endif

                    @if ($payment->reviewed_at)
                        <p
                            class="mt-5 text-xs text-slate-400"
                        >
                            تمت المراجعة
                            {{ $payment->reviewed_at->format('Y-m-d H:i') }}

                            @if ($payment->reviewer)
                                بواسطة
                                {{ $payment->reviewer->name }}
                            @endif
                        </p>
                    @endif
                </article>

            @empty
                <div
                    class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900"
                >
                    <div class="text-4xl">
                        💳
                    </div>

                    <h2 class="mt-4 text-xl font-black">
                        لا توجد دفعات
                    </h2>

                    <p class="mt-2 text-slate-500">
                        لا توجد دفعات في هذا التبويب حالياً.
                    </p>
                </div>
            @endforelse
        </div>

        @if ($payments->hasPages())
            <div class="mt-8">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</section>
@endsection