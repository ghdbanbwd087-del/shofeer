<?php

namespace App\Http\Controllers\Driver;

use App\Enums\BookingStatus;
use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\User;
use App\Services\DriverPassengerPrivacyService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PassengerController extends Controller
{
    public function index(
        Request $request,
        DriverPassengerPrivacyService $privacyService
    ): View {
        $user =
            $request->user();

        $driver =
            $user
                ->driver()
                ->firstOrFail();

        $trips =
            Trip::query()
                ->where(
                    'driver_id',
                    $driver->id
                )
                ->whereIn(
                    'status',
                    [
                        TripStatus::Scheduled
                            ->value,

                        TripStatus::Boarding
                            ->value,

                        TripStatus::InProgress
                            ->value,
                    ]
                )
                ->with([
                    'fromCity',
                    'toCity',

                    'bookings' =>
                        function (
                            $query
                        ): void {
                            $query
                                ->where(
                                    'status',
                                    BookingStatus::Confirmed
                                        ->value
                                )
                                ->select([
                                    'id',
                                    'trip_id',
                                    'booking_code',
                                    'passenger_name',
                                    'passenger_whatsapp',
                                    'seat_number',
                                    'status',
                                ])
                                ->orderBy(
                                    'seat_number'
                                );
                        },
                ])
                ->orderBy(
                    'departure_at'
                )
                ->get();

        $tripRows =
            $trips->map(
                function (
                    Trip $trip
                ) use (
                    $privacyService
                ): array {
                    $canRevealWhatsApp =
                        $privacyService
                            ->canRevealWhatsApp(
                                $trip
                            );

                    $passengers =
                        $trip
                            ->bookings
                            ->map(
                                function (
                                    Booking $booking
                                ) use (
                                    $privacyService,
                                    $canRevealWhatsApp
                                ): array {
                                    $whatsAppUrl =
                                        $canRevealWhatsApp
                                            ? $privacyService
                                                ->whatsappUrl(
                                                    $booking
                                                        ->passenger_whatsapp
                                                )
                                            : null;

                                    return [
                                        'booking_id' =>
                                            $booking->id,

                                        'booking_code' =>
                                            $booking
                                                ->booking_code,

                                        'first_name' =>
                                            $privacyService
                                                ->firstName(
                                                    $booking
                                                        ->passenger_name
                                                ),

                                        'seat_number' =>
                                            $booking
                                                ->seat_number,

                                        'whatsapp_available' =>
                                            $whatsAppUrl
                                            !== null,

                                        'whatsapp_url' =>
                                            $whatsAppUrl,
                                    ];
                                }
                            );

                    return [
                        'trip' =>
                            $trip,

                        'passengers' =>
                            $passengers,

                        'passengers_count' =>
                            $passengers->count(),

                        'whatsapp_window_open' =>
                            $canRevealWhatsApp,
                    ];
                }
            );

        return view(
            'driver.passengers.index',
            [
                'driver' =>
                    $driver,

                'tripRows' =>
                    $tripRows,
            ]
        );
    }

    public function contactAdmin(
        Request $request,
        Booking $booking,
        NotificationService $notificationService
    ): RedirectResponse {
        $user =
            $request->user();

        $driver =
            $user
                ->driver()
                ->firstOrFail();

        $booking->loadMissing(
            'trip'
        );

        if (
            $booking->trip === null
            || (string) $booking
                ->trip
                ->driver_id
                !== (string) $driver->id
            || $booking->status
                !== BookingStatus::Confirmed
        ) {
            abort(403);
        }

        if (
            ! in_array(
                $booking
                    ->trip
                    ->status,
                [
                    TripStatus::Scheduled,
                    TripStatus::Boarding,
                    TripStatus::InProgress,
                ],
                true
            )
        ) {
            abort(403);
        }

        $admins =
            User::query()
                ->where(
                    'role',
                    'admin'
                )
                ->where(
                    'is_active',
                    true
                )
                ->get();

        foreach (
            $admins
            as $admin
        ) {
            $notificationService
                ->sendToUser(
                    user: $admin,

                    title:
                        'طلب مساعدة من سائق',

                    message:
                        'السائق '.
                        $user->name.
                        ' يطلب التواصل بخصوص الحجز '.
                        $booking
                            ->booking_code.
                        '، المقعد '.
                        $booking
                            ->seat_number.
                        '.',

                    sender:
                        $user,
                );
        }

        if (
            $admins->isEmpty()
        ) {
            return back()
                ->with(
                    'error',
                    'لا يوجد إداري نشط لاستقبال الطلب حاليًا.'
                );
        }

        return back()
            ->with(
                'success',
                'تم إرسال طلب المساعدة إلى الإدارة.'
            );
    }
}
