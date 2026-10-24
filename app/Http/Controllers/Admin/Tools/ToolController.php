<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tools\ToolRequest;
use App\Models\Tool;
use App\Models\ToolJourney;
use App\Services\Tools\ToolService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;

class ToolController extends Controller
{
    public function __construct(
        private readonly ToolService $toolService
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => [
                'nullable',
                'string',
                'in:active,planned,inactive',
            ],
            'journey_id' => [
                'nullable',
                'integer',
                'min:1',
                'exists:tool_journeys,id',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        return view('admin.tools.index', [
            'tools' => $this->toolService->paginate($filters),
            'statistics' => $this->toolService->statistics(),
            'filters' => $filters,
            'journeys' => ToolJourney::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tools.create', [
            'tool' => new Tool(),
            'journeys' => $this->availableJourneys(),
        ]);
    }

    public function store(ToolRequest $request): RedirectResponse
    {
        try {
            $tool = $this->toolService->create(
                $request->validated()
            );

            return redirect()
                ->route('admin.tools.index')
                ->with('success', 'Tool created successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to create tool.', [
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create the tool. Please review the details and try again.'
                );
        }
    }


    public function show(Tool $tool): View
    {
        $tool->load('journeys');

        return view('admin.tools.show', [
            'tool' => $tool,
        ]);
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'required',
                'integer',
                'distinct',
                'min:1',
            ],
        ]);

        $ids = array_map('intval', $validated['ids']);

        $tools = Tool::query()
            ->whereIn('id', $ids)
            ->get();

        if ($tools->count() !== count($ids)) {
            return back()
                ->withInput()
                ->with('error', 'One or more selected tools could not be found.');
        }

        try {
            DB::transaction(function () use ($tools): void {
                foreach ($tools as $tool) {
                    // Reuse the existing deletion logic.
                    $this->toolService->delete($tool);
                }
            });

            return redirect()
                ->route('admin.tools.index')
                ->with(
                    'success',
                    $tools->count() . ' tools deleted successfully.'
                );
        } catch (Throwable $exception) {
            Log::error('Failed to bulk delete tools.', [
                'tool_ids' => $ids,
                'exception' => $exception,
            ]);

            return back()->with(
                'error',
                'Unable to delete the selected tools. Please try again.'
            );
        }
    }

    public function edit(Tool $tool): View
    {
        $tool->load('journeys:id,name,slug');

        return view('admin.tools.edit', [
            'tool' => $tool,
            'journeys' => $this->availableJourneys(),
        ]);
    }

    public function update(
        ToolRequest $request,
        Tool $tool
    ): RedirectResponse {
        try {
            $this->toolService->update(
                $tool,
                $request->validated()
            );

            return redirect()
                ->route('admin.tools.index')
                ->with('success', 'Tool updated successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to update tool.', [
                'tool_id' => $tool->getKey(),
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update the tool. Please try again.'
                );
        }
    }

    public function destroy(Tool $tool): RedirectResponse
    {
        try {
            $this->toolService->delete($tool);

            return redirect()
                ->route('admin.tools.index')
                ->with('success', 'Tool deleted successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to delete tool.', [
                'tool_id' => $tool->getKey(),
                'exception' => $exception,
            ]);

            return back()->with(
                'error',
                'Unable to delete the tool. Please try again.'
            );
        }
    }

    private function availableJourneys()
    {
        return ToolJourney::query()
            ->where('status', 'active')
            ->select(['id', 'name', 'slug'])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }
}
