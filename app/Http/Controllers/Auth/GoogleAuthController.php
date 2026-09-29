<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * إرسال المستخدم إلى Google.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes([
                'openid',
                'profile',
                'email',
            ])
            ->redirect();
    }

    /**
     * استقبال OAuth Callback.
     */
    public function callback(
        Request $request
    ): RedirectResponse {
        try {
            $googleUser = Socialite::driver(
                'google'
            )->user();

            $googleId = (string) $googleUser
                ->getId();

            $email = $googleUser
                ->getEmail();

            $name = $googleUser->getName()
                ?: $googleUser->getNickname()
                ?: (
                    $email !== null
                        ? Str::before(
                            $email,
                            '@'
                        )
                        : null
                )
                ?: 'مستخدم شوفير';

            /*
            |--------------------------------------------------------------------------
            | Find or Create User
            |--------------------------------------------------------------------------
            */

            $user = DB::transaction(
                function () use (
                    $googleId,
                    $email,
                    $name
                ): User {
                    /*
                     * البحث بواسطة google_id.
                     */
                    $user = User::query()
                        ->where(
                            'google_id',
                            $googleId
                        )
                        ->first();

                    if ($user !== null) {
                        if (
                            $user->email === null &&
                            $email !== null
                        ) {
                            $user->email = $email;

                            $user->save();
                        }

                        return $user;
                    }

                    /*
                     * ربط حساب موجود بنفس البريد.
                     */
                    if ($email !== null) {
                        $user = User::query()
                            ->where(
                                'email',
                                $email
                            )
                            ->first();

                        if ($user !== null) {
                            if (
                                $user->google_id !== null &&
                                $user->google_id !== $googleId
                            ) {
                                throw new RuntimeException(
                                    'هذا البريد مرتبط بحساب Google آخر.'
                                );
                            }

                            $user->google_id =
                                $googleId;

                            $user->save();

                            return $user;
                        }
                    }

                    /*
                     * إنشاء Passenger جديد.
                     */
                    return User::query()->create([
                        'name' => $name,

                        'email' => $email,

                        'google_id' => $googleId,

                        'phone' => null,

                        'password' => null,

                        'role' => UserRole::Passenger,

                        'is_active' => true,
                    ]);
                }
            );

            /*
             * منع الحساب الموقوف.
             */
            if (! $user->is_active) {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'google' => 'هذا الحساب موقوف حالياً. يرجى التواصل مع الإدارة.',
                    ]);
            }

            /*
             * تسجيل الدخول.
             */
            Auth::login(
                $user,
                true
            );

            /*
             * حماية Session.
             */
            $request
                ->session()
                ->regenerate();

            return redirect()->intended(
                route(
                    $user
                        ->role
                        ->dashboardRouteName()
                )
            );
        } catch (Throwable $exception) {
            /*
             * تسجيل الخطأ بدون عرضه للمستخدم.
             */
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors([
                    'google' => 'تعذر تسجيل الدخول بواسطة Google. يرجى المحاولة مرة أخرى.',
                ]);
        }
    }
}
