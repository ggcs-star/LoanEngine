<?php

namespace App\Services\Calculators;

class HomeLoanEligibilityCalculator
{
    public function calculate(array $inputs): array
    {
        $type = $inputs['loan_type'] ?? 'home';

        if (!in_array($type, ['home', 'personal', 'car'], true)) {
            $type = 'home';
        }

        $income = max(0, (float) ($inputs['monthly_income'] ?? 0));
        $existingEmi = max(0, (float) ($inputs['existing_emi'] ?? 0));
        $propertyValue = max(0, (float) ($inputs['property_value'] ?? 0));
        $interestRate = max(0, (float) ($inputs['interest_rate'] ?? 8.5));
        $tenureYears = max(1, (int) ($inputs['tenure_years'] ?? 20));
        $method = $inputs['calculation_method'] ?? 'standard';

        $foirOptions = [
            'conservative' => 40,
            'standard' => 50,
            'aggressive' => 60,
        ];

        $foirPercent = $foirOptions[$method] ?? 50;

        $maxTenure = match ($type) {
            'personal' => 7,
            'car' => 8,
            default => 30,
        };

        $tenureYears = min($tenureYears, $maxTenure);
        $tenureMonths = $tenureYears * 12;

        // Step 1: Maximum EMI capacity based on income.
        $allowedTotalEmi = $income * ($foirPercent / 100);

        // Step 2: Subtract existing monthly EMIs.
        $availableEmi = max(0, $allowedTotalEmi - $existingEmi);

        // Step 3: Convert affordable EMI into loan principal.
        $incomeBasedLoan = $this->principalFromEmi(
            $availableEmi,
            $interestRate,
            $tenureMonths
        );

        // Step 4: Apply the property/vehicle LTV cap where applicable.
        $ltvPercent = max(
            10,
            min(90, (float) ($inputs['ltv_percent'] ?? ($type === 'car' ? 90 : 80)))
        );

        $propertyBasedCap = null;

        if ($type === 'home' || $type === 'car') {
            $propertyBasedCap = $propertyValue * ($ltvPercent / 100);
            $eligibleLoan = min($incomeBasedLoan, $propertyBasedCap);
        } else {
            // Personal loan eligibility is based on repayment capacity.
            $eligibleLoan = $incomeBasedLoan;
        }

        $eligibleLoan = max(0, $eligibleLoan);

        // Step 5: Calculate EMI and repayment totals.
        $monthlyEmi = $this->monthlyEmi(
            $eligibleLoan,
            $interestRate,
            $tenureMonths
        );

        $totalPayment = $monthlyEmi * $tenureMonths;
        $totalInterest = max(0, $totalPayment - $eligibleLoan);

        $foirAfterLoan = $income > 0
            ? (($existingEmi + $monthlyEmi) / $income) * 100
            : 0;

        $status = $eligibleLoan > 0 && $availableEmi > 0
            ? 'PASSED'
            : 'REVIEW';

        $messages = [];

        if ($income <= 0) {
            $messages[] = 'Enter a valid monthly income.';
        } elseif ($availableEmi <= 0) {
            $messages[] = 'Your existing EMIs use up the selected repayment capacity.';
        } else {
            $messages[] = 'This is an indicative estimate. Final eligibility depends on lender policies, credit history and documentation.';
        }

        // Recommendations are illustrative estimates, not promises.
        $recommendations = [];

        $longerTenure = min($maxTenure, $tenureYears + 5);

        if ($longerTenure > $tenureYears && $availableEmi > 0) {
            $longerLoan = $this->principalFromEmi(
                $availableEmi,
                $interestRate,
                $longerTenure * 12
            );

            if ($propertyBasedCap !== null) {
                $longerLoan = min($longerLoan, $propertyBasedCap);
            }

            if ($longerLoan > $eligibleLoan + 1) {
                $recommendations[] = [
                    'title' => 'Increase Tenure',
                    'description' => 'A longer tenure could increase the estimated eligible loan amount to ' .
                        $this->shortMoney($longerLoan) . '.',
                    'icon' => 'trending-up',
                ];
            }
        }

        if ($existingEmi > 0) {
            $withoutExistingEmi = $this->principalFromEmi(
                $allowedTotalEmi,
                $interestRate,
                $tenureMonths
            );

            if ($propertyBasedCap !== null) {
                $withoutExistingEmi = min($withoutExistingEmi, $propertyBasedCap);
            }

            $increase = max(0, $withoutExistingEmi - $eligibleLoan);

            if ($increase > 1) {
                $recommendations[] = [
                    'title' => 'Reduce Existing EMIs',
                    'description' => 'Reducing existing monthly obligations could increase estimated eligibility by ' .
                        $this->shortMoney($increase) . '.',
                    'icon' => 'wallet',
                ];
            }
        }

        $recommendations[] = [
            'title' => 'Consider a Co-applicant',
            'description' => 'A co-applicant with documented income may increase combined repayment capacity, subject to lender rules.',
            'icon' => 'user-plus',
        ];

        return [
            'loan_type' => $type,
            'calculation_method' => $method,
            'foir_percent' => $foirPercent,
            'eligible_loan' => round($eligibleLoan, 2),
            'income_based_loan' => round($incomeBasedLoan, 2),
            'property_based_cap' => $propertyBasedCap === null
                ? null
                : round($propertyBasedCap, 2),
            'monthly_emi' => round($monthlyEmi, 2),
            'total_payment' => round($totalPayment, 2),
            'total_interest' => round($totalInterest, 2),
            'available_emi' => round($availableEmi, 2),
            'allowed_total_emi' => round($allowedTotalEmi, 2),
            'foir_after_loan' => round($foirAfterLoan, 2),
            'tenure_years' => $tenureYears,
            'tenure_months' => $tenureMonths,
            'property_value' => round($propertyValue, 2),
            'ltv_percent' => $ltvPercent,
            'status' => $status,
            'messages' => $messages,
            'recommendations' => $recommendations,
        ];
    }

    private function monthlyEmi(
        float $principal,
        float $annualRate,
        int $months
    ): float {
        if ($principal <= 0 || $months <= 0) {
            return 0;
        }

        $monthlyRate = $annualRate / 1200;

        if ($monthlyRate <= 0) {
            return $principal / $months;
        }

        $power = (1 + $monthlyRate) ** $months;

        return $principal * $monthlyRate * $power / ($power - 1);
    }

    private function principalFromEmi(
        float $emi,
        float $annualRate,
        int $months
    ): float {
        if ($emi <= 0 || $months <= 0) {
            return 0;
        }

        $monthlyRate = $annualRate / 1200;

        if ($monthlyRate <= 0) {
            return $emi * $months;
        }

        $power = (1 + $monthlyRate) ** $months;

        return $emi * (($power - 1) / ($monthlyRate * $power));
    }

    private function shortMoney(float $amount): string
    {
        if ($amount >= 10000000) {
            return '₹' . number_format($amount / 10000000, 1) . 'Cr';
        }

        if ($amount >= 100000) {
            return '₹' . number_format($amount / 100000, 1) . 'L';
        }

        return '₹' . number_format($amount, 0);
    }
}