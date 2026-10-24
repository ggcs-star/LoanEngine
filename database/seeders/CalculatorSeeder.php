<?php

namespace Database\Seeders;

use App\Models\Calculator;
use App\Models\Tool;
use App\Support\Calculators\CalculatorFormulaRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CalculatorSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            [
                'name' => 'Home Affordability Calculator',
                'slug' => 'home-affordability-calculator',
                'formula_type' => 'home_affordability',
                'short_description' => 'Estimate a home budget based on income, expenses and savings.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 1,
            ],
            [
                'name' => 'Home Loan EMI Calculator',
                'slug' => 'home-loan-emi-calculator',
                'formula_type' => 'home_loan_emi',
                'short_description' => 'Estimate monthly EMI, total interest and total repayment.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 2,
            ],
            [
                'name' => 'Home Loan Eligibility Calculator',
                'slug' => 'home-loan-eligibility-calculator',
                'formula_type' => 'home_loan_eligibility',
                'short_description' => 'Estimate potential home loan eligibility from financial details.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 3,
            ],
            [
                'name' => 'Stamp Duty Calculator',
                'slug' => 'stamp-duty-calculator',
                'formula_type' => 'stamp_duty',
                'short_description' => 'Estimate property stamp duty and registration costs.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 4,
            ],
            [
                'name' => 'Interest Rate Calculator',
                'slug' => 'interest-rate-calculator',
                'formula_type' => 'interest_rate',
                'short_description' => 'Estimate the interest rate based on loan repayment details.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 5,
            ],
            [
                'name' => 'Loan Prepayment Calculator',
                'slug' => 'prepayment-calculator',
                'formula_type' => 'loan_prepayment',
                'short_description' => 'Estimate how additional repayments affect interest and loan tenure.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 6,
            ],
            [
                'name' => 'EMI vs Tenure Calculator',
                'slug' => 'emi-vs-tenure-calculator',
                'formula_type' => 'emi_vs_tenure',
                'short_description' => 'Compare monthly EMI, total interest and repayment across loan tenures.',
                'icon' => 'calculator',
                'status' => 'active',
                'display_order' => 7,
            ],
        ];

        DB::transaction(function () use ($records): void {
            foreach ($records as $record) {
                $formula = CalculatorFormulaRegistry::get(
                    $record['formula_type']
                );

                $calculator = Calculator::query()->updateOrCreate(
                    ['slug' => $record['slug']],
                    array_merge($record, [
                        'route_name' => 'calculators.show',
                        'input_config' => $formula['inputs'],
                        'result_config' => $formula['results'],
                    ])
                );

                $tool = Tool::query()->updateOrCreate(
                    ['slug' => $calculator->slug],
                    [
                        'name' => $calculator->name,
                        'short_description' => $calculator->short_description,
                        'icon' => $calculator->icon ?: 'calculator',
                        'category' => 'Calculators',
                        'type' => 'Calculator',
                        'status' => $calculator->status,
                        'display_order' => $calculator->display_order,
                        'page_route' => 'tools/' . $calculator->slug,
                        'show_on_toolkit_home' => false,
                    ]
                );

                $journeyIds = $calculator->journeys()
                    ->pluck('tool_journeys.id')
                    ->all();

                $tool->journeys()->sync($journeyIds);
            }
        });
    }
}
