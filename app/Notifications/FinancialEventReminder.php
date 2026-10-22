<?php
namespace App\Notifications;

use App\Models\FinancialEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FinancialEventReminder extends Notification
{
    use Queueable;

    public function __construct(public FinancialEvent $event) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $time = $this->event->reminder_time
            ? \Illuminate\Support\Carbon::parse($this->event->reminder_time)->format('h:i A')
            : null;

        return (new MailMessage)
            ->subject('LoanEngine reminder: '.$this->event->description)
            ->greeting('Hello '.($notifiable->name ?? 'there').',')
            ->line('This is a reminder for your scheduled financial event.')
            ->line('Event: '.$this->event->description)
            ->line('Date: '.$this->event->event_date->format('d M Y').($time ? ' at '.$time : ''))
            ->line('Amount: ₹'.number_format((float) $this->event->amount, 2))
            ->line('Please open LoanEngine to review your event.');
    }

    public function toDatabase(object $notifiable): array
    {
        $time = $this->event->reminder_time
            ? \Illuminate\Support\Carbon::parse($this->event->reminder_time)->format('h:i A')
            : null;

        return [
            'type' => 'financial_event_reminder',
            'financial_event_id' => $this->event->id,
            'title' => 'Financial event reminder',
            'message' => 'Reminder: '.$this->event->description.' is scheduled for '
                .$this->event->event_date->format('d M Y').($time ? ' at '.$time : '').'.',
            'event_date' => $this->event->event_date->toDateString(),
            'reminder_time' => $this->event->reminder_time,
            'amount' => (float) $this->event->amount,
            'url' => route('user.financial-flow.index'),
        ];
    }
}
