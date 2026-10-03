<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(
        Request $request,
        NotificationService $notificationService
    ): View {
        $user =
            $request->user();

        $notifications =
            AppNotification::query()
                ->where(
                    'notifiable_type',
                    User::class
                )
                ->where(
                    'notifiable_id',
                    $user->id
                )
                ->latest()
                ->paginate(20);

        return view(
            'driver.notifications.index',
            [
                'notifications' =>
                    $notifications,

                'unreadCount' =>
                    $notificationService
                        ->unreadCount(
                            $user
                        ),
            ]
        );
    }

    public function read(
        Request $request,
        AppNotification $notification,
        NotificationService $notificationService
    ): RedirectResponse {
        $notificationService->markRead(
            notification: $notification,
            user: $request->user(),
        );

        return back();
    }

    public function readAll(
        Request $request,
        NotificationService $notificationService
    ): RedirectResponse {
        $notificationService->markAllRead(
            $request->user()
        );

        return back()->with(
            'success',
            'تم تعليم جميع الإشعارات كمقروءة.'
        );
    }
}
