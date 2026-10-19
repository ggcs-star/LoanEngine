
@extends('layouts.admin')

@section('title', $lesson->title)

@section('content')
<div class="dashboard-page">
    <div class="welcome-row">
        <div>
            <span class="eyebrow">{{ $module->title }}</span>
            <h1>{{ $lesson->title }}</h1>
            <p>{{ $lesson->description }}</p>
        </div>
        <a class="text-button" href="{{ route('education.show', $module->slug) }}">← Back to lessons</a>
    </div>

    <section class="panel">
        @php
            $mediaUrl = $lesson->media_path
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($lesson->media_path)
                : $lesson->external_media_url;
        @endphp

        @if($lesson->content_type === 'video' || $lesson->content_type === 'reel')
            @if($mediaUrl)
                <video controls playsinline preload="metadata"
                       style="width:100%;max-height:560px;border-radius:12px;background:#111">
                    <source src="{{ $mediaUrl }}">
                    Your browser does not support video playback.
                </video>
            @else
                <p class="muted-text">Video media has not been added yet.</p>
            @endif
        @elseif($lesson->content_type === 'audio')
            @if($mediaUrl)
                <audio controls preload="metadata" style="width:100%">
                    <source src="{{ $mediaUrl }}">
                    Your browser does not support audio playback.
                </audio>
            @else
                <p class="muted-text">Audio media has not been added yet.</p>
            @endif
        @endif

        @if($lesson->content)
            <article style="margin-top:24px;line-height:1.9;white-space:pre-line">
                {{ $lesson->content }}
            </article>
        @endif

        <div class="panel-heading" style="margin-top:24px">
            <span class="muted-text">{{ $lesson->formatted_duration }}</span>

            @if($nextLesson)
                <a class="primary-button"
                   href="{{ route('education.lesson', [$module->slug, $nextLesson->slug]) }}">
                    Next Lesson →
                </a>
            @endif
        </div>
    </section>
</div>
@endsection
