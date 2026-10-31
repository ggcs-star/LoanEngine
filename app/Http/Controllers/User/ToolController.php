<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use App\Services\Tracking\UserActivityTracker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function index(): View
    {
        $tools = app(\App\Services\Tools\ToolService::class)
            ->toolkitHomeTools();

        $categories = $tools->pluck('category')
            ->filter()
            ->unique()
            ->values();

        $filters = [
            'search' => request('search', ''),
            'category' => request('category', ''),
        ];

        $sort = request('sort', 'recommended');

        return view('user.tools.index', compact(
            'tools',
            'categories',
            'filters',
            'sort'
        ));
    }

    public function show(
        Tool $tool,
        UserActivityTracker $tracker
    ): View {
        abort_unless($tool->isActive(), 404);

        // Record actual tool opening.
        $tracker->tool(
            request()->user(),
            'tool_opened',
            'tool',
            $tool->id
        );

        $tool->load([
            'journeys' => fn ($query) => $query
                ->where('tool_journeys.status', 'active')
                ->orderBy('tool_journeys.display_order'),
        ]);

        $relatedTools = Tool::query()
            ->active()
            ->where('category', $tool->category)
            ->whereKeyNot($tool->getKey())
            ->ordered()
            ->limit(3)
            ->get();

        return view('user.tools.show', compact(
            'tool',
            'relatedTools'
        ));
    }
}
