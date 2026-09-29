<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * التحقق من دور المستخدم.
     *
     * مثال:
     *
     * role:admin
     *
     * role:admin,driver
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        /*
         * المستخدم غير مسجل.
         */
        if ($user === null) {
            return redirect()->guest(
                route('login')
            );
        }

        /*
         * الحساب موقوف.
         */
        if (! $user->is_active) {
            Auth::logout();

            $request
                ->session()
                ->invalidate();

            $request
                ->session()
                ->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'account' => 'هذا الحساب موقوف حالياً.',
                ]);
        }

        /*
         * الحصول على قيمة الدور.
         */
        $currentRole =
            $user->role instanceof UserRole
                ? $user->role->value
                : (string) $user->role;

        /*
         * التحقق من الصلاحية.
         */
        if (
            ! in_array(
                $currentRole,
                $roles,
                true
            )
        ) {
            abort(
                Response::HTTP_FORBIDDEN,
                'غير مصرح لك بالوصول إلى هذه الصفحة.'
            );
        }

        return $next($request);
    }
}
