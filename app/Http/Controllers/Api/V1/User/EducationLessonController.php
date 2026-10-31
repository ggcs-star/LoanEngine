<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\Api\Education\EducationLessonService;
use App\Services\Tracking\UserActivityTracker;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EducationLessonController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly EducationLessonService $lessonService,
        private readonly UserActivityTracker $activityTracker
    ) {}

    public function show(int $lesson, Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return $this->unauthorizedResponse('Something went wrong');
            }

            $item = $this->lessonService->findAvailableLesson($lesson);

            if (! $item) {
                return $this->notFoundResponse('Something went wrong');
            }

            $lessons = $this->lessonService
                ->getPublishedLessonsForModule($item->education_module_id);

            $currentIndex = $lessons->search(
                fn ($row) => (string) $row->id === (string) $item->id
            );

            $previous = $currentIndex !== false && $currentIndex > 0
                ? $lessons->get($currentIndex - 1)
                : null;

            $next = $currentIndex !== false
                ? $lessons->get($currentIndex + 1)
                : null;

            $completed = $this->lessonService->isCompleted(
                $user,
                (int) $item->id
            );

            // Track successful lesson views without breaking the API response.
            try {
                $this->activityTracker->education(
                    $user,
                    'lesson_viewed',
                    'education_lesson',
                    (int) $item->id
                );
            } catch (Throwable $exception) {
                $this->logApiException($exception);
            }

            return $this->successResponse(
                'Education lesson fetched successfully.',
                [
                    'lesson' => [
                        'id' => $item->id,
                        'education_module_id' => $item->education_module_id,
                        'title' => $item->title,
                        'slug' => $item->slug,
                        'description' => $item->description,
                        'content' => $item->content,
                        'content_type' => $item->content_type,
                        'media_path' => $item->media_path,
                        'external_media_url' => $item->external_media_url,
                        'thumbnail_path' => $item->thumbnail_path,
                        'duration_seconds' => $item->duration_seconds,
                        'sort_order' => $item->sort_order,
                        'is_completed' => $completed,
                    ],
                    'module' => [
                        'id' => $item->module->id,
                        'title' => $item->module->title,
                        'slug' => $item->module->slug,
                    ],
                    'category' => [
                        'id' => $item->module->category->id,
                        'name' => $item->module->category->name,
                        'slug' => $item->module->category->slug,
                    ],
                    'previous_lesson' => $previous ? [
                        'id' => $previous->id,
                        'title' => $previous->title,
                        'slug' => $previous->slug,
                        'duration_seconds' => $previous->duration_seconds,
                    ] : null,
                    'next_lesson' => $next ? [
                        'id' => $next->id,
                        'title' => $next->title,
                        'slug' => $next->slug,
                        'duration_seconds' => $next->duration_seconds,
                    ] : null,
                ]
            );
        } catch (Throwable $exception) {
            $this->logApiException($exception);

            return $this->serverErrorResponse('Something went wrong');
        }
    }
}
