<?php

namespace Database\Seeders;

use App\Models\EducationCategory;
use Illuminate\Database\Seeder;

class EducationCategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Videos', 'videos', 'Video guides, tutorials and expert tips.', 1],
            ['Audio', 'audio', 'Learn about home loans and personal finance on the go.', 2],
            ['Podcast', 'podcast', 'Expert conversations and market insights.', 3],
            ['Reels', 'reels', 'Short, practical loan and finance explainers.', 4],
        ];

        foreach ($rows as [$name, $slug, $description, $order]) {
            EducationCategory::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'sort_order' => $order,
                 'is_active' => true, 'thumbnail_path' => null, 'deleted_at' => null]
            );
        }
    }
}
