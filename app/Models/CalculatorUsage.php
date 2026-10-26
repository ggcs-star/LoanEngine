<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculatorUsage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'calculator_id',
        'inputs',
        'result',
        'event_type',
        'status',
        'session_id',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'calculator_id' => 'integer',
            'inputs' => 'array',
            'result' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(Calculator::class);
    }

    public function scopeCalculated($query)
    {
        return $query->where('event_type', 'calculated');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
