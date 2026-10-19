@extends('layouts.admin')
@section('title', $lesson->title . ' | Lesson')
@section('content')
@php
    $mediaUrl = $lesson->media_path ? asset('storage/' . ltrim($lesson->media_path, '/')) : $lesson->external_media_url;
    $thumbnailUrl = $lesson->thumbnail_path ? asset('storage/' . ltrim($lesson->thumbnail_path, '/')) : null;
@endphp
<div class="edu-lessons-page edu-lesson-show-page">
    <div class="edu-lessons-heading"><div><span class="edu-lessons-eyebrow">LESSON DETAILS</span><h1>{{ $lesson->title }}</h1><p>Review lesson information and preview its media.</p></div><div class="edu-show-heading-actions"><a class="edu-lessons-secondary" href="{{ route('admin.education.lessons.index') }}">← Back to Lessons</a><a class="edu-lessons-primary" href="{{ route('admin.education.lessons.edit', $lesson) }}">✎ Edit Lesson</a></div></div>
    <section class="edu-lesson-show-grid"><div class="edu-lessons-card edu-lesson-show-main">
        <div class="edu-lesson-show-media">
            @if($mediaUrl && in_array($lesson->content_type, ['video','reel']))<video controls playsinline preload="metadata" @if($thumbnailUrl) poster="{{ $thumbnailUrl }}" @endif><source src="{{ $mediaUrl }}">Your browser does not support HTML video. Check that the file URL is a direct video URL.</video>
            @elseif($mediaUrl && $lesson->content_type === 'audio')<div class="edu-lesson-audio-art">♫</div><audio controls preload="metadata"><source src="{{ $mediaUrl }}">Your browser does not support HTML audio.</audio>
            @elseif($thumbnailUrl)<img src="{{ $thumbnailUrl }}" alt="{{ $lesson->title }}">
            @else<div class="edu-lesson-show-placeholder">{{ $lesson->content_type === 'article' ? 'Aa' : '▶' }}<span>No preview media uploaded</span></div>@endif
        </div>
        <div class="edu-lesson-show-copy"><span class="edu-lesson-type edu-lesson-type-{{ $lesson->content_type }}">{{ ucfirst($lesson->content_type) }}</span><h2>About this lesson</h2><p>{{ $lesson->description ?: 'No description has been added for this lesson.' }}</p>@if($lesson->content)<div class="edu-lesson-article-content">{!! nl2br(e($lesson->content)) !!}</div>@endif</div>
    </div><aside class="edu-lessons-card edu-lesson-details-card"><h2>Lesson information</h2><dl><div><dt>Module</dt><dd>{{ $lesson->module?->title ?? 'Unassigned' }}</dd></div><div><dt>Category</dt><dd>{{ $lesson->module?->category?->name ?? '—' }}</dd></div><div><dt>Slug</dt><dd class="edu-break-word">{{ $lesson->slug }}</dd></div><div><dt>Content type</dt><dd>{{ ucfirst($lesson->content_type) }}</dd></div><div><dt>Duration</dt><dd>{{ $lesson->formatted_duration }}</dd></div><div><dt>Display order</dt><dd>{{ $lesson->sort_order }}</dd></div><div><dt>Status</dt><dd><span class="edu-lesson-status edu-lesson-status-{{ $lesson->status }}"><i></i>{{ ucfirst($lesson->status) }}</span></dd></div><div><dt>Media source</dt><dd>{{ $lesson->media_path ? 'Uploaded file' : ($lesson->external_media_url ? 'External URL' : 'Not set') }}</dd></div></dl>
        @if($lesson->external_media_url)<a class="edu-lessons-secondary edu-external-link" href="{{ $lesson->external_media_url }}" target="_blank" rel="noopener noreferrer">Open external media ↗</a>@endif
    </aside></section>
</div>
@endsection
