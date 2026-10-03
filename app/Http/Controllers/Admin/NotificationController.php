<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendNotificationRequest;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $users =
            User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->limit(500)
                ->get([
                    'id',
                    'name',
                    'email',
                    'role',
                ]);

        $recent =
            AppNotification::query()
                ->with('notifiable')
                ->latest()
                ->paginate(25);

        return view(
            'admin.notifications.index',
            compact(
                'users',
                'recent'
            )
        );
    }

    public function store(
        SendNotificationRequest $request,
        NotificationService $notificationService
    ): RedirectResponse {
        $data =
            $request->validated();

        if ($data['mode'] === 'single') {
            $user =
                User::query()
                    ->where('is_active', true)
                    ->findOrFail(
                        $data['user_id']
                    );

            $notificationService->sendToUser(
                user: $user,
                title: $data['title'],
                message: $data['message'],
                sender: $request->user(),
            );

            return back()->with(
                'success',
                'تم إرسال الإشعار للمستخدم.'
            );
        }

        $count =
            $notificationService->broadcast(
                sender: $request->user(),
                title: $data['title'],
                message: $data['message'],
            );

        return back()->with(
            'success',
            'تم إرسال الإشعار الجماعي إلى '.
            $count.
            ' مستخدم.'
        );
    }
}
