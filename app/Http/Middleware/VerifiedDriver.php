<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedDriver
{
    /**
     * السماح فقط للسائق المعتمد.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        /*
         * Route يفترض أنها محمية أصلاً بـauth + role:driver.
         */
        if ($user === null) {
            return redirect()->guest(
                route('login')
            );
        }

        $driver = $user->driver;

        /*
         * المستخدم Driver لكنه لم يقدم طلب توثيق بعد.
         */
        if ($driver === null) {
            return redirect()
                ->route(
                    'driver.register'
                )
                ->withErrors([
                    'driver' => 'يجب إكمال طلب توثيق السائق أولاً.',
                ]);
        }

        /*
         * السماح فقط للحالة approved.
         */
        if (! $driver->isApproved()) {
            return redirect()
                ->route(
                    'driver.register'
                )
                ->withErrors([
                    'driver' => 'حساب السائق غير معتمد بعد.',
                ]);
        }

        return $next($request);
    }
}
