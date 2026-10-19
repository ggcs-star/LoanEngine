<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;


class Tool extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PLANNED = 'planned';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'icon',
        'category',
        'type',
        'status',
        'display_order',
        'page_route',
        'show_on_toolkit_home',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'show_on_toolkit_home' => 'boolean',
        ];
    }

    public function journeys(): BelongsToMany
    {
        return $this->belongsToMany(
            ToolJourney::class,
            'journey_tool',
            'tool_id',
            'journey_id'
        )->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
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
                ->orWhere('short_description', 'like', "%{$term}%");
        });
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = trim($value);
    }

    public function setSlugAttribute(string $value): void
    {
        $this->attributes['slug'] = Str::slug($value);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
    public function journeySteps(): BelongsToMany
    {
        return $this->belongsToMany(
            JourneyStep::class,
            'journey_step_tool',
            'tool_id',
            'step_id'
        )->withTimestamps();
    }
}
