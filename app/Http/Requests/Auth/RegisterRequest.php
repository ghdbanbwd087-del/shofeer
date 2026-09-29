<?php

namespace App\Http\Requests\Auth;

use App\Services\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * السماح بالطلب.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * تجهيز البيانات قبل Validation.
     */
    protected function prepareForValidation(): void
    {
        $normalizer = app(
            PhoneNormalizer::class
        );

        $this->merge([
            'name' => is_string(
                $this->input('name')
            )
                ? trim(
                    $this->input('name')
                )
                : $this->input('name'),

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
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'phone' => [
                'required',
                'string',
                'max:32',

                'regex:/^\+[1-9]\d{7,14}$/',

                Rule::unique(
                    'users',
                    'phone'
                ),
            ],

            'password' => [
                'required',
                'string',
                'confirmed',

                Password::min(
                    (int) config(
                        'auth.password_min_length',
                        12
                    )
                ),
            ],

            'terms' => [
                'required',
                'accepted',
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
            'name.required' => 'الاسم مطلوب.',

            'name.string' => 'الاسم غير صالح.',

            'name.min' => 'يجب ألا يقل الاسم عن حرفين.',

            'name.max' => 'يجب ألا يزيد الاسم عن 100 حرف.',

            'phone.required' => 'رقم الجوال مطلوب.',

            'phone.string' => 'رقم الجوال غير صالح.',

            'phone.max' => 'رقم الجوال غير صالح.',

            'phone.regex' => 'يرجى إدخال رقم جوال يمني أو سعودي صحيح.',

            'phone.unique' => 'رقم الجوال مسجل مسبقاً.',

            'password.required' => 'كلمة المرور مطلوبة.',

            'password.string' => 'كلمة المرور غير صالحة.',

            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',

            'password.min' => 'يجب ألا تقل كلمة المرور عن :min حرفاً.',

            'terms.required' => 'يجب الموافقة على الشروط والأحكام.',

            'terms.accepted' => 'يجب الموافقة على الشروط والأحكام.',
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
            'name' => 'الاسم',

            'phone' => 'رقم الجوال',

            'password' => 'كلمة المرور',

            'password_confirmation' => 'تأكيد كلمة المرور',

            'terms' => 'الشروط والأحكام',
        ];
    }
}
