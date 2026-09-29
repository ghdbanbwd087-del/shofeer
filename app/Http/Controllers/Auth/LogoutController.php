<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * تسجيل خروج المستخدم.
     */
    public function __invoke(
        Request $request
    ): RedirectResponse {
        /*
         * تسجيل الخروج.
         */
        Auth::logout();

        /*
         * إبطال Session.
         */
        $request
            ->session()
            ->invalidate();

        /*
         * إنشاء CSRF Token جديد.
         */
        $request
            ->session()
            ->regenerateToken();

        return redirect()
            ->route('home')
            ->with(
                'success',
                'تم تسجيل خروجك بنجاح.'
            );
    }
}
