@extends('layouts.app')

@section('title', 'الإشعارات - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-semibold text-amber-600">
            لوحة الإدارة
        </p>

        <h1 class="mt-1 text-3xl font-bold text-slate-900">
            الإشعارات
        </h1>

        <p class="mt-2 text-sm text-slate-600">
            إرسال إشعار فردي أو جماعي داخل منصة SHOFEER.
        </p>
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

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-1">
            <h2 class="text-xl font-bold text-slate-900">
                إرسال إشعار
            </h2>

            <form
                method="POST"
                action="{{ route('admin.notifications.store') }}"
                class="mt-6 space-y-4"
            >
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        نوع الإرسال
                    </label>

                    <select
                        name="mode"
                        id="notification-mode"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >
                        <option value="single" @selected(old('mode', 'single') === 'single')>
                            فردي
                        </option>

                        <option value="broadcast" @selected(old('mode') === 'broadcast')>
                            جماعي لكل المستخدمين النشطين
                        </option>
                    </select>
                </div>

                <div id="single-user-wrap">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        المستخدم
                    </label>

                    <select
                        name="user_id"
                        class="w-full rounded-xl border-slate-300"
                    >
                        <option value="">
                            اختر مستخدمًا
                        </option>

                        @foreach ($users as $user)
                            <option
                                value="{{ $user->id }}"
                                @selected(old('user_id') === $user->id)
                            >
                                {{ $user->name }}
                                —
                                {{ $user->role->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        العنوان
                    </label>

                    <input
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        maxlength="120"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        الرسالة
                    </label>

                    <textarea
                        name="message"
                        rows="6"
                        maxlength="2000"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >{{ old('message') }}</textarea>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-blue-900 px-5 py-3 text-sm font-bold text-white"
                >
                    إرسال الإشعار
                </button>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="border-b border-slate-100 p-5">
                <h2 class="text-xl font-bold text-slate-900">
                    آخر الإشعارات المرسلة
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-right text-xs font-semibold text-slate-500">
                            <th class="px-4 py-3">المستلم</th>
                            <th class="px-4 py-3">العنوان</th>
                            <th class="px-4 py-3">الحالة</th>
                            <th class="px-4 py-3">وقت الإرسال</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recent as $notification)
                            @php
                                $data = $notification->data ?? [];
                            @endphp

                            <tr>
                                <td class="px-4 py-4 font-semibold text-slate-900">
                                    {{ $notification->notifiable?->name ?? '—' }}
                                </td>

                                <td class="px-4 py-4 text-slate-700">
                                    {{ $data['title'] ?? '—' }}
                                </td>

                                <td class="px-4 py-4">
                                    @if ($notification->read_at)
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                            مقروء
                                        </span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                            غير مقروء
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-slate-500">
                                    {{ $notification->created_at?->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="4"
                                    class="px-4 py-12 text-center text-slate-500"
                                >
                                    لم يتم إرسال إشعارات بعد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-5">
                {{ $recent->links() }}
            </div>
        </section>
    </div>
</div>

<script>
(() => {
    const mode = document.getElementById('notification-mode');
    const wrap = document.getElementById('single-user-wrap');

    function syncMode() {
        if (!mode || !wrap) {
            return;
        }

        wrap.style.display =
            mode.value === 'single'
                ? ''
                : 'none';
    }

    mode?.addEventListener(
        'change',
        syncMode
    );

    syncMode();
})();
</script>
@endsection
