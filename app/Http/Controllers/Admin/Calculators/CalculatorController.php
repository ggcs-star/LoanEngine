<?php

namespace App\Http\Controllers\Admin\Calculators;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Calculators\CalculatorRequest;
use App\Models\Calculator;
use App\Models\Tool;
use App\Models\ToolJourney;
use App\Support\Calculators\CalculatorFormulaRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CalculatorController extends Controller
{
    public function index(Request $request): View
    {
        try {
            $filters = $request->validate([
                'search' => ['nullable', 'string', 'max:120'],
                'status' => ['nullable', 'in:active,inactive'],
            ]);

            $calculators = Calculator::query()
                ->with('journeys:id,name')
                ->when($filters['search'] ?? null, function ($query, $term): void {
                    $query->where(function ($subQuery) use ($term): void {
                        $subQuery
                            ->where('name', 'like', "%{$term}%")
                            ->orWhere('slug', 'like', "%{$term}%");
                    });
                })
                ->when(
                    $filters['status'] ?? null,
                    fn ($query, $status) => $query->where('status', $status)
                )
                ->ordered()
                ->paginate(15)
                ->withQueryString();

            return view('admin.calculators.index', compact('calculators', 'filters'));
        } catch (Throwable $exception) {
            Log::error('Failed to load calculators index.', [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function create(): View
    {
        try {
            return view('admin.calculators.create', [
                'calculator' => new Calculator([
                    'status' => 'active',
                    'display_order' => 1,
                    'icon' => 'calculator',
                ]),
                'journeys' => $this->availableJourneys(),
                'formulaTypes' => CalculatorFormulaRegistry::options(),
                'formulaDefinitions' => CalculatorFormulaRegistry::all(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load calculator creation page.', [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function store(CalculatorRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $journeyIds = $data['journey_ids'] ?? [];

            unset($data['journey_ids']);

            $formula = CalculatorFormulaRegistry::get($data['formula_type']);

            // Definitions come from the trusted server registry, not client JSON.
            $data['input_config'] = $formula['inputs'];
            $data['result_config'] = $formula['results'];

            DB::transaction(function () use ($data, $journeyIds): void {
                $calculator = Calculator::query()->create($data);

                $calculator->journeys()->sync($journeyIds);

                $this->syncCalculatorTool($calculator);
            });

            return redirect()
                ->route('admin.calculators.index')
                ->with('success', 'Calculator created and Tool synchronized successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to create calculator.', [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function edit(Calculator $calculator): View
    {
        try {
            $calculator->load('journeys:id');

            return view('admin.calculators.edit', [
                'calculator' => $calculator,
                'journeys' => $this->availableJourneys(),
                'formulaTypes' => CalculatorFormulaRegistry::options(),
                'formulaDefinitions' => CalculatorFormulaRegistry::all(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load calculator edit page.', [
                'calculator_id' => $calculator->getKey(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function update(
        CalculatorRequest $request,
        Calculator $calculator
    ): RedirectResponse {
        try {
            $data = $request->validated();
            $journeyIds = $data['journey_ids'] ?? [];

            unset($data['journey_ids']);

            $formula = CalculatorFormulaRegistry::get($data['formula_type']);

            $data['input_config'] = $formula['inputs'];
            $data['result_config'] = $formula['results'];

            DB::transaction(function () use ($calculator, $data, $journeyIds): void {
                $calculator->update($data);

                $calculator->journeys()->sync($journeyIds);

                $this->syncCalculatorTool($calculator);
            });

            return redirect()
                ->route('admin.calculators.index')
                ->with('success', 'Calculator updated and Tool synchronized successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to update calculator.', [
                'calculator_id' => $calculator->getKey(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function destroy(Calculator $calculator): RedirectResponse
    {
        try {
            DB::transaction(function () use ($calculator): void {
                $calculator->journeys()->detach();

                $calculator->delete();
            });

            return redirect()
                ->route('admin.calculators.index')
                ->with('success', 'Calculator deleted successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to delete calculator.', [
                'calculator_id' => $calculator->getKey(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        try {
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
        } catch (Throwable $exception) {
            Log::error('Failed to bulk delete calculators.', [
                'calculator_ids' => $request->input('ids'),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    private function availableJourneys()
    {
        try {
            return ToolJourney::query()
                ->active()
                ->ordered()
                ->get(['id', 'name', 'short_label']);
        } catch (Throwable $exception) {
            Log::error('Failed to retrieve available tool journeys.', [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    private function syncCalculatorTool(Calculator $calculator): void
    {
        try {
            $tool = Tool::query()->updateOrCreate(
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

            $journeyIds = $calculator->journeys()
                ->pluck('tool_journeys.id')
                ->all();

            $tool->journeys()->sync($journeyIds);
        } catch (Throwable $exception) {
            Log::error('Failed to synchronize calculator tool.', [
                'calculator_id' => $calculator->getKey(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}
