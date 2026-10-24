<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialEvent extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'amount',
        'category',
        'event_date',
        'reminder_time',       // Added
        'reminder_sent_at',    // Added
        'payment_status',
        'source_event_id',
        'notes',
        'is_recurring',
        'recurrence_frequency',
        'recurrence_end_date',
        'last_processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'event_date' => 'date',
            'reminder_sent_at' => 'datetime', // Added
            'is_recurring' => 'boolean',
            'recurrence_end_date' => 'date',
            'last_processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where('user_id', $userId);
    }

    public function scopeForMonth(
        Builder $query,
        int $year,
        int $month
    ): Builder {
        return $query
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $month);
    }
}
