<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\StoreCarRequest;
use App\Models\Car;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CarController extends Controller
{
    /**
     * سيارات السائق.
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

        return view(
            'driver.cars.index',
            [
                'cars' => $driver
                    ->cars()
                    ->latest()
                    ->get(),
            ]
        );
    }

    /**
     * صفحة إضافة سيارة.
     */
    public function create(): View
    {
        return view(
            'driver.cars.create'
        );
    }

    /**
     * تخزين السيارة.
     */
    public function store(
        StoreCarRequest $request
    ): RedirectResponse {
        $driver = $request
            ->user()
            ->driver;

        abort_if(
            $driver === null,
            403
        );

        $data = $request->validated();

        if (
            $request->hasFile('image')
        ) {
            $data['image'] = $request
                ->file('image')
                ->store(
                    'drivers/'.
                    $driver->id.
                    '/cars',
                    'local'
                );
        }

        if (
            $request->hasFile(
                'registration_image'
            )
        ) {
            $data['registration_image'] =
                $request
                    ->file(
                        'registration_image'
                    )
                    ->store(
                        'drivers/'.
                        $driver->id.
                        '/car-documents',
                        'local'
                    );
        }

        $driver
            ->cars()
            ->create($data);

        return redirect()
            ->route(
                'driver.cars.index'
            )
            ->with(
                'success',
                'تمت إضافة السيارة بنجاح.'
            );
    }

    /**
     * حذف سيارة.
     */
    public function destroy(
        Car $car
    ): RedirectResponse {
        $driver = request()
            ->user()
            ->driver;

        abort_unless(
            $driver !== null &&
            $car->driver_id === $driver->id,
            403
        );

        if ($car->image) {
            Storage::disk('local')
                ->delete($car->image);
        }

        if (
            $car->registration_image
        ) {
            Storage::disk('local')
                ->delete(
                    $car->registration_image
                );
        }

        $car->delete();

        return back()->with(
            'success',
            'تم حذف السيارة.'
        );
    }
}
