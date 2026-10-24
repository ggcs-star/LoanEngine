<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tools\JourneyRequest;
use App\Models\ToolJourney;
use App\Services\Tools\JourneyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ToolJourneyController extends Controller
{
    public function __construct(
        private readonly JourneyService $journeyService
    ) {
    }

    public function index(Request $request): View
    {
        try {
            $filters = $request->validate([
                'search' => ['nullable', 'string', 'max:120'],
                'status' => ['nullable', 'in:active,inactive'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);

            return view('admin.tool-journeys.index', [
                'journeys' => $this->journeyService->paginate($filters),
                'statistics' => $this->journeyService->statistics(),
                'filters' => $filters,
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Failed to load journeys.', [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function show(ToolJourney $tool_journey): View
    {
        try {
            $tool_journey->load([
                'steps',
                'tools',
            ]);

            $tool_journey->loadCount([
                'steps',
                'tools',
            ]);

            return view('admin.tool-journeys.show', [
                'journey' => $tool_journey,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load journey details.', [
                'journey_id' => $tool_journey->getKey(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function create(): View
    {
        try {
            return view('admin.tool-journeys.create', [
                'journey' => new ToolJourney(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load journey creation form.', [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function store(JourneyRequest $request): RedirectResponse
    {
        try {
            $this->journeyService->create($request->validated());

            return redirect()
                ->route('admin.tool-journeys.index')
                ->with('success', 'Journey created successfully.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Failed to create journey.', [
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to create the journey. Please try again.'
                );
        }
    }

    public function edit(ToolJourney $tool_journey): View
    {
        try {
            return view('admin.tool-journeys.edit', [
                'journey' => $tool_journey,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load journey edit form.', [
                'journey_id' => $tool_journey->getKey(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function update(
        JourneyRequest $request,
        ToolJourney $tool_journey
    ): RedirectResponse {
        try {
            $this->journeyService->update(
                $tool_journey,
                $request->validated()
            );

            return redirect()
                ->route('admin.tool-journeys.index')
                ->with('success', 'Journey updated successfully.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Failed to update journey.', [
                'journey_id' => $tool_journey->getKey(),
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update the journey. Please try again.'
                );
        }
    }

    public function destroy(
        ToolJourney $tool_journey
    ): RedirectResponse {
        try {
            $this->journeyService->delete($tool_journey);

            return redirect()
                ->route('admin.tool-journeys.index')
                ->with('success', 'Journey deleted successfully.');
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            Log::error('Failed to delete journey.', [
                'journey_id' => $tool_journey->getKey(),
                'exception' => $exception,
            ]);

            return back()->with(
                'error',
                'Unable to delete the journey. Please try again.'
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

        $journeys = ToolJourney::query()
            ->whereIn('id', $ids)
            ->get();

        if ($journeys->count() !== count($ids)) {
            return back()
                ->withInput()
                ->with('error', 'One or more selected journeys could not be found.');
        }

        $deleted = 0;
        $blocked = 0;

        foreach ($journeys as $journey) {
            try {
                $this->journeyService->delete($journey);
                $deleted++;
            } catch (ValidationException $exception) {
                $blocked++;

                Log::notice('Journey bulk deletion skipped due to validation restrictions.', [
                    'journey_id' => $journey->getKey(),
                    'errors' => $exception->errors(),
                ]);
            } catch (Throwable $exception) {
                $blocked++;

                Log::error('Failed to delete journey during bulk deletion.', [
                    'journey_id' => $journey->getKey(),
                    'exception' => $exception,
                ]);
            }
        }

        if ($deleted === 0) {
            return redirect()
                ->route('admin.tool-journeys.index')
                ->with(
                    'error',
                    'No journeys were deleted. Selected journeys may have linked steps or tools.'
                );
        }

        $message = $deleted . ($deleted === 1
            ? ' journey deleted successfully.'
            : ' journeys deleted successfully.');

        if ($blocked > 0) {
            $message .= ' ' . $blocked .
                ($blocked === 1
                    ? ' journey could not be deleted because of restrictions or an error.'
                    : ' journeys could not be deleted because of restrictions or errors.');
        }

        return redirect()
            ->route('admin.tool-journeys.index')
            ->with('success', $message);
    }
}
