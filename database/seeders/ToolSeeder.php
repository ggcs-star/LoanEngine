<?php
namespace Database\Seeders;

use App\Models\Tool;
use App\Models\ToolJourney;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        // First ten tool names/visible fields follow the supplied screenshot. Last two complete its stated total of 12.
        $rows = [
            ['Home Affordability Calculator','home-affordability-calculator','Find the property budget that suits you based on income, expenses and eligibility.','house','Calculators','Calculator','active',1,'/tools/home-affordability-calculator',true,['first-time-home-buyer']],
            ['Home Loan Eligibility Calculator','home-loan-eligibility-calculator','Know how much home loan you may qualify for.','calculator','Calculators','Calculator','active',2,'/tools/home-loan-eligibility-calculator',true,['first-time-home-buyer']],
            ['Home Loan EMI Calculator','home-loan-emi-calculator','Calculate your estimated monthly home loan EMI.','calculator','Calculators','Calculator','active',3,'/tools/home-loan-emi-calculator',true,['first-time-home-buyer','existing-home-loan','top-up-loan','balance-transfer','prepayment','loan-closure']],
            ['Stamp Duty Calculator','stamp-duty-calculator','Estimate stamp duty and registration costs.','receipt','Calculators','Calculator','active',4,'/tools/stamp-duty-calculator',true,['first-time-home-buyer']],
            ['Interest Rate Finder','interest-rate-finder','Compare home loan interest rates and offers.','percent','Calculators','Calculator','planned',5,'/tools/interest-rate-finder',false,['existing-home-loan','balance-transfer']],
            ['Loan Tenure Calculator','loan-tenure-calculator','Plan your repayment tenure.','calendar','Calculators','Calculator','active',6,'/tools/loan-tenure-calculator',true,['existing-home-loan','top-up-loan','prepayment']],
            ['Financial Flow Calendar','financial-flow-calendar','Track income, EMIs and financial commitments.','calendar-days','Planning','Calendar','active',7,'/tools/financial-flow-calendar',true,['first-time-home-buyer','existing-home-loan','top-up-loan','balance-transfer','prepayment','loan-closure']],
            ['Prepayment Calculator','prepayment-calculator','Calculate potential interest savings from prepayments.','trending-up','Calculators','Calculator','active',8,'/tools/prepayment-calculator',true,['prepayment']],
            ['Loan Closure Calculator','loan-closure-calculator','Estimate the final payable amount before closure.','check-circle','Calculators','Calculator','planned',9,'/tools/loan-closure-calculator',false,['loan-closure']],
            ['Glossary & Learning','glossary-learning','Important home loan terms explained simply.','book-open','Resources','Information','active',10,'/tools/glossary-learning',true,['first-time-home-buyer','existing-home-loan','top-up-loan','balance-transfer','prepayment','loan-closure']],
            ['Document Checklist','document-checklist','Keep track of common home loan documents.','clipboard-list','Resources','Checklist','active',11,'/tools/document-checklist',true,['first-time-home-buyer','top-up-loan','balance-transfer']],
            ['Repayment Planner','repayment-planner','Review repayment options and plan ahead.','calendar-check','Planning','Planner','active',12,'/tools/repayment-planner',true,['existing-home-loan','prepayment','loan-closure']],
        ];
        foreach ($rows as [$name,$slug,$description,$icon,$category,$type,$status,$order,$route,$home,$journeySlugs]) {
            $tool = Tool::updateOrCreate(['slug'=>$slug],[
                'name'=>$name,'short_description'=>$description,'icon'=>$icon,'category'=>$category,'type'=>$type,
                'status'=>$status,'display_order'=>$order,'page_route'=>$route,'show_on_toolkit_home'=>$home,
            ]);
            $journeyIds = ToolJourney::whereIn('slug',$journeySlugs)->pluck('id')->all();
            $tool->journeys()->syncWithoutDetaching($journeyIds);
        }
    }
}
