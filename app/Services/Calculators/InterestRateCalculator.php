<?php

namespace App\Services\Calculators;

class InterestRateCalculator
{
    public function calculate(array $inputs): array
    {
        $emi = (float) $inputs['monthly_emi'];
        $principal = (float) $inputs['loan_amount'];
        $months = ($inputs['tenure_unit'] ?? 'years') === 'months'
            ? (int) $inputs['tenure_value']
            : (int) $inputs['tenure_value'] * 12;

        if ($emi * $months < $principal) {
            return [
                'annual_rate' => null,
                'monthly_emi' => $emi,
                'loan_amount' => $principal,
                'tenure_months' => $months,
            ];
        }

        // Zero-interest EMI is principal / months.
        if (abs($emi - ($principal / $months)) < 0.01) {
            return [
                'annual_rate' => 0.0,
                'monthly_emi' => $emi,
                'loan_amount' => $principal,
                'tenure_months' => $months,
            ];
        }

        // Binary search for the monthly rate that matches the EMI.
        $low = 0.0;
        $high = 1.0;

        for ($i = 0; $i < 120; $i++) {
            $rate = ($low + $high) / 2;

            $factor = pow(1 + $rate, $months);
            $calculatedEmi = $principal * $rate * $factor / ($factor - 1);

            if ($calculatedEmi > $emi) {
                $high = $rate;
            } else {
                $low = $rate;
            }
        }

        $monthlyRate = ($low + $high) / 2;
        $annualRate = $monthlyRate * 12 * 100;

        return [
            'annual_rate' => $annualRate <= 1200 ? round($annualRate, 4) : null,
            'monthly_emi' => $emi,
            'loan_amount' => $principal,
            'tenure_months' => $months,
        ];
    }
}
