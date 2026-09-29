<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TripRequestStatus;
use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveTripRequestRequest;
use App\Models\Car;
use App\Models\Trip;
use App\Models\TripRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TripRequestController extends Controller
{
    /**
     * عرض جميع طلبات الرحلات.
     */
    public function index(
        Request $request
    ): View {
        $query = TripRequest::query()
            ->with([
                'driver.user',
                'driver.cars',
                'fromCity',
                'toCity',
                'reviewer',
            ])
            ->latest();

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request
                    ->string('status')
                    ->toString()
            );
        }

        $tripRequests = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.trip-requests.index',
            compact('tripRequests')
        );
    }

    /**
     * اعتماد طلب الرحلة وإنشاء رحلة فعلية.
     */
    public function approve(
        ApproveTripRequestRequest $request,
        TripRequest $tripRequest
    ): RedirectResponse {
        $validated =
            $request->validated();

        DB::transaction(
            function () use (
                $request,
                $tripRequest,
                $validated
            ): void {
                /*
                 * قفل الطلب أثناء المعاملة لمنع اعتماده مرتين.
                 */
                $lockedRequest =
                    TripRequest::query()
                        ->whereKey(
                            $tripRequest->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (! $lockedRequest->isPending()) {
                    throw ValidationException::withMessages([
                        'trip_request' => 'تمت مراجعة هذا الطلب مسبقاً.',
                    ]);
                }

                /*
                 * السيارة يجب أن تكون مفعلة
                 * وتابعة للسائق صاحب الطلب.
                 */
                $car = Car::query()
                    ->whereKey(
                        $validated['car_id']
                    )
                    ->where(
                        'driver_id',
                        $lockedRequest->driver_id
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->first();

                if ($car === null) {
                    throw ValidationException::withMessages([
                        'car_id' => 'السيارة المحددة لا تتبع لهذا السائق أو غير مفعلة.',
                    ]);
                }

                /*
                 * عدد المقاعد المطلوبة لا يمكن
                 * أن يتجاوز سعة السيارة.
                 */
                if (
                    $lockedRequest->requested_seats
                    > $car->seat_count
                ) {
                    throw ValidationException::withMessages([
                        'car_id' => 'عدد المقاعد المطلوبة أكبر من سعة السيارة.',
                    ]);
                }

                /*
                 * دمج التاريخ والوقت في departure_at.
                 */
                $departureAt = Carbon::parse(
                    $lockedRequest
                        ->travel_date
                        ->format('Y-m-d')
                    .' '.
                    $lockedRequest
                        ->departure_time
                );

                if ($departureAt->lte(now())) {
                    throw ValidationException::withMessages([
                        'trip_request' => 'موعد الرحلة المطلوب أصبح في الماضي.',
                    ]);
                }

                /*
                 * إنشاء الرحلة الفعلية.
                 */
                Trip::query()->create([
                    'driver_id' => $lockedRequest->driver_id,

                    'car_id' => $car->id,

                    'source_trip_request_id' => $lockedRequest->id,

                    'from_city_id' => $lockedRequest->from_city_id,

                    'to_city_id' => $lockedRequest->to_city_id,

                    'departure_at' => $departureAt,

                    'meeting_point' => $validated['meeting_point'],

                    'destination_point' => $validated[
                            'destination_point'
                        ] ?? null,

                    'price' => $validated['price'],

                    'seat_count' => $lockedRequest
                        ->requested_seats,

                    'available_seats' => $lockedRequest
                        ->requested_seats,

                    'status' => TripStatus::Scheduled,

                    'is_published' => $request->boolean(
                        'is_published',
                        true
                    ),

                    'notes' => $validated['notes']
                        ?? null,

                    'created_by' => $request
                        ->user()
                        ->id,
                ]);

                /*
                 * إغلاق طلب الرحلة بعد نجاح الإنشاء.
                 */
                $lockedRequest->update([
                    'status' => TripRequestStatus::Approved,

                    'rejection_reason' => null,

                    'reviewed_by' => $request
                        ->user()
                        ->id,

                    'reviewed_at' => now(),
                ]);
            },
            3
        );

        return back()->with(
            'success',
            'تمت الموافقة على الطلب وإنشاء الرحلة.'
        );
    }

    /**
     * رفض طلب رحلة.
     */
    public function reject(
        Request $request,
        TripRequest $tripRequest
    ): RedirectResponse {
        $validated =
            $request->validate([
                'rejection_reason' => [
                    'required',
                    'string',
                    'min:5',
                    'max:2000',
                ],
            ]);

        DB::transaction(
            function () use (
                $request,
                $tripRequest,
                $validated
            ): void {
                /*
                 * قفل الطلب لمنع مراجعته بالتزامن.
                 */
                $lockedRequest =
                    TripRequest::query()
                        ->whereKey(
                            $tripRequest->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                if (! $lockedRequest->isPending()) {
                    throw ValidationException::withMessages([
                        'trip_request' => 'تمت مراجعة هذا الطلب مسبقاً.',
                    ]);
                }

                $lockedRequest->update([
                    'status' => TripRequestStatus::Rejected,

                    'rejection_reason' => $validated[
                            'rejection_reason'
                        ],

                    'reviewed_by' => $request
                        ->user()
                        ->id,

                    'reviewed_at' => now(),
                ]);
            },
            3
        );

        return back()->with(
            'success',
            'تم رفض طلب الرحلة.'
        );
    }
}
