<?php
namespace App\Console\Commands;

use App\Models\FinancialEvent;
use App\Notifications\FinancialEventReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendFinancialEventReminders extends Command
{
    protected $signature = 'financial-flow:send-reminders';
    protected $description = 'Send due financial event reminders in-app and by email';

    public function handle(): int
    {
        $today = now()->toDateString();
        $time = now()->format('H:i:s');
        $sent = 0;

        FinancialEvent::query()
            ->whereDate('event_date', $today)
            ->whereNotNull('reminder_time')
            ->whereTime('reminder_time', '<=', $time)
            ->whereNull('reminder_sent_at')
            ->orderBy('id')
            ->chunkById(100, function ($events) use (&$sent) {
                foreach ($events as $candidate) {
                    try {
                        $didSend = DB::transaction(function () use ($candidate) {
                            $event = FinancialEvent::query()
                                ->whereKey($candidate->id)->lockForUpdate()->first();

                            if (!$event || $event->reminder_sent_at || !$event->reminder_time) {
                                return false;
                            }

                            $user = $event->user;
                            if (!$user || !$user->email) {
                                Log::warning('Financial reminder skipped: user/email missing.', [
                                    'financial_event_id' => $event->id,
                                ]);
                                return false;
                            }

                            // Synchronous mail + database notification.
                            $user->notify(new FinancialEventReminder($event));
                            $event->forceFill(['reminder_sent_at' => now()])->save();

                            return true;
                        });

                        if ($didSend) $sent++;
                    } catch (Throwable $e) {
                        Log::error('Financial reminder failed.', [
                            'financial_event_id' => $candidate->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Financial event reminders sent: {$sent}");
        return self::SUCCESS;
    }
}
