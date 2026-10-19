<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JourneyStep extends Model
{
    protected $table = 'journey_steps';

    protected $fillable = [
        'journey_id',
        'step_number',
        'title',
        'description',
        'what_you_need',
        'watch_out_for',
        'display_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'journey_id' => 'integer',
            'step_number' => 'integer',
            'display_order' => 'integer',
        ];
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(ToolJourney::class, 'journey_id');
    }

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(
            Tool::class,
            'journey_step_tool',
            'step_id',
            'tool_id'
        )->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('step_number')
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('what_you_need', 'like', "%{$term}%")
                ->orWhere('watch_out_for', 'like', "%{$term}%");
        });
    }
}
