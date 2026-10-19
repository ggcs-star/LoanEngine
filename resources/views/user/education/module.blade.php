
@extends('user.layouts.app')

@section('title', $module->title . ' | Education')
@section('page_heading', 'Education')

@section('content')
<div class="user-education-page">
    <a class="education-back-link"
       href="{{ route('user.education.category', $module->category) }}">
        ← {{ $module->category->name }}
    </a>

    <section class="education-hero">
        <span class="education-eyebrow">LEARNING MODULE</span>
        <h2>{{ $module->title }}</h2>
        <p>{{ $module->description ?: 'Follow the lessons in this module to continue learning.' }}</p>
        <span class="education-count-badge">{{ $lessons->count() }} lessons</span>
    </section>

    <div class="education-heading">
        <div>
            <span class="education-eyebrow">MODULE CONTENT</span>
            <h2>Lessons</h2>
        </div>
    </div>

    <div class="education-lesson-list">
        @forelse($lessons as $lesson)
            <a class="education-lesson-row"
            href="{{ route('user.education.lesson', $lesson) }}">

                {{-- Lesson thumbnail --}}
<div class="education-lesson-thumbnail">
    @php
        $mediaService = app(
            \App\Services\Education\EducationMediaService::class
        );

        $thumbnailPath = $lesson->thumbnail_path;
        $thumbnailUrl = $mediaService->url($thumbnailPath);
        $thumbnailExists = $mediaService->exists($thumbnailPath);

        $fallbackIcon = match ($lesson->content_type) {
            'video' => '▶',
            'reel'  => '▷',
            'audio' => '♫',
            default => 'Aa',
        };
    @endphp

    @if($thumbnailUrl && $thumbnailExists)
        <img
            src="{{ $thumbnailUrl }}"
            alt="{{ $lesson->title }}"
            class="education-lesson-thumbnail-image"
            loading="lazy"
            onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
        >

        <span class="lesson-type-icon" hidden>
            {{ $fallbackIcon }}
        </span>
    @else
        <span class="lesson-type-icon">
            {{ $fallbackIcon }}
        </span>
    @endif
</div>

                <span class="education-lesson-copy">
                    <strong>{{ $lesson->title }}</strong>
                    <small>
                        {{ $lesson->description ?: 'Open to view this lesson.' }}
                    </small>
                </span>

                <span class="education-lesson-meta">
                    {{ ucfirst($lesson->content_type) }}
                    @if($lesson->duration_seconds > 0)
                        · {{ $lesson->formatted_duration }}
                    @endif
                </span>

                <span aria-hidden="true">→</span>
            </a>
        @empty
            <div class="education-empty">
                <h3>No published lessons yet</h3>
                <p>Published lessons will appear here when available.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
