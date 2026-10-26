<?php

namespace App\Http\Controllers\Admin\Calculators;

use App\Http\Controllers\Controller;
use App\Models\Calculator;
use App\Models\CalculatorUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class CalculatorInsightsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $filters = $request->validate([
                'tab' => ['nullable', 'in:recent,users,calculators'],
                'q' => ['nullable', 'string', 'max:150'],
                'calculator_id' => ['nullable', 'integer', 'exists:calculators,id'],
                'user_id' => ['nullable', 'integer', 'exists:users,id'],
                'status' => ['nullable', 'in:completed,failed'],
                'from' => ['nullable', 'date_format:Y-m-d'],
                'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
                'detail' => ['nullable', 'integer', 'min:1'],
                'per_page' => ['nullable', 'integer', 'in:10,15,25,50'],
            ]);

            $tab = $filters['tab'] ?? 'recent';
            $search = trim($filters['q'] ?? '');
            $perPage = (int) ($filters['per_page'] ?? 10);

            $baseQuery = CalculatorUsage::query()
                ->with([
                    'user:id,name,email',
                    'calculator:id,name,slug,formula_type',
                ]);

            $this->applyUsageFilters($baseQuery, $filters, $search);

            $totalCalculations = (clone $baseQuery)->count();

            $completedCalculations = (clone $baseQuery)
                ->where('status', 'completed')
                ->count();

            $uniqueUsers = (clone $baseQuery)
                ->whereNotNull('user_id')
                ->distinct()
                ->count('user_id');

            $calculatorsUsed = (clone $baseQuery)
                ->whereNotNull('calculator_id')
                ->distinct()
                ->count('calculator_id');

            $recentCalculations = (clone $baseQuery)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate($perPage)
                ->withQueryString();

            $usersQuery = User::query()
                ->where('role', 'user')
                ->withCount([
                    'calculatorUsages as total_calculations' => function (Builder $query) use ($filters, $search): void {
                        $this->applyUsageFilters($query, $filters, $search, false);
                    },
                ])
                ->withMax([
                    'calculatorUsages as last_calculation_at' => function (Builder $query) use ($filters): void {
                        $this->applyDateFilters($query, $filters);

                        if (! empty($filters['calculator_id'])) {
                            $query->where('calculator_id', $filters['calculator_id']);
                        }

                        if (! empty($filters['status'])) {
                            $query->where('status', $filters['status']);
                        }
                    },
                ], 'created_at');

            if ($search !== '') {
                $usersQuery->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if (! empty($filters['user_id'])) {
                $usersQuery->whereKey($filters['user_id']);
            }

            $users = $usersQuery
                ->orderByDesc('total_calculations')
                ->orderBy('name')
                ->paginate($perPage, ['*'], 'users_page')
                ->withQueryString();

            $calculatorsQuery = Calculator::query()
                ->withCount([
                    'usages as total_calculations' => function (Builder $query) use ($filters): void {
                        $this->applyDateFilters($query, $filters);

                        if (! empty($filters['user_id'])) {
                            $query->where('user_id', $filters['user_id']);
                        }

                        if (! empty($filters['status'])) {
                            $query->where('status', $filters['status']);
                        }
                    },
                ]);

            if ($search !== '') {
                $calculatorsQuery->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            if (! empty($filters['calculator_id'])) {
                $calculatorsQuery->whereKey($filters['calculator_id']);
            }

            $calculators = $calculatorsQuery
                ->orderByDesc('total_calculations')
                ->orderBy('name')
                ->paginate($perPage, ['*'], 'calculators_page')
                ->withQueryString();

            $chartStart = now()->startOfDay()->subDays(6);
            $chartEnd = now()->endOfDay();

            $dailyCounts = CalculatorUsage::query()
                ->whereBetween('created_at', [$chartStart, $chartEnd])
                ->selectRaw('DATE(created_at) as usage_date, COUNT(*) as total')
                ->groupBy('usage_date')
                ->pluck('total', 'usage_date');

            $chartData = collect(range(6, 0))
                ->map(function (int $daysAgo) use ($dailyCounts): array {
                    $date = now()->subDays($daysAgo);
                    $key = $date->format('Y-m-d');

                    return [
                        'date' => $key,
                        'label' => $date->format('M d'),
                        'count' => (int) ($dailyCounts[$key] ?? 0),
                    ];
                });

            $calculatorOptions = Calculator::query()
                ->ordered()
                ->get(['id', 'name']);

            $userOptions = User::query()
                ->where('role', 'user')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            $selectedUsage = null;

            if (! empty($filters['detail'])) {
                $selectedUsage = CalculatorUsage::query()
                    ->with([
                        'user:id,name,email',
                        'calculator:id,name,slug,formula_type',
                    ])
                    ->findOrFail($filters['detail']);
            }

            $topCalculators = Calculator::query()
                ->withCount([
                    'usages as total_calculations' => function (Builder $query): void {
                        $query->where('status', 'completed');
                    },
                ])
                ->orderByDesc('total_calculations')
                ->limit(5)
                ->get(['id', 'name', 'slug']);

            return view('admin.calculator-insights.index', [
                'tab' => $tab,
                'search' => $search,
                'filters' => $filters,
                'totalCalculations' => $totalCalculations,
                'completedCalculations' => $completedCalculations,
                'uniqueUsers' => $uniqueUsers,
                'calculatorsUsed' => $calculatorsUsed,
                'recentCalculations' => $recentCalculations,
                'users' => $users,
                'calculators' => $calculators,
                'chartData' => $chartData,
                'calculatorOptions' => $calculatorOptions,
                'userOptions' => $userOptions,
                'selectedUsage' => $selectedUsage,
                'topCalculators' => $topCalculators,
            ]);
        } catch (Throwable $exception) {
            Log::error('Unable to load calculator insights.', [
                'admin_id' => $request->user()?->getKey(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    private function applyUsageFilters(
        Builder $query,
        array $filters,
        string $search = '',
        bool $applyUserSearch = true
    ): void {
        $this->applyDateFilters($query, $filters);

        if (! empty($filters['calculator_id'])) {
            $query->where('calculator_id', $filters['calculator_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if ($search !== '') {
            $query->where(function (Builder $nestedQuery) use ($search, $applyUserSearch): void {
                $nestedQuery->whereHas('calculator', function (Builder $calculatorQuery) use ($search): void {
                    $calculatorQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });

                if ($applyUserSearch) {
                    $nestedQuery->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            });
        }
    }

    private function applyDateFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
    }
}
