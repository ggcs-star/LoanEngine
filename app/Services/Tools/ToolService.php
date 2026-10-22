<?php

namespace App\Services\Tools;

use App\Models\Tool;
use App\Models\ToolJourney;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ToolService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 100);

        return Tool::query()
            ->with('journeys:id,name,slug')
            ->when(
                filled($filters['search'] ?? null),
                fn (Builder $query) => $query->search($filters['search'])
            )
            ->when(
                filled($filters['status'] ?? null),
                fn (Builder $query) => $query->where(
                    'status',
                    $filters['status']
                )
            )
            ->when(
                filled($filters['journey_id'] ?? null),
                fn (Builder $query) => $query->whereHas(
                    'journeys',
                    fn (Builder $journeyQuery) => $journeyQuery->whereKey(
                        $filters['journey_id']
                    )
                )
            )
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Tool
    {
        return DB::transaction(function () use ($data): Tool {
            $toolData = $this->extractToolData($data);

            $tool = Tool::query()->create($toolData);

            $this->syncJourneys($tool, $data['journey_ids'] ?? []);

            return $tool->load('journeys:id,name,slug');
        });
    }

    public function update(Tool $tool, array $data): Tool
    {
        return DB::transaction(function () use ($tool, $data): Tool {
            $toolData = $this->extractToolData($data);

            $tool->update($toolData);

            if (array_key_exists('journey_ids', $data)) {
                $this->syncJourneys($tool, $data['journey_ids'] ?? []);
            }

            return $tool->refresh()->load('journeys:id,name,slug');
        });
    }

    public function delete(Tool $tool): void
    {
        DB::transaction(function () use ($tool): void {
            /*
             * Remove journey mappings explicitly.
             * Step mappings are also removed to avoid orphaned associations.
             */
            $tool->journeys()->detach();
            $tool->journeySteps()->detach();

            $tool->delete();
        });
    }

    public function statistics(): array
    {
        $counts = Tool::query()
            ->select('status')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total' => (int) $counts->sum(),
            'active' => (int) ($counts[Tool::STATUS_ACTIVE] ?? 0),
            'planned' => (int) ($counts[Tool::STATUS_PLANNED] ?? 0),
            'inactive' => (int) ($counts[Tool::STATUS_INACTIVE] ?? 0),
        ];
    }

    public function toolkitHomeTools()
    {
        return Tool::query()
            ->active()
            ->where('show_on_toolkit_home', true)
            ->with('journeys:id,name,slug')
            ->ordered()
            ->get();
    }

    private function extractToolData(array $data): array
    {
        $toolData = [
            'name' => trim($data['name']),
            'slug' => $this->makeUniqueSlug(
                $data['slug'] ?? $data['name'],
                $data['tool_id'] ?? null
            ),
            'short_description' => trim($data['short_description']),
            'icon' => $data['icon'],
            'category' => $data['category'],
            'type' => $data['type'],
            'status' => $data['status'],
            'display_order' => (int) ($data['display_order'] ?? 1),
            'page_route' => trim((string) ($data['page_route'] ?? ('user/tools/' . ($data['slug'] ?? Str::slug($data['name']))))),
            'show_on_toolkit_home' => (bool) (
                $data['show_on_toolkit_home'] ?? false
            ),
        ];

        return $toolData;
    }

    private function syncJourneys(Tool $tool, array $journeyIds): void
    {
        $journeyIds = collect($journeyIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($journeyIds->isNotEmpty()) {
            $existingIds = ToolJourney::query()
                ->whereIn('id', $journeyIds)
                ->pluck('id');

            if ($existingIds->count() !== $journeyIds->count()) {
                throw ValidationException::withMessages([
                    'journey_ids' => 'One or more selected journeys do not exist.',
                ]);
            }
        }

        $tool->journeys()->sync($journeyIds->all());
    }

    private function makeUniqueSlug(string $value, ?int $ignoreToolId = null): string
    {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Please provide a valid name or slug.',
            ]);
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Tool::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreToolId,
                    fn (Builder $query) => $query->whereKeyNot($ignoreToolId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
