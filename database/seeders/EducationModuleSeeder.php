<?php

namespace Database\Seeders;

use App\Models\EducationCategory;
use App\Models\EducationModule;
use Illuminate\Database\Seeder;

class EducationModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Module names visible in the supplied mobile screenshot.
        $rows = [
            ['audio', 'Basics', 'basics', 'Learn the fundamentals of home loans.', 1],
            ['audio', 'Eligibility & Credit Score', 'eligibility-credit-score', 'Understand eligibility factors and credit scores.', 2],
        ];

        foreach ($rows as [$categorySlug, $title, $slug, $description, $order]) {
            $category = EducationCategory::where('slug', $categorySlug)->first();
            if (!$category) {
                $this->command?->warn("Missing category {$categorySlug}; run EducationCategorySeeder first.");
                continue;
            }

            EducationModule::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                ['education_category_id' => $category->id, 'title' => $title,
                 'description' => $description, 'thumbnail_path' => null,
                 'sort_order' => $order, 'status' => 'published', 'deleted_at' => null]
            );
        }
    }
}
