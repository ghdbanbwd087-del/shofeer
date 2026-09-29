<?php

namespace App\Http\Requests\Booking;

use App\Enums\PaymentMethod;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking =
            $this->route('booking');

        return $booking instanceof Booking
            && $this->user()?->isPassenger()
            && $booking->user_id ===
                $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Booking $booking */
        $booking =
            $this->route('booking');

        return [
            'passenger_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'passenger_phone' => [
                'required',
                'string',
                'max:32',
            ],

            'passenger_id' => [
                'required',
                'string',
                'max:100',
            ],

            /*
             * لا نسمح بتغيير الجنس بعد
             * اختيار المقعد لتجنب تجاوز
             * قواعد المقاعد.
             */
            'passenger_gender' => [
                'required',

                Rule::in([
                    $booking
                        ->passenger_gender
                        ->value,
                ]),
            ],

            'passenger_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'whatsapp_same' => [
                'nullable',
                'boolean',
            ],

            'passenger_whatsapp' => [
                'nullable',
                'string',
                'max:32',
                'required_unless:whatsapp_same,1',
            ],

            'payment_method' => [
                'required',

                Rule::enum(
                    PaymentMethod::class
                ),
            ],

            'transaction_number' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],

            'payment_proof' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],

            'terms' => [
                'accepted',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'passenger_name.required' => 'اسم الراكب مطلوب.',

            'passenger_phone.required' => 'رقم الجوال مطلوب.',

            'passenger_id.required' => 'رقم الهوية مطلوب.',

            'passenger_gender.in' => 'لا يمكن تغيير جنس الراكب بعد اختيار المقعد.',

            'passenger_whatsapp.required_unless' => 'أدخل رقم واتساب.',

            'payment_method.required' => 'اختر وسيلة الدفع.',

            'transaction_number.required' => 'رقم العملية مطلوب.',

            'payment_proof.required' => 'إثبات الدفع مطلوب.',

            'payment_proof.mimes' => 'صيغة إثبات الدفع غير مدعومة.',

            'payment_proof.max' => 'حجم إثبات الدفع يجب ألا يتجاوز 5MB.',

            'terms.accepted' => 'يجب الموافقة على الشروط.',
        ];
    }
}
