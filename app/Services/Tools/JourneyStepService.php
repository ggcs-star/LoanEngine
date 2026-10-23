<?php

namespace App\Services\Tools;

use App\Models\JourneyStep;
use App\Models\Tool;
use App\Models\ToolJourney;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JourneyStepService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 100);

        return JourneyStep::query()
            ->with([
                'journey:id,name,slug',
                'tools:id,name,slug',
            ])
            ->withCount('tools')
            ->when(
                filled($filters['search'] ?? null),
                fn (Builder $query) => $query->search($filters['search'])
            )
            ->when(
                filled($filters['journey_id'] ?? null),
                fn (Builder $query) => $query->where(
                    'journey_id',
                    (int) $filters['journey_id']
                )
            )
            ->when(
                filled($filters['status'] ?? null),
                fn (Builder $query) => $query->where(
                    'status',
                    $filters['status']
                )
            )
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): JourneyStep
    {
        return DB::transaction(function () use ($data): JourneyStep {
            $toolIds = $this->validatedToolIds(
                (int) $data['journey_id'],
                $data['tool_ids'] ?? []
            );

            $step = JourneyStep::query()->create(
                $this->stepAttributes($data)
            );

            $step->tools()->sync($toolIds);

            return $step->load(['journey', 'tools']);
        });
    }

    public function update(
        JourneyStep $step,
        array $data
    ): JourneyStep {
        return DB::transaction(function () use ($step, $data): JourneyStep {
            $journeyId = (int) $data['journey_id'];

            $toolIds = $this->validatedToolIds(
                $journeyId,
                $data['tool_ids'] ?? []
            );

            $step->update($this->stepAttributes($data));

            // Always sync: an empty selection clears previous mappings.
            $step->tools()->sync($toolIds);

            return $step->refresh()->load(['journey', 'tools']);
        });
    }

    public function delete(JourneyStep $step): void
    {
        DB::transaction(function () use ($step): void {
            $step->tools()->detach();
            $step->delete();
        });
    }

    public function statistics(): array
    {
        return [
            'total' => JourneyStep::query()->count(),

            'active' => JourneyStep::query()
                ->where('status', 'active')
                ->count(),

            'inactive' => JourneyStep::query()
                ->where('status', 'inactive')
                ->count(),
        ];
    }

    public function availableToolsForJourney(int $journeyId)
    {
        return ToolJourney::query()
            ->whereKey($journeyId)
            ->firstOrFail()
            ->tools()
            ->select('tools.id', 'tools.name', 'tools.slug', 'tools.status')
            ->orderBy('tools.name')
            ->get();
    }

    private function stepAttributes(array $data): array
    {
        return [
            'journey_id' => (int) $data['journey_id'],
            'step_number' => (int) $data['step_number'],
            'title' => trim($data['title']),
            'description' => $data['description'] ?? null,
            'what_you_need' => $data['what_you_need'] ?? null,
            'watch_out_for' => $data['watch_out_for'] ?? null,
            'display_order' => (int) $data['display_order'],
            'status' => $data['status'],
        ];
    }

    private function validatedToolIds(
        int $journeyId,
        array $toolIds
    ): array {
        $toolIds = collect($toolIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($toolIds->isEmpty()) {
            return [];
        }

        $journey = ToolJourney::query()->findOrFail($journeyId);

        $allowedIds = $journey->tools()
            ->whereIn('tools.id', $toolIds)
            ->pluck('tools.id')
            ->map(fn ($id) => (int) $id);

        if ($allowedIds->count() !== $toolIds->count()) {
            throw ValidationException::withMessages([
                'tool_ids' => 'Select only tools associated with the chosen journey.',
            ]);
        }

        return $allowedIds->all();
    }
}
