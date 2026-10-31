<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\Api\Tools\ToolService;
use App\Services\Tracking\UserActivityTracker;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ToolController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ToolService $toolService,
        private readonly UserActivityTracker $activityTracker
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'search' => ['nullable', 'string', 'max:120'],
                'category' => ['nullable', 'string', 'max:120'],
                'sort' => ['nullable', 'in:recommended,name,latest'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
                'page' => ['nullable', 'integer', 'min:1'],
            ]);

            $tools = $this->toolService->paginate($filters);

            return $this->successResponse(
                'Tools fetched successfully.',
                [
                    'items' => $tools->getCollection()->map(
                        fn ($tool) => [
                            'id' => $tool->id,
                            'name' => $tool->name,
                            'slug' => $tool->slug,
                            'description' => $tool->description,
                            'category' => $tool->category,
                            'type' => $tool->type,
                            'status' => $tool->status,
                            'display_order' => $tool->display_order,
                            'journeys' => $tool->journeys->map(
                                fn ($journey) => [
                                    'id' => $journey->id,
                                    'name' => $journey->name,
                                    'slug' => $journey->slug,
                                ]
                            )->values(),
                        ]
                    )->values(),

                    'pagination' => [
                        'current_page' => $tools->currentPage(),
                        'per_page' => $tools->perPage(),
                        'total' => $tools->total(),
                        'last_page' => $tools->lastPage(),
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

    public function show(int $tool, Request $request): JsonResponse
    {
        try {
            $item = $this->toolService->findActiveTool($tool);

            if (! $item) {
                return $this->notFoundResponse('Something went wrong');
            }

            $relatedTools = $this->toolService->relatedTools($item);

            // Track successful tool views without breaking the API response.
            try {
                $user = $request->user();

                if ($user) {
                    $this->activityTracker->tool(
                        $user,
                        'tool_viewed',
                        'tool',
                        (int) $item->id
                    );
                }
            } catch (Throwable $exception) {
                $this->logApiException($exception);
            }

            return $this->successResponse(
                'Tool fetched successfully.',
                [
                    'tool' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'description' => $item->description,
                        'category' => $item->category,
                        'type' => $item->type,
                        'status' => $item->status,
                        'display_order' => $item->display_order,
                        'journeys' => $item->journeys->map(
                            fn ($journey) => [
                                'id' => $journey->id,
                                'name' => $journey->name,
                                'slug' => $journey->slug,
                            ]
                        )->values(),
                    ],
                    'related_tools' => $relatedTools->map(
                        fn ($related) => [
                            'id' => $related->id,
                            'name' => $related->name,
                            'slug' => $related->slug,
                            'description' => $related->description,
                            'category' => $related->category,
                            'type' => $related->type,
                        ]
                    )->values(),
                ]
            );
        } catch (Throwable $exception) {
            $this->logApiException($exception);

            return $this->serverErrorResponse('Something went wrong');
        }
    }
}
