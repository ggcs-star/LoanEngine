<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\ToolJourney;
use App\Services\Api\Tools\JourneyStepService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Throwable;

class JourneyStepController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly JourneyStepService $stepService
    ) {}

    public function show(
        int $journey,
        int $step
    ): JsonResponse {
        try {
            $journeyModel = ToolJourney::query()
                ->active()
                ->find($journey);

            if (! $journeyModel) {
                return $this->notFoundResponse('Something went wrong');
            }

            $item = $this->stepService->findActiveStep(
                $journeyModel,
                $step
            );

            if (! $item) {
                return $this->notFoundResponse('Something went wrong');
            }

            $steps = $this->stepService->getActiveSteps($journeyModel);

            $navigation = $this->stepService->getAdjacentSteps(
                $steps,
                $item->id
            );

            return $this->successResponse(
                'Journey step fetched successfully.',
                [
                    'journey' => [
                        'id' => $journeyModel->id,
                        'name' => $journeyModel->name,
                        'slug' => $journeyModel->slug,
                    ],
                    'step' => [
                        'id' => $item->id,
                        'journey_id' => $item->journey_id,
                        'title' => $item->title,
                        'slug' => $item->slug,
                        'description' => $item->description,
                        'sort_order' => $item->sort_order,
                        'tools' => $item->tools->map(
                            fn ($tool) => [
                                'id' => $tool->id,
                                'name' => $tool->name,
                                'slug' => $tool->slug,
                                'description' => $tool->description,
                                'category' => $tool->category,
                                'type' => $tool->type,
                            ]
                        )->values(),
                    ],
                    'navigation' => [
                        'current_index' => $navigation['current_index'],
                        'previous_step' => $navigation['previous_step']
                            ? [
                                'id' => $navigation['previous_step']->id,
                                'title' => $navigation['previous_step']->title,
                                'slug' => $navigation['previous_step']->slug,
                            ]
                            : null,
                        'next_step' => $navigation['next_step']
                            ? [
                                'id' => $navigation['next_step']->id,
                                'title' => $navigation['next_step']->title,
                                'slug' => $navigation['next_step']->slug,
                            ]
                            : null,
                    ],
                ]
            );
        } catch (Throwable $exception) {
            $this->logApiException($exception);

            return $this->serverErrorResponse('Something went wrong');
        }
    }
}
