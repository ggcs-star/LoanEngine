<?php

namespace App\Http\Requests\Api\V1\User\Calendar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:180'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999'],
            'category' => [
                'required',
                Rule::in([
                    'income',
                    'investment',
                    'loan_emi',
                    'fixed_bill',
                    'living_expense',
                ]),
            ],
            'event_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:2000-01-01',
                'before_or_equal:2100-12-31',
            ],
            'reminder_time' => ['nullable', 'date_format:H:i'],
            'payment_status' => [
                'required',
                Rule::in(['projected', 'cleared']),
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_recurring' => ['sometimes', 'boolean'],
            'recurrence_frequency' => [
                'nullable',
                Rule::in(['weekly', 'monthly', 'yearly']),
                'required_if:is_recurring,1',
            ],
            'recurrence_end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:event_date',
                'before_or_equal:2100-12-31',
                'required_if:is_recurring,1',
            ],
        ];
    }
}
