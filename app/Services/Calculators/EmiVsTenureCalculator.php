<?php

namespace App\Services\Calculators;

class EmiVsTenureCalculator
{
    private const TENURES = [10, 15, 20, 25, 30];

    public function defaults(): array
    {
        return [
            'loan_amount' => 7000000,
            'interest_rate' => 8.5,
            'tenure_years' => 20,
        ];
    }

    public function calculate(array $inputs): array
    {
        $loanAmount = (float) ($inputs['loan_amount'] ?? 7000000);
        $interestRate = (float) ($inputs['interest_rate'] ?? 8.5);
        $tenureYears = (int) ($inputs['tenure_years'] ?? 20);

        if (
            $loanAmount < 1000000 ||
            $loanAmount > 10000000 ||
            $interestRate < 5 ||
            $interestRate > 15 ||
            !in_array($tenureYears, self::TENURES, true)
        ) {
            throw new \InvalidArgumentException(
                'Invalid EMI calculator inputs.'
            );
        }

        $comparisons = [];

        foreach (self::TENURES as $years) {
            $months = $years * 12;
            $monthlyRate = $interestRate / 1200;

            if ($monthlyRate == 0.0) {
                $emi = $loanAmount / $months;
            } else {
                $factor = (1 + $monthlyRate) ** $months;

                $emi = $loanAmount
                    * $monthlyRate
                    * $factor
                    / ($factor - 1);
            }

            $totalPayment = $emi * $months;

            $comparisons[] = [
                'tenure_years' => $years,
                'emi' => (int) round($emi),
                'total_payment' => (int) round($totalPayment),
                'total_interest' => (int) round(
                    $totalPayment - $loanAmount
                ),
                'selected' => $years === $tenureYears,
            ];
        }

        $selected = collect($comparisons)->firstWhere(
            'tenure_years',
            $tenureYears
        );

        return [
            'loan_amount' => $loanAmount,
            'interest_rate' => $interestRate,
            'tenure_years' => $tenureYears,
            'comparisons' => $comparisons,
            'selected' => $selected,
        ];
    }
}
