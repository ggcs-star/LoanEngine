<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationModule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'education_category_id',
        'title',
        'slug',
        'description',
        'thumbnail_path',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'education_category_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            EducationCategory::class,
            'education_category_id'
        );
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(EducationLesson::class, 'education_module_id');
    }

    public function publishedLessons(): HasMany
    {
        return $this->lessons()->where('status', 'published');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
