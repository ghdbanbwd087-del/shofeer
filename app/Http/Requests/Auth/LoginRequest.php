<?php

namespace App\Http\Requests\Auth;

use App\Services\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * السماح بالطلب.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * توحيد رقم الهاتف.
     */
    protected function prepareForValidation(): void
    {
        $normalizer = app(
            PhoneNormalizer::class
        );

        $this->merge([
            'phone' => $normalizer->normalize(
                is_string(
                    $this->input('phone')
                )
                    ? $this->input('phone')
                    : null
            ),
        ]);
    }

    /**
     * قواعد تسجيل الدخول.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'max:32',
                'regex:/^\+[1-9]\d{7,14}$/',
            ],

            'password' => [
                'required',
                'string',
            ],

            'remember' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * رسائل الأخطاء.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'رقم الجوال مطلوب.',

            'phone.string' => 'رقم الجوال غير صالح.',

            'phone.max' => 'رقم الجوال غير صالح.',

            'phone.regex' => 'يرجى إدخال رقم جوال صحيح.',

            'password.required' => 'كلمة المرور مطلوبة.',

            'password.string' => 'كلمة المرور غير صالحة.',

            'remember.boolean' => 'قيمة تذكرني غير صالحة.',
        ];
    }

    /**
     * أسماء الحقول.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'phone' => 'رقم الجوال',

            'password' => 'كلمة المرور',

            'remember' => 'تذكرني',
        ];
    }
}
