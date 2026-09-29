<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * عرض صفحة الدخول.
     */
    public function create(): View
    {
        return view(
            'auth.login'
        );
    }

    /**
     * تنفيذ تسجيل الدخول.
     */
    public function store(
        LoginRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        /*
         * الحساب الموقوف لا يستطيع الدخول.
         */
        $credentials = [
            'phone' => $validated['phone'],

            'password' => $validated['password'],

            'is_active' => true,
        ];

        $remember = $request->boolean(
            'remember'
        );

        if (
            ! Auth::attempt(
                $credentials,
                $remember
            )
        ) {
            return back()
                ->withErrors([
                    'phone' => 'رقم الجوال أو كلمة المرور غير صحيحة.',
                ])
                ->onlyInput(
                    'phone'
                );
        }

        /*
         * حماية من Session Fixation.
         */
        $request
            ->session()
            ->regenerate();

        /** @var User $user */
        $user = $request->user();

        return redirect()->intended(
            route(
                $user
                    ->role
                    ->dashboardRouteName()
            )
        );
    }
}
