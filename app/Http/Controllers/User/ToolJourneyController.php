<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JourneyStep;
use App\Models\ToolJourney;
use App\Services\Tracking\UserActivityTracker;
use Illuminate\Contracts\View\View;

class ToolJourneyController extends Controller
{
    public function index(): View
    {
        $journeys = ToolJourney::query()
            ->active()
            ->ordered()
            ->withCount([
                'steps' => fn ($query) => $query->where('status', 'active'),
            ])
            ->get();

        return view('user.tools.journeys', compact('journeys'));
    }

    public function show(
        ToolJourney $journey,
        UserActivityTracker $tracker
    ): View {
        abort_unless($journey->status === 'active', 404);

        // Calculator flow: keep existing behavior unchanged.
        if (request()->query('for') === 'calculators') {
            $tools = $journey->tools()
                ->where('tools.status', 'active')
                ->where('tools.type', 'Calculator')
                ->orderBy('tools.display_order')
                ->orderBy('tools.name')
                ->get();

            return view('user.tools.journey-calculators', compact(
                'journey',
                'tools'
            ));
        }

        // Record actual journey opening.
        $tracker->tool(
            request()->user(),
            'journey_opened',
            'journey',
            $journey->id
        );

        // Existing Resources / Planning flow.
        $steps = $journey->steps()
            ->where('status', 'active')
            ->with([
                'tools' => fn ($query) => $query
                    ->where('tools.status', 'active')
                    ->orderBy('tools.display_order')
                    ->orderBy('tools.name'),
            ])
            ->get();

        return view('user.tools.journey', compact(
            'journey',
            'steps'
        ));
    }

    public function step(
        ToolJourney $journey,
        JourneyStep $step,
        UserActivityTracker $tracker
    ): View {
        abort_unless($journey->status === 'active', 404);
        abort_unless($step->journey_id === $journey->id, 404);
        abort_unless($step->status === 'active', 404);

        // Record successful step opening.
        $tracker->tool(
            request()->user(),
            'step_opened',
            'step',
            $step->id
        );

        $step->load([
            'journey',
            'tools' => fn ($query) => $query
                ->where('tools.status', 'active')
                ->orderBy('tools.display_order')
                ->orderBy('tools.name'),
        ]);

        $steps = $journey->steps()
            ->where('status', 'active')
            ->get();

        $currentIndex = $steps->search(
            fn ($item) => $item->id === $step->id
        );

        $previousStep = $currentIndex !== false && $currentIndex > 0
            ? $steps->get($currentIndex - 1)
            : null;

        $nextStep = $currentIndex !== false
            ? $steps->get($currentIndex + 1)
            : null;

        return view('user.tools.step', compact(
            'journey',
            'step',
            'steps',
            'previousStep',
            'nextStep',
            'currentIndex'
        ));
    }
}
