<?php

namespace App\Http\Requests\Admin\Calculators;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        $calculator = $this->route('calculator');
        $calculatorId = $calculator?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'slug' => [
                'required',
                'string',
                'max:180',
                'alpha_dash',
                Rule::unique('calculators', 'slug')->ignore($calculatorId),
            ],

            'short_description' => [
                'required',
                'string',
                'max:1000',
            ],

            'icon' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],

            'display_order' => [
                'required',
                'integer',
                'min:0',
            ],

            'route_name' => [
                'nullable',
                'string',
                'max:180',
            ],

            'journey_ids' => [
                'nullable',
                'array',
            ],

            'journey_ids.*' => [
                'integer',
                'distinct',
                'exists:tool_journeys,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'short_description.required' =>
                'Please enter a short description for the calculator.',

            'slug.unique' =>
                'This calculator slug is already in use.',

            'journey_ids.*.exists' =>
                'One of the selected journeys does not exist.',
        ];
    }
}
