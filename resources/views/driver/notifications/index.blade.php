@extends('layouts.app')

@section('title', 'إشعاراتي - SHOFEER')

@section('content')
<div dir="rtl" class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-600">
                لوحة السائق
            </p>

            <h1 class="mt-1 text-3xl font-bold text-slate-900">
                إشعاراتي
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                لديك
                <span class="font-bold text-blue-900">
                    {{ $unreadCount }}
                </span>
                إشعار غير مقروء.
            </p>
        </div>

        @if ($unreadCount > 0)
            <form
                method="POST"
                action="{{ route('driver.notifications.read-all') }}"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-bold text-blue-900"
                >
                    تعليم الكل كمقروء
                </button>
            </form>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <section class="space-y-4">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $unread = $notification->read_at === null;
            @endphp

            <article class="rounded-2xl border p-5 shadow-sm {{ $unread ? 'border-blue-200 bg-blue-50/60' : 'border-slate-200 bg-white' }}">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-bold text-slate-900">
                                {{ $data['title'] ?? 'إشعار' }}
                            </h2>

                            @if ($unread)
                                <span class="rounded-full bg-blue-900 px-2.5 py-1 text-[11px] font-bold text-white">
                                    جديد
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-sm leading-7 text-slate-700">
                            {{ $data['message'] ?? '' }}
                        </p>

                        <p class="mt-3 text-xs text-slate-400">
                            {{ $notification->created_at?->format('Y-m-d H:i') }}
                        </p>
                    </div>

                    @if ($unread)
                        <form
                            method="POST"
                            action="{{ route('driver.notifications.read', $notification) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700"
                            >
                                تعليم كمقروء
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white px-6 py-14 text-center text-sm text-slate-500 shadow-sm">
                لا توجد إشعارات حتى الآن.
            </div>
        @endforelse
    </section>

    @if ($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
