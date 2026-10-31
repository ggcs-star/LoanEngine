<?php

namespace App\Services\Api\Education;

use App\Models\EducationCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EducationCategoryService
{
    public function paginateCategories(
        array $filters
    ): LengthAwarePaginator {
        return EducationCategory::query()
            ->active()
            ->withCount([
                'modules as published_modules_count' =>
                    fn ($query) => $query->published(),
            ])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 9);
    }

    public function findActiveCategory(int $id): ?EducationCategory
    {
        return EducationCategory::query()
            ->active()
            ->withCount([
                'modules as published_modules_count' =>
                    fn ($query) => $query->published(),
            ])
            ->find($id);
    }

    public function paginateCategoryModules(
        int $categoryId,
        array $filters
    ): ?array {
        $category = EducationCategory::query()
            ->active()
            ->find($categoryId);

        if (! $category) {
            return null;
        }

        $modules = $category->modules()
            ->published()
            ->whereHas('publishedLessons')
            ->withCount([
                'publishedLessons as published_lessons_count',
            ])
            ->withSum(
                'publishedLessons as published_duration_seconds',
                'duration_seconds'
            )
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate($filters['per_page'] ?? 9);

        return [
            'category' => $category,
            'modules' => $modules,
        ];
    }
}
