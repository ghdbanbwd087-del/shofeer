<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\Passenger\BadgeController;
use App\Http\Controllers\Passenger\BalanceController;
use App\Http\Controllers\Passenger\DashboardController;
use App\Http\Controllers\Passenger\PointController;
use App\Http\Controllers\Passenger\RatingController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::view(
    '/',
    'home'
)->name(
    'home'
);

/*
|--------------------------------------------------------------------------
| Public Trips
|--------------------------------------------------------------------------
*/

Route::get(
    '/trips',
    [
        TripController::class,
        'index',
    ]
)->name(
    'trips.index'
);

Route::get(
    '/trips/{trip}',
    [
        TripController::class,
        'show',
    ]
)->name(
    'trips.show'
);

/*
|--------------------------------------------------------------------------
| Public Package Tracking
|--------------------------------------------------------------------------
*/

Route::get(
    '/track',
    [
        PackageController::class,
        'track',
    ]
)->name(
    'packages.track'
);

/*
|--------------------------------------------------------------------------
| Guest Authentication
|--------------------------------------------------------------------------
*/

Route::middleware(
    'guest'
)->group(
    function (): void {
        Route::get(
            '/login',
            [
                LoginController::class,
                'create',
            ]
        )->name(
            'login'
        );

        Route::post(
            '/login',
            [
                LoginController::class,
                'store',
            ]
        )
            ->middleware(
                'throttle:5,1'
            )
            ->name(
                'login.store'
            );

        Route::get(
            '/register',
            [
                RegisterController::class,
                'create',
            ]
        )->name(
            'register'
        );

        Route::post(
            '/register',
            [
                RegisterController::class,
                'store',
            ]
        )
            ->middleware(
                'throttle:3,60'
            )
            ->name(
                'register.store'
            );

        Route::get(
            '/auth/google/redirect',
            [
                GoogleAuthController::class,
                'redirect',
            ]
        )
            ->middleware(
                'throttle:10,1'
            )
            ->name(
                'auth.google.redirect'
            );

        Route::get(
            '/auth/google/callback',
            [
                GoogleAuthController::class,
                'callback',
            ]
        )
            ->middleware(
                'throttle:10,1'
            )
            ->name(
                'auth.google.callback'
            );
    }
);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(
    'auth'
)->group(
    function (): void {
        Route::post(
            '/logout',
            LogoutController::class
        )->name(
            'logout'
        );

        Route::middleware(
            'role:passenger'
        )->group(
            function (): void {
                Route::get(
                    '/dashboard',
                    [
                        DashboardController::class,
                        'index',
                    ]
                )->name(
                    'dashboard'
                );

                Route::get(
                    '/dashboard/badges',
                    [
                        BadgeController::class,
                        'index',
                    ]
                )->name(
                    'dashboard.badges'
                );

                Route::get(
                    '/dashboard/balance',
                    [
                        BalanceController::class,
                        'index',
                    ]
                )->name(
                    'dashboard.balance'
                );

                Route::get(
                    '/dashboard/points',
                    [
                        PointController::class,
                        'index',
                    ]
                )->name(
                    'dashboard.points'
                );

                /*
                |--------------------------------------------------------------------------
                | Ratings
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/dashboard/ratings',
                    [
                        RatingController::class,
                        'index',
                    ]
                )->name(
                    'dashboard.ratings.index'
                );

                Route::post(
                    '/dashboard/bookings/{booking}/rating',
                    [
                        RatingController::class,
                        'store',
                    ]
                )
                    ->middleware(
                        'throttle:5,1'
                    )
                    ->name(
                        'dashboard.bookings.rating.store'
                    );

                Route::patch(
                    '/dashboard/ratings/{rating}',
                    [
                        RatingController::class,
                        'update',
                    ]
                )
                    ->middleware(
                        'throttle:10,1'
                    )
                    ->name(
                        'dashboard.ratings.update'
                    );

                /*
                |--------------------------------------------------------------------------
                | Packages
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/packages/create',
                    [
                        PackageController::class,
                        'create',
                    ]
                )->name(
                    'packages.create'
                );

                Route::post(
                    '/packages',
                    [
                        PackageController::class,
                        'store',
                    ]
                )
                    ->middleware(
                        'throttle:5,10'
                    )
                    ->name(
                        'packages.store'
                    );

                Route::get(
                    '/dashboard/packages',
                    [
                        PackageController::class,
                        'index',
                    ]
                )->name(
                    'dashboard.packages.index'
                );

                Route::get(
                    '/dashboard/packages/{package}',
                    [
                        PackageController::class,
                        'show',
                    ]
                )->name(
                    'dashboard.packages.show'
                );

                Route::patch(
                    '/dashboard/packages/{package}/cancel',
                    [
                        PackageController::class,
                        'cancel',
                    ]
                )
                    ->middleware(
                        'throttle:5,1'
                    )
                    ->name(
                        'dashboard.packages.cancel'
                    );

                /*
                |--------------------------------------------------------------------------
                | Bookings
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/dashboard/bookings',
                    [
                        DashboardController::class,
                        'bookings',
                    ]
                )->name(
                    'dashboard.bookings.index'
                );

                Route::get(
                    '/dashboard/bookings/{booking}',
                    [
                        DashboardController::class,
                        'show',
                    ]
                )->name(
                    'dashboard.bookings.show'
                );

                Route::patch(
                    '/dashboard/bookings/{booking}/cancel',
                    [
                        DashboardController::class,
                        'cancel',
                    ]
                )
                    ->middleware(
                        'throttle:5,1'
                    )
                    ->name(
                        'dashboard.bookings.cancel'
                    );

                Route::post(
                    '/dashboard/bookings/{booking}/refund',
                    [
                        DashboardController::class,
                        'requestRefund',
                    ]
                )
                    ->middleware(
                        'throttle:3,10'
                    )
                    ->name(
                        'dashboard.bookings.refund'
                    );

                /*
                |--------------------------------------------------------------------------
                | Seats
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/trips/{trip}/seats',
                    [
                        SeatController::class,
                        'show',
                    ]
                )->name(
                    'trips.seats.show'
                );

                Route::post(
                    '/trips/{trip}/seats',
                    [
                        SeatController::class,
                        'store',
                    ]
                )
                    ->middleware(
                        'throttle:10,1'
                    )
                    ->name(
                        'trips.seats.store'
                    );

                /*
                |--------------------------------------------------------------------------
                | Payment
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/booking/{booking}/pay',
                    [
                        BookingController::class,
                        'pay',
                    ]
                )->name(
                    'booking.pay'
                );

                Route::post(
                    '/booking/{booking}/pay',
                    [
                        BookingController::class,
                        'submitPayment',
                    ]
                )
                    ->middleware(
                        'throttle:5,1'
                    )
                    ->name(
                        'booking.pay.submit'
                    );

                Route::get(
                    '/booking/{booking}/pending',
                    [
                        BookingController::class,
                        'pending',
                    ]
                )->name(
                    'booking.pending'
                );

                Route::get(
                    '/booking/{booking}/success',
                    [
                        BookingController::class,
                        'success',
                    ]
                )->name(
                    'booking.success'
                );

                Route::get(
                    '/booking/{booking}/failed',
                    [
                        BookingController::class,
                        'failed',
                    ]
                )->name(
                    'booking.failed'
                );
            }
        );
    }
);
