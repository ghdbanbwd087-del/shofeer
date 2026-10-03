<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mode' => [
                'required',
                'string',
                Rule::in([
                    'single',
                    'broadcast',
                ]),
            ],

            'user_id' => [
                'nullable',
                'required_if:mode,single',
                'uuid',
                Rule::exists('users', 'id')
                    ->where(
                        fn ($query) =>
                            $query->where(
                                'is_active',
                                true
                            )
                    ),
            ],

            'title' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],

            'message' => [
                'required',
                'string',
                'min:2',
                'max:2000',
            ],
        ];
    }
}
