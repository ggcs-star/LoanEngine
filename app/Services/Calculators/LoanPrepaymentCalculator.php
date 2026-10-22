<?php

namespace App\Services\Calculators;

class LoanPrepaymentCalculator
{
    public function calculate(array $inputs): array
    {
        $principal = (float) $inputs['outstanding_loan'];
        $annualRate = (float) $inputs['interest_rate'];
        $months = (int) $inputs['remaining_tenure'];
        $strategy = $inputs['prepayment_strategy'];
        $amount = (float) $inputs['prepayment_amount'];
        $goal = $inputs['strategy_goal'];

        if ($principal <= 0 || $months < 1 || $amount < 0 || $annualRate < 0) {
            throw new \InvalidArgumentException('Invalid loan calculation inputs.');
        }

        $rate = $annualRate / 1200;

        $originalEmi = $this->emi($principal, $rate, $months);
        $original = $this->buildSchedule($principal, $rate, $originalEmi, $months);

        // One-time payment is applied before the next month's interest.
        $balance = $strategy === 'one_time'
            ? max(0, $principal - min($principal, $amount))
            : $principal;

        $regularEmi = $goal === 'reduce_emi'
            ? $this->emi($balance, $rate, $months)
            : $originalEmi;

        $schedule = [];
        $totalInterest = 0.0;
        $totalPaid = $strategy === 'one_time' ? min($principal, $amount) : 0.0;

        for ($month = 1; $month <= $months && $balance > 0.005; $month++) {
            $interest = $balance * $rate;

            if ($goal === 'reduce_emi' && $strategy === 'monthly') {
                // Monthly additions reduce principal each month; recalculate
                // the scheduled EMI over the remaining original tenure.
                $regularEmi = $this->emi($balance, $rate, $months - $month + 1);
            }

            $regularPayment = min($balance + $interest, $regularEmi);
            $principalPayment = min($balance, max(0, $regularPayment - $interest));
            $balance -= $principalPayment;

            $extraPayment = 0.0;

            if ($strategy === 'monthly') {
                $extraPayment = min($balance, $amount);
                $balance -= $extraPayment;
            }

            $balance = max(0, $balance);
            $payment = $regularPayment + $extraPayment;

            $totalInterest += $interest;
            $totalPaid += $payment;

            $schedule[] = [
                'month' => $month,
                'interest' => round($interest, 2),
                'payment' => round($payment, 2),
                'balance' => round($balance, 2),
            ];
        }

        $originalInterest = (float) $original['interest'];
        $interestSaved = max(0, $originalInterest - $totalInterest);
        $monthsReduced = max(0, $original['months'] - count($schedule));

        $timeline = [];
        $maxPoints = 6;

        for ($i = 0; $i < $months; $i += max(1, (int) ceil($months / $maxPoints))) {
            $timeline[] = [
                'label' => 'Month ' . ($i + 1),
                'original' => $original['schedule'][$i]['balance'] ?? 0,
                'prepaid' => $schedule[$i]['balance'] ?? 0,
            ];
        }

        if ($months > 0 && count($timeline) < $maxPoints) {
            $last = $months - 1;

            $timeline[] = [
                'label' => 'Final',
                'original' => $original['schedule'][$last]['balance'] ?? 0,
                'prepaid' => $schedule[$last]['balance'] ?? 0,
            ];
        }

        $newEmi = $goal === 'reduce_emi'
            ? $this->emi(
                max(0, $principal - ($strategy === 'one_time' ? min($principal, $amount) : 0)),
                $rate,
                $months
            )
            : $originalEmi;

        return [
            'outstanding_loan' => round($principal, 2),
            'interest_rate' => $annualRate,
            'remaining_tenure' => $months,
            'prepayment_strategy' => $strategy,
            'prepayment_amount' => round($amount, 2),
            'strategy_goal' => $goal,
            'original_emi' => round($originalEmi, 2),
            'revised_emi' => round($newEmi, 2),
            'original_months' => $original['months'],
            'prepaid_months' => count($schedule),
            'months_reduced' => $monthsReduced,
            'interest_saved' => round($interestSaved, 2),
            'original_interest' => round($originalInterest, 2),
            'prepaid_interest' => round($totalInterest, 2),
            'original_total_payment' => round($original['total_payment'], 2),
            'prepaid_total_payment' => round($totalPaid, 2),
            'saving_percent' => $originalInterest > 0
                ? round(($interestSaved / $originalInterest) * 100, 2)
                : 0,
            'timeline' => $timeline,
        ];
    }

    private function emi(float $principal, float $monthlyRate, int $months): float
    {
        if ($principal <= 0 || $months < 1) {
            return 0;
        }

        if ($monthlyRate <= 0) {
            return $principal / $months;
        }

        $factor = pow(1 + $monthlyRate, $months);

        return $principal * $monthlyRate * $factor / ($factor - 1);
    }

    private function buildSchedule(
        float $principal,
        float $monthlyRate,
        float $emi,
        int $months
    ): array {
        $balance = $principal;
        $interestTotal = 0.0;
        $totalPaid = 0.0;
        $schedule = [];

        for ($month = 1; $month <= $months && $balance > 0.005; $month++) {
            $interest = $balance * $monthlyRate;
            $payment = min($balance + $interest, $emi);
            $principalPaid = min($balance, max(0, $payment - $interest));
            $balance = max(0, $balance - $principalPaid);

            $interestTotal += $interest;
            $totalPaid += $payment;

            $schedule[] = [
                'month' => $month,
                'interest' => round($interest, 2),
                'payment' => round($payment, 2),
                'balance' => round($balance, 2),
            ];
        }

        return [
            'months' => count($schedule),
            'interest' => $interestTotal,
            'total_payment' => $totalPaid,
            'schedule' => $schedule,
        ];
    }
}
