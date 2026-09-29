<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\VerifiedDriver;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        /*
        |--------------------------------------------------------------------------
        | Main Web Routes
        |--------------------------------------------------------------------------
        */

        web: __DIR__.'/../routes/web.php',

        commands: __DIR__.'/../routes/console.php',

        health: '/up',

        /*
        |--------------------------------------------------------------------------
        | Additional Route Files
        |--------------------------------------------------------------------------
        */

        then: function (): void {
            /*
             * admin.php يحتوي داخله بالفعل على:
             *
             * prefix('admin')
             * name('admin.')
             */
            Route::middleware('web')
                ->group(
                    base_path(
                        'routes/admin.php'
                    )
                );

            /*
             * driver.php يستخدم Routes داخلية مثل:
             *
             * /
             * /register
             * /trips
             * /cars
             *
             * لذلك نعزلها تحت:
             *
             * /driver/*
             * driver.*
             */
            Route::middleware('web')
                ->prefix('driver')
                ->name('driver.')
                ->group(
                    base_path(
                        'routes/driver.php'
                    )
                );
        }
    )
    ->withMiddleware(
        function (
            Middleware $middleware
        ): void {
            /*
            |--------------------------------------------------------------------------
            | Middleware Aliases
            |--------------------------------------------------------------------------
            */

            $middleware->alias([
                'role' => RoleMiddleware::class,

                'verified.driver' => VerifiedDriver::class,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Guest Redirect
            |--------------------------------------------------------------------------
            |
            | المستخدم غير المسجل الذي يدخل Route محمية
            | يعود إلى صفحة تسجيل الدخول.
            |
            */

            $middleware->redirectGuestsTo(
                fn (Request $request): string => route('login')
            );

            /*
            |--------------------------------------------------------------------------
            | Authenticated User Redirect
            |--------------------------------------------------------------------------
            |
            | middleware: guest
            |
            | Passenger -> dashboard
            | Driver    -> driver.dashboard
            | Admin     -> admin.dashboard
            |
            */

            $middleware->redirectUsersTo(
                function (
                    Request $request
                ): string {
                    $user = $request->user();

                    if (! $user) {
                        return route('home');
                    }

                    /*
                     * role قد تكون Backed Enum
                     * أو string، لذلك ندعم الحالتين.
                     */
                    $role = $user->role;

                    $roleValue =
                        $role instanceof BackedEnum
                            ? $role->value
                            : (string) $role;

                    return match ($roleValue) {
                        'admin' => route(
                            'admin.dashboard'
                        ),

                        'driver' => route(
                            'driver.dashboard'
                        ),

                        default => route(
                            'dashboard'
                        ),
                    };
                }
            );
        }
    )
    ->withExceptions(
        function (
            Exceptions $exceptions
        ): void {
            /*
            |--------------------------------------------------------------------------
            | JSON Exceptions
            |--------------------------------------------------------------------------
            */

            $exceptions->shouldRenderJsonWhen(
                fn (Request $request) => $request->is('api/*')
                    || $request->expectsJson()
            );
        }
    )
    ->create();
