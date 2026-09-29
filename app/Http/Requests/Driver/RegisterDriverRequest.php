<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class RegisterDriverRequest extends FormRequest
{
    /**
     * فقط مستخدم Driver.
     */
    public function authorize(): bool
    {
        return $this->user()?->isDriver()
            ?? false;
    }

    /**
     * قواعد طلب توثيق السائق.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'national_id' => [
                'required',
                'string',
                'min:5',
                'max:100',

                Rule::unique(
                    'drivers',
                    'national_id'
                )->ignore(
                    $this->user()?->driver?->id
                ),
            ],

            'license_number' => [
                'required',
                'string',
                'min:3',
                'max:100',

                Rule::unique(
                    'drivers',
                    'license_number'
                )->ignore(
                    $this->user()?->driver?->id
                ),
            ],

            'id_image_front' => [
                $this->user()?->driver
                    ? 'nullable'
                    : 'required',

                File::image()
                    ->max('5mb'),
            ],

            'id_image_back' => [
                $this->user()?->driver
                    ? 'nullable'
                    : 'required',

                File::image()
                    ->max('5mb'),
            ],

            'license_image' => [
                $this->user()?->driver
                    ? 'nullable'
                    : 'required',

                File::image()
                    ->max('5mb'),
            ],

            'license_expiry' => [
                'required',
                'date',
                'after:today',
            ],

            'experience_years' => [
                'required',
                'integer',
                'min:0',
                'max:70',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * رسائل عربية.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'national_id.required' => 'رقم الهوية مطلوب.',

            'national_id.unique' => 'رقم الهوية مستخدم مسبقاً.',

            'license_number.required' => 'رقم الرخصة مطلوب.',

            'license_number.unique' => 'رقم الرخصة مستخدم مسبقاً.',

            'id_image_front.required' => 'صورة الوجه الأمامي للهوية مطلوبة.',

            'id_image_back.required' => 'صورة الوجه الخلفي للهوية مطلوبة.',

            'license_image.required' => 'صورة رخصة القيادة مطلوبة.',

            'license_expiry.required' => 'تاريخ انتهاء الرخصة مطلوب.',

            'license_expiry.after' => 'يجب أن تكون الرخصة سارية المفعول.',

            'experience_years.required' => 'عدد سنوات الخبرة مطلوب.',

            'experience_years.integer' => 'سنوات الخبرة يجب أن تكون رقماً.',
        ];
    }
}
