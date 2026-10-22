<?php

namespace App\Console\Commands;

use App\Models\FinancialEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessFinancialRecurringEvents extends Command
{
    protected $signature = 'financial-flow:process-recurring';
    protected $description = 'Create due occurrences for recurring financial flow events';

    public function handle(): int
    {
        $processed = 0;
        $failed = 0;

        FinancialEvent::query()
            ->where('is_recurring', true)
            ->whereNotNull('recurrence_frequency')
            ->whereDate('event_date', '<=', now()->toDateString())
            ->orderBy('id')
            ->chunkById(100, function ($events) use (&$processed, &$failed) {
                foreach ($events as $event) {
                    try {
                        $count = DB::transaction(function () use ($event) {
                            $template = FinancialEvent::query()
                                ->whereKey($event->getKey())
                                ->lockForUpdate()
                                ->first();

                            if (
                                !$template ||
                                !$template->is_recurring ||
                                !$template->recurrence_frequency ||
                                $template->event_date->isFuture()
                            ) {
                                return 0;
                            }

                            $created = 0;
                            $iterations = 0;
                            $dueDate = $template->event_date->copy()->startOfDay();
                            $endDate = $template->recurrence_end_date?->copy()->startOfDay();

                            // Cap catch-up to avoid an unexpectedly huge batch in one run.
                            while ($dueDate->toDateString() <= now()->toDateString() && $iterations < 120) {
                                if ($endDate && $dueDate->gt($endDate)) {
                                    break;
                                }

                                FinancialEvent::create([
                                    'user_id' => $template->user_id,
                                    'description' => $template->description,
                                    'amount' => $template->amount,
                                    'category' => $template->category,
                                    'event_date' => $dueDate->toDateString(),
                                    'payment_status' => 'projected',
                                    'notes' => $template->notes,
                                    'is_recurring' => false,
                                    'recurrence_frequency' => null,
                                    'recurrence_end_date' => null,
                                    'last_processed_at' => now(),
                                ]);

                                $created++;
                                $iterations++;

                                $dueDate = $this->nextDate(
                                    $dueDate,
                                    $template->recurrence_frequency
                                );
                            }

                            $template->last_processed_at = now();

                            if ($endDate && $dueDate->gt($endDate)) {
                                $template->is_recurring = false;
                                $template->recurrence_frequency = null;
                                $template->recurrence_end_date = null;
                            } else {
                                $template->event_date = $dueDate->toDateString();
                            }

                            $template->save();

                            return $created;
                        });

                        $processed += $count;
                    } catch (Throwable $exception) {
                        $failed++;
                        report($exception);
                        $this->error("Failed recurring event ID {$event->getKey()}; details written to application log.");
                    }
                }
            });

        $this->info("Recurring occurrences created: {$processed}");
        if ($failed > 0) {
            $this->warn("Recurring templates with errors: {$failed}");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function nextDate(Carbon $date, string $frequency): Carbon
    {
        return match ($frequency) {
            'weekly' => $date->copy()->addWeek(),
            'monthly' => $date->copy()->addMonthNoOverflow(),
            'yearly' => $date->copy()->addYearNoOverflow(),
            default => throw new \InvalidArgumentException('Unsupported recurrence frequency.'),
        };
    }
}
