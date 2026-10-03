<?php

use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\DriverReviewController;
use App\Http\Controllers\Admin\LiveController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\TripRequestController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'role:admin',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(
        function (): void {
            Route::get(
                '/',
                [
                    DashboardController::class,
                    'index',
                ]
            )->name(
                'dashboard'
            );


            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/users',
                [
                    UserController::class,
                    'index',
                ]
            )->name(
                'users.index'
            );

            Route::patch(
                '/users/{user}/status',
                [
                    UserController::class,
                    'updateStatus',
                ]
            )
                ->middleware(
                    'throttle:20,1'
                )
                ->name(
                    'users.status.update'
                );

            /*
            |--------------------------------------------------------------------------
            | Drivers
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/drivers',
                [
                    DriverReviewController::class,
                    'index',
                ]
            )->name(
                'drivers.index'
            );

            Route::get(
                '/drivers/{driver}',
                [
                    DriverController::class,
                    'show',
                ]
            )->name(
                'drivers.show'
            );

            Route::get(
                '/drivers/{driver}/documents/{document}',
                [
                    DriverController::class,
                    'document',
                ]
            )->name(
                'drivers.document'
            );

            Route::patch(
                '/drivers/{driver}/approve',
                [
                    DriverController::class,
                    'approve',
                ]
            )->name(
                'drivers.approve'
            );

            Route::patch(
                '/drivers/{driver}/reject',
                [
                    DriverController::class,
                    'reject',
                ]
            )->name(
                'drivers.reject'
            );

            Route::patch(
                '/drivers/{driver}/suspend',
                [
                    DriverController::class,
                    'suspend',
                ]
            )->name(
                'drivers.suspend'
            );

            /*
            |--------------------------------------------------------------------------
            | Cars
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/cars',
                [
                    CarController::class,
                    'index',
                ]
            )->name(
                'cars.index'
            );

            Route::get(
                '/cars/{car}',
                [
                    CarController::class,
                    'show',
                ]
            )->name(
                'cars.show'
            );

            /*
            |--------------------------------------------------------------------------
            | Trip Requests
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/trip-requests',
                [
                    TripRequestController::class,
                    'index',
                ]
            )->name(
                'trip-requests.index'
            );

            Route::patch(
                '/trip-requests/{tripRequest}/approve',
                [
                    TripRequestController::class,
                    'approve',
                ]
            )->name(
                'trip-requests.approve'
            );

            Route::patch(
                '/trip-requests/{tripRequest}/reject',
                [
                    TripRequestController::class,
                    'reject',
                ]
            )->name(
                'trip-requests.reject'
            );

            /*
            |--------------------------------------------------------------------------
            | Trips
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
                '/trips/create',
                [
                    TripController::class,
                    'create',
                ]
            )->name(
                'trips.create'
            );

            Route::post(
                '/trips',
                [
                    TripController::class,
                    'store',
                ]
            )->name(
                'trips.store'
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

            Route::patch(
                '/trips/{trip}/cancel',
                [
                    TripController::class,
                    'cancel',
                ]
            )->name(
                'trips.cancel'
            );

            /*
            |--------------------------------------------------------------------------
            | Payments
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/payments',
                [
                    PaymentController::class,
                    'index',
                ]
            )->name(
                'payments.index'
            );

            Route::get(
                '/payments/{payment}/proof',
                [
                    PaymentController::class,
                    'proof',
                ]
            )->name(
                'payments.proof'
            );

            Route::patch(
                '/payments/{payment}/approve',
                [
                    PaymentController::class,
                    'approve',
                ]
            )->name(
                'payments.approve'
            );

            Route::patch(
                '/payments/{payment}/reject',
                [
                    PaymentController::class,
                    'reject',
                ]
            )->name(
                'payments.reject'
            );

            /*
            |--------------------------------------------------------------------------
            | Refunds
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/refunds',
                [
                    RefundController::class,
                    'index',
                ]
            )->name(
                'refunds.index'
            );

            Route::patch(
                '/refunds/{refund}/process',
                [
                    RefundController::class,
                    'process',
                ]
            )
                ->middleware(
                    'throttle:5,1'
                )
                ->name(
                    'refunds.process'
                );

            Route::patch(
                '/refunds/{refund}/reject',
                [
                    RefundController::class,
                    'reject',
                ]
            )
                ->middleware(
                    'throttle:5,1'
                )
                ->name(
                    'refunds.reject'
                );

            /*
            |--------------------------------------------------------------------------
            | Packages
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/packages',
                [
                    PackageController::class,
                    'index',
                ]
            )->name(
                'packages.index'
            );

            Route::patch(
                '/packages/{package}/assign',
                [
                    PackageController::class,
                    'assign',
                ]
            )
                ->middleware(
                    'throttle:10,1'
                )
                ->name(
                    'packages.assign'
                );

            Route::patch(
                '/packages/{package}/status',
                [
                    PackageController::class,
                    'updateStatus',
                ]
            )
                ->middleware(
                    'throttle:10,1'
                )
                ->name(
                    'packages.status'
                );

            /*
            |--------------------------------------------------------------------------
            | Live Map
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/live',
                [
                    LiveController::class,
                    'index',
                ]
            )->name(
                'live.index'
            );

            Route::get(
                '/live/data',
                [
                    LiveController::class,
                    'data',
                ]
            )
                ->middleware(
                    'throttle:60,1'
                )
                ->name(
                    'live.data'
                );

            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/notifications',
                [
                    NotificationController::class,
                    'index',
                ]
            )->name(
                'notifications.index'
            );

            Route::post(
                '/notifications',
                [
                    NotificationController::class,
                    'store',
                ]
            )
                ->middleware(
                    'throttle:10,1'
                )
                ->name(
                    'notifications.store'
                );

            /*
            |--------------------------------------------------------------------------
            | Financial Rewards
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/rewards',
                [
                    RewardController::class,
                    'index',
                ]
            )->name(
                'rewards.index'
            );

            Route::post(
                '/rewards',
                [
                    RewardController::class,
                    'store',
                ]
            )
                ->middleware(
                    'throttle:20,1'
                )
                ->name(
                    'rewards.store'
                );

            Route::patch(
                '/rewards/{financialReward}',
                [
                    RewardController::class,
                    'update',
                ]
            )
                ->middleware(
                    'throttle:20,1'
                )
                ->name(
                    'rewards.update'
                );

            Route::post(
                '/rewards/run-monthly',
                [
                    RewardController::class,
                    'runMonthly',
                ]
            )
                ->middleware(
                    'throttle:2,1'
                )
                ->name(
                    'rewards.run-monthly'
                );
        }
    );
