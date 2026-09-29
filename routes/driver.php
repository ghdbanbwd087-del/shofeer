<?php

use App\Http\Controllers\Driver\CarController;
use App\Http\Controllers\Driver\RegistrationController;
use App\Http\Controllers\Driver\TripController;
use App\Http\Controllers\Driver\TripRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Driver Dashboard
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    function (
        Request $request
    ) {
        return response()->json([
            'message' => 'SHOFEER driver area is ready.',

            'user' => [
                'id' => $request
                    ->user()
                    ->id,

                'name' => $request
                    ->user()
                    ->name,

                'role' => $request
                    ->user()
                    ->role
                    ->value,
            ],
        ]);
    }
)
    ->middleware([
        'auth',
        'role:driver',
    ])
    ->name(
        'dashboard'
    );
/*
|--------------------------------------------------------------------------
| Driver Verification
|--------------------------------------------------------------------------
*/

Route::get(
    '/register',
    [
        RegistrationController::class,
        'create',
    ]
)->name('register');

Route::post(
    '/register',
    [
        RegistrationController::class,
        'store',
    ]
)
    ->middleware(
        'throttle:5,1'
    )
    ->name(
        'register.store'
    );

/*
|--------------------------------------------------------------------------
| Cars
|--------------------------------------------------------------------------
|
| لا نضع verified.driver هنا لأن السيارة جزء من عملية توثيق السائق.
|
*/

Route::get(
    '/cars',
    [
        CarController::class,
        'index',
    ]
)->name('cars.index');

Route::get(
    '/cars/create',
    [
        CarController::class,
        'create',
    ]
)->name('cars.create');

Route::post(
    '/cars',
    [
        CarController::class,
        'store',
    ]
)->name('cars.store');

Route::delete(
    '/cars/{car}',
    [
        CarController::class,
        'destroy',
    ]
)->name('cars.destroy');

/*
|--------------------------------------------------------------------------
| Verified Driver Operations
|--------------------------------------------------------------------------
*/

Route::middleware(
    'verified.driver'
)->group(
    function (): void {
        /*
        |--------------------------------------------------------------------------
        | Request Trip
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/request-trip',
            [
                TripRequestController::class,
                'create',
            ]
        )->name('request-trip');

        Route::post(
            '/request-trip',
            [
                TripRequestController::class,
                'store',
            ]
        )
            ->middleware(
                'throttle:10,1'
            )
            ->name(
                'request-trip.store'
            );

        /*
        |--------------------------------------------------------------------------
        | Driver Trips
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/trips',
            [
                TripController::class,
                'index',
            ]
        )->name('trips.index');
    }
);
