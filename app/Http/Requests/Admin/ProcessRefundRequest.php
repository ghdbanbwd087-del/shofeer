<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProcessRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'admin_reference' => [
                'required',
                'string',
                'min:3',
                'max:191',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_reference.required' => 'أدخل مرجع عملية الاسترداد.',
            'admin_reference.min' => 'مرجع الاسترداد قصير جداً.',
            'admin_reference.max' => 'مرجع الاسترداد يجب ألا يتجاوز 191 حرفاً.',
        ];
    }
}
