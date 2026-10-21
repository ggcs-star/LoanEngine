<?php

namespace App\Services\Calculators;

class HomeAffordabilityCalculator
{
    
    public function calculate(array $input): array
    {
        $price = (float) $input['property_price'];
        $downPercent = (float) $input['down_payment_percent'];
        $interestRate = (float) $input['interest_rate'];
        $tenure = (int) $input['tenure_years'];
        $rent = (float) $input['monthly_rent'];
        $appreciation = (float) $input['appreciation_rate'];
        $rentIncrease = (float) $input['rent_increase_rate'];
        $investmentReturn = (float) $input['investment_return'];
        $inflation = (float) ($input['inflation_rate'] ?? 6);
        $maintenancePercent = (float) $input['maintenance_percent'];
        $years = (int) $input['analysis_years'];

        $downPayment = $price * $downPercent / 100;
        $loan = max(0, $price - $downPayment);
        $emi = $this->emi($loan, $interestRate, $tenure * 12);

        $stampDuty = $price * 0.05;
        $fees = $price * 0.02;
        $renovation = $price * 0.10;

        $upfront = $downPayment + $stampDuty + $fees + $renovation;

        $monthlyLoanRate = $interestRate / 1200;

        $monthlyInvestmentRate = pow(
            1 + $investmentReturn / 100,
            1 / 12
        ) - 1;

        $outstandingLoan = $loan;

        $rentPortfolio = $upfront;

        $progression = [];
        $breakEvenYear = null;
        $buyingWealth = 0.0;
        $propertyValue = $price;

        for ($month = 1; $month <= $years * 12; $month++) {
            $elapsedYears = ($month - 1) / 12;

            $propertyValue = $price * pow(
                1 + $appreciation / 100,
                $month / 12
            );

            $currentRent = $rent * pow(
                1 + $rentIncrease / 100,
                $elapsedYears
            );

            $annualMaintenance = $propertyValue
                * ($maintenancePercent / 100)
                * pow(1 + $inflation / 100, $elapsedYears);

            $monthlyMaintenance = $annualMaintenance / 12;

            $interest = $outstandingLoan * $monthlyLoanRate;

            $principal = min(
                $outstandingLoan,
                max(0, $emi - $interest)
            );

            $outstandingLoan = max(
                0,
                $outstandingLoan - $principal
            );

            $buyingWealth = $propertyValue - $outstandingLoan;

            $rentPortfolio *= (1 + $monthlyInvestmentRate);

            $emiForMonth = $month <= $tenure * 12
                ? $emi
                : 0;

            $buyingCost = $emiForMonth + $monthlyMaintenance;

            $monthlyDifference = $buyingCost - $currentRent;

            $rentPortfolio += $monthlyDifference;

            $rentPortfolio = max(0, $rentPortfolio);

            if ($month % 12 === 0) {
                $year = (int) ($month / 12);

                $progression[] = [
                    'year' => $year,
                    'buying' => round($buyingWealth, 2),
                    'renting' => round($rentPortfolio, 2),
                ];

                if (
                    $breakEvenYear === null
                    && $buyingWealth >= $rentPortfolio
                ) {
                    $breakEvenYear = $year;
                }
            }
        }

        $difference = $buyingWealth - $rentPortfolio;

        return [
            'property_value' => round($propertyValue, 2),
            'loan_amount' => round($loan, 2),
            'monthly_emi' => round($emi, 2),
            'down_payment' => round($downPayment, 2),
            'stamp_duty' => round($stampDuty, 2),
            'fees' => round($fees, 2),
            'renovation' => round($renovation, 2),
            'upfront_investment' => round($upfront, 2),
            'buying_wealth' => round($buyingWealth, 2),
            'renting_wealth' => round($rentPortfolio, 2),
            'difference' => round(abs($difference), 2),
            'recommendation' => $difference >= 0 ? 'BUY' : 'RENT',
            'break_even_year' => $breakEvenYear,
            'analysis_years' => $years,
            'wealth_progression' => $progression,
        ];
    }


    public function emi(float $principal, float $annualRate, int $months): float
    {
        if ($principal <= 0 || $months <= 0) {
            return 0;
        }

        $monthlyRate = $annualRate / 1200;

        if ($monthlyRate == 0.0) {
            return $principal / $months;
        }

        $factor = pow(1 + $monthlyRate, $months);

        return $principal * $monthlyRate * $factor / ($factor - 1);
    }
}
