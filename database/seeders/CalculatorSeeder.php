<?php

namespace Database\Seeders;

use App\Models\Calculator;
use Illuminate\Database\Seeder;

class CalculatorSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['name' => 'Home Affordability Calculator', 'slug' => 'home-affordability-calculator', 'short_description' => 'Estimate a home budget based on income, expenses and savings.', 'icon' => 'calculator', 'status' => 'active', 'display_order' => 1, 'route_name' => 'calculators.show'],
            ['name' => 'Home Loan EMI Calculator', 'slug' => 'home-loan-emi-calculator', 'short_description' => 'Estimate monthly EMI and total repayment.', 'icon' => 'calculator', 'status' => 'active', 'display_order' => 2, 'route_name' => 'calculators.show'],
            ['name' => 'Home Loan Eligibility Calculator', 'slug' => 'home-loan-eligibility-calculator', 'short_description' => 'Estimate loan eligibility from financial details.', 'icon' => 'calculator', 'status' => 'inactive', 'display_order' => 3, 'route_name' => null],
            ['name' => 'Stamp Duty Calculator', 'slug' => 'stamp-duty-calculator', 'short_description' => 'Estimate property stamp duty and registration costs.', 'icon' => 'calculator', 'status' => 'inactive', 'display_order' => 4, 'route_name' => null],
        ];

        foreach ($records as $record) {
            Calculator::query()->updateOrCreate(['slug' => $record['slug']], $record);
        }
    }
}
