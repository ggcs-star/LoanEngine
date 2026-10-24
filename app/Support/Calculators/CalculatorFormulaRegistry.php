<?php

namespace App\Support\Calculators;

final class CalculatorFormulaRegistry
{
    private const FORMULAS = [
        'home_affordability' => [
            'label' => 'Home Affordability Calculator',
            'view' => 'user.calculators.show',
            'inputs' => [
                'property_price' => [
                    'label' => 'Property Price',
                    'type' => 'number',
                    'default' => 10000000,
                    'min' => 1000000,
                    'max' => 50000000,
                    'step' => 10000,
                ],
                'down_payment_percent' => [
                    'label' => 'Down Payment (%)',
                    'type' => 'number',
                    'default' => 40,
                    'min' => 10,
                    'max' => 90,
                    'step' => 1,
                ],
                'interest_rate' => [
                    'label' => 'Interest Rate (%)',
                    'type' => 'number',
                    'default' => 6.9,
                    'min' => 0,
                    'max' => 30,
                    'step' => 0.1,
                ],
                'tenure_years' => [
                    'label' => 'Loan Tenure (Years)',
                    'type' => 'number',
                    'default' => 20,
                    'min' => 1,
                    'max' => 40,
                    'step' => 1,
                ],
                'monthly_rent' => [
                    'label' => 'Monthly Rent',
                    'type' => 'number',
                    'default' => 15000,
                    'min' => 5000,
                    'max' => 150000,
                    'step' => 500,
                ],
                'appreciation_rate' => [
                    'label' => 'Property Appreciation (%)',
                    'type' => 'number',
                    'default' => 6,
                    'min' => -10,
                    'max' => 20,
                    'step' => 0.1,
                ],
                'rent_increase_rate' => [
                    'label' => 'Rent Increase (%)',
                    'type' => 'number',
                    'default' => 7,
                    'min' => -10,
                    'max' => 20,
                    'step' => 0.1,
                ],
                'investment_return' => [
                    'label' => 'Investment Return (%)',
                    'type' => 'number',
                    'default' => 12,
                    'min' => -10,
                    'max' => 30,
                    'step' => 0.1,
                ],
                'inflation_rate' => [
                    'label' => 'Inflation (%)',
                    'type' => 'number',
                    'default' => 6,
                    'min' => -10,
                    'max' => 30,
                    'step' => 0.1,
                ],
                'maintenance_percent' => [
                    'label' => 'Maintenance (%)',
                    'type' => 'number',
                    'default' => 1,
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                ],
                'analysis_years' => [
                    'label' => 'Analysis Period (Years)',
                    'type' => 'number',
                    'default' => 15,
                    'min' => 5,
                    'max' => 30,
                    'step' => 1,
                ],
            ],
            'results' => [
                'monthly_emi' => 'Monthly EMI',
                'total_interest' => 'Total Interest',
                'total_payment' => 'Total Payment',
            ],
        ],

        'home_loan_emi' => [
            'label' => 'Home Loan EMI Calculator',
            'view' => 'user.calculators.emi',
            'inputs' => [
                'loan_type' => [
                    'label' => 'Loan Type',
                    'type' => 'select',
                    'default' => 'home',
                    'options' => [
                        'home' => 'Home Loan',
                        'personal' => 'Personal Loan',
                    ],
                ],
                'loan_amount' => [
                    'label' => 'Loan Amount',
                    'type' => 'number',
                    'default' => 5000000,
                    'min' => 100000,
                    'max' => 50000000,
                    'step' => 10000,
                ],
                'interest_rate' => [
                    'label' => 'Interest Rate (%)',
                    'type' => 'number',
                    'default' => 8.5,
                    'min' => 0,
                    'max' => 35,
                    'step' => 0.1,
                ],
                'tenure_months' => [
                    'label' => 'Tenure (Months)',
                    'type' => 'number',
                    'default' => 240,
                    'min' => 1,
                    'max' => 360,
                    'step' => 1,
                ],
                'processing_fee' => [
                    'label' => 'Processing Fee',
                    'type' => 'number',
                    'default' => 10000,
                    'min' => 0,
                    'max' => 1000000,
                    'step' => 100,
                ],
            ],
            'results' => [
                'monthly_emi' => 'Monthly EMI',
                'total_interest' => 'Total Interest',
                'total_payment' => 'Total Payment',
            ],
        ],

        'home_loan_eligibility' => [
            'label' => 'Home Loan Eligibility Calculator',
            'view' => 'user.calculators.eligibility',
            'inputs' => [
                'monthly_income' => [
                    'label' => 'Monthly Income',
                    'type' => 'number',
                    'default' => 100000,
                    'min' => 10000,
                    'max' => 100000000,
                    'step' => 1000,
                ],
                'existing_emi' => [
                    'label' => 'Existing EMI',
                    'type' => 'number',
                    'default' => 0,
                    'min' => 0,
                    'max' => 100000000,
                    'step' => 500,
                ],
                'property_value' => [
                    'label' => 'Property Value',
                    'type' => 'number',
                    'default' => 7500000,
                    'min' => 100000,
                    'max' => 500000000,
                    'step' => 10000,
                ],
                'ltv_percent' => [
                    'label' => 'Loan-to-Value (%)',
                    'type' => 'number',
                    'default' => 80,
                    'min' => 10,
                    'max' => 90,
                    'step' => 1,
                ],
                'interest_rate' => [
                    'label' => 'Interest Rate (%)',
                    'type' => 'number',
                    'default' => 8.5,
                    'min' => 0,
                    'max' => 35,
                    'step' => 0.1,
                ],
                'tenure_years' => [
                    'label' => 'Tenure (Years)',
                    'type' => 'number',
                    'default' => 20,
                    'min' => 1,
                    'max' => 30,
                    'step' => 1,
                ],
                'calculation_method' => [
                    'label' => 'Calculation Method',
                    'type' => 'select',
                    'default' => 'standard',
                    'options' => [
                        'standard' => 'Standard',
                        'conservative' => 'Conservative',
                        'aggressive' => 'Aggressive',
                    ],
                ],
            ],
            'results' => [
                'eligible_loan_amount' => 'Eligible Loan Amount',
                'monthly_emi' => 'Estimated Monthly EMI',
            ],
        ],

        'emi_vs_tenure' => [
            'label' => 'EMI vs Tenure Calculator',
            'view' => 'user.calculators.emi-vs-tenure',
            'inputs' => [
                'loan_amount' => [
                    'label' => 'Loan Amount',
                    'type' => 'number',
                    'default' => 7000000,
                    'min' => 1000000,
                    'max' => 10000000,
                    'step' => 10000,
                ],
                'interest_rate' => [
                    'label' => 'Interest Rate (%)',
                    'type' => 'number',
                    'default' => 8.5,
                    'min' => 5,
                    'max' => 15,
                    'step' => 0.1,
                ],
                'tenure_years' => [
                    'label' => 'Selected Tenure (Years)',
                    'type' => 'select',
                    'default' => 20,
                    'options' => [
                        10 => '10 Years',
                        15 => '15 Years',
                        20 => '20 Years',
                        25 => '25 Years',
                        30 => '30 Years',
                    ],
                ],
            ],
            'results' => [
                'comparisons' => 'EMI and Interest Comparison by Tenure',
                'selected.emi' => 'Selected Monthly EMI',
                'selected.total_interest' => 'Selected Total Interest',
                'selected.total_payment' => 'Selected Total Payment',
            ],
        ],

        'stamp_duty' => [
            'label' => 'Stamp Duty Calculator',
            'view' => 'user.calculators.stamp-duty',
            'inputs' => [
                'property_value' => [
                    'label' => 'Property Value',
                    'type' => 'number',
                    'default' => 5000000,
                    'min' => 100000,
                    'max' => 1000000000,
                    'step' => 10000,
                ],
                'state' => [
                    'label' => 'State',
                    'type' => 'text',
                    'default' => 'Gujarat',
                ],
            ],
            'results' => [
                'stamp_duty' => 'Stamp Duty',
                'registration_fee' => 'Registration Fee',
                'total_charges' => 'Total Charges',
            ],
        ],

        'interest_rate' => [
            'label' => 'Interest Rate Calculator',
            'view' => 'user.calculators.interest-rate',
            'inputs' => [
                'monthly_emi' => [
                    'label' => 'Monthly EMI',
                    'type' => 'number',
                    'default' => 20000,
                    'min' => 1000,
                    'max' => 1000000,
                    'step' => 500,
                ],
                'loan_amount' => [
                    'label' => 'Loan Amount',
                    'type' => 'number',
                    'default' => 400000,
                    'min' => 50000,
                    'max' => 100000000,
                    'step' => 10000,
                ],
                'tenure_value' => [
                    'label' => 'Tenure Value',
                    'type' => 'number',
                    'default' => 2,
                    'min' => 1,
                    'max' => 360,
                    'step' => 1,
                ],
                'tenure_unit' => [
                    'label' => 'Tenure Unit',
                    'type' => 'select',
                    'default' => 'years',
                    'options' => [
                        'years' => 'Years',
                        'months' => 'Months',
                    ],
                ],
            ],
            'results' => [
                'interest_rate' => 'Estimated Interest Rate',
            ],
        ],

        'loan_prepayment' => [
            'label' => 'Loan Prepayment Calculator',
            'view' => 'user.calculators.prepayment',
            'inputs' => [
                'outstanding_loan' => [
                    'label' => 'Outstanding Loan',
                    'type' => 'number',
                    'default' => 22500000,
                    'min' => 10000,
                    'max' => 500000000,
                    'step' => 10000,
                ],
                'interest_rate' => [
                    'label' => 'Interest Rate (%)',
                    'type' => 'number',
                    'default' => 8.55,
                    'min' => 0,
                    'max' => 35,
                    'step' => 0.1,
                ],
                'remaining_tenure' => [
                    'label' => 'Remaining Tenure (Months)',
                    'type' => 'number',
                    'default' => 215,
                    'min' => 1,
                    'max' => 600,
                    'step' => 1,
                ],
                'prepayment_strategy' => [
                    'label' => 'Prepayment Strategy',
                    'type' => 'select',
                    'default' => 'one_time',
                    'options' => [
                        'one_time' => 'One-time',
                        'monthly' => 'Monthly',
                    ],
                ],
                'prepayment_amount' => [
                    'label' => 'Prepayment Amount',
                    'type' => 'number',
                    'default' => 500000,
                    'min' => 1,
                    'max' => 500000000,
                    'step' => 1000,
                ],
                'strategy_goal' => [
                    'label' => 'Strategy Goal',
                    'type' => 'select',
                    'default' => 'reduce_tenure',
                    'options' => [
                        'reduce_tenure' => 'Reduce Tenure',
                        'reduce_emi' => 'Reduce EMI',
                    ],
                ],
            ],
            'results' => [
                'interest_saved' => 'Interest Saved',
                'tenure_saved' => 'Tenure Saved',
                'new_emi' => 'Revised EMI',
            ],
        ],
    ];

    public static function all(): array
    {
        return self::FORMULAS;
    }

    public static function options(): array
    {
        return collect(self::FORMULAS)
            ->mapWithKeys(fn (array $formula, string $key) => [
                $key => $formula['label'],
            ])
            ->all();
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::FORMULAS);
    }

    public static function get(string $key): array
    {
        if (! self::has($key)) {
            throw new \InvalidArgumentException(
                "Unsupported calculator formula type [{$key}]."
            );
        }

        return self::FORMULAS[$key];
    }
}