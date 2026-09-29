<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'اكتب سبب رفض طلب الاسترداد.',
            'rejection_reason.min' => 'سبب الرفض يجب أن يكون واضحاً.',
            'rejection_reason.max' => 'سبب الرفض يجب ألا يتجاوز 1000 حرف.',
        ];
    }
}
