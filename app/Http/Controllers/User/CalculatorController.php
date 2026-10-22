<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use App\Services\Calculators\HomeAffordabilityCalculator;
use App\Services\Calculators\HomeLoanEmiCalculator;
use App\Services\Calculators\HomeLoanEligibilityCalculator;
use App\Services\Calculators\StampDutyCalculator;
use App\Services\Calculators\LoanPrepaymentCalculator;
use App\Services\Calculators\InterestRateCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    private const HOME_AFFORDABILITY = 'home-affordability-calculator';
    private const HOME_LOAN_EMI = 'home-loan-emi-calculator';

    private function defaults(): array
    {
        return [
            'property_price' => 10000000,
            'down_payment_percent' => 40,
            'interest_rate' => 6.9,
            'tenure_years' => 20,
            'monthly_rent' => 15000,
            'appreciation_rate' => 6,
            'rent_increase_rate' => 7,
            'investment_return' => 12,
            'inflation_rate' => 6,
            'maintenance_percent' => 1,
            'analysis_years' => 15,
        ];
    }

    private function emiDefaults(): array
    {
        return [
            'loan_type' => 'home',
            'loan_amount' => 5000000,
            'interest_rate' => 8.5,
            'tenure_months' => 240,
            'processing_fee' => 10000,
        ];
    }

    
    public function show(
        Tool $tool,
        HomeLoanEmiCalculator $emiCalculator
    ): View|RedirectResponse {
        abort_unless($tool->isActive(), 404);

        if ($tool->slug === 'home-loan-emi-calculator') {
            $inputs = $this->emiDefaults();

            return view('user.calculators.emi', [
                'tool' => $tool,
                'inputs' => $inputs,
                'result' => null,
            ]);
        }

        if ($tool->slug === 'home-affordability-calculator') {
            return view('user.calculators.show', [
                'tool' => $tool,
                'inputs' => $this->defaults(),
                'result' => null,
            ]);
        }
        if ($tool->slug === 'home-loan-eligibility-calculator') {
            return view('user.calculators.eligibility', [
                'tool' => $tool,
                'inputs' => [
                    'monthly_income' => 100000,
                    'existing_emi' => 0,
                    'property_value' => 7500000,
                    'down_payment' => 1500000,
                    'foir_percent' => 50,
                    'ltv_percent' => 80,
                    'interest_rate' => 8.5,
                    'tenure_years' => 20,
                ],
                'result' => null,
            ]);
        }
        if ($tool->slug === 'stamp-duty-calculator') {
            return view('user.calculators.stamp-duty', [
                'tool' => $tool,
                'inputs' => [
                    'property_value' => 5000000,
                    'state' => 'Gujarat',
                    'property_type' => 'residential_flat',
                    'buyer_category' => 'male',
                    'stamp_duty_method' => 'percentage',
                    'stamp_duty_rate' => 5,
                    'registration_type' => 'fixed',
                    'registration_value' => 30000,
                    'other_charges' => 15000,
                ],
                'result' => null,
            ]);
        }
        if ($tool->slug === 'interest-rate-calculator') {
            return view('user.calculators.interest-rate', [
                'tool' => $tool,
                'inputs' => [
                    'monthly_emi' => 20000,
                    'loan_amount' => 400000,
                    'tenure_value' => 2,
                    'tenure_unit' => 'years',
                ],
                'result' => null,
            ]);
        }
        if (in_array($tool->slug, [
            'prepayment-calculator',
            'loan-prepayment-calculator',
        ], true)) {
            return view('user.calculators.prepayment', [
                'tool' => $tool,
                'inputs' => [
                    'outstanding_loan' => 22500000,
                    'interest_rate' => 8.55,
                    'remaining_tenure' => 215,
                    'prepayment_strategy' => 'one_time',
                    'prepayment_amount' => 500000,
                    'strategy_goal' => 'reduce_tenure',
                ],
                'result' => null,
            ]);
        }

        return redirect()
            ->route('user.tools.show', $tool->slug)
            ->with(
                'info',
                'This calculator needs its own calculation logic.'
            );
    }


    public function calculate(
        Request $request,
        Tool $tool,
        HomeAffordabilityCalculator $calculator,
        HomeLoanEmiCalculator $emiCalculator,
        HomeLoanEligibilityCalculator $eligibilityCalculator,
        StampDutyCalculator $stampDutyCalculator,
        InterestRateCalculator $interestRateCalculator,
        LoanPrepaymentCalculator $prepaymentCalculator
    ): View|RedirectResponse {
        abort_unless($tool->isActive(), 404);

        if ($tool->slug === self::HOME_LOAN_EMI) {
            $validated = $request->validate([
                'loan_type' => ['required', 'in:home,personal'],

                'loan_amount' => [
                    'required',
                    'numeric',
                    'min:100000',
                    'max:' . ($request->input('loan_type') === 'personal'
                        ? 5000000
                        : 50000000),
                ],

                'interest_rate' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:' . ($request->input('loan_type') === 'personal'
                        ? 35
                        : 20),
                ],

                'tenure_months' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:' . ($request->input('loan_type') === 'personal'
                        ? 84
                        : 360),
                ],

                // Processing fee is optional.
                'processing_fee' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:1000000',
                ],
            ]);

           $inputs = array_merge($this->emiDefaults(), $validated);

            $inputs['processing_fee'] = (float) (
                $validated['processing_fee'] ?? 0
            );

            $result = $emiCalculator->calculate($inputs);

            return view('user.calculators.emi', compact(
                'tool',
                'inputs',
                'result'
            ));
        }

        if ($tool->slug === self::HOME_AFFORDABILITY) {
            $validated = $request->validate([
                'property_price' => [
                    'required', 'numeric', 'min:1000000', 'max:50000000',
                ],
                'down_payment_percent' => [
                    'required', 'numeric', 'min:10', 'max:90',
                ],
                'interest_rate' => [
                    'required', 'numeric', 'min:0', 'max:30',
                ],
                'tenure_years' => [
                    'required', 'integer', 'min:1', 'max:40',
                ],
                'monthly_rent' => [
                    'required', 'numeric', 'min:5000', 'max:150000',
                ],
                'appreciation_rate' => [
                    'required', 'numeric', 'min:-10', 'max:20',
                ],
                'rent_increase_rate' => [
                    'required', 'numeric', 'min:-10', 'max:20',
                ],
                'investment_return' => [
                    'required', 'numeric', 'min:-10', 'max:30',
                ],
                'inflation_rate' => [
                    'required', 'numeric', 'min:-10', 'max:30',
                ],
                'maintenance_percent' => [
                    'required', 'numeric', 'min:0', 'max:10',
                ],
                'analysis_years' => [
                    'required', 'integer', 'min:5', 'max:30',
                ],
            ]);
            

            $inputs = array_merge($this->defaults(), $validated);
            $result = $calculator->calculate($inputs);

            return view('user.calculators.show', compact(
                'tool',
                'inputs',
                'result'
            ));
        }
        
        if ($tool->slug === 'home-loan-eligibility-calculator') {
            $loanType = $request->input('loan_type', 'home');

            $validated = $request->validate([
                'loan_type' => [
                    'required',
                    'in:home,personal,car',
                ],

                'monthly_income' => [
                    'required',
                    'numeric',
                    'min:10000',
                    'max:100000000',
                ],

                'existing_emi' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100000000',
                ],

                'property_value' => [
                    'required',
                    'numeric',
                    'min:100000',
                    'max:500000000',
                ],

                'ltv_percent' => [
                    'required',
                    'numeric',
                    'min:10',
                    'max:90',
                ],

                'interest_rate' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:35',
                ],

                'tenure_years' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:' . (
                        $loanType === 'personal' ? 7 :
                        ($loanType === 'car' ? 8 : 30)
                    ),
                ],

                'calculation_method' => [
                    'required',
                    'in:standard,conservative,aggressive',
                ],
            ]);

            $foirOptions = [
                'standard' => 50,
                'conservative' => 40,
                'aggressive' => 60,
            ];

            $validated['foir_percent'] =
                $foirOptions[$validated['calculation_method']];

            // UI mein Down Payment field nahi hai.
            $validated['down_payment'] = 0;

            
            $inputs = $validated;

            $result = $eligibilityCalculator->calculate($inputs);


            return view('user.calculators.eligibility', compact(
                'tool',
                'inputs',
                'result'
            ));
        }

        if ($tool->slug === 'stamp-duty-calculator') {
            $validated = $request->validate([
                'property_value' => [
                    'required', 'numeric', 'min:100000', 'max:1000000000',
                ],
                'state' => [
                    'required', 'in:Andhra Pradesh,Arunachal Pradesh,Assam,Bihar,Chhattisgarh,Goa,Gujarat,Haryana,Himachal Pradesh,Jharkhand,Karnataka,Kerala,Madhya Pradesh,Maharashtra,Manipur,Meghalaya,Mizoram,Nagaland,Odisha,Punjab,Rajasthan,Sikkim,Tamil Nadu,Telangana,Tripura,Uttar Pradesh,Uttarakhand,West Bengal,Andaman and Nicobar Islands,Chandigarh,Dadra and Nagar Haveli and Daman and Diu,Delhi,Jammu and Kashmir,Ladakh,Lakshadweep,Puducherry',
                ],
                'property_type' => [
                    'required', 'in:residential_flat,own_land,pent_house',
                ],
                'buyer_category' => [
                    'required', 'in:male,female,joint,senior,first_time',
                ],
                'stamp_duty_method' => [
                    'required', 'in:percentage,slab',
                ],
                
                'stamp_duty_rate' => [
                    'required_if:stamp_duty_method,percentage', 'nullable', 'numeric', 'min:0', 'max:20',
                ],

                'registration_type' => [
                    'required', 'in:fixed,percentage',
                ],
                'registration_value' => [
                    'required', 'numeric', 'min:0', 'max:100000000',
                ],
                'other_charges' => [
                    'required', 'numeric', 'min:0', 'max:100000000',
                ],
            ]);

            $inputs = $validated;

            $result = $stampDutyCalculator->calculate($inputs);

            return view('user.calculators.stamp-duty', compact(
                'tool',
                'inputs',
                'result'
            ));
        }
        
        if ($tool->slug === 'interest-rate-calculator') {
            $validated = $request->validate([
                'monthly_emi' => [
                    'required', 'numeric', 'min:1000', 'max:1000000',
                ],
                'loan_amount' => [
                    'required', 'numeric', 'min:50000', 'max:100000000',
                ],
                'tenure_value' => [
                    'required', 'integer', 'min:1',
                ],
                'tenure_unit' => [
                    'required', 'in:years,months',
                ],
            ]);

            $maxTenure = $validated['tenure_unit'] === 'months' ? 360 : 30;

            if ($validated['tenure_value'] > $maxTenure) {
                return back()
                    ->withErrors([
                        'tenure_value' => 'Please select a valid loan tenure.',
                    ])
                    ->withInput();
            }

            $inputs = $validated;

            $result = $interestRateCalculator->calculate($inputs);

            return view('user.calculators.interest-rate', compact(
                'tool',
                'inputs',
                'result'
            ));
        }
        
        if (in_array($tool->slug, [
            'prepayment-calculator',
            'loan-prepayment-calculator',
        ], true)) {
            $validated = $request->validate([
                'outstanding_loan' => [
                    'required', 'numeric', 'min:10000', 'max:500000000',
                ],
                'interest_rate' => [
                    'required', 'numeric', 'min:0', 'max:35',
                ],
                'remaining_tenure' => [
                    'required', 'integer', 'min:1', 'max:600',
                ],
                'prepayment_strategy' => [
                    'required', 'in:one_time,monthly',
                ],
                'prepayment_amount' => [
                    'required', 'numeric', 'min:1', 'max:500000000',
                ],
                'strategy_goal' => [
                    'required', 'in:reduce_tenure,reduce_emi',
                ],
            ]);

    $inputs = $validated;

    $result = $prepaymentCalculator->calculate($inputs);

    return view('user.calculators.prepayment', compact(
        'tool',
        'inputs',
        'result'
    ));
}




        return redirect()
            ->route('user.tools.show', $tool->slug)
            ->with('info', 'This calculator needs its own calculation logic.');
    }
}
