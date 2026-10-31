<?php

namespace App\Services\Api\Education;

use App\Models\EducationModule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EducationModuleService
{
    public function paginateModules(
        array $filters
    ): LengthAwarePaginator {
        $query = EducationModule::query()
            ->published()
            ->whereHas('category', fn ($query) => $query->active())
            ->whereHas('publishedLessons')
            ->with('category:id,name,slug')
            ->withCount([
                'publishedLessons as published_lessons_count',
            ])
            ->withSum(
                'publishedLessons as published_duration_seconds',
                'duration_seconds'
            )
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(
                $filters['category_id'] ?? null,
                fn ($query, $categoryId) =>
                    $query->where('education_category_id', $categoryId)
            );

        switch ($filters['sort'] ?? 'latest') {
            case 'oldest':
                $query->orderBy('created_at');
                break;

            case 'title':
                $query->orderBy('title');
                break;

            default:
                $query->latest();
                break;
        }

        return $query->paginate($filters['per_page'] ?? 9);
    }

    public function findPublishedModule(int $id): ?EducationModule
    {
        return EducationModule::query()
            ->published()
            ->whereHas('category', fn ($query) => $query->active())
            ->whereHas('publishedLessons')
            ->with([
                'category:id,name,slug',
            ])
            ->withCount([
                'publishedLessons as published_lessons_count',
            ])
            ->withSum(
                'publishedLessons as published_duration_seconds',
                'duration_seconds'
            )
            ->find($id);
    }

    public function findFirstPublishedLesson(EducationModule $module)
    {
        return $module->publishedLessons()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }
}
