<?php

namespace App\Http\Controllers\Admin\Calculators;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Calculators\CalculatorRequest;
use App\Models\Calculator;
use App\Models\Tool;
use App\Models\ToolJourney;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalculatorController extends Controller
{
    /**
     * Calculator slugs that have an existing user-facing UI
     * and a compatible calculator controller.
     */
    private const SUPPORTED_CALCULATORS = [
        'home-affordability-calculator',
        'home-loan-emi-calculator',
    ];

    /**
     * Display calculator management.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $calculators = Calculator::query()
            ->with('journeys:id,name')
            ->when(
                $filters['search'] ?? null,
                fn ($query, $term) => $query->where(
                    fn ($subQuery) => $subQuery
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%")
                )
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('admin.calculators.index', compact(
            'calculators',
            'filters'
        ));
    }

    /**
     * Show the create calculator form.
     */
    public function create(): View
    {
        return view('admin.calculators.create', [
            'calculator' => new Calculator([
                'status' => 'active',
                'display_order' => 1,
            ]),
            'journeys' => ToolJourney::query()
                ->active()
                ->ordered()
                ->get(['id', 'name', 'short_label']),
        ]);
    }

    /**
     * Store a calculator and synchronize its existing UI mapping.
     */
    public function store(CalculatorRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $journeyIds = $data['journey_ids'] ?? [];

        unset($data['journey_ids']);

        DB::transaction(function () use ($data, $journeyIds): void {
            $calculator = Calculator::query()->create($data);

            $calculator->journeys()->sync($journeyIds);

            $this->syncExistingCalculatorTool($calculator);
        });

        return redirect()
            ->route('admin.calculators.index')
            ->with('success', 'Calculator created successfully.');
    }

    /**
     * Show the edit calculator form.
     */
    public function edit(Calculator $calculator): View
    {
        $calculator->load('journeys:id');

        return view('admin.calculators.edit', [
            'calculator' => $calculator,
            'journeys' => ToolJourney::query()
                ->active()
                ->ordered()
                ->get(['id', 'name', 'short_label']),
        ]);
    }

    /**
     * Update a calculator, journey assignments and UI mapping.
     */
    public function update(
        CalculatorRequest $request,
        Calculator $calculator
    ): RedirectResponse {
        $data = $request->validated();
        $journeyIds = $data['journey_ids'] ?? [];

        unset($data['journey_ids']);

        DB::transaction(function () use (
            $calculator,
            $data,
            $journeyIds
        ): void {
            $calculator->update($data);

            $calculator->journeys()->sync($journeyIds);

            $this->syncExistingCalculatorTool($calculator);
        });

        return redirect()
            ->route('admin.calculators.index')
            ->with('success', 'Calculator updated successfully.');
    }

    /**
     * Delete a calculator and detach its journey assignments.
     */
    public function destroy(Calculator $calculator): RedirectResponse
    {
        DB::transaction(function () use ($calculator): void {
            $calculator->journeys()->detach();

            $calculator->delete();
        });

        return redirect()
            ->route('admin.calculators.index')
            ->with('success', 'Calculator deleted successfully.');
    }

    /**
     * Delete multiple calculators.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:calculators,id',
            ],
        ]);

        DB::transaction(function () use ($validated): void {
            $calculators = Calculator::query()
                ->whereIn('id', $validated['ids'])
                ->get();

            foreach ($calculators as $calculator) {
                $calculator->journeys()->detach();

                $calculator->delete();
            }
        });

        return redirect()
            ->route('admin.calculators.index')
            ->with('success', 'Selected calculators deleted successfully.');
    }

    /**
     * Synchronize the Tool record only for calculators whose
     * user-facing implementation is already supported.
     *
     * This keeps the existing calculator UI and formulas unchanged.
     */
    private function syncExistingCalculatorTool(
        Calculator $calculator
    ): void {
        if (! in_array(
            $calculator->slug,
            self::SUPPORTED_CALCULATORS,
            true
        )) {
            return;
        }

        Tool::query()->updateOrCreate(
            ['slug' => $calculator->slug],
            [
                'name' => $calculator->name,
                'short_description' => $calculator->short_description,
                'icon' => $calculator->icon ?: 'calculator',
                'category' => 'Calculators',
                'type' => 'Calculator',
                'status' => $calculator->status,
                'display_order' => $calculator->display_order,
                'page_route' => 'tools/' . $calculator->slug,
                'show_on_toolkit_home' => false,
            ]
        );
    }
}
