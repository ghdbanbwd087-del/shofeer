<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DriverStatus;
use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTripRequest;
use App\Models\Car;
use App\Models\City;
use App\Models\Driver;
use App\Models\Trip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TripController extends Controller
{
    /**
     * جميع الرحلات.
     */
    public function index(): View
    {
        $trips = Trip::query()
            ->with([
                'driver.user',
                'car',
                'fromCity',
                'toCity',
            ])
            ->latest('departure_at')
            ->paginate(20);

        return view(
            'admin.trips.index',
            compact('trips')
        );
    }

    /**
     * نموذج إنشاء رحلة مباشرة.
     */
    public function create(): View
    {
        $drivers = Driver::query()
            ->where(
                'status',
                DriverStatus::Approved->value
            )
            ->with([
                'user',
                'cars' => fn ($query) => $query->where(
                    'is_active',
                    true
                ),
            ])
            ->orderBy('created_at')
            ->get();

        $cities = City::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name_ar')
            ->get();

        return view(
            'admin.trips.create',
            compact(
                'drivers',
                'cities'
            )
        );
    }

    /**
     * إنشاء رحلة مباشرة بواسطة الإدارة.
     */
    public function store(
        StoreTripRequest $request
    ): RedirectResponse {
        $validated =
            $request->validated();

        $trip = DB::transaction(
            function () use (
                $request,
                $validated
            ): Trip {
                $car = Car::query()
                    ->whereKey(
                        $validated['car_id']
                    )
                    ->where(
                        'driver_id',
                        $validated['driver_id']
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->first();

                if ($car === null) {
                    throw ValidationException::withMessages([
                        'car_id' => 'السيارة المحددة لا تتبع للسائق المحدد.',
                    ]);
                }

                if (
                    $validated['seat_count']
                    > $car->seat_count
                ) {
                    throw ValidationException::withMessages([
                        'seat_count' => 'عدد المقاعد يتجاوز سعة السيارة.',
                    ]);
                }

                return Trip::query()->create([
                    'driver_id' => $validated['driver_id'],

                    'car_id' => $validated['car_id'],

                    'source_trip_request_id' => null,

                    'from_city_id' => $validated['from_city_id'],

                    'to_city_id' => $validated['to_city_id'],

                    'departure_at' => $validated['departure_at'],

                    'meeting_point' => $validated['meeting_point'],

                    'destination_point' => $validated[
                            'destination_point'
                        ] ?? null,

                    'price' => $validated['price'],

                    'seat_count' => $validated['seat_count'],

                    'available_seats' => $validated['seat_count'],

                    'status' => TripStatus::Scheduled,

                    'is_published' => $request->boolean(
                        'is_published',
                        true
                    ),

                    'notes' => $validated['notes']
                        ?? null,

                    'created_by' => $request->user()->id,
                ]);
            }
        );

        return redirect()
            ->route(
                'admin.trips.show',
                $trip
            )
            ->with(
                'success',
                'تم إنشاء الرحلة بنجاح.'
            );
    }

    /**
     * تفاصيل رحلة.
     */
    public function show(
        Trip $trip
    ): View {
        $trip->load([
            'driver.user',
            'car',
            'fromCity',
            'toCity',
            'creator',
            'sourceRequest',
        ]);

        return view(
            'admin.trips.show',
            compact('trip')
        );
    }

    /**
     * إلغاء الرحلة.
     */
    public function cancel(
        Trip $trip
    ): RedirectResponse {
        if (
            in_array(
                $trip->status,
                [
                    TripStatus::Completed,
                    TripStatus::Cancelled,
                ],
                true
            )
        ) {
            return back()
                ->withErrors([
                    'trip' => 'لا يمكن إلغاء هذه الرحلة بحالتها الحالية.',
                ]);
        }

        $trip->update([
            'status' => TripStatus::Cancelled,

            'is_published' => false,
        ]);

        return back()->with(
            'success',
            'تم إلغاء الرحلة.'
        );
    }
}
