<?php

namespace App\Services\Api\Education;

use App\Models\EducationLesson;
use Illuminate\Database\Eloquent\Collection;

class EducationLessonService
{
    public function findAvailableLesson(int $id): ?EducationLesson
    {
        return EducationLesson::query()
            ->published()
            ->whereHas('module', function ($query) {
                $query->published()
                    ->whereHas('category', fn ($categoryQuery) =>
                        $categoryQuery->active()
                    );
            })
            ->with([
                'module:id,education_category_id,title,slug',
                'module.category:id,name,slug',
            ])
            ->find($id);
    }

    public function getPublishedLessonsForModule(
        int $moduleId
    ): Collection {
        return EducationLesson::query()
            ->published()
            ->where('education_module_id', $moduleId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'education_module_id',
                'title',
                'slug',
                'duration_seconds',
                'sort_order',
            ]);
    }

    public function isCompleted(object $user, int $lessonId): bool
    {
        return $user->lessonProgress()
            ->where('lesson_id', $lessonId)
            ->whereNotNull('completed_at')
            ->exists();
    }
}
