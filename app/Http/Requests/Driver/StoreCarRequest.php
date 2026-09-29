<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreCarRequest extends FormRequest
{
    /**
     * فقط Driver لديه ملف Driver.
     */
    public function authorize(): bool
    {
        return $this->user()?->isDriver()
            && $this->user()?->driver !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $carId = $this->route('car')?->id;

        return [
            'make' => [
                'required',
                'string',
                'max:100',
            ],

            'model' => [
                'required',
                'string',
                'max:100',
            ],

            'year' => [
                'required',
                'integer',
                'min:1990',
                'max:'.(now()->year + 1),
            ],

            'plate_number' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'cars',
                    'plate_number'
                )->ignore($carId),
            ],

            'color' => [
                'nullable',
                'string',
                'max:50',
            ],

            'seat_count' => [
                'required',
                'integer',
                'min:1',
                'max:60',
            ],

            'type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'features' => [
                'nullable',
                'array',
                'max:20',
            ],

            'features.*' => [
                'string',
                'max:100',
            ],

            'image' => [
                'nullable',

                File::image()
                    ->max('5mb'),
            ],

            'registration_image' => [
                'nullable',

                File::image()
                    ->max('5mb'),
            ],
        ];
    }
}
