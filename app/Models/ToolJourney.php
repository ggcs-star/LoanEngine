<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToolJourney extends Model
{
    protected $table = 'tool_journeys';

    protected $fillable = [
        'name',
        'slug',
        'short_label',
        'description',
        'display_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
        ];
    }

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(
            Tool::class,
            'journey_tool',
            'journey_id',
            'tool_id'
        )->withTimestamps();
    }

    public function steps(): HasMany
    {
        return $this->hasMany(JourneyStep::class, 'journey_id')
            ->orderBy('step_number')
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('display_order')
            ->orderBy('name');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('name', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%")
                ->orWhere('short_label', 'like', "%{$term}%");
        });
    }
}
