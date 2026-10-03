<?php

use App\Http\Controllers\Driver\DashboardController as DriverDashboardController;
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
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        then: function (): void {
            /*
            |--------------------------------------------------------------------------
            | Admin Routes
            |--------------------------------------------------------------------------
            |
            | routes/admin.php already contains its own /admin prefix and
            | admin.* route-name prefix.
            |
            */

            Route::middleware('web')
                ->group(
                    base_path('routes/admin.php')
                );

            /*
            |--------------------------------------------------------------------------
            | Legacy Driver Routes
            |--------------------------------------------------------------------------
            |
            | This file historically defined routes such as:
            |
            | /register
            | /cars
            | /request-trip
            | /trips
            |
            | and names such as:
            |
            | register.store
            | cars.store
            | request-trip.store
            | trips.index
            |
            | Driver feature tests and SHOFEER's intended URL structure expect:
            |
            | /driver/register
            | /driver/cars
            | /driver/request-trip
            | /driver/trips
            |
            | with names:
            |
            | driver.register.store
            | driver.cars.store
            | driver.request-trip.store
            | driver.trips.index
            |
            | Therefore the entire legacy file is isolated under /driver and
            | driver.* here. This also prevents it from overriding public /
            | home, /register, /trips and other passenger/public routes.
            |
            */

            Route::middleware('web')
                ->prefix('driver')
                ->name('driver.')
                ->group(
                    base_path('routes/driver.php')
                );

            /*
            |--------------------------------------------------------------------------
            | Canonical Driver Dashboard
            |--------------------------------------------------------------------------
            |
            | routes/driver.php contains the historical driver dashboard.
            | Register the Stage 5.9 dashboard last so driver.dashboard always
            | resolves to the real Blade dashboard controller.
            |
            */

            Route::middleware([
                'web',
                'auth',
                'role:driver',
                'verified.driver',
            ])
                ->prefix('driver')
                ->name('driver.')
                ->group(
                    function (): void {
                        Route::get(
                            '/',
                            [
                                DriverDashboardController::class,
                                'index',
                            ]
                        )->name(
                            'dashboard'
                        );
                    }
                );
        }
    )
    ->withMiddleware(
        function (
            Middleware $middleware
        ): void {
            $middleware->alias([
                'role' =>
                    RoleMiddleware::class,

                'verified.driver' =>
                    VerifiedDriver::class,
            ]);
        }
    )
    ->withExceptions(
        function (
            Exceptions $exceptions
        ): void {
            $exceptions->shouldRenderJsonWhen(
                fn (Request $request) =>
                    $request->is('api/*')
                    || $request->expectsJson()
            );
        }
    )
    ->create();
