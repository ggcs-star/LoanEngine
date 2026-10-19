<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            EducationCategorySeeder::class,
            EducationModuleSeeder::class,
            EducationLessonSeeder::class,

            ToolJourneySeeder::class,
            ToolSeeder::class,
            JourneyStepSeeder::class,
        ]);
    }
}