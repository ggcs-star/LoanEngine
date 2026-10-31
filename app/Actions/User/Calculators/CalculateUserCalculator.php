<?php

namespace App\Actions\User\Calculators;

use App\Models\Calculator;
use App\Models\User;
use App\Services\Calculators\CalculatorUsageTracker;
use App\Services\Calculators\EmiVsTenureCalculator;
use App\Services\Calculators\HomeAffordabilityCalculator;
use App\Services\Calculators\HomeLoanEmiCalculator;
use App\Services\Calculators\HomeLoanEligibilityCalculator;
use App\Services\Calculators\InterestRateCalculator;
use App\Services\Calculators\LoanPrepaymentCalculator;
use App\Services\Calculators\StampDutyCalculator;
use App\Support\Calculators\CalculatorFormulaRegistry;
use Illuminate\Validation\ValidationException;

class CalculateUserCalculator
{
    public function __construct(
        private readonly HomeAffordabilityCalculator $affordability,
        private readonly HomeLoanEmiCalculator $emi,
        private readonly HomeLoanEligibilityCalculator $eligibility,
        private readonly EmiVsTenureCalculator $emiVsTenure,
        private readonly StampDutyCalculator $stampDuty,
        private readonly InterestRateCalculator $interestRate,
        private readonly LoanPrepaymentCalculator $prepayment,
        private readonly CalculatorUsageTracker $tracker,
    ) {}

    public function execute(
        User $user,
        Calculator $calculator,
        array $inputs
    ): array {
        $this->ensureSupported($calculator);

        $inputs = $this->normalizeInputs(
            $calculator->formula_type,
            $inputs
        );

        $result = match ($calculator->formula_type) {
            'home_affordability' => $this->affordability->calculate($inputs),
            'home_loan_emi' => $this->emi->calculate($inputs),
            'home_loan_eligibility' => $this->eligibility->calculate($inputs),
            'emi_vs_tenure' => $this->emiVsTenure->calculate($inputs),
            'stamp_duty' => $this->stampDuty->calculate($inputs),
            'interest_rate' => $this->interestRate->calculate($inputs),
            'loan_prepayment' => $this->prepayment->calculate($inputs),
            default => throw ValidationException::withMessages([
                'calculator' => ['This calculator formula is not supported.'],
            ]),
        };

        $this->tracker->record(
            $user,
            $calculator,
            $inputs,
            $result
        );

        return $result;
    }

    private function ensureSupported(Calculator $calculator): void
    {
        if (
            $calculator->status !== 'active'
            || ! $calculator->formula_type
            || ! array_key_exists(
                $calculator->formula_type,
                CalculatorFormulaRegistry::all()
            )
        ) {
            throw ValidationException::withMessages([
                'calculator' => ['This calculator is currently unavailable.'],
            ]);
        }
    }

    private function normalizeInputs(string $formulaType, array $inputs): array
    {
        if ($formulaType === 'home_loan_emi') {
            $inputs['processing_fee'] = (float) ($inputs['processing_fee'] ?? 0);
        }

        if ($formulaType === 'home_loan_eligibility') {
            $inputs['foir_percent'] = match ($inputs['calculation_method']) {
                'conservative' => 40,
                'aggressive' => 60,
                default => 50,
            };

            $inputs['down_payment'] = 0;
        }

        if ($formulaType === 'stamp_duty') {
            $inputs['stamp_duty_rate'] = (float) ($inputs['stamp_duty_rate'] ?? 5);
        }

        return $inputs;
    }
}