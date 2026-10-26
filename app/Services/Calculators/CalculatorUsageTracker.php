<?php

namespace App\Services\Calculators;

use App\Models\Calculator;
use App\Models\CalculatorUsage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class CalculatorUsageTracker
{
    public function record(
        User $user,
        Calculator $calculator,
        array $inputs = [],
        array $result = []
    ): void {
        try {
            CalculatorUsage::query()->create([
                'user_id' => $user->getKey(),
                'calculator_id' => $calculator->getKey(),
                'inputs' => $inputs,
                'result' => $result,
                'event_type' => 'calculated',
                'status' => 'completed',
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to record calculator insights.', [
                'user_id' => $user->getKey(),
                'calculator_id' => $calculator->getKey(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}
