<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationLesson extends Model
{
    use SoftDeletes;

    public const CONTENT_TYPES = [
        'video',
        'audio',
        'article',
        'reel',
    ];

    protected $fillable = [
        'education_module_id',
        'title',
        'slug',
        'description',
        'content',
        'content_type',
        'media_path',
        'external_media_url',
        'thumbnail_path',
        'duration_seconds',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'education_module_id' => 'integer',
            'duration_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(EducationModule::class, 'education_module_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function getFormattedDurationAttribute(): string
    {
        $seconds = (int) $this->duration_seconds;

        if ($seconds < 3600) {
            return sprintf('%d min', (int) ceil($seconds / 60));
        }

        return sprintf(
            '%d hr %d min',
            intdiv($seconds, 3600),
            intdiv($seconds % 3600, 60)
        );
    }
}
