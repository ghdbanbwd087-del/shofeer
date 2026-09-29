<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * عرض صفحة التسجيل.
     */
    public function create(): View
    {
        return view(
            'auth.register'
        );
    }

    /**
     * إنشاء حساب راكب جديد.
     */
    public function store(
        RegisterRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        /*
         * التسجيل العام ينشئ Passenger دائماً.
         */
        $user = User::query()->create([
            'name' => $validated['name'],

            'phone' => $validated['phone'],

            'password' => $validated['password'],

            'role' => UserRole::Passenger,

            'is_active' => true,
        ]);

        /*
         * Laravel Registered event.
         */
        event(
            new Registered($user)
        );

        /*
         * تسجيل الدخول مباشرة.
         */
        Auth::login($user);

        /*
         * حماية Session Fixation.
         */
        $request
            ->session()
            ->regenerate();

        return redirect()
            ->route(
                $user
                    ->role
                    ->dashboardRouteName()
            )
            ->with(
                'success',
                'تم إنشاء حسابك في شوفير بنجاح.'
            );
    }
}
