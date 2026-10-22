<?php

namespace App\Services\Calculators;

class HomeLoanEligibilityCalculator
{
    /**
     * Estimate eligibility using an income/FOIR cap and (where applicable)
     * a property/LTV cap. This is an estimate, not a lender approval.
     *
     * Accepted loan_type values: home, personal, car.
     */
    public function calculate(array $inputs): array
    {
        $type = $inputs['loan_type'] ?? 'home';
        $monthlyIncome = max(0, (float) ($inputs['monthly_income'] ?? 0));
        $existingEmi = max(0, (float) ($inputs['existing_emi'] ?? 0));
        $method = $inputs['calculation_method'] ?? 'standard';
        $methodFoir = ['conservative' => 40, 'standard' => 50, 'aggressive' => 60];
        $foirPercent = (float) ($methodFoir[$method] ?? ($inputs['foir_percent'] ?? 50));
        $foirPercent = max(0, min(100, $foirPercent));
        $interestRate = max(0, (float) ($inputs['interest_rate'] ?? 0));
        $tenureYears = max(1, (int) ($inputs['tenure_years'] ?? 1));
        $tenureMonths = $tenureYears * 12;

        $allowedTotalEmi = $monthlyIncome * ($foirPercent / 100);
        $availableEmi = max(0, $allowedTotalEmi - $existingEmi);
        $incomeBasedLoan = $this->principalFromEmi(
            $availableEmi,
            $interestRate,
            $tenureMonths
        );

        $propertyValue = 0.0;
        $downPayment = 0.0;
        $ltvPercent = 0.0;
        $requestedLoan = $incomeBasedLoan;
        $propertyBasedCap = null;

        if ($type === 'home') {
            $propertyValue = max(0, (float) ($inputs['property_value'] ?? 0));
            $downPayment = max(0, (float) ($inputs['down_payment'] ?? 0));
            $ltvPercent = max(0, min(100, (float) ($inputs['ltv_percent'] ?? 80)));
            $requestedLoan = max(0, $propertyValue - $downPayment);
            $propertyBasedCap = $propertyValue * ($ltvPercent / 100);
        } elseif ($type === 'car') {
            $propertyValue = max(0, (float) ($inputs['property_value'] ?? 0));
            $downPayment = max(0, (float) ($inputs['down_payment'] ?? 0));
            $ltvPercent = max(0, min(100, (float) ($inputs['ltv_percent'] ?? 90)));
            $requestedLoan = max(0, $propertyValue - $downPayment);
            $propertyBasedCap = $propertyValue * ($ltvPercent / 100);
        }

        $caps = [$incomeBasedLoan, $requestedLoan];
        if ($propertyBasedCap !== null) {
            $caps[] = $propertyBasedCap;
        }

        $eligibleLoan = max(0, min($caps));
        $emi = $this->monthlyEmi($eligibleLoan, $interestRate, $tenureMonths);
        $totalPayment = $emi * $tenureMonths;
        $totalInterest = max(0, $totalPayment - $eligibleLoan);
        $foirAfterLoan = $monthlyIncome > 0
            ? (($existingEmi + $emi) / $monthlyIncome) * 100
            : 0;

        $requiredDownPayment = ($type === 'home' || $type === 'car')
            ? max(0, $propertyValue - $eligibleLoan)
            : null;

        $messages = [];
        if ($monthlyIncome <= 0) {
            $messages[] = 'Enter a valid monthly income to estimate eligibility.';
        } elseif ($availableEmi <= 0) {
            $messages[] = 'Your existing EMIs use the selected monthly repayment capacity. Consider reducing current obligations or increasing the tenure.';
        } elseif ($eligibleLoan <= 0) {
            $messages[] = 'Based on these inputs, no additional loan capacity is estimated.';
        } else {
            $messages[] = 'This is an indicative estimate. The lender may calculate eligibility differently after reviewing credit history, employment, age, and policy rules.';
        }

        if ($type === 'home' && $requiredDownPayment !== null && $downPayment < $requiredDownPayment) {
            $messages[] = 'Your available down payment is below the amount implied by this estimate. You may need a larger contribution or a lower-priced property.';
        }

        return [
            'loan_type' => $type,
            'calculation_method' => $method,
            'foir_percent' => $foirPercent,
            'eligible_loan' => round($eligibleLoan, 2),
            'requested_loan' => round($requestedLoan, 2),
            'income_based_loan' => round($incomeBasedLoan, 2),
            'property_based_cap' => $propertyBasedCap !== null ? round($propertyBasedCap, 2) : null,
            'monthly_emi' => round($emi, 2),
            'total_payment' => round($totalPayment, 2),
            'total_interest' => round($totalInterest, 2),
            'available_emi' => round($availableEmi, 2),
            'foir_after_loan' => round($foirAfterLoan, 2),
            'required_down_payment' => $requiredDownPayment !== null ? round($requiredDownPayment, 2) : null,
            'tenure_months' => $tenureMonths,
            'messages' => $messages,
        ];
    }

    private function monthlyEmi(float $principal, float $annualRate, int $months): float
    {
        if ($principal <= 0 || $months <= 0) {
            return 0.0;
        }

        $monthlyRate = $annualRate / 1200;
        if ($monthlyRate <= 0) {
            return $principal / $months;
        }

        $power = (1 + $monthlyRate) ** $months;
        return $principal * $monthlyRate * $power / ($power - 1);
    }

    private function principalFromEmi(float $emi, float $annualRate, int $months): float
    {
        if ($emi <= 0 || $months <= 0) {
            return 0.0;
        }

        $monthlyRate = $annualRate / 1200;
        if ($monthlyRate <= 0) {
            return $emi * $months;
        }

        $power = (1 + $monthlyRate) ** $months;
        return $emi * (($power - 1) / ($monthlyRate * $power));
    }
}
