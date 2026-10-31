<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\Api\Tools\ToolJourneyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ToolJourneyController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ToolJourneyService $journeyService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $journeys = $this->journeyService->getActiveJourneys();

            return $this->successResponse(
                'Tool journeys fetched successfully.',
                [
                    'items' => $journeys->map(
                        fn ($journey) => [
                            'id' => $journey->id,
                            'name' => $journey->name,
                            'slug' => $journey->slug,
                            'description' => $journey->description,
                            'display_order' => $journey->display_order,
                            'active_steps_count' => $journey->steps_count,
                        ]
                    )->values(),
                ]
            );
        } catch (Throwable $exception) {
            $this->logApiException($exception);

            return $this->serverErrorResponse('Something went wrong');
        }
    }

    public function show(
        int $journey,
        Request $request
    ): JsonResponse {
        try {
            $item = $this->journeyService->findActiveJourney($journey);

            if (! $item) {
                return $this->notFoundResponse('Something went wrong');
            }

            $calculatorsOnly = $request->query('for') === 'calculators';

            if ($request->query('for') !== null && ! $calculatorsOnly) {
                return $this->errorResponse('Something went wrong', 422);
            }

            if ($calculatorsOnly) {
                $tools = $this->journeyService->getActiveJourneyTools(
                    $item,
                    true
                );

                return $this->successResponse(
                    'Journey calculators fetched successfully.',
                    [
                        'journey' => [
                            'id' => $item->id,
                            'name' => $item->name,
                            'slug' => $item->slug,
                        ],
                        'items' => $tools->map(
                            fn ($tool) => [
                                'id' => $tool->id,
                                'name' => $tool->name,
                                'slug' => $tool->slug,
                                'description' => $tool->description,
                                'category' => $tool->category,
                                'type' => $tool->type,
                            ]
                        )->values(),
                    ]
                );
            }

            $steps = $this->journeyService->getActiveJourneySteps($item);

            return $this->successResponse(
                'Tool journey fetched successfully.',
                [
                    'journey' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'description' => $item->description,
                        'display_order' => $item->display_order,
                    ],
                    'steps' => $steps->map(
                        fn ($step) => [
                            'id' => $step->id,
                            'title' => $step->title,
                            'slug' => $step->slug,
                            'description' => $step->description,
                            'sort_order' => $step->sort_order,
                            'tools' => $step->tools->map(
                                fn ($tool) => [
                                    'id' => $tool->id,
                                    'name' => $tool->name,
                                    'slug' => $tool->slug,
                                    'type' => $tool->type,
                                ]
                            )->values(),
                        ]
                    )->values(),
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
