<?php

namespace App\Http\Requests\Passenger;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class CancelBookingRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    public function authorize(): bool
    {
        $booking =
            $this->route(
                'booking'
            );

        if (
            ! $booking instanceof Booking
        ) {
            return false;
        }

        $user =
            $this->user();

        if (! $user) {
            return false;
        }

        return $user->can(
            'cancel',
            $booking
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        return [
            'cancel_reason' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [
            'cancel_reason.max' => 'سبب الإلغاء يجب ألا يتجاوز 500 حرف.',
        ];
    }
}
