<?php

namespace App\Http\Requests\Api\V1\User\Calendar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCalendarEventStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_status' => [
                'required',
                Rule::in(['projected', 'cleared']),
            ],
        ];
    }
}
