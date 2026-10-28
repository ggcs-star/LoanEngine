<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

class UserManagementService
{
    public function getUserDetails(User $user): array
    {
        $hasRole = Schema::hasColumn('users', 'role');

        if ($hasRole && $user->role !== 'user') {
            abort(404);
        }

        $usageHasCalculator = Schema::hasTable('calculators');
        $usageHasCreatedAt = Schema::hasColumn(
            'calculator_usages',
            'created_at'
        );

        $calculations = $user->calculatorUsages();

        if ($usageHasCalculator) {
            $calculations->with('calculator:id,name,slug');
        }

        if ($usageHasCreatedAt) {
            $calculations->orderByDesc('created_at');
        }

        $totalCalculations = (clone $calculations)->count();

        $completedCalculations = (clone $calculations)
            ->where('status', 'completed')
            ->count();

        $failedCalculations = (clone $calculations)
            ->where('status', 'failed')
            ->count();

        $recentCalculations = $calculations
            ->paginate(10, ['*'], 'calculations_page')
            ->withQueryString();

        return [
            'user' => $user,
            'hasMobile' => Schema::hasColumn('users', 'mobile'),
            'hasRole' => $hasRole,
            'hasAddress' => Schema::hasColumn('users', 'address'),
            'hasCity' => Schema::hasColumn('users', 'city'),
            'hasEmploymentType' => Schema::hasColumn(
                'users',
                'employment_type'
            ),
            'hasAnnualIncome' => Schema::hasColumn(
                'users',
                'annual_income'
            ),
            'hasVerified' => Schema::hasColumn(
                'users',
                'email_verified_at'
            ),
            'totalCalculations' => $totalCalculations,
            'completedCalculations' => $completedCalculations,
            'failedCalculations' => $failedCalculations,
            'recentCalculations' => $recentCalculations,
        ];
    }
}
