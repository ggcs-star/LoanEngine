<?php

namespace App\Services\Calculators;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

class HomeLoanEmiCalculator
{
    public function calculate(array $inputs): array
    {
        $principal = (float) $inputs['loan_amount'];
        $annualRate = (float) $inputs['interest_rate'];
        $months = (int) $inputs['tenure_months'];
        $fee = (float) ($inputs['processing_fee'] ?? 0);

        if ($principal <= 0 || $months < 1) {
            throw new InvalidArgumentException('Loan amount and tenure must be greater than zero.');
        }

        if ($annualRate < 0 || $annualRate > 100 || $fee < 0) {
            throw new InvalidArgumentException('Please provide valid interest rate and processing fee.');
        }

        $monthlyRate = $annualRate / 1200;
        if ($monthlyRate == 0.0) {
            $emi = $principal / $months;
        } else {
            $factor = (1 + $monthlyRate) ** $months;
            $emi = $principal * $monthlyRate * $factor / ($factor - 1);
        }

        $emi = round($emi, 2);
        $balance = round($principal, 2);
        $totalInterest = 0.0;
        $totalPrincipal = 0.0;
        $schedule = [];
        $startMonth = Carbon::now()->startOfMonth();

        for ($month = 1; $month <= $months; $month++) {
            $interest = round($balance * $monthlyRate, 2);
            $principalPaid = $month === $months
                ? $balance
                : min($balance, round($emi - $interest, 2));

            $payment = round($principalPaid + $interest, 2);
            $balance = max(0, round($balance - $principalPaid, 2));
            $totalInterest += $interest;
            $totalPrincipal += $principalPaid;

            $schedule[] = [
                'emi_number' => $month,
                'month' => $startMonth->copy()->addMonths($month)->format('M Y'),
                'emi' => $payment,
                'principal' => round($principalPaid, 2),
                'interest' => $interest,
                'balance' => $balance,
            ];
        }

        $totalInterest = round($totalInterest, 2);
        $totalPrincipal = round($totalPrincipal, 2);
        $totalPayment = round($totalPrincipal + $totalInterest, 2);

        return [
            'loan_amount' => $principal,
            'interest_rate' => $annualRate,
            'tenure_months' => $months,
            'tenure_years' => round($months / 12, 2),
            'processing_fee' => $fee,
            'emi' => $emi,
            'total_principal' => $totalPrincipal,
            'total_interest' => $totalInterest,
            'total_payment' => $totalPayment,
            'total_cost_with_fee' => round($totalPayment + $fee, 2),
            'principal_percentage' => $totalPayment > 0 ? round($totalPrincipal / $totalPayment * 100, 1) : 0,
            'interest_percentage' => $totalPayment > 0 ? round($totalInterest / $totalPayment * 100, 1) : 0,
            'schedule' => $schedule,
        ];
    }
}
