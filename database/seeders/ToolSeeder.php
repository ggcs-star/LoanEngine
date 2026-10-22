<?php

namespace Database\Seeders;

use App\Models\Tool;
use App\Models\ToolJourney;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $journeySlugs = [
            'first-time-home-buyer', 'existing-home-loan', 'top-up-loan',
            'balance-transfer', 'prepayment', 'loan-closure',
        ];

        $rows = [
            ['name' => 'First Time Buyer', 'slug' => 'first-time-buyer', 'description' => 'Guidance and resources for first-time home buyers.', 'icon' => 'house', 'category' => 'Resources', 'type' => 'Resource', 'status' => 'active', 'order' => 1, 'route' => '/user/tools/first-time-buyer', 'journeys' => ['first-time-home-buyer']],
            ['name' => 'Existing Owner', 'slug' => 'existing-owner', 'description' => 'Guidance for existing home loan owners.', 'icon' => 'house', 'category' => 'Resources', 'type' => 'Resource', 'status' => 'active', 'order' => 2, 'route' => '/user/tools/existing-owner', 'journeys' => ['existing-home-loan']],
            ['name' => 'Document Checklist', 'slug' => 'document-checklist', 'description' => 'Review common home loan application documents.', 'icon' => 'book', 'category' => 'Resources', 'type' => 'Resource', 'status' => 'active', 'order' => 3, 'route' => '/user/tools/document-checklist', 'journeys' => ['first-time-home-buyer', 'balance-transfer', 'loan-closure']],
            ['name' => 'Glossary Learning', 'slug' => 'glossary-learning', 'description' => 'Learn common home loan and property finance terms.', 'icon' => 'book', 'category' => 'Resources', 'type' => 'Resource', 'status' => 'active', 'order' => 4, 'route' => '/user/tools/glossary-learning', 'journeys' => ['first-time-home-buyer', 'balance-transfer', 'loan-closure']],
            ['name' => 'Home Loan Calculators', 'slug' => 'home-loan-calculators', 'description' => 'Browse home loan and property finance calculators.', 'icon' => 'calculator', 'category' => 'Calculators', 'type' => 'Calculator', 'status' => 'active', 'order' => 5, 'route' => '/tools', 'journeys' => $journeySlugs],
            ['name' => 'Home Loan EMI Calculator', 'slug' => 'home-loan-emi-calculator', 'description' => 'Calculate monthly EMI, total interest and repayment.', 'icon' => 'calculator', 'category' => 'Calculators', 'type' => 'Calculator', 'status' => 'active', 'order' => 6, 'route' => '/tools/home-loan-emi-calculator', 'journeys' => $journeySlugs],
            ['name' => 'Home Affordability Calculator', 'slug' => 'home-affordability-calculator', 'description' => 'Estimate a comfortable property budget.', 'icon' => 'calculator', 'category' => 'Calculators', 'type' => 'Calculator', 'status' => 'active', 'order' => 7, 'route' => '/tools/home-affordability-calculator', 'journeys' => ['first-time-home-buyer']],
            ['name' => 'Home Loan Eligibility Calculator', 'slug' => 'home-loan-eligibility-calculator', 'description' => 'Estimate potential home loan eligibility.', 'icon' => 'calculator', 'category' => 'Calculators', 'type' => 'Calculator', 'status' => 'active', 'order' => 8, 'route' => '/tools/home-loan-eligibility-calculator', 'journeys' => ['first-time-home-buyer', 'top-up-loan']],
            ['name' => 'Stamp Duty Calculator', 'slug' => 'stamp-duty-calculator', 'description' => 'Estimate property stamp duty and registration costs.', 'icon' => 'calculator', 'category' => 'Calculators', 'type' => 'Calculator', 'status' => 'active', 'order' => 9, 'route' => '/tools/stamp-duty-calculator', 'journeys' => ['first-time-home-buyer']],
            ['name' => 'Interest Rate Calculator', 'slug' => 'interest-rate-calculator', 'description' => 'Review interest rate and repayment estimates.', 'icon' => 'calculator', 'category' => 'Calculators', 'type' => 'Calculator', 'status' => 'active', 'order' => 10, 'route' => '/tools/interest-rate-calculator', 'journeys' => ['first-time-home-buyer', 'existing-home-loan', 'balance-transfer']],
            ['name' => 'Loan Prepayment Calculator', 'slug' => 'prepayment-calculator', 'description' => 'Estimate the effect of extra loan repayments.', 'icon' => 'calculator', 'category' => 'Calculators', 'type' => 'Calculator', 'status' => 'active', 'order' => 11, 'route' => '/tools/prepayment-calculator', 'journeys' => ['prepayment', 'existing-home-loan']],
            ['name' => 'Loan Tenure Calculator', 'slug' => 'loan-tenure-calculator', 'description' => 'Explore loan tenure and repayment options.', 'icon' => 'calculator', 'category' => 'Calculators', 'type' => 'Calculator', 'status' => 'planned', 'order' => 12, 'route' => '/tools/loan-tenure-calculator', 'journeys' => ['first-time-home-buyer', 'existing-home-loan', 'top-up-loan', 'balance-transfer', 'prepayment']],
            ['name' => 'Repayment Planner', 'slug' => 'repayment-planner', 'description' => 'Plan repayments around monthly income and expenses.', 'icon' => 'calculator', 'category' => 'Calculators', 'type' => 'Calculator', 'status' => 'planned', 'order' => 13, 'route' => '/tools/repayment-planner', 'journeys' => ['first-time-home-buyer', 'existing-home-loan', 'top-up-loan', 'prepayment']],
            ['name' => 'Loan Closure Calculator', 'slug' => 'loan-closure-calculator', 'description' => 'Plan the final repayment and loan closure.', 'icon' => 'calculator', 'category' => 'Calculators', 'type' => 'Calculator', 'status' => 'planned', 'order' => 14, 'route' => '/tools/loan-closure-calculator', 'journeys' => ['loan-closure']],
            ['name' => 'Financial Flow Calendar', 'slug' => 'financial-flow-calendar', 'description' => 'Track income, EMIs and financial commitments.', 'icon' => 'calendar', 'category' => 'Planning', 'type' => 'Planning Tool', 'status' => 'active', 'order' => 15, 'route' => '/user/financial-flow-calendar', 'journeys' => $journeySlugs],
        ];

        DB::transaction(function () use ($rows): void {
            $slugs = collect($rows)->pluck('slug')->all();

            // Purge only records managed by this seeder, not unrelated admin-created tools.
            $oldTools = Tool::query()->whereIn('slug', $slugs)->get();
            foreach ($oldTools as $oldTool) {
                $oldTool->journeys()->detach();
                $oldTool->journeySteps()->detach();
                $oldTool->delete();
            }

            foreach ($rows as $row) {
                $tool = Tool::create([
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'short_description' => $row['description'],
                    'icon' => $row['icon'],
                    'category' => $row['category'],
                    'type' => $row['type'],
                    'status' => $row['status'],
                    'display_order' => $row['order'],
                    'page_route' => $row['route'],
                    'show_on_toolkit_home' => $row['category'] !== 'Calculators',
                ]);

                $journeyIds = ToolJourney::query()
                    ->whereIn('slug', $row['journeys'])
                    ->pluck('id')
                    ->all();

                $tool->journeys()->sync($journeyIds);
            }
        });
    }
}
