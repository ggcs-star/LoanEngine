<?php

namespace Database\Seeders;

use App\Models\Tool;
use App\Models\ToolJourney;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'name' => 'First Time Buyer',
                'slug' => 'first-time-buyer',
                'description' => 'Comprehensive pathways for new homeowners.',
                'icon' => 'house',
                'category' => 'Resources',
                'type' => 'Resource',
                'order' => 1,
                'route' => '/user/tools/first-time-buyer',
                'journeys' => ['first-time-home-buyer'],
            ],
            [
                'name' => 'Existing Owner',
                'slug' => 'existing-owner',
                'description' => 'Refinancing strategies and equity optimization.',
                'icon' => 'house',
                'category' => 'Resources',
                'type' => 'Resource',
                'order' => 2,
                'route' => '/user/tools/existing-owner',
                'journeys' => ['existing-home-loan'],
            ],
            [
                'name' => 'Home Loan Calculators',
                'slug' => 'home-loan-calculators',
                'description' => 'Home loan and property finance calculators.',
                'icon' => 'calculator',
                'category' => 'Calculators',
                'type' => 'Calculator',
                'order' => 3,
                'route' => '/tools',
                'journeys' => [
                    'first-time-home-buyer',
                    'existing-home-loan',
                    'top-up-loan',
                    'balance-transfer',
                    'prepayment',
                    'loan-closure',
                ],
            ],
            [
                'name' => 'Financial Flow Calendar',
                'slug' => 'financial-flow-calendar',
                'description' => 'Track income, EMIs and financial commitments.',
                'icon' => 'calendar-days',
                'category' => 'Planning',
                'type' => 'Calendar',
                'order' => 4,
                'route' => '/user/financial-calendar',
                'journeys' => [
                    'first-time-home-buyer',
                    'existing-home-loan',
                    'top-up-loan',
                    'balance-transfer',
                    'prepayment',
                    'loan-closure',
                ],
            ],
        ];

        foreach ($rows as $row) {
            $tool = Tool::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'short_description' => $row['description'],
                    'icon' => $row['icon'],
                    'category' => $row['category'],
                    'type' => $row['type'],
                    'status' => 'active',
                    'display_order' => $row['order'],
                    'page_route' => $row['route'],
                    'show_on_toolkit_home' => true,
                ]
            );

            $journeyIds = ToolJourney::query()
                ->whereIn('slug', $row['journeys'])
                ->pluck('id')
                ->all();

            $tool->journeys()->sync($journeyIds);
        }
    }
}
