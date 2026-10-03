<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateDriverPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->isDriver();
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
                'current_password:web',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(
                    (int) config(
                        'security.password_min_length',
                        12
                    )
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' =>
                'أدخل كلمة المرور الحالية.',

            'current_password.current_password' =>
                'كلمة المرور الحالية غير صحيحة.',

            'password.required' =>
                'أدخل كلمة المرور الجديدة.',

            'password.confirmed' =>
                'تأكيد كلمة المرور الجديدة غير مطابق.',

            'password.min' =>
                'يجب ألا تقل كلمة المرور الجديدة عن :min حرفًا.',
        ];
    }
}
