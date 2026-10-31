<?php

namespace App\Services\Api\Tools;

use App\Models\Tool;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ToolService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Tool::query()
            ->active()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                   $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(
                $filters['category'] ?? null,
                fn ($query, $category) => $query->where('category', $category)
            )
            ->when($filters['sort'] ?? 'recommended', function ($query, $sort) {
                match ($sort) {
                    'name' => $query->orderBy('name'),
                    'latest' => $query->latest(),
                    default => $query->ordered(),
                };
            })
            ->with([
                'journeys' => fn ($query) => $query
                    ->where('tool_journeys.status', 'active')
                    ->orderBy('tool_journeys.display_order'),
            ])
            ->paginate($filters['per_page'] ?? 12);
    }

    public function findActiveTool(int $id): ?Tool
    {
        return Tool::query()
            ->active()
            ->with([
                'journeys' => fn ($query) => $query
                    ->where('tool_journeys.status', 'active')
                    ->orderBy('tool_journeys.display_order'),
            ])
            ->find($id);
    }

    public function relatedTools(Tool $tool, int $limit = 3): Collection
    {
        return Tool::query()
            ->active()
            ->where('category', $tool->category)
            ->whereKeyNot($tool->getKey())
            ->ordered()
            ->limit($limit)
            ->get();
    }
}
