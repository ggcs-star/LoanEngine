<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use App\Services\Calculators\HomeAffordabilityCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    /**
     * Slugs that use the home affordability calculation.
     */
    private function isSupported(Tool $tool): bool
    {
        return in_array($tool->slug, [
            'home-affordability-calculator',
            'home-loan-emi-calculator',
        ], true);
    }

    /**
     * Default values displayed by the calculator form.
     */
    private function defaults(): array
    {
        return [
            'property_price' => 10000000,
            'down_payment_percent' => 40,
            'interest_rate' => 6.9,
            'tenure_years' => 20,
            'monthly_rent' => 15000,
            'appreciation_rate' => 6,
            'rent_increase_rate' => 7,
            'investment_return' => 12,
            'inflation_rate' => 6,
            'maintenance_percent' => 1,
            'analysis_years' => 15,
        ];
    }

    /**
     * Display calculator form.
     */
    public function show(Tool $tool): View|RedirectResponse
    {
        abort_unless($tool->isActive(), 404);

        if (!$this->isSupported($tool)) {
            return redirect()
                ->route('user.tools.show', $tool->slug)
                ->with('info', 'This calculator needs its own calculation logic.');
        }

        return view('user.calculators.show', [
            'tool' => $tool,
            'inputs' => $this->defaults(),
            'result' => null,
        ]);
    }

    /**
     * Validate inputs, calculate results, and show them on the same page.
     */
    public function calculate(
        Request $request,
        Tool $tool,
        HomeAffordabilityCalculator $calculator
    ): View|RedirectResponse {
        abort_unless($tool->isActive(), 404);

        if (!$this->isSupported($tool)) {
            return redirect()
                ->route('user.tools.show', $tool->slug)
                ->with('info', 'This calculator needs its own calculation logic.');
        }

        $rules = [
            'property_price' => [
                'required', 'numeric', 'min:1000000', 'max:50000000',
            ],
            'down_payment_percent' => [
                'required', 'numeric', 'min:10', 'max:90',
            ],
            'interest_rate' => [
                'required', 'numeric', 'min:0', 'max:30',
            ],
            'tenure_years' => [
                'required', 'integer', 'min:1', 'max:40',
            ],
            'monthly_rent' => [
                'required', 'numeric', 'min:5000', 'max:150000',
            ],
            'appreciation_rate' => [
                'required', 'numeric', 'min:-10', 'max:20',
            ],
            'rent_increase_rate' => [
                'required', 'numeric', 'min:-10', 'max:20',
            ],
            'investment_return' => [
                'required', 'numeric', 'min:-10', 'max:30',
            ],
            'inflation_rate' => [
                'required', 'numeric', 'min:-10', 'max:30',
            ],
            'maintenance_percent' => [
                'required', 'numeric', 'min:0', 'max:10',
            ],
            'analysis_years' => [
                'required', 'integer', 'min:5', 'max:30',
            ],
        ];

        $validated = $request->validate($rules);

        // Preserve defaults for any non-editable values the service needs.
        $inputs = array_merge($this->defaults(), $validated);

        $result = $calculator->calculate($inputs);

        return view('user.calculators.show', [
            'tool' => $tool,
            'inputs' => $inputs,
            'result' => $result,
        ]);
    }
}
