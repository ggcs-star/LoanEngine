<?php

namespace Database\Seeders;

use App\Models\JourneyStep;
use App\Models\Tool;
use App\Models\ToolJourney;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JourneyStepSeeder extends Seeder
{
    public function run(): void
    {
        $journeys = [
            'first-time-home-buyer' => [
                [
                    'Understand the Concept',
                    'Learn the basics of home loans, including principal, interest, EMI and tenure.',
                    'Income, expenses and repayment basics.',
                    'Maximum eligibility does not always mean affordability.',
                    ['glossary-learning', 'home-loan-emi-calculator'],
                ],
                [
                    'Assess Your Budget',
                    'Estimate a comfortable property budget based on your finances.',
                    'Monthly income, expenses and savings.',
                    'Keep emergency savings separate.',
                    ['home-affordability-calculator'],
                ],
                [
                    'Check Loan Eligibility',
                    'Review common lender eligibility factors.',
                    'Income proof, employment details and existing EMIs.',
                    'An estimate is not a loan approval.',
                    ['home-loan-eligibility-calculator'],
                ],
                [
                    'Plan Upfront Costs',
                    'Estimate additional property purchase costs.',
                    'Property price and location.',
                    'Taxes and registration charges vary by location.',
                    ['stamp-duty-calculator'],
                ],
                [
                    'Prepare Documents',
                    'Organize common home loan application documents.',
                    'Identity, address, income and bank documents.',
                    'Lenders may request additional documents.',
                    ['document-checklist'],
                ],
                [
                    'Compare Loan Offers',
                    'Compare interest rates, repayment terms and overall costs.',
                    'Loan amount, tenure and lender offers.',
                    'Compare total cost, not only the interest rate.',
                    ['interest-rate-calculator', 'home-loan-emi-calculator'],
                ],
                [
                    'Choose Your Loan Tenure',
                    'Understand how tenure affects EMI and total interest.',
                    'Loan amount, rate and monthly budget.',
                    'Longer tenure can increase total interest.',
                    ['loan-tenure-calculator'],
                ],
                [
                    'Plan Your Monthly Cash Flow',
                    'Review how the future EMI fits your income and expenses.',
                    'Expected EMI, income and recurring expenses.',
                    'Leave room for unexpected expenses.',
                    ['financial-flow-calendar'],
                ],
                [
                    'Review Your Repayment Plan',
                    'Consider repayment options that fit your finances.',
                    'EMI, savings and monthly commitments.',
                    'Avoid committing all available savings.',
                    ['repayment-planner'],
                ],
                [
                    'Understand Additional Payments',
                    'Learn how optional extra repayments may affect the loan.',
                    'Outstanding balance and lender terms.',
                    'Check applicable lender conditions and charges.',
                    ['prepayment-calculator'],
                ],
                [
                    'Prepare for Your Next Steps',
                    'Review estimates and organize information before proceeding.',
                    'Budget, eligibility estimate and documents.',
                    'Confirm final terms with your lender.',
                    ['document-checklist', 'glossary-learning'],
                ],
            ],

            'existing-home-loan' => [
                [
                    'Review Your Current Loan',
                    'Review outstanding balance, interest rate and remaining tenure.',
                    'Latest loan statement.',
                    'Use current lender figures.',
                    ['home-loan-emi-calculator'],
                ],
                [
                    'Review Monthly Repayments',
                    'Understand your current EMI and monthly commitments.',
                    'Outstanding principal, rate and tenure.',
                    'Consider all financial obligations.',
                    ['home-loan-emi-calculator', 'financial-flow-calendar'],
                ],
                [
                    'Evaluate Your Interest Rate',
                    'Review your rate against available lender offers.',
                    'Current loan terms and comparable offers.',
                    'Check fees before making a decision.',
                    ['interest-rate-calculator'],
                ],
                [
                    'Explore Tenure Options',
                    'Consider how a different tenure affects repayment.',
                    'Balance, rate and remaining tenure.',
                    'A lower EMI can mean higher total interest.',
                    ['loan-tenure-calculator'],
                ],
                [
                    'Plan Future Repayments',
                    'Review your repayment budget and upcoming commitments.',
                    'Income, expenses and current EMI.',
                    'Maintain emergency savings.',
                    ['financial-flow-calendar', 'repayment-planner'],
                ],
            ],

            'top-up-loan' => [
                [
                    'Review Your Existing Loan',
                    'Review your loan balance and repayment history.',
                    'Latest statement and repayment schedule.',
                    'Top-up eligibility depends on lender policy.',
                    ['home-loan-emi-calculator'],
                ],
                [
                    'Estimate Additional Borrowing',
                    'Estimate how additional borrowing could affect repayment.',
                    'Proposed amount, rate and tenure.',
                    'Consider total debt, not only the new EMI.',
                    ['home-loan-eligibility-calculator', 'home-loan-emi-calculator'],
                ],
                [
                    'Compare Repayment Options',
                    'Compare possible repayment scenarios.',
                    'Current loan details and proposed borrowing.',
                    'Confirm terms and charges with your lender.',
                    ['loan-tenure-calculator'],
                ],
                [
                    'Review Your Budget',
                    'Check whether repayment fits your monthly finances.',
                    'Income, expenses and existing EMIs.',
                    'Avoid borrowing beyond comfortable capacity.',
                    ['financial-flow-calendar', 'repayment-planner'],
                ],
            ],

            'balance-transfer' => [
                [
                    'Collect Current Loan Details',
                    'Gather your balance, interest rate and remaining tenure.',
                    'Latest statement and loan agreement.',
                    'Include transfer-related charges.',
                    ['home-loan-emi-calculator'],
                ],
                [
                    'Compare Available Offers',
                    'Review offers from other lenders.',
                    'Existing loan details and new lender terms.',
                    'A lower rate does not guarantee net savings.',
                    ['interest-rate-calculator'],
                ],
                [
                    'Estimate Potential Savings',
                    'Compare estimated repayments under different loan terms.',
                    'Outstanding balance, rate and tenure.',
                    'Include fees and other costs.',
                    ['home-loan-emi-calculator', 'loan-tenure-calculator'],
                ],
                [
                    'Review Transfer Costs',
                    'Identify possible transfer costs and required documents.',
                    'Current loan terms and lender requirements.',
                    'Confirm charges with the lenders.',
                    ['document-checklist', 'glossary-learning'],
                ],
                [
                    'Plan Your Next Steps',
                    'Review the comparison before deciding whether to proceed.',
                    'Loan comparison and estimated total costs.',
                    'Verify the final terms first.',
                    ['financial-flow-calendar'],
                ],
            ],

            'prepayment' => [
                [
                    'Review Your Outstanding Loan',
                    'Review outstanding principal, rate and remaining tenure.',
                    'Latest loan statement.',
                    'Use the current balance.',
                    ['home-loan-emi-calculator'],
                ],
                [
                    'Estimate Prepayment Savings',
                    'Estimate how an extra payment may affect interest and tenure.',
                    'Outstanding balance and extra payment amount.',
                    'Results depend on lender terms.',
                    ['prepayment-calculator'],
                ],
                [
                    'Compare Repayment Options',
                    'Compare repayment scenarios and their budget impact.',
                    'Available savings and monthly expenses.',
                    'Do not use money needed for essential expenses.',
                    ['loan-tenure-calculator', 'repayment-planner'],
                ],
                [
                    'Review Your Cash Flow',
                    'Check how an extra payment affects available savings.',
                    'Income, expenses and financial commitments.',
                    'Maintain emergency savings.',
                    ['financial-flow-calendar'],
                ],
            ],

            'loan-closure' => [
                [
                    'Confirm Outstanding Dues',
                    'Review remaining principal and other payable amounts.',
                    'Latest statement and lender closure quote.',
                    'Confirm the final amount with your lender.',
                    ['home-loan-emi-calculator', 'loan-closure-calculator'],
                ],
                [
                    'Estimate the Closure Amount',
                    'Review the estimated amount needed to close the loan.',
                    'Outstanding balance and applicable charges.',
                    'The final amount may differ from an estimate.',
                    ['loan-closure-calculator'],
                ],
                [
                    'Prepare Closure Documents',
                    'Organize documents required by the lender.',
                    'Loan account details and identity documents.',
                    'Follow the lender document process.',
                    ['document-checklist'],
                ],
                [
                    'Review Your Final Payment',
                    'Plan the final payment and available funds.',
                    'Confirmed closure quote and payment arrangements.',
                    'Keep funds for other essential commitments.',
                    ['financial-flow-calendar'],
                ],
                [
                    'Confirm Loan Closure',
                    'Follow up with the lender and retain closure confirmation.',
                    'Payment confirmation and closure documents.',
                    'Keep the records safely.',
                    ['glossary-learning'],
                ],
            ],
        ];

        DB::transaction(function () use ($journeys): void {
            foreach ($journeys as $journeySlug => $steps) {
                $journey = ToolJourney::query()
                    ->where('slug', $journeySlug)
                    ->first();

                if (! $journey) {
                    $this->command?->warn(
                        "Journey {$journeySlug} not found; skipping."
                    );

                    continue;
                }

                // Remove existing steps for this seeded journey.
                // This also clears pivot associations via cascade/delete.
                $oldSteps = JourneyStep::query()
                    ->where('journey_id', $journey->id)
                    ->get();

                foreach ($oldSteps as $oldStep) {
                    $oldStep->tools()->detach();
                    $oldStep->delete();
                }

                foreach ($steps as $index => $row) {
                    [
                        $title,
                        $description,
                        $whatYouNeed,
                        $watchOutFor,
                        $toolSlugs,
                    ] = $row;

                    $step = JourneyStep::create([
                        'journey_id' => $journey->id,
                        'step_number' => $index + 1,
                        'title' => $title,
                        'description' => $description,
                        'what_you_need' => $whatYouNeed,
                        'watch_out_for' => $watchOutFor,
                        'display_order' => $index + 1,
                        'status' => 'active',
                    ]);

                    $toolIds = Tool::query()
                        ->whereIn('slug', $toolSlugs)
                        ->pluck('id')
                        ->all();

                    $step->tools()->sync($toolIds);
                }
            }
        });
    }
}
