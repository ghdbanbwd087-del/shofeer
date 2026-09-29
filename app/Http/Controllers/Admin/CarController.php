<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\View\View;

class CarController extends Controller
{
    /**
     * جميع السيارات.
     */
    public function index(): View
    {
        $cars = Car::query()
            ->with([
                'driver.user',
            ])
            ->latest()
            ->paginate(20);

        return view(
            'admin.cars.index',
            compact('cars')
        );
    }

    /**
     * تفاصيل سيارة.
     */
    public function show(
        Car $car
    ): View {
        $car->load(
            'driver.user'
        );

        return view(
            'admin.cars.show',
            compact('car')
        );
    }
}
