<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Driver\BadgeController as DriverBadgeController;
use App\Http\Controllers\Driver\DashboardController as DriverDashboardController;
use App\Http\Controllers\Driver\LiveController as DriverLiveController;
use App\Http\Controllers\Driver\PassengerController as DriverPassengerController;
use App\Http\Controllers\Driver\ProfileController as DriverProfileController;
use App\Http\Controllers\Driver\NotificationController as DriverNotificationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\Passenger\BadgeController;
use App\Http\Controllers\Passenger\BalanceController;
use App\Http\Controllers\Passenger\DashboardController;
use App\Http\Controllers\Passenger\LiveController;
use App\Http\Controllers\Passenger\PointController;
use App\Http\Controllers\Passenger\RatingController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get(
    '/trips',
    [TripController::class, 'index']
)->name('trips.index');

Route::get(
    '/trips/{trip}',
    [TripController::class, 'show']
)->name('trips.show');

Route::get(
    '/track',
    [PackageController::class, 'track']
)->name('packages.track');

Route::middleware('guest')->group(
    function (): void {
        Route::get(
            '/login',
            [LoginController::class, 'create']
        )->name('login');

        Route::post(
            '/login',
            [LoginController::class, 'store']
        )
            ->middleware('throttle:5,1')
            ->name('login.store');

        Route::get(
            '/register',
            [RegisterController::class, 'create']
        )->name('register');

        Route::post(
            '/register',
            [RegisterController::class, 'store']
        )
            ->middleware('throttle:3,60')
            ->name('register.store');

        Route::get(
            '/auth/google/redirect',
            [GoogleAuthController::class, 'redirect']
        )
            ->middleware('throttle:10,1')
            ->name('auth.google.redirect');

        Route::get(
            '/auth/google/callback',
            [GoogleAuthController::class, 'callback']
        )
            ->middleware('throttle:10,1')
            ->name('auth.google.callback');
    }
);

Route::middleware('auth')->group(
    function (): void {
        Route::post(
            '/logout',
            LogoutController::class
        )->name('logout');


        Route::prefix('driver')
            ->name('driver.')
            ->middleware([
                'role:driver',
                'verified.driver',
            ])
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

                    Route::get(
                        '/earnings',
                        [
                            DriverDashboardController::class,
                            'earnings',
                        ]
                    )->name(
                        'earnings.index'
                    );


                    Route::get(
                        '/badges',
                        [
                            DriverBadgeController::class,
                            'index',
                        ]
                    )->name(
                        'badges.index'
                    );


                    Route::get(
                        '/passengers',
                        [
                            DriverPassengerController::class,
                            'index',
                        ]
                    )->name(
                        'passengers.index'
                    );

                    Route::post(
                        '/passengers/{booking}/contact-admin',
                        [
                            DriverPassengerController::class,
                            'contactAdmin',
                        ]
                    )
                        ->middleware(
                            'throttle:5,1'
                        )
                        ->name(
                            'passengers.contact-admin'
                        );


                    Route::get(
                        '/profile',
                        [
                            DriverProfileController::class,
                            'index',
                        ]
                    )->name(
                        'profile.index'
                    );

                    Route::patch(
                        '/profile/password',
                        [
                            DriverProfileController::class,
                            'updatePassword',
                        ]
                    )
                        ->middleware(
                            'throttle:5,1'
                        )
                        ->name(
                            'profile.password.update'
                        );

                    Route::get(
                        '/live',
                        [
                            DriverLiveController::class,
                            'index',
                        ]
                    )->name(
                        'live.index'
                    );

                    Route::post(
                        '/live/location',
                        [
                            DriverLiveController::class,
                            'store',
                        ]
                    )
                        ->middleware(
                            'throttle:20,1'
                        )
                        ->name(
                            'live.location.store'
                        );


                    Route::get(
                        '/notifications',
                        [
                            DriverNotificationController::class,
                            'index',
                        ]
                    )->name(
                        'notifications.index'
                    );

                    Route::patch(
                        '/notifications/read-all',
                        [
                            DriverNotificationController::class,
                            'readAll',
                        ]
                    )
                        ->middleware(
                            'throttle:20,1'
                        )
                        ->name(
                            'notifications.read-all'
                        );

                    Route::patch(
                        '/notifications/{notification}/read',
                        [
                            DriverNotificationController::class,
                            'read',
                        ]
                    )
                        ->middleware(
                            'throttle:30,1'
                        )
                        ->name(
                            'notifications.read'
                        );
                }
            );

        Route::middleware('role:passenger')->group(
            function (): void {
                Route::get(
                    '/dashboard',
                    [DashboardController::class, 'index']
                )->name('dashboard');

                Route::get(
                    '/dashboard/badges',
                    [BadgeController::class, 'index']
                )->name('dashboard.badges');

                Route::get(
                    '/dashboard/balance',
                    [BalanceController::class, 'index']
                )->name('dashboard.balance');

                Route::get(
                    '/dashboard/points',
                    [PointController::class, 'index']
                )->name('dashboard.points');

                Route::get(
                    '/dashboard/live',
                    [LiveController::class, 'index']
                )->name('dashboard.live.index');

                Route::get(
                    '/dashboard/live/data',
                    [LiveController::class, 'data']
                )
                    ->middleware('throttle:30,1')
                    ->name('dashboard.live.data');

                Route::get(
                    '/dashboard/ratings',
                    [RatingController::class, 'index']
                )->name('dashboard.ratings.index');

                Route::post(
                    '/dashboard/bookings/{booking}/rating',
                    [RatingController::class, 'store']
                )
                    ->middleware('throttle:5,1')
                    ->name('dashboard.bookings.rating.store');

                Route::patch(
                    '/dashboard/ratings/{rating}',
                    [RatingController::class, 'update']
                )
                    ->middleware('throttle:10,1')
                    ->name('dashboard.ratings.update');

                Route::get(
                    '/packages/create',
                    [PackageController::class, 'create']
                )->name('packages.create');

                Route::post(
                    '/packages',
                    [PackageController::class, 'store']
                )
                    ->middleware('throttle:5,10')
                    ->name('packages.store');

                Route::get(
                    '/dashboard/packages',
                    [PackageController::class, 'index']
                )->name('dashboard.packages.index');

                Route::get(
                    '/dashboard/packages/{package}',
                    [PackageController::class, 'show']
                )->name('dashboard.packages.show');

                Route::patch(
                    '/dashboard/packages/{package}/cancel',
                    [PackageController::class, 'cancel']
                )
                    ->middleware('throttle:5,1')
                    ->name('dashboard.packages.cancel');

                Route::get(
                    '/dashboard/bookings',
                    [DashboardController::class, 'bookings']
                )->name('dashboard.bookings.index');

                Route::get(
                    '/dashboard/bookings/{booking}',
                    [DashboardController::class, 'show']
                )->name('dashboard.bookings.show');

                Route::patch(
                    '/dashboard/bookings/{booking}/cancel',
                    [DashboardController::class, 'cancel']
                )
                    ->middleware('throttle:5,1')
                    ->name('dashboard.bookings.cancel');

                Route::post(
                    '/dashboard/bookings/{booking}/refund',
                    [DashboardController::class, 'requestRefund']
                )
                    ->middleware('throttle:3,10')
                    ->name('dashboard.bookings.refund');

                Route::get(
                    '/trips/{trip}/seats',
                    [SeatController::class, 'show']
                )->name('trips.seats.show');

                Route::post(
                    '/trips/{trip}/seats',
                    [SeatController::class, 'store']
                )
                    ->middleware('throttle:10,1')
                    ->name('trips.seats.store');

                Route::get(
                    '/booking/{booking}/pay',
                    [BookingController::class, 'pay']
                )->name('booking.pay');

                Route::post(
                    '/booking/{booking}/pay',
                    [BookingController::class, 'submitPayment']
                )
                    ->middleware('throttle:5,1')
                    ->name('booking.pay.submit');

                Route::get(
                    '/booking/{booking}/pending',
                    [BookingController::class, 'pending']
                )->name('booking.pending');

                Route::get(
                    '/booking/{booking}/success',
                    [BookingController::class, 'success']
                )->name('booking.success');

                Route::get(
                    '/booking/{booking}/failed',
                    [BookingController::class, 'failed']
                )->name('booking.failed');
            }
        );
    }
);
