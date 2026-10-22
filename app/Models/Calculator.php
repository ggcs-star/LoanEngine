<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Calculator extends Model
{
    protected $fillable = ['name', 'slug', 'short_description', 'icon', 'status', 'display_order', 'route_name'];

    protected function casts(): array
    {
        return ['display_order' => 'integer'];
    }

    public function journeys(): BelongsToMany
    {
        return $this->belongsToMany(ToolJourney::class, 'calculator_journey', 'calculator_id', 'journey_id')->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $query;

        return $query->where(fn (Builder $q) => $q
            ->where('name', 'like', "%{$term}%")
            ->orWhere('slug', 'like', "%{$term}%"));
    }
}
