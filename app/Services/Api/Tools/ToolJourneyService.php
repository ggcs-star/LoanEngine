<?php

namespace App\Services\Api\Tools;

use App\Models\ToolJourney;
use Illuminate\Database\Eloquent\Collection;

class ToolJourneyService
{
    public function getActiveJourneys(): Collection
    {
        return ToolJourney::query()
            ->active()
            ->ordered()
            ->withCount([
                'steps' => fn ($query) => $query->where('status', 'active'),
            ])
            ->get();
    }

    public function findActiveJourney(int $id): ?ToolJourney
    {
        return ToolJourney::query()
            ->active()
            ->withCount([
                'steps' => fn ($query) => $query->where('status', 'active'),
                'tools' => fn ($query) => $query->where('tools.status', 'active'),
            ])
            ->find($id);
    }

    public function getActiveJourneyTools(
        ToolJourney $journey,
        bool $calculatorsOnly = false
    ): Collection {
        $query = $journey->tools()
            ->where('tools.status', 'active')
            ->orderBy('tools.display_order')
            ->orderBy('tools.name');

        if ($calculatorsOnly) {
            $query->where('tools.type', 'Calculator');
        }

        return $query->get();
    }

    
    public function getActiveJourneySteps(
        ToolJourney $journey
    ): Collection {
        return $journey->steps()
            ->where('status', 'active')
            ->with([
                'tools' => fn ($query) => $query
                    ->where('tools.status', 'active')
                    ->orderBy('tools.display_order')
                    ->orderBy('tools.name'),
            ])
            ->orderBy('step_number')
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();
    }

}
