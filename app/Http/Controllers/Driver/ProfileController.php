<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\UpdateDriverPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(
        Request $request
    ): View {
        $user =
            $request->user();

        $driver =
            $user
                ->driver()
                ->firstOrFail();

        return view(
            'driver.profile.index',
            [
                'user' =>
                    $user,

                'driver' =>
                    $driver,
            ]
        );
    }

    public function updatePassword(
        UpdateDriverPasswordRequest $request
    ): RedirectResponse {
        $user =
            $request->user();

        $user->forceFill([
            'password' =>
                Hash::make(
                    $request->validated(
                        'password'
                    )
                ),

            'remember_token' =>
                Str::random(60),
        ])->save();

        $request
            ->session()
            ->regenerate();

        return back()
            ->with(
                'success',
                'تم تغيير كلمة المرور بنجاح.'
            );
    }
}
