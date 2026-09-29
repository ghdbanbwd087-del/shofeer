<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TripController extends Controller
{
    /**
     * رحلات السائق.
     *
     * لا نرسل السعر إلى View السائق.
     */
    public function index(): View
    {
        $driver = request()
            ->user()
            ->driver;

        abort_if(
            $driver === null,
            403
        );

        $trips = $driver
            ->trips()
            ->with([
                'fromCity',
                'toCity',
                'car',
            ])
            ->latest(
                'departure_at'
            )
            ->get([
                'id',
                'driver_id',
                'car_id',
                'from_city_id',
                'to_city_id',
                'departure_at',
                'meeting_point',
                'destination_point',
                'seat_count',
                'available_seats',
                'status',
                'is_published',
                'notes',
                'created_at',
                'updated_at',
            ]);

        return view(
            'driver.trips',
            compact('trips')
        );
    }
}
