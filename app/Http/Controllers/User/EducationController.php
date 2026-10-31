<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EducationCategory;
use App\Models\EducationLesson;
use App\Models\EducationModule;
use App\Services\Tracking\UserActivityTracker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $search = trim($filters['search'] ?? '');

        $categories = EducationCategory::query()
            ->active()
            ->withCount([
                'modules as published_modules_count' => function ($query) {
                    $query->published();
                },
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('user.education.index', compact(
            'categories',
            'filters'
        ));
    }

    public function category(
        EducationCategory $category,
        UserActivityTracker $tracker
    ): View {
        abort_unless($category->is_active, 404);

        $tracker->education(
            auth()->user(),
            'category_viewed',
            'category',
            $category->id
        );

        $modules = EducationModule::query()
            ->where('education_category_id', $category->getKey())
            ->published()
            ->withCount([
                'publishedLessons as published_lessons_count',
            ])
            ->withSum([
                'publishedLessons as published_duration_seconds' => function ($query) {
                    $query->published();
                },
            ], 'duration_seconds')
            ->having('published_lessons_count', '>', 0)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(9)
            ->withQueryString();

        return view('user.education.category', compact(
            'category',
            'modules'
        ));
    }

    public function module(
        EducationModule $module,
        UserActivityTracker $tracker
    ) {
        abort_unless($module->status === 'published', 404);
        abort_unless($module->category?->is_active, 404);

        $tracker->education(
            auth()->user(),
            'module_viewed',
            'module',
            $module->id
        );

        $firstLesson = $module->publishedLessons()
            ->whereHas('module', function ($query) {
                $query->where('status', 'published')
                    ->whereHas('category', function ($categoryQuery) {
                        $categoryQuery->where('is_active', true);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if (! $firstLesson) {
            return view('user.education.module', [
                'module' => $module,
                'lessons' => collect(),
            ]);
        }

        return redirect()->route(
            'user.education.lesson',
            $firstLesson
        );
    }

    public function lesson(
        Request $request,
        EducationLesson $lesson,
        UserActivityTracker $tracker
    ): View {
        abort_unless($lesson->status === 'published', 404);

        $lesson->load('module.category');

        abort_unless(
            $lesson->module
                && $lesson->module->status === 'published'
                && $lesson->module->category?->is_active,
            404
        );

        $tracker->education(
            $request->user(),
            'lesson_viewed',
            'lesson',
            $lesson->id
        );

        $siblings = EducationLesson::query()
            ->where('education_module_id', $lesson->education_module_id)
            ->published()
            ->whereHas('module', function ($query) {
                $query->where('status', 'published')
                    ->whereHas('category', function ($categoryQuery) {
                        $categoryQuery->where('is_active', true);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'education_module_id',
                'title',
                'description',
                'content',
                'content_type',
                'media_path',
                'external_media_url',
                'thumbnail_path',
                'duration_seconds',
                'sort_order',
                'status',
            ]);

        $currentIndex = $siblings->search(
            fn ($item) => (int) $item->id === (int) $lesson->id
        );

        abort_if($currentIndex === false, 404);

        $previousLesson = $currentIndex > 0
            ? $siblings[$currentIndex - 1]
            : null;

        $nextLesson = $currentIndex < $siblings->count() - 1
            ? $siblings[$currentIndex + 1]
            : null;

        // Check completion for this authenticated user only.
        $isCompleted = $request->user()
            ->lessonProgress()
            ->where('lesson_id', $lesson->id)
            ->whereNotNull('completed_at')
            ->exists();

        return view('user.education.lesson', compact(
            'lesson',
            'siblings',
            'previousLesson',
            'nextLesson',
            'isCompleted'
        ));
    }
}
