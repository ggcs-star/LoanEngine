<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ToolController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:40'],
            'sort' => ['nullable', Rule::in(['recommended', 'name', 'newest'])],
        ]);

        $query = Tool::query()->active()->search($filters['search'] ?? null)
            ->when(filled($filters['category'] ?? null), fn ($q) => $q->where('category', $filters['category']));

        match ($filters['sort'] ?? 'recommended') {
            'name' => $query->orderBy('name'),
            'newest' => $query->orderByDesc('created_at')->orderBy('display_order'),
            default => $query->ordered(),
        };

        $tools = $query->with(['journeys' => fn ($q) => $q->where('status', 'active')->orderBy('display_order')])
            ->paginate(9)->withQueryString();

        $categories = Tool::query()->active()->select('category')->distinct()->orderBy('category')->pluck('category');

        return view('user.tools.index', [
            'tools' => $tools,
            'categories' => $categories,
            'filters' => $filters,
            'sort' => $filters['sort'] ?? 'recommended',
        ]);
    }

    public function show(Tool $tool): View
    {
        abort_unless($tool->isActive(), 404);

        $tool->load(['journeys' => fn ($q) => $q->where('status', 'active')->orderBy('display_order')]);
        $relatedTools = Tool::query()->active()->where('category', $tool->category)
            ->whereKeyNot($tool->getKey())->ordered()->limit(3)->get();

        return view('user.tools.show', compact('tool', 'relatedTools'));
    }
}
