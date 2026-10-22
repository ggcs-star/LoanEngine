<?php

namespace Database\Seeders;

use App\Models\ToolJourney;
use Illuminate\Database\Seeder;

class ToolJourneySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'name' => 'First-Time Home Buyer',
                'slug' => 'first-time-home-buyer',
                'short_label' => 'First Home',
                'description' => 'Step-by-step guidance for buying your first home.',
                'display_order' => 1,
            ],
            [
                'name' => 'Existing Home Loan',
                'slug' => 'existing-home-loan',
                'short_label' => 'Existing',
                'description' => 'Manage and review an existing home loan.',
                'display_order' => 2,
            ],
            [
                'name' => 'Top-Up Loan',
                'slug' => 'top-up-loan',
                'short_label' => 'Top-Up',
                'description' => 'Explore considerations for a home loan top-up.',
                'display_order' => 3,
            ],
            [
                'name' => 'Balance Transfer',
                'slug' => 'balance-transfer',
                'short_label' => 'Transfer',
                'description' => 'Review whether a balance transfer may suit your loan.',
                'display_order' => 4,
            ],
            [
                'name' => 'Prepayment',
                'slug' => 'prepayment',
                'short_label' => 'Prepayment',
                'description' => 'Plan additional payments against your loan.',
                'display_order' => 5,
            ],
            [
                'name' => 'Loan Closure',
                'slug' => 'loan-closure',
                'short_label' => 'Closure',
                'description' => 'Prepare for final repayment and loan closure.',
                'display_order' => 6,
            ],
        ];

        foreach ($rows as $row) {
            ToolJourney::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    ...$row,
                    'status' => 'active',
                ]
            );
        }
    }
}
