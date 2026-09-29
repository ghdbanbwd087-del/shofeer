<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HoldSeatRequest extends FormRequest
{
    /**
     * الراكب فقط.
     */
    public function authorize(): bool
    {
        return $this->user()?->isPassenger()
            ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'seat_number' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'passenger_gender' => [
                'required',

                Rule::in([
                    'male',
                    'female',
                ]),
            ],

            /*
             * تستخدم فقط عندما يكون رجل
             * بجوار راكبة من أقاربه.
             */
            'family_relation' => [
                'nullable',
                'string',

                Rule::in([
                    'husband',
                    'brother',
                    'father',
                    'son',
                    'paternal_uncle',
                    'maternal_uncle',
                ]),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'seat_number.required' => 'اختر المقعد أولاً.',

            'seat_number.integer' => 'رقم المقعد غير صالح.',

            'passenger_gender.required' => 'حدد جنس الراكب.',

            'passenger_gender.in' => 'قيمة الجنس غير صالحة.',

            'family_relation.in' => 'صلة القرابة المحددة غير صالحة.',
        ];
    }
}
