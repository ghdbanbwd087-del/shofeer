<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'description' => [
                'required',
                'string',
                'max:1500',
            ],

            'category' => [
                'required',
                'string',
                'max:100',
            ],

            'weight_kg' => [
                'required',
                'numeric',
                'min:0.01',
                'max:1000',
            ],

            'size' => [
                'required',
                'string',
                'max:100',
            ],

            'images' => [
                'nullable',
                'array',
                'max:4',
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'sender_name' => [
                'required',
                'string',
                'max:100',
            ],

            'sender_phone' => [
                'required',
                'string',
                'max:32',
            ],

            'sender_whatsapp' => [
                'nullable',
                'string',
                'max:32',
            ],

            'sender_city' => [
                'required',
                'string',
                'max:100',
            ],

            'recipient_name' => [
                'required',
                'string',
                'max:100',
            ],

            'recipient_phone' => [
                'required',
                'string',
                'max:32',
            ],

            'recipient_city' => [
                'required',
                'string',
                'max:100',
            ],

            'from_city' => [
                'required',
                'string',
                'max:100',
            ],

            'to_city' => [
                'required',
                'string',
                'max:100',
                'different:from_city',
            ],

            'requested_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
        ];
    }
}
