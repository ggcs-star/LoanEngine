<?php
namespace Database\Seeders;

use App\Models\JourneyStep;
use App\Models\Tool;
use App\Models\ToolJourney;
use Illuminate\Database\Seeder;

class JourneyStepSeeder extends Seeder
{
    public function run(): void
    {
        // Step 01 is based on the supplied screenshot. Other step titles below are starter copy because the full original 11-step content was not present in the accessible source files.
        $journeys = [
            'first-time-home-buyer' => [
                ['Understand the Concept','Get comfortable with how home loans actually work — principal, interest, EMI, tenure, and how a lender decides how much to lend you.','Basic clarity on income proof, existing debts, and repayment ability.','Skipping this step and jumping straight to property-hunting without knowing your real borrowing capacity.',['home-loan-emi-calculator','glossary-learning']],
                ['Assess Your Budget','Estimate a comfortable property budget.','Monthly income, expenses and current obligations.','Keep emergency savings separate from upfront costs.',['home-affordability-calculator','home-loan-emi-calculator']],
                ['Check Loan Eligibility','Review common eligibility factors before applying.','Income, employment details and existing EMIs.','An estimate is not a loan approval.',['home-loan-eligibility-calculator']],
                ['Plan Upfront Costs','Estimate stamp duty and purchase costs.','Property price and location.','Charges vary by location and property details.',['stamp-duty-calculator']],
                ['Prepare Documents','Organize common documents for an application.','Identity, address, income and bank documents.','The lender may request additional documents.',['document-checklist']],
                ['Compare Loan Offers','Compare rates, tenure and total costs.','Loan amount, tenure and available lender offers.','Do not compare offers using interest rate alone.',['interest-rate-finder','glossary-learning']],
            ],
            'existing-home-loan' => [
                ['Review Your Current Loan','Review outstanding balance, rate and remaining tenure.','Latest loan statement and repayment schedule.','Use current lender figures for estimates.',['home-loan-emi-calculator','loan-tenure-calculator']],
                ['Plan Monthly Repayments','Understand the impact of EMI and tenure.','Outstanding principal, rate and remaining tenure.','Check lender terms before changing repayment plans.',['home-loan-emi-calculator','financial-flow-calendar']],
            ],
            'top-up-loan' => [
                ['Review Your Existing Loan','Check current loan status and outstanding balance.','Current loan statement and repayment history.','Top-up availability depends on lender policy.',['home-loan-emi-calculator']],
                ['Estimate Repayment','Estimate how additional borrowing affects repayment.','Proposed top-up amount, rate and tenure.','Consider total debt, not only the new EMI.',['home-loan-emi-calculator','loan-tenure-calculator']],
            ],
            'balance-transfer' => [
                ['Collect Current Loan Details','Gather current rate, balance and remaining tenure.','Latest statement and transfer charges if applicable.','Check processing fees and other transfer costs.',['home-loan-emi-calculator','glossary-learning']],
                ['Compare Transfer Offers','Compare possible savings against transfer costs.','Existing loan terms and new lender offer.','A lower rate does not guarantee net savings.',['interest-rate-finder','home-loan-emi-calculator']],
            ],
            'prepayment' => [
                ['Review Prepayment Options','Estimate how an extra payment may affect your loan.','Outstanding balance, rate, tenure and extra payment amount.','Check applicable prepayment terms and charges.',['prepayment-calculator']],
                ['Choose a Repayment Strategy','Compare possible repayment and tenure scenarios.','Budget and lender repayment terms.','Keep sufficient savings for emergencies.',['loan-tenure-calculator','financial-flow-calendar']],
            ],
            'loan-closure' => [
                ['Confirm Outstanding Dues','Review remaining principal and payable charges.','Latest statement and lender-provided closure quote.','Confirm the final amount with the lender.',['loan-closure-calculator']],
                ['Prepare for Closure','Follow the lender process for final payment and documents.','Loan account details and required identity documents.','Keep closure confirmation and related documents safely.',['document-checklist','glossary-learning']],
            ],
        ];

        foreach ($journeys as $journeySlug=>$steps) {
            $journey = ToolJourney::where('slug',$journeySlug)->first();
            if (!$journey) { $this->command?->warn("Missing journey {$journeySlug}; run ToolJourneySeeder first."); continue; }
            foreach ($steps as $i=>$row) {
                [$title,$description,$whatYouNeed,$watchOutFor,$toolSlugs] = $row;
                $step = JourneyStep::updateOrCreate(
                    ['journey_id'=>$journey->id,'step_number'=>$i+1],
                    ['title'=>$title,'description'=>$description,'what_you_need'=>$whatYouNeed,'watch_out_for'=>$watchOutFor,'display_order'=>$i+1,'status'=>'active']
                );
                $toolIds = Tool::whereIn('slug',$toolSlugs)->pluck('id')->all();
                $step->tools()->syncWithoutDetaching($toolIds);
            }
        }
    }
}
