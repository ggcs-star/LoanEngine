<?php

namespace App\Console\Commands;

use App\Models\FinancialEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessFinancialRecurringEvents extends Command
{
    protected $signature = 'financial-flow:process-recurring';

    protected $description = 'Process due recurring financial events safely';

    private const CHUNK_SIZE = 100;
    private const MAX_OCCURRENCES_PER_TEMPLATE = 120;

    public function handle(): int
    {
        $createdCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        FinancialEvent::query()
            ->where('is_recurring', true)
            ->whereIn('recurrence_frequency', [
                'weekly',
                'monthly',
                'yearly',
            ])
            ->whereDate('event_date', '<=', today()->toDateString())
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($templates) use (
                &$createdCount,
                &$skippedCount,
                &$failedCount
            ): void {
                foreach ($templates as $candidate) {
                    try {
                        $result = DB::transaction(function () use ($candidate) {
                            $template = FinancialEvent::query()
                                ->whereKey($candidate->getKey())
                                ->lockForUpdate()
                                ->first();

                            if (
                                !$template ||
                                !$template->is_recurring ||
                                !in_array($template->recurrence_frequency, [
                                    'weekly',
                                    'monthly',
                                    'yearly',
                                ], true)
                            ) {
                                return [
                                    'created' => 0,
                                    'skipped' => 1,
                                ];
                            }

                            $frequency = $template->recurrence_frequency;
                            $today = today()->startOfDay();
                            $endDate = $template->recurrence_end_date
                                ? $template->recurrence_end_date->copy()->startOfDay()
                                : null;

                            $dueDate = $template->event_date->copy()->startOfDay();

                            // On first processing, the template date is the
                            // original occurrence, so start with its next date.
                            if (!$template->last_processed_at) {
                                $dueDate = $this->nextDate($dueDate, $frequency);
                            }

                            $created = 0;
                            $iterations = 0;

                            while (
                                $dueDate->lte($today) &&
                                (!$endDate || $dueDate->lte($endDate)) &&
                                $iterations < self::MAX_OCCURRENCES_PER_TEMPLATE
                            ) {
                                $occurrenceExists = FinancialEvent::query()
                                    ->where('source_event_id', $template->getKey())
                                    ->whereDate('event_date', $dueDate->toDateString())
                                    ->exists();

                                if (!$occurrenceExists) {
                                    FinancialEvent::create([
                                        'user_id' => $template->user_id,
                                        'source_event_id' => $template->getKey(),
                                        'description' => $template->description,
                                        'amount' => $template->amount,
                                        'category' => $template->category,
                                        'event_date' => $dueDate->toDateString(),
                                        'reminder_time' => $template->reminder_time,
                                        'reminder_sent_at' => null,
                                        'payment_status' => 'projected',
                                        'notes' => $template->notes,
                                        'is_recurring' => false,
                                        'recurrence_frequency' => null,
                                        'recurrence_end_date' => null,
                                        'last_processed_at' => null,
                                    ]);

                                    $created++;
                                }

                                $iterations++;
                                $dueDate = $this->nextDate($dueDate, $frequency);
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

                            return [
                                'created' => $created,
                                'skipped' => 0,
                            ];
                        }, 3);

                        $createdCount += $result['created'];
                        $skippedCount += $result['skipped'];
                    } catch (Throwable $exception) {
                        $failedCount++;

                        Log::error('Recurring financial event processing failed.', [
                            'template_id' => $candidate->getKey(),
                            'exception' => $exception->getMessage(),
                        ]);

                        $this->error(
                            "Template ID {$candidate->getKey()} failed. Check the application log."
                        );
                    }
                }
            });

        $this->info("Occurrences created: {$createdCount}");
        $this->info("Templates skipped: {$skippedCount}");

        if ($failedCount > 0) {
            $this->error("Templates failed: {$failedCount}");

            return self::FAILURE;
        }

        $this->info('Recurring financial event processing completed.');

        return self::SUCCESS;
    }

    private function nextDate(Carbon $date, string $frequency): Carbon
    {
        return match ($frequency) {
            'weekly' => $date->copy()->addWeek(),
            'monthly' => $date->copy()->addMonthNoOverflow(),
            'yearly' => $date->copy()->addYearNoOverflow(),
            default => throw new \InvalidArgumentException(
                "Unsupported recurrence frequency: {$frequency}"
            ),
        };
    }
}
