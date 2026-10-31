<?php

namespace App\Services\Api\Tools;

use App\Models\JourneyStep;
use App\Models\ToolJourney;
use Illuminate\Database\Eloquent\Collection;

class JourneyStepService
{
    public function findActiveStep(
        ToolJourney $journey,
        int $stepId
    ): ?JourneyStep {
        if ($journey->status !== 'active') {
            return null;
        }

        return $journey->steps()
            ->whereKey($stepId)
            ->where('status', 'active')
            ->with([
                'journey',
                'tools' => fn ($query) => $query
                    ->where('tools.status', 'active')
                    ->orderBy('tools.display_order')
                    ->orderBy('tools.name'),
            ])
            ->first();
    }

    public function getActiveSteps(ToolJourney $journey): Collection
    {
        return $journey->steps()
            ->where('status', 'active')
            ->orderBy('step_number')
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();
    }

    public function getAdjacentSteps(
        Collection $steps,
        int|string $stepId
    ): array {
        $index = $steps->search(
            fn ($item) => (string) $item->getKey() === (string) $stepId
        );

        return [
            'current_index' => $index,
            'previous_step' => $index !== false && $index > 0
                ? $steps->get($index - 1)
                : null,
            'next_step' => $index !== false
                ? $steps->get($index + 1)
                : null,
        ];
    }
}
