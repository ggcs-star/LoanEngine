<?php

namespace App\Http\Requests\Admin\Calculators;

use App\Support\Calculators\CalculatorFormulaRegistry;
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
                'max:120',
            ],

            'slug' => [
                'required',
                'string',
                'max:140',
                'alpha_dash',
                Rule::unique('calculators', 'slug')->ignore($calculatorId),
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:200',
            ],

            'icon' => [
                'nullable',
                'string',
                'max:60',
            ],

            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],

            'display_order' => [
                'required',
                'integer',
                'min:0',
                'max:4294967295',
            ],

            'route_name' => [
                'nullable',
                'string',
                'max:180',
            ],

            'formula_type' => [
                'required',
                'string',
                Rule::in(array_keys(CalculatorFormulaRegistry::all())),
            ],

            'journey_ids' => [
                'nullable',
                'array',
            ],

            'journey_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:tool_journeys,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the calculator name.',
            'slug.required' => 'Please enter a calculator slug.',
            'slug.unique' => 'This calculator slug is already in use.',
            'short_description.required' => 'Please enter a short description.',
            'formula_type.required' => 'Please select a formula type.',
            'formula_type.in' => 'The selected formula type is not supported.',
            'journey_ids.*.exists' => 'One of the selected journeys does not exist.',
        ];
    }
}
