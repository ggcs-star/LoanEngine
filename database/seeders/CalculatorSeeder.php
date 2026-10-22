<?php

namespace Database\Seeders;

use App\Models\Calculator;
use Illuminate\Database\Seeder;

class CalculatorSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            [
                'name' => 'Home Affordability Calculator',
                'slug' => 'home-affordability-calculator',
                'short_description' => 'Estimate a home budget based on income, expenses and savings.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 1,
                'route_name' => 'calculators.show',
            ],
            [
                'name' => 'Home Loan EMI Calculator',
                'slug' => 'home-loan-emi-calculator',
                'short_description' => 'Estimate monthly EMI, total interest and total repayment.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 2,
                'route_name' => 'calculators.show',
            ],
            [
                'name' => 'Home Loan Eligibility Calculator',
                'slug' => 'home-loan-eligibility-calculator',
                'short_description' => 'Estimate potential home loan eligibility from financial details.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 3,
                'route_name' => 'calculators.show',
            ],
            [
                'name' => 'Stamp Duty Calculator',
                'slug' => 'stamp-duty-calculator',
                'short_description' => 'Estimate property stamp duty and registration costs.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 4,
                'route_name' => 'calculators.show',
            ],
            [
                'name' => 'Interest Rate Calculator',
                'slug' => 'interest-rate-calculator',
                'short_description' => 'Review interest rates and estimate their impact on loan repayment.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 5,
                'route_name' => 'calculators.show',
            ],
            [
                'name' => 'Loan Prepayment Calculator',
                'slug' => 'prepayment-calculator',
                'short_description' => 'Estimate the impact of additional repayments on your loan.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 6,
                'route_name' => 'calculators.show',
            ],
            [
                'name' => 'Loan Tenure Calculator',
                'slug' => 'loan-tenure-calculator',
                'short_description' => 'Explore loan tenure and repayment options.',
                'icon' => 'calculator',
                'status' => 'inactive',
                'display_order' => 7,
                'route_name' => null,
            ],
            [
                'name' => 'Repayment Planner',
                'slug' => 'repayment-planner',
                'short_description' => 'Plan repayments around income and monthly expenses.',
                'icon' => 'calculator',
                'status' => 'inactive',
                'display_order' => 8,
                'route_name' => null,
            ],
            [
                'name' => 'Loan Closure Calculator',
                'slug' => 'loan-closure-calculator',
                'short_description' => 'Plan remaining repayments and loan closure.',
                'icon' => 'calculator',
                'status' => 'inactive',
                'display_order' => 9,
                'route_name' => null,
            ],
        ];

        foreach ($records as $record) {
            Calculator::query()->updateOrCreate(
                ['slug' => $record['slug']],
                $record
            );
        }
    }
}
