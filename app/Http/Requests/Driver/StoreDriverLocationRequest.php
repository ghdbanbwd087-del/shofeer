<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDriver()
            ?? false;
    }

    public function rules(): array
    {
        return [
            'trip_id' => [
                'required',
                'uuid',
                'exists:trips,id',
            ],

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'accuracy_m' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'speed_kmh' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'heading' => [
                'nullable',
                'integer',
                'between:0,359',
            ],

            'recorded_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}
