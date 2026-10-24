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
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'calculator_id' => 'integer',
            'created_at' => 'datetime',
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
}
