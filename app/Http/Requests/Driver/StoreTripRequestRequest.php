<?php

namespace App\Http\Requests\Driver;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTripRequestRequest extends FormRequest
{
    /**
     * السائق الموثق فقط يستطيع طلب رحلة.
     */
    public function authorize(): bool
    {
        return $this->user()?->isDriver()
            && $this->user()?->driver?->isApproved();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $activeCity = fn (
            Builder $query
        ) => $query->where(
            'is_active',
            true
        );

        return [
            'from_city_id' => [
                'required',
                'uuid',

                Rule::exists(
                    'cities',
                    'id'
                )->where(
                    $activeCity
                ),
            ],

            'to_city_id' => [
                'required',
                'uuid',
                'different:from_city_id',

                Rule::exists(
                    'cities',
                    'id'
                )->where(
                    $activeCity
                ),
            ],

            'travel_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'departure_time' => [
                'required',
                'date_format:H:i',
            ],

            'requested_seats' => [
                'required',
                'integer',
                'min:1',
                'max:60',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'from_city_id.required' => 'مدينة الانطلاق مطلوبة.',

            'from_city_id.exists' => 'مدينة الانطلاق غير متاحة.',

            'to_city_id.required' => 'مدينة الوصول مطلوبة.',

            'to_city_id.exists' => 'مدينة الوصول غير متاحة.',

            'to_city_id.different' => 'مدينة الوصول يجب أن تختلف عن مدينة الانطلاق.',

            'travel_date.required' => 'تاريخ الرحلة مطلوب.',

            'travel_date.after_or_equal' => 'لا يمكن طلب رحلة بتاريخ سابق.',

            'departure_time.required' => 'وقت الانطلاق مطلوب.',

            'requested_seats.required' => 'عدد المقاعد مطلوب.',

            'requested_seats.min' => 'يجب أن يكون عدد المقاعد مقعداً واحداً على الأقل.',

            'requested_seats.max' => 'عدد المقاعد المطلوب غير صالح.',
        ];
    }
}
