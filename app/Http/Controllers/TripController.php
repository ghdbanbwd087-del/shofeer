<?php

namespace App\Http\Controllers;

use App\Enums\TripStatus;
use App\Models\City;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TripController extends Controller
{
    /**
     * قائمة الرحلات العامة مع البحث والفلترة.
     */
    public function index(
        Request $request
    ): View {
        $query = Trip::query()
            ->published()
            ->upcoming()
            ->with([
                'driver.user:id,name',
                'car',
                'fromCity',
                'toCity',
            ]);

        /*
        |--------------------------------------------------------------------------
        | From
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('from')
        ) {
            $query->where(
                'from_city_id',
                $request->string(
                    'from'
                )->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | To
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('to')
        ) {
            $query->where(
                'to_city_id',
                $request->string(
                    'to'
                )->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('date')
        ) {
            $query->whereDate(
                'departure_at',
                $request->string(
                    'date'
                )->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'min_price'
            )
        ) {
            $query->where(
                'price',
                '>=',
                $request->input(
                    'min_price'
                )
            );
        }

        if (
            $request->filled(
                'max_price'
            )
        ) {
            $query->where(
                'price',
                '<=',
                $request->input(
                    'max_price'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Seats
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('seats')
        ) {
            $query->where(
                'available_seats',
                '>=',
                max(
                    1,
                    (int) $request->input(
                        'seats'
                    )
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Car Type
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('type')
        ) {
            $type = $request
                ->string('type')
                ->toString();

            $query->whereHas(
                'car',
                fn (
                    Builder $carQuery
                ) => $carQuery->where(
                    'type',
                    $type
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        match (
            $request
                ->string(
                    'sort',
                    'soonest'
                )
                ->toString()
        ) {
            'latest' => $query->latest(),

            'cheapest' => $query->orderBy(
                'price'
            ),

            'highest' => $query
                ->join(
                    'drivers',
                    'trips.driver_id',
                    '=',
                    'drivers.id'
                )
                ->orderByDesc(
                    'drivers.rating'
                )
                ->select(
                    'trips.*'
                ),

            default => $query->orderBy(
                'departure_at'
            ),
        };

        $trips = $query
            ->paginate(12)
            ->withQueryString();

        $cities = City::query()
            ->active()
            ->orderBy(
                'sort_order'
            )
            ->orderBy(
                'name_ar'
            )
            ->get();

        return view(
            'trips.index',
            compact(
                'trips',
                'cities'
            )
        );
    }

    /**
     * تفاصيل الرحلة.
     */
    public function show(
        Trip $trip
    ): View {
        abort_unless(
            $trip->is_published,
            404
        );

        abort_if(
            $trip->status ===
                TripStatus::Cancelled,
            404
        );

        $trip->load([
            'driver.user',
            'car',
            'fromCity',
            'toCity',
        ]);

        return view(
            'trips.show',
            compact('trip')
        );
    }
}
