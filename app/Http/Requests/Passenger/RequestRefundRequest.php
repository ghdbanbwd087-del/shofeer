<?php

namespace App\Http\Requests\Passenger;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class RequestRefundRequest extends FormRequest
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

        $user =
            $this->user();

        if (
            ! $booking instanceof Booking
            || ! $user
        ) {
            return false;
        }

        /*
         * الراكب يستطيع طلب Refund
         * لحجزه فقط.
         */
        return (string) $booking->user_id
            === (string) $user->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        return [
            'reason' => [
                'required',
                'string',
                'min:5',
                'max:1000',
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
            'reason.required' => 'يرجى كتابة سبب طلب الاسترداد.',

            'reason.min' => 'سبب الاسترداد يجب أن يكون واضحًا.',

            'reason.max' => 'سبب الاسترداد يجب ألا يتجاوز 1000 حرف.',
        ];
    }
}
