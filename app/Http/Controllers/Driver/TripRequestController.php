<?php

namespace App\Http\Controllers\Driver;

use App\Enums\TripRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\StoreTripRequestRequest;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TripRequestController extends Controller
{
    /**
     * صفحة طلب رحلة.
     */
    public function create(): View
    {
        $driver = request()
            ->user()
            ->driver;

        abort_if(
            $driver === null,
            403
        );

        $cities = City::query()
            ->active()
            ->orderBy(
                'sort_order'
            )
            ->orderBy(
                'name_ar'
            )
            ->get();

        $recentRequests = $driver
            ->tripRequests()
            ->with([
                'fromCity',
                'toCity',
            ])
            ->latest()
            ->limit(10)
            ->get();

        return view(
            'driver.request-trip',
            compact(
                'cities',
                'recentRequests'
            )
        );
    }

    /**
     * إرسال طلب الرحلة للإدارة.
     */
    public function store(
        StoreTripRequestRequest $request
    ): RedirectResponse {
        $driver = $request
            ->user()
            ->driver;

        abort_if(
            $driver === null,
            403
        );

        $validated =
            $request->validated();

        /*
         * منع Duplicate Pending Request.
         */
        $alreadyExists = $driver
            ->tripRequests()
            ->where(
                'from_city_id',
                $validated[
                    'from_city_id'
                ]
            )
            ->where(
                'to_city_id',
                $validated[
                    'to_city_id'
                ]
            )
            ->whereDate(
                'travel_date',
                $validated[
                    'travel_date'
                ]
            )
            ->where(
                'departure_time',
                $validated[
                    'departure_time'
                ]
            )
            ->where(
                'status',
                TripRequestStatus::Pending
                    ->value
            )
            ->exists();

        if ($alreadyExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'trip_request' => 'يوجد لديك طلب مطابق قيد المراجعة بالفعل.',
                ]);
        }

        $driver
            ->tripRequests()
            ->create([
                ...$validated,

                'status' => TripRequestStatus::Pending,
            ]);

        return redirect()
            ->route(
                'driver.request-trip'
            )
            ->with(
                'success',
                'تم إرسال طلب الرحلة للإدارة بنجاح.'
            );
    }
}
