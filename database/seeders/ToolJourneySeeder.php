<?php
namespace Database\Seeders;

use App\Models\ToolJourney;
use Illuminate\Database\Seeder;

class ToolJourneySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['First-Time Home Buyer','first-time-home-buyer','First Home','Plan the steps involved in buying your first home.',1],
            ['Existing Home Loan','existing-home-loan','Existing','Manage and review an existing home loan.',2],
            ['Top-Up Loan','top-up-loan','Top-Up','Explore considerations for a home loan top-up.',3],
            ['Balance Transfer','balance-transfer','Transfer','Review whether a balance transfer may suit your loan.',4],
            ['Prepayment','prepayment','Prepayment','Plan additional payments against your loan.',5],
            ['Loan Closure','loan-closure','Closure','Prepare for final repayment and loan closure.',6],
        ];
        foreach ($rows as [$name,$slug,$label,$description,$order]) {
            ToolJourney::updateOrCreate(['slug'=>$slug],[
                'name'=>$name,'short_label'=>$label,'description'=>$description,'display_order'=>$order,'status'=>'active',
            ]);
        }
    }
}
