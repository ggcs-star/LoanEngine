<?php

namespace Database\Seeders;

use App\Models\EducationCategory;
use App\Models\EducationLesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EducationLessonSeeder extends Seeder
{
    public function run(): void
    {
        $categories = EducationCategory::with('modules')
            ->whereIn('slug', ['videos', 'audio', 'podcast', 'reels'])
            ->orderBy('sort_order')
            ->get();

        if ($categories->isEmpty()) {
            $this->command?->warn(
                'Videos, Audio, Podcast, Reels categories nahi mili. Category slugs check karo.'
            );

            return;
        }

        $lessonTemplates = [
            'videos' => [
                'content_type' => 'video',
                'title' => 'Understanding the Basics',
                'description' => 'Learn the essential concepts step by step.',
                'content' => "In this lesson, you will learn the basic concepts, important terms, and practical points related to this topic.\n\nKey takeaways:\n- Understand the main concept.\n- Learn the important terminology.\n- Identify common mistakes.\n- Review the next steps before making a financial decision.",
            ],
            'audio' => [
                'content_type' => 'audio',
                'title' => 'Audio Learning Guide',
                'description' => 'A concise audio lesson with key concepts and takeaways.',
                'content' => "Welcome to this audio learning guide.\n\nToday, we will explore the key concepts related to this topic. Start by understanding the basic terminology, review the important points, and consider how they apply to your financial situation.\n\nBefore making a decision, compare your options and check the relevant terms and conditions.",
            ],
            'podcast' => [
                'content_type' => 'audio',
                'title' => 'Financial Insights Podcast',
                'description' => 'An educational podcast lesson covering practical financial insights.',
                'content' => "Welcome to this financial insights episode.\n\nIn this episode, we discuss the topic's fundamentals, common questions, and practical considerations. Understand the available options, compare their costs, and evaluate the long-term impact before making a decision.\n\nThis educational content is general information, not personalised financial advice.",
            ],
            'reels' => [
                'content_type' => 'reel',
                'title' => 'Quick Financial Tips',
                'description' => 'A short lesson highlighting essential financial tips.',
                'content' => "Quick learning points:\n1. Understand the total cost, not just the monthly payment.\n2. Compare available options before deciding.\n3. Read eligibility conditions and fees carefully.\n4. Keep your financial documents organised.\n\nTake time to verify information before making a financial commitment.",
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($categories as $category) {
            $categorySlug = Str::slug($category->slug);

            if (! isset($lessonTemplates[$categorySlug])) {
                continue;
            }

            $template = $lessonTemplates[$categorySlug];

            foreach ($category->modules as $module) {
                $title = $template['title'];
                $slug = Str::slug($title);

                $existing = EducationLesson::withTrashed()
                    ->where('education_module_id', $module->id)
                    ->where('slug', $slug)
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                EducationLesson::create([
                    'education_module_id' => $module->id,
                    'title' => $title,
                    'slug' => $slug,
                    'description' => $template['description'],
                    'content' => $template['content'],
                    'content_type' => $template['content_type'],
                    'media_path' => null,
                    'external_media_url' => null,
                    'thumbnail_path' => null,
                    'duration_seconds' => 0,
                    'sort_order' => 1,
                    'status' => 'draft',
                ]);

                $created++;
            }
        }

        $this->command?->info("Lessons created: {$created}");
        $this->command?->info("Existing lessons skipped: {$skipped}");
    }
}