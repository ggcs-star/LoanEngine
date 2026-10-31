<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\Api\Education\EducationCategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class EducationCategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly EducationCategoryService $categoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'search' => ['nullable', 'string', 'max:150'],
                'page' => ['nullable', 'integer', 'min:1'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            ]);

            $categories = $this->categoryService->paginateCategories($filters);

            $data = [
                'items' => $categories->getCollection()->map(
                    fn ($category) => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'published_modules_count' =>
                            $category->published_modules_count,
                    ]
                )->values(),
                'pagination' => [
                    'current_page' => $categories->currentPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'last_page' => $categories->lastPage(),
                ],
            ];

            return $this->successResponse(
                'Education categories fetched successfully.',
                $data
            );
        } catch (ValidationException $exception) {
            return $this->errorResponse('Something went wrong', 422);
        } catch (Throwable $exception) {
            $this->logApiException($exception);

            return $this->serverErrorResponse('Something went wrong');
        }
    }

    public function show(int $category): JsonResponse
    {
        try {
            $item = $this->categoryService->findActiveCategory($category);

            if (! $item) {
                return $this->notFoundResponse('Something went wrong');
            }

            return $this->successResponse(
                'Education category fetched successfully.',
                [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'published_modules_count' =>
                        $item->published_modules_count,
                ]
            );
        } catch (Throwable $exception) {
            $this->logApiException($exception);

            return $this->serverErrorResponse('Something went wrong');
        }
    }

    public function modules(Request $request, int $category): JsonResponse
    {
        try {
            $filters = $request->validate([
                'page' => ['nullable', 'integer', 'min:1'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            ]);

            $result = $this->categoryService->paginateCategoryModules(
                $category,
                $filters
            );

            if (! $result) {
                return $this->notFoundResponse('Something went wrong');
            }

            $modules = $result['modules'];
            $item = $result['category'];

            return $this->successResponse(
                'Education modules fetched successfully.',
                [
                    'category' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'slug' => $item->slug,
                    ],
                    'items' => $modules->getCollection()->map(
                        fn ($module) => [
                            'id' => $module->id,
                            'education_category_id' =>
                                $module->education_category_id,
                            'title' => $module->title,
                            'slug' => $module->slug,
                            'description' => $module->description,
                            'thumbnail_path' => $module->thumbnail_path,
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
}
