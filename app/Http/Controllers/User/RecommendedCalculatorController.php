<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Calculator;
use App\Models\ToolJourney;
use Illuminate\Contracts\View\View;

class RecommendedCalculatorController extends Controller
{
    /**
     * Display available journeys.
     */
    public function index(): View
    {
        $journeys = ToolJourney::query()
            ->active()
            ->ordered()
            ->get();

        return view('user.calculators.journeys', compact('journeys'));
    }

    /**
     * Display active calculators assigned to a journey.
     */
    public function show(ToolJourney $journey): View
    {
        abort_unless($journey->status === 'active', 404);

        $calculators = $journey->calculators()
            ->where('calculators.status', 'active')
            ->orderBy('calculators.display_order')
            ->orderBy('calculators.name')
            ->get();

        return view('user.tools.recommended-calculators', [
            'journey' => $journey,
            'calculators' => $calculators,
        ]);
    }
}
