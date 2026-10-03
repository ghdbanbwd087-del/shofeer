<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(
        Request $request
    ): View {
        $query =
            User::query()
                ->orderByDesc(
                    'created_at'
                );

        $search =
            trim(
                $request
                    ->string('q')
                    ->toString()
            );

        if (
            $search !== ''
        ) {
            $query->where(
                function (
                    $builder
                ) use (
                    $search
                ): void {
                    $builder
                        ->where(
                            'name',
                            'like',
                            '%'.$search.'%'
                        )
                        ->orWhere(
                            'email',
                            'like',
                            '%'.$search.'%'
                        );
                }
            );
        }

        $role =
            $request
                ->string('role')
                ->toString();

        $allowedRoles =
            array_map(
                fn (
                    UserRole $role
                ): string =>
                    $role->value,
                UserRole::cases()
            );

        if (
            in_array(
                $role,
                $allowedRoles,
                true
            )
        ) {
            $query->where(
                'role',
                $role
            );
        } else {
            $role = '';
        }

        $status =
            $request
                ->string('status')
                ->toString();

        if (
            $status === 'active'
        ) {
            $query->where(
                'is_active',
                true
            );
        } elseif (
            $status === 'inactive'
        ) {
            $query->where(
                'is_active',
                false
            );
        } else {
            $status = '';
        }

        $users =
            $query
                ->paginate(20)
                ->withQueryString();

        $counts = [
            'all' =>
                User::query()
                    ->count(),

            'active' =>
                User::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->count(),

            'inactive' =>
                User::query()
                    ->where(
                        'is_active',
                        false
                    )
                    ->count(),

            'passengers' =>
                User::query()
                    ->where(
                        'role',
                        UserRole::Passenger
                            ->value
                    )
                    ->count(),

            'drivers' =>
                User::query()
                    ->where(
                        'role',
                        UserRole::Driver
                            ->value
                    )
                    ->count(),

            'admins' =>
                User::query()
                    ->where(
                        'role',
                        UserRole::Admin
                            ->value
                    )
                    ->count(),
        ];

        return view(
            'admin.users.index',
            [
                'users' =>
                    $users,

                'counts' =>
                    $counts,

                'search' =>
                    $search,

                'role' =>
                    $role,

                'status' =>
                    $status,

                'roles' =>
                    UserRole::cases(),
            ]
        );
    }

    public function updateStatus(
        UpdateUserStatusRequest $request,
        User $user
    ): RedirectResponse {
        $admin =
            $request->user();

        $isActive =
            (bool) $request->boolean(
                'is_active'
            );

        if (
            (string) $user->id
            === (string) $admin->id
            && ! $isActive
        ) {
            throw ValidationException::withMessages([
                'user' =>
                    'لا يمكنك تعطيل حسابك الإداري الحالي.',
            ]);
        }

        $targetRole =
            $user->role
            instanceof UserRole
                ? $user->role
                : UserRole::tryFrom(
                    (string) $user->role
                );

        if (
            $targetRole === UserRole::Admin
            && ! $isActive
            && $user->is_active
        ) {
            $activeAdmins =
                User::query()
                    ->where(
                        'role',
                        UserRole::Admin
                            ->value
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->count();

            if (
                $activeAdmins <= 1
            ) {
                throw ValidationException::withMessages([
                    'user' =>
                        'لا يمكن تعطيل آخر حساب إداري نشط.',
                ]);
            }
        }

        $user->forceFill([
            'is_active' =>
                $isActive,
        ])->save();

        return back()
            ->with(
                'success',
                $isActive
                    ? 'تم تفعيل حساب المستخدم.'
                    : 'تم تعطيل حساب المستخدم.'
            );
    }
}
