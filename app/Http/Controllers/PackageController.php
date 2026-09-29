<?php

namespace App\Http\Controllers;

use App\Enums\PackageStatus;
use App\Http\Requests\Package\StorePackageRequest;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function create(
        Request $request
    ): View {
        return view(
            'packages.create',
            [
                'user' => $request->user(),
            ]
        );
    }

    public function store(
        StorePackageRequest $request
    ): RedirectResponse {
        $data =
            $request->validated();

        $package =
            Package::query()->create([
                'user_id' =>
                    $request->user()->id,

                'tracking_code' =>
                    $this->makeTrackingCode(),

                'description' =>
                    $data['description'],

                'category' =>
                    $data['category'],

                'weight_kg' =>
                    $data['weight_kg'],

                'size' =>
                    $data['size'],

                'image_paths' =>
                    null,

                'sender_name' =>
                    $data['sender_name'],

                'sender_phone' =>
                    $data['sender_phone'],

                'sender_whatsapp' =>
                    $data['sender_whatsapp']
                    ?? null,

                'sender_city' =>
                    $data['sender_city'],

                'recipient_name' =>
                    $data['recipient_name'],

                'recipient_phone' =>
                    $data['recipient_phone'],

                'recipient_city' =>
                    $data['recipient_city'],

                'from_city' =>
                    $data['from_city'],

                'to_city' =>
                    $data['to_city'],

                'requested_date' =>
                    $data['requested_date'],

                'assigned_driver_id' =>
                    null,

                'trip_id' =>
                    null,

                'status' =>
                    PackageStatus::Received,
            ]);

        $paths = [];

        foreach (
            $request->file(
                'images',
                []
            ) as $image
        ) {
            $paths[] =
                $image->store(
                    'packages/'.
                    $package->id,
                    'local'
                );
        }

        if ($paths !== []) {
            $package->update([
                'image_paths' => $paths,
            ]);
        }

        return redirect()
            ->route(
                'dashboard.packages.show',
                $package
            )
            ->with(
                'success',
                'تم تسجيل البضاعة بنجاح. كود التتبع: '.
                $package->tracking_code
            );
    }

    public function index(
        Request $request
    ): View {
        $tab =
            $request
                ->string(
                    'tab'
                )
                ->toString();

        if (
            ! in_array(
                $tab,
                [
                    'active',
                    'delivered',
                    'cancelled',
                ],
                true
            )
        ) {
            $tab = 'active';
        }

        $query =
            Package::query()
                ->where(
                    'user_id',
                    $request->user()->id
                )
                ->latest();

        if ($tab === 'active') {
            $query->whereIn(
                'status',
                [
                    PackageStatus::Received->value,
                    PackageStatus::Assigned->value,
                    PackageStatus::InTransit->value,
                ]
            );
        }

        if ($tab === 'delivered') {
            $query->where(
                'status',
                PackageStatus::Delivered->value
            );
        }

        if ($tab === 'cancelled') {
            $query->where(
                'status',
                PackageStatus::Cancelled->value
            );
        }

        $packages =
            $query
                ->paginate(15)
                ->withQueryString();

        $counts = [
            'active' =>
                Package::query()
                    ->where(
                        'user_id',
                        $request->user()->id
                    )
                    ->whereIn(
                        'status',
                        [
                            PackageStatus::Received->value,
                            PackageStatus::Assigned->value,
                            PackageStatus::InTransit->value,
                        ]
                    )
                    ->count(),

            'delivered' =>
                Package::query()
                    ->where(
                        'user_id',
                        $request->user()->id
                    )
                    ->where(
                        'status',
                        PackageStatus::Delivered->value
                    )
                    ->count(),

            'cancelled' =>
                Package::query()
                    ->where(
                        'user_id',
                        $request->user()->id
                    )
                    ->where(
                        'status',
                        PackageStatus::Cancelled->value
                    )
                    ->count(),
        ];

        return view(
            'passenger.packages.index',
            compact(
                'packages',
                'tab',
                'counts'
            )
        );
    }

    public function show(
        Package $package
    ): View {
        Gate::authorize(
            'view',
            $package
        );

        $package->load([
            'driver.user',
            'trip',
        ]);

        return view(
            'passenger.packages.show',
            compact(
                'package'
            )
        );
    }

    public function cancel(
        Request $request,
        Package $package
    ): RedirectResponse {
        Gate::authorize(
            'cancel',
            $package
        );

        $package->forceFill([
            'status' =>
                PackageStatus::Cancelled,

            'cancel_reason' =>
                'ألغيت بواسطة صاحب الطلب.',

            'cancelled_at' =>
                now(),
        ])->save();

        return redirect()
            ->route(
                'dashboard.packages.show',
                $package
            )
            ->with(
                'success',
                'تم إلغاء طلب البضاعة.'
            );
    }

    public function track(
        Request $request
    ): View {
        $code =
            Str::upper(
                trim(
                    $request
                        ->string(
                            'code'
                        )
                        ->toString()
                )
            );

        $package = null;

        if ($code !== '') {
            $package =
                Package::query()
                    ->where(
                        'tracking_code',
                        $code
                    )
                    ->first();
        }

        return view(
            'packages.track',
            [
                'code' => $code,
                'package' => $package,
            ]
        );
    }

    private function makeTrackingCode(): string
    {
        do {
            $code =
                'SHF-'.
                Str::upper(
                    Str::random(
                        10
                    )
                );
        } while (
            Package::query()
                ->where(
                    'tracking_code',
                    $code
                )
                ->exists()
        );

        return $code;
    }
}
