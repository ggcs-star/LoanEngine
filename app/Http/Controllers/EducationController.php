<?php

namespace App\Http\Controllers;

use App\Models\EducationCategory;
use App\Models\EducationLesson;
use App\Models\EducationModule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducationController extends Controller
{
    public function index(Request $request): View
    {
        $categories = EducationCategory::query()
            ->active()
            ->whereHas('modules', fn ($query) => $query->published())
            ->with([
                'modules' => fn ($query) => $query
                    ->published()
                    ->withCount('publishedLessons')
                    ->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->get();

        return view('education.index', compact('categories'));
    }

    public function show(string $slug): View
    {
        $module = EducationModule::query()
            ->published()
            ->where('slug', $slug)
            ->whereHas('category', fn ($query) => $query->active())
            ->with([
                'category',
                'publishedLessons' => fn ($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->firstOrFail();

        return view('education.show', compact('module'));
    }

    public function lesson(string $moduleSlug, string $lessonSlug): View
    {
        $module = EducationModule::query()
            ->published()
            ->where('slug', $moduleSlug)
            ->whereHas('category', fn ($query) => $query->active())
            ->firstOrFail();

        $lesson = EducationLesson::query()
            ->published()
            ->where('education_module_id', $module->id)
            ->where('slug', $lessonSlug)
            ->firstOrFail();

        $nextLesson = EducationLesson::query()
            ->published()
            ->where('education_module_id', $module->id)
            ->where(function ($query) use ($lesson) {
                $query->where('sort_order', '>', $lesson->sort_order)
                    ->orWhere(function ($nested) use ($lesson) {
                        $nested->where('sort_order', $lesson->sort_order)
                            ->where('id', '>', $lesson->id);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return view(
            'education.lesson',
            compact('module', 'lesson', 'nextLesson')
        );
    }
}
