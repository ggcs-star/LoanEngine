<?php

namespace App\Services\Tools;

use App\Models\ToolJourney;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JourneyService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 100);

        return ToolJourney::query()
            ->withCount(['steps', 'tools'])
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
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): ToolJourney
    {
        return DB::transaction(function () use ($data): ToolJourney {
            $data['slug'] = $this->makeUniqueSlug(
                $data['slug'] ?: $data['name']
            );

            return ToolJourney::query()->create($data);
        });
    }

    public function update(
        ToolJourney $journey,
        array $data
    ): ToolJourney {
        return DB::transaction(function () use ($journey, $data): ToolJourney {
            $data['slug'] = $this->makeUniqueSlug(
                $data['slug'] ?: $data['name'],
                $journey->getKey()
            );

            $journey->update($data);

            return $journey->refresh();
        });
    }

    public function delete(ToolJourney $journey): void
    {
        DB::transaction(function () use ($journey): void {
            $journey->loadCount(['steps', 'tools']);

            if ($journey->steps_count > 0 || $journey->tools_count > 0) {
                throw ValidationException::withMessages([
                    'journey' => 'This journey has linked steps or tools. Remove those associations before deleting it.',
                ]);
            }

            $journey->delete();
        });
    }

    public function statistics(): array
    {
        return [
            'total' => ToolJourney::query()->count(),

            'active' => ToolJourney::query()
                ->where('status', 'active')
                ->count(),

            'inactive' => ToolJourney::query()
                ->where('status', 'inactive')
                ->count(),
        ];
    }

    private function makeUniqueSlug(
        string $value,
        ?int $ignoreJourneyId = null
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Enter a valid journey name or slug.',
            ]);
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            ToolJourney::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreJourneyId,
                    fn (Builder $query) => $query->where(
                        'id',
                        '!=',
                        $ignoreJourneyId
                    )
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
