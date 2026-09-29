<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveTripRequestRequest extends FormRequest
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
        return [
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

            'price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
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
