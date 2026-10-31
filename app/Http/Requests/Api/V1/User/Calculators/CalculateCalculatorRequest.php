<?php

namespace App\Http\Requests\Api\V1\User\Calculators;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculateCalculatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return match ($this->route('calculator')?->formula_type) {
            'home_affordability' => $this->homeAffordabilityRules(),
            'home_loan_emi' => $this->homeLoanEmiRules(),
            'home_loan_eligibility' => $this->homeLoanEligibilityRules(),
            'emi_vs_tenure' => $this->emiVsTenureRules(),
            'stamp_duty' => $this->stampDutyRules(),
            'interest_rate' => $this->interestRateRules(),
            'loan_prepayment' => $this->loanPrepaymentRules(),
            default => [],
        };
    }

    private function homeAffordabilityRules(): array
    {
        return [
            'property_price' => ['required', 'numeric', 'min:1000000', 'max:50000000'],
            'down_payment_percent' => ['required', 'numeric', 'min:10', 'max:90'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:30'],
            'tenure_years' => ['required', 'integer', 'min:1', 'max:40'],
            'monthly_rent' => ['required', 'numeric', 'min:5000', 'max:150000'],
            'appreciation_rate' => ['required', 'numeric', 'min:-10', 'max:20'],
            'rent_increase_rate' => ['required', 'numeric', 'min:-10', 'max:20'],
            'investment_return' => ['required', 'numeric', 'min:-10', 'max:30'],
            'inflation_rate' => ['required', 'numeric', 'min:-10', 'max:30'],
            'maintenance_percent' => ['required', 'numeric', 'min:0', 'max:10'],
            'analysis_years' => ['required', 'integer', 'min:5', 'max:30'],
        ];
    }

    private function homeLoanEmiRules(): array
    {
        $loanType = $this->input('loan_type', 'home');

        return [
            'loan_type' => ['required', Rule::in(['home', 'personal'])],
            'loan_amount' => [
                'required',
                'numeric',
                'min:100000',
                'max:' . ($loanType === 'personal' ? 5000000 : 50000000),
            ],
            'interest_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:' . ($loanType === 'personal' ? 35 : 20),
            ],
            'tenure_months' => [
                'required',
                'integer',
                'min:1',
                'max:' . ($loanType === 'personal' ? 84 : 360),
            ],
            'processing_fee' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
        ];
    }

    private function homeLoanEligibilityRules(): array
    {
        $loanType = $this->input('loan_type', 'home');

        return [
            'loan_type' => ['required', Rule::in(['home', 'personal', 'car'])],
            'monthly_income' => ['required', 'numeric', 'min:10000', 'max:100000000'],
            'existing_emi' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'property_value' => ['required', 'numeric', 'min:100000', 'max:500000000'],
            'ltv_percent' => ['required', 'numeric', 'min:10', 'max:90'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:35'],
            'tenure_years' => [
                'required',
                'integer',
                'min:1',
                'max:' . match ($loanType) {
                    'personal' => 7,
                    'car' => 8,
                    default => 30,
                },
            ],
            'calculation_method' => [
                'required',
                Rule::in(['standard', 'conservative', 'aggressive']),
            ],
        ];
    }

    private function emiVsTenureRules(): array
    {
        return [
            'loan_amount' => ['required', 'numeric', 'min:1000000', 'max:10000000'],
            'interest_rate' => ['required', 'numeric', 'min:5', 'max:15'],
            'tenure_years' => ['required', 'integer', Rule::in([10, 15, 20, 25, 30])],
        ];
    }

    private function stampDutyRules(): array
    {
        $states = [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar',
            'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh',
            'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra',
            'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
            'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
            'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
            'Andaman and Nicobar Islands', 'Chandigarh',
            'Dadra and Nagar Haveli and Daman and Diu', 'Delhi',
            'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry',
        ];

        return [
            'property_value' => ['required', 'numeric', 'min:100000', 'max:1000000000'],
            'state' => ['required', 'string', Rule::in($states)],
            'property_type' => [
                'required',
                Rule::in(['residential_flat', 'own_land', 'pent_house']),
            ],
            'buyer_category' => [
                'required',
                Rule::in(['male', 'female', 'joint', 'senior', 'first_time']),
            ],
            'stamp_duty_method' => [
                'required',
                Rule::in(['percentage', 'slab']),
            ],
            'stamp_duty_rate' => [
                'required_if:stamp_duty_method,percentage',
                'nullable',
                'numeric',
                'min:0',
                'max:20',
            ],
            'registration_type' => [
                'required',
                Rule::in(['fixed', 'percentage']),
            ],
            'registration_value' => [
                'required',
                'numeric',
                'min:0',
                'max:100000000',
            ],
            'other_charges' => [
                'required',
                'numeric',
                'min:0',
                'max:100000000',
            ],
        ];
    }

    private function interestRateRules(): array
    {
        $tenureUnit = $this->input('tenure_unit');

        return [
            'monthly_emi' => ['required', 'numeric', 'min:1000', 'max:1000000'],
            'loan_amount' => ['required', 'numeric', 'min:50000', 'max:100000000'],
            'tenure_value' => [
                'required',
                'integer',
                'min:1',
                'max:' . ($tenureUnit === 'months' ? 360 : 30),
            ],
            'tenure_unit' => ['required', Rule::in(['years', 'months'])],
        ];
    }

    private function loanPrepaymentRules(): array
    {
        return [
            'outstanding_loan' => ['required', 'numeric', 'min:10000', 'max:500000000'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:35'],
            'remaining_tenure' => ['required', 'integer', 'min:1', 'max:600'],
            'prepayment_strategy' => [
                'required',
                Rule::in(['one_time', 'monthly']),
            ],
            'prepayment_amount' => ['required', 'numeric', 'min:1', 'max:500000000'],
            'strategy_goal' => [
                'required',
                Rule::in(['reduce_tenure', 'reduce_emi']),
            ],
        ];
    }
}