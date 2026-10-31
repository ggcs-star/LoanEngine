<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\Api\Education\EducationModuleService;
use App\Services\Tracking\UserActivityTracker;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class EducationModuleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly EducationModuleService $moduleService,
        private readonly UserActivityTracker $activityTracker
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'search' => ['nullable', 'string', 'max:150'],
                'category_id' => ['nullable', 'integer', 'min:1'],
                'sort' => ['nullable', 'in:latest,oldest,title'],
                'page' => ['nullable', 'integer', 'min:1'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            ]);

            $modules = $this->moduleService->paginateModules($filters);

            return $this->successResponse(
                'Education modules fetched successfully.',
                [
                    'items' => $modules->getCollection()->map(
                        fn ($module) => [
                            'id' => $module->id,
                            'education_category_id' =>
                                $module->education_category_id,
                            'title' => $module->title,
                            'slug' => $module->slug,
                            'description' => $module->description,
                            'thumbnail_path' => $module->thumbnail_path,
                            'category' => $module->category ? [
                                'id' => $module->category->id,
                                'name' => $module->category->name,
                                'slug' => $module->category->slug,
                            ] : null,
                            'published_lessons_count' =>
                                $module->published_lessons_count,
                            'duration_seconds' =>
                                (int) ($module->published_duration_seconds ?? 0),
                        ]
                    )->values(),

                    'pagination' => [
                        'current_page' => $modules->currentPage(),
                        'per_page' => $modules->perPage(),
                        'total' => $modules->total(),
                        'last_page' => $modules->lastPage(),
                    ],
                ]
            );
        } catch (ValidationException $exception) {
            return $this->errorResponse('Something went wrong', 422);
        } catch (Throwable $exception) {
            $this->logApiException($exception);

            return $this->serverErrorResponse('Something went wrong');
        }
    }

    public function show(int $module, Request $request): JsonResponse
    {
        try {
            $item = $this->moduleService->findPublishedModule($module);

            if (! $item) {
                return $this->notFoundResponse('Something went wrong');
            }

            $firstLesson = $this->moduleService
                ->findFirstPublishedLesson($item);

            // Track successful module views without breaking the API response.
            try {
                $user = $request->user();

                if ($user) {
                    $this->activityTracker->education(
                        $user,
                        'module_viewed',
                        'education_module',
                        (int) $item->id
                    );
                }
            } catch (Throwable $exception) {
                $this->logApiException($exception);
            }

            return $this->successResponse(
                'Education module fetched successfully.',
                [
                    'id' => $item->id,
                    'education_category_id' => $item->education_category_id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'thumbnail_path' => $item->thumbnail_path,
                    'category' => [
                        'id' => $item->category->id,
                        'name' => $item->category->name,
                        'slug' => $item->category->slug,
                    ],
                    'published_lessons_count' =>
                        $item->published_lessons_count,
                    'duration_seconds' =>
                        (int) ($item->published_duration_seconds ?? 0),
                    'first_lesson' => $firstLesson ? [
                        'id' => $firstLesson->id,
                        'title' => $firstLesson->title,
                        'slug' => $firstLesson->slug,
                        'duration_seconds' => $firstLesson->duration_seconds,
                    ] : null,
                ]
            );
        } catch (Throwable $exception) {
            $this->logApiException($exception);

            return $this->serverErrorResponse('Something went wrong');
        }
    }
}
