<?php

namespace App\Http\Requests\Admin;

use App\Enums\DriverStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'driver_id' => [
                'required',
                'uuid',
                Rule::exists(
                    'drivers',
                    'id'
                )->where(
                    fn ($query) =>
                        $query->where(
                            'status',
                            DriverStatus::Approved->value
                        )
                ),
            ],

            'trip_id' => [
                'nullable',
                'uuid',
                Rule::exists(
                    'trips',
                    'id'
                ),
            ],
        ];
    }
}
