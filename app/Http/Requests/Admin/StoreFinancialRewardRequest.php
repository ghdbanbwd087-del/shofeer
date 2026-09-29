<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancialRewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique(
                    'financial_rewards',
                    'code'
                ),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999.99',
            ],

            'min_completed_trips' => [
                'required',
                'integer',
                'min:0',
                'max:100000',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:65535',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' =>
                $this->boolean(
                    'is_active'
                ),
        ]);
    }
}
