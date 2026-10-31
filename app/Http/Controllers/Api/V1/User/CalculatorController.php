<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Actions\User\Calculators\CalculateUserCalculator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\Calculators\CalculateCalculatorRequest;
use App\Http\Resources\Api\V1\User\Calculators\CalculatorResource;
use App\Http\Resources\Api\V1\User\Calculators\CalculatorUsageResource;
use App\Models\Calculator;
use App\Models\CalculatorUsage;
use App\Support\Calculators\CalculatorFormulaRegistry;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $calculators = Calculator::query()
            ->active()
            ->whereIn(
                'formula_type',
                array_keys(CalculatorFormulaRegistry::all())
            )
            ->ordered()
            ->get();

        return $this->successResponse(
            message: 'Calculators fetched successfully.',
            data: CalculatorResource::collection($calculators)->resolve()
        );
    }

    public function show(Calculator $calculator)
    {
        $this->ensureAvailable($calculator);

        return $this->successResponse(
            message: 'Calculator details fetched successfully.',
            data: (new CalculatorResource($calculator))->resolve()
        );
    }

    public function calculate(
        CalculateCalculatorRequest $request,
        Calculator $calculator,
        CalculateUserCalculator $action
    ) {
        $this->ensureAvailable($calculator);

        $inputs = $request->validated();

        $result = $action->execute(
            $request->user(),
            $calculator,
            $inputs
        );

        return $this->successResponse(
            message: 'Calculation completed successfully.',
            data: [
                'calculator' => (new CalculatorResource($calculator))->resolve(),
                'inputs' => $inputs,
                'result' => $result,
            ]
        );
    }

    public function history(Request $request)
    {
        $filters = $request->validate([
            'calculator_id' => [
                'nullable',
                'integer',
                'exists:calculators,id',
            ],
            'status' => [
                'nullable',
                'in:completed,failed',
            ],
            'from' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:from',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'in:10,15,25,50',
            ],
        ]);

        $history = CalculatorUsage::query()
            ->with('calculator:id,name,slug,formula_type,icon')
            ->where('user_id', $request->user()->getKey())
            ->when(
                $filters['calculator_id'] ?? null,
                fn ($query, $calculatorId) => $query->where(
                    'calculator_id',
                    $calculatorId
                )
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['from'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
            )
            ->when(
                $filters['to'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        $history->through(
            fn (CalculatorUsage $usage) => (new CalculatorUsageResource($usage))->resolve()
        );

        return $this->successResponse(
            message: 'Calculator history fetched successfully.',
            data: $history
        );
    }

    public function historyShow(Request $request, int $usage)
    {
        $record = CalculatorUsage::query()
            ->with('calculator:id,name,slug,formula_type,icon')
            ->where('user_id', $request->user()->getKey())
            ->findOrFail($usage);

        return $this->successResponse(
            message: 'Calculator history details fetched successfully.',
            data: (new CalculatorUsageResource($record))->resolve()
        );
    }

    private function ensureAvailable(Calculator $calculator): void
    {
        abort_unless(
            $calculator->status === 'active'
                && $calculator->formula_type !== null
                && array_key_exists(
                    $calculator->formula_type,
                    CalculatorFormulaRegistry::all()
                ),
            404
        );
    }
}
