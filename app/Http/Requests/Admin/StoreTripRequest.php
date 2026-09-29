<?php

namespace App\Http\Requests\Admin;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTripRequest extends FormRequest
{
    /**
     * Admin فقط.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin()
            ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $activeCity = fn (
            Builder $query
        ) => $query->where(
            'is_active',
            true
        );

        return [
            'driver_id' => [
                'required',
                'uuid',

                Rule::exists(
                    'drivers',
                    'id'
                )->where(
                    fn ($query) => $query->where(
                        'status',
                        'approved'
                    )
                ),
            ],

            'car_id' => [
                'required',
                'uuid',

                Rule::exists(
                    'cars',
                    'id'
                )->where(
                    fn ($query) => $query->where(
                        'is_active',
                        true
                    )
                ),
            ],

            'from_city_id' => [
                'required',
                'uuid',

                Rule::exists(
                    'cities',
                    'id'
                )->where(
                    $activeCity
                ),
            ],

            'to_city_id' => [
                'required',
                'uuid',
                'different:from_city_id',

                Rule::exists(
                    'cities',
                    'id'
                )->where(
                    $activeCity
                ),
            ],

            'departure_at' => [
                'required',
                'date',
                'after:now',
            ],

            'meeting_point' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'destination_point' => [
                'nullable',
                'string',
                'max:255',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
            ],

            'seat_count' => [
                'required',
                'integer',
                'min:1',
                'max:60',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'is_published' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
