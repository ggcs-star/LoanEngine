<?php

namespace App\Http\Resources\Api\V1\User\Calendar;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'category' => $this->category,
            'event_date' => $this->event_date?->format('Y-m-d'),
            'reminder_time' => $this->reminder_time,
            'reminder_sent_at' => $this->reminder_sent_at?->toISOString(),
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'is_recurring' => (bool) $this->is_recurring,
            'recurrence_frequency' => $this->recurrence_frequency,
            'recurrence_end_date' => $this->recurrence_end_date?->format('Y-m-d'),
            'source_event_id' => $this->source_event_id,
            'last_processed_at' => $this->last_processed_at?->toISOString(),
            'is_locked' => $this->reminder_sent_at !== null
                || $this->source_event_id !== null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
