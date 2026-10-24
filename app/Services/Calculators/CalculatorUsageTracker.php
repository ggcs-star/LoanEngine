<?php

namespace App\Services\Calculators;

use App\Models\Calculator;
use App\Models\CalculatorUsage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class CalculatorUsageTracker
{
    public function record(User $user, Calculator $calculator): void
    {
        try {
            CalculatorUsage::query()->create([
                'user_id' => $user->getKey(),
                'calculator_id' => $calculator->getKey(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to record calculator usage.', [
                'user_id' => $user->getKey(),
                'calculator_id' => $calculator->getKey(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}
