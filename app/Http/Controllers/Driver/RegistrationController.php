<?php

namespace App\Http\Controllers\Driver;

use App\Enums\DriverStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\RegisterDriverRequest;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * عرض طلب التوثيق وحالته.
     */
    public function create(): View
    {
        return view(
            'driver.register',
            [
                'driver' => request()
                    ->user()
                    ->driver,
            ]
        );
    }

    /**
     * إنشاء أو تحديث طلب التوثيق.
     */
    public function store(
        RegisterDriverRequest $request
    ): RedirectResponse {
        $user = $request->user();

        $existingDriver = $user->driver;

        $driver = DB::transaction(
            function () use (
                $request,
                $user,
                $existingDriver
            ): Driver {
                $data = $request->validated();

                /*
                |--------------------------------------------------------------------------
                | Identity Front
                |--------------------------------------------------------------------------
                */

                if (
                    $request->hasFile(
                        'id_image_front'
                    )
                ) {
                    if (
                        $existingDriver?->id_image_front
                    ) {
                        Storage::disk('local')
                            ->delete(
                                $existingDriver
                                    ->id_image_front
                            );
                    }

                    $data['id_image_front'] =
                        $request
                            ->file(
                                'id_image_front'
                            )
                            ->store(
                                'drivers/'.
                                $user->id.
                                '/identity',
                                'local'
                            );
                }

                /*
                |--------------------------------------------------------------------------
                | Identity Back
                |--------------------------------------------------------------------------
                */

                if (
                    $request->hasFile(
                        'id_image_back'
                    )
                ) {
                    if (
                        $existingDriver?->id_image_back
                    ) {
                        Storage::disk('local')
                            ->delete(
                                $existingDriver
                                    ->id_image_back
                            );
                    }

                    $data['id_image_back'] =
                        $request
                            ->file(
                                'id_image_back'
                            )
                            ->store(
                                'drivers/'.
                                $user->id.
                                '/identity',
                                'local'
                            );
                }

                /*
                |--------------------------------------------------------------------------
                | Driver License
                |--------------------------------------------------------------------------
                */

                if (
                    $request->hasFile(
                        'license_image'
                    )
                ) {
                    if (
                        $existingDriver?->license_image
                    ) {
                        Storage::disk('local')
                            ->delete(
                                $existingDriver
                                    ->license_image
                            );
                    }

                    $data['license_image'] =
                        $request
                            ->file(
                                'license_image'
                            )
                            ->store(
                                'drivers/'.
                                $user->id.
                                '/license',
                                'local'
                            );
                }

                /*
                 * أي تعديل على طلب مرفوض يعيده للمراجعة.
                 */
                $data['status'] =
                    DriverStatus::Pending;

                $data['rejection_reason'] =
                    null;

                $data['verified_at'] =
                    null;

                $data['verified_by'] =
                    null;

                return Driver::query()
                    ->updateOrCreate(
                        [
                            'user_id' => $user->id,
                        ],
                        $data
                    );
            }
        );

        return redirect()
            ->route(
                'driver.register'
            )
            ->with(
                'success',
                'تم إرسال طلب التوثيق بنجاح وهو الآن قيد المراجعة.'
            );
    }
}
