<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DriverStatus;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriverController extends Controller
{
    /**
     * قائمة السائقين.
     */
    public function index(
        Request $request
    ): View {
        $query = Driver::query()
            ->with('user')
            ->latest();

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request
                    ->string('status')
                    ->toString()
            );
        }

        $drivers = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.drivers.index',
            [
                'drivers' => $drivers,

                'statuses' => DriverStatus::cases(),
            ]
        );
    }

    /**
     * تفاصيل ومراجعة السائق.
     */
    public function show(
        Driver $driver
    ): View {
        $driver->load([
            'user',
            'cars',
            'verifier',
        ]);

        return view(
            'admin.drivers.show',
            compact('driver')
        );
    }

    /**
     * عرض مستند حساس داخل منطقة الإدارة فقط.
     */
    public function document(
        Driver $driver,
        string $document
    ): StreamedResponse {
        $field = match ($document) {
            'id-front' => 'id_image_front',

            'id-back' => 'id_image_back',

            'license' => 'license_image',

            default => abort(404),
        };

        $path = $driver->{$field};

        abort_if(
            blank($path),
            404
        );

        abort_unless(
            Storage::disk('local')
                ->exists($path),
            404
        );

        return Storage::disk('local')
            ->response($path);
    }

    /**
     * اعتماد السائق.
     */
    public function approve(
        Request $request,
        Driver $driver
    ): RedirectResponse {
        $driver->update([
            'status' => DriverStatus::Approved,

            'rejection_reason' => null,

            'verified_at' => now(),

            'verified_by' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            'تم اعتماد السائق بنجاح.'
        );
    }

    /**
     * رفض السائق.
     */
    public function reject(
        Request $request,
        Driver $driver
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

        $driver->update([
            'status' => DriverStatus::Rejected,

            'rejection_reason' => $validated[
                    'rejection_reason'
                ],

            'verified_at' => null,

            'verified_by' => $request->user()->id,

            'is_online' => false,
        ]);

        return back()->with(
            'success',
            'تم رفض طلب السائق.'
        );
    }

    /**
     * إيقاف السائق.
     */
    public function suspend(
        Request $request,
        Driver $driver
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

        $driver->update([
            'status' => DriverStatus::Suspended,

            'rejection_reason' => $validated[
                    'rejection_reason'
                ],

            'verified_at' => null,

            'verified_by' => $request->user()->id,

            'is_online' => false,
        ]);

        return back()->with(
            'success',
            'تم إيقاف السائق.'
        );
    }
}
