<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tools\JourneyStepRequest;
use App\Models\JourneyStep;
use App\Models\ToolJourney;
use App\Services\Tools\JourneyStepService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class JourneyStepController extends Controller
{
    public function __construct(
        private readonly JourneyStepService $stepService
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'journey_id' => [
                'nullable',
                'integer',
                'min:1',
                'exists:tool_journeys,id',
            ],
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return view('admin.journey-steps.index', [
            'steps' => $this->stepService->paginate($filters),
            'statistics' => $this->stepService->statistics(),
            'filters' => $filters,
            'journeys' => ToolJourney::query()
                ->select(['id', 'name'])
                ->ordered()
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $journeyId = $request->query('journey_id');

        $journey = $journeyId
            ? ToolJourney::query()->findOrFail($journeyId)
            : null;

        return view('admin.journey-steps.create', [
            'step' => new JourneyStep(),
            'journeys' => ToolJourney::query()->active()->ordered()->get(),
            'selectedJourney' => $journey,
            'tools' => $journey
                ? $this->stepService->availableToolsForJourney($journey->id)
                : collect(),
        ]);
    }

    public function store(JourneyStepRequest $request): RedirectResponse
    {
        try {
            $step = $this->stepService->create($request->validated());

            return redirect()
                ->route('admin.journey-steps.index', [
                    'journey_id' => $step->journey_id,
                ])
                ->with('success', 'Journey step created successfully.');
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            Log::error('Failed to create journey step.', [
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Unable to create the step. Please try again.');
        }
    }
    public function show(JourneyStep $journey_step): View
    {
        $journey_step->load(['journey', 'tools']);

        return view('admin.journey-steps.show', [
            'step' => $journey_step,
        ]);
    }
    public function edit(JourneyStep $journey_step): View
    {
        $journey_step->load(['journey', 'tools']);

        return view('admin.journey-steps.edit', [
            'step' => $journey_step,
            'journeys' => ToolJourney::query()->active()->ordered()->get(),
            'selectedJourney' => $journey_step->journey,
            'tools' => $this->stepService->availableToolsForJourney(
                $journey_step->journey_id
            ),
        ]);
    }

    public function update(
        JourneyStepRequest $request,
        JourneyStep $journey_step
    ): RedirectResponse {
        try {
            $step = $this->stepService->update(
                $journey_step,
                $request->validated()
            );

            return redirect()
                ->route('admin.journey-steps.index', [
                    'journey_id' => $step->journey_id,
                ])
                ->with('success', 'Journey step updated successfully.');
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            Log::error('Failed to update journey step.', [
                'step_id' => $journey_step->getKey(),
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the step. Please try again.');
        }
    }

    public function destroy(JourneyStep $journey_step): RedirectResponse
    {
        $journeyId = $journey_step->journey_id;

        try {
            $this->stepService->delete($journey_step);

            return redirect()
                ->route('admin.journey-steps.index', [
                    'journey_id' => $journeyId,
                ])
                ->with('success', 'Journey step deleted successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to delete journey step.', [
                'step_id' => $journey_step->getKey(),
                'exception' => $exception,
            ]);

            return back()->with(
                'error',
                'Unable to delete the step. Please try again.'
            );
        }
    }
    public function bulkDestroy(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'ids' => ['required', 'array', 'min:1'],
        'ids.*' => ['required', 'integer', 'distinct', 'min:1'],
    ]);

    $ids = array_map('intval', $validated['ids']);
    $steps = JourneyStep::query()->whereIn('id', $ids)->get();

    if ($steps->count() !== count($ids)) {
        return back()->with('error', 'One or more selected journey steps could not be found.');
    }

    $deleted = 0;
    $failed = 0;

    foreach ($steps as $step) {
        try {
            $this->stepService->delete($step);
            $deleted++;
        } catch (Throwable $exception) {
            $failed++;
            Log::warning('Unable to bulk-delete journey step.', [
                'step_id' => $step->getKey(),
                'exception' => $exception,
            ]);
        }
    }

    if ($deleted === 0) {
        return back()->with('error', 'No selected journey steps could be deleted. Please review the application log.');
    }

    $message = $deleted . ($deleted === 1 ? ' journey step deleted.' : ' journey steps deleted.');
    if ($failed > 0) {
        $message .= ' ' . $failed . ($failed === 1 ? ' step could not be deleted.' : ' steps could not be deleted.');
    }

    return redirect()->route('admin.journey-steps.index')->with('success', $message);
}

    public function toolsForJourney(ToolJourney $tool_journey)
    {
        return response()->json([
            'data' => $this->stepService
                ->availableToolsForJourney($tool_journey->id)
                ->map(fn ($tool) => [
                    'id' => $tool->id,
                    'name' => $tool->name,
                    'slug' => $tool->slug,
                    'status' => $tool->status,
                ]),
        ]);
    }
}