
@extends('layouts.admin')

@section('title', $module->title)

@section('content')
<div class="dashboard-page">
    <div class="welcome-row">
        <div>
            <span class="eyebrow">{{ $module->category->name }}</span>
            <h1>{{ $module->title }}</h1>
            <p>{{ $module->description }}</p>
        </div>
        <a class="text-button" href="{{ route('user.education.index') }}">← All Education</a>
    </div>

    @if($module->thumbnail_path)
        <img
            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($module->thumbnail_path) }}"
            alt="{{ $module->title }}"
            style="width:100%;max-height:330px;object-fit:cover;border-radius:16px">
    @endif

    <section class="panel">
        <div class="panel-heading">
            <h2>Lessons</h2>
            <span class="muted-label">{{ $module->publishedLessons->count() }} lessons</span>
        </div>

        @forelse($module->publishedLessons as $lesson)
            <a href="{{ route('education.lesson', [$module->slug, $lesson->slug]) }}"
               class="activity-item" style="text-decoration:none">
                <span class="activity-icon">
                    @switch($lesson->content_type)
                        @case('video') ▶ @break
                        @case('audio') ♪ @break
                        @case('reel') ▷ @break
                        @default ▤
                    @endswitch
                </span>
                <div>
                    <strong>{{ $lesson->title }}</strong>
                    <span>{{ $lesson->description }}</span>
                </div>
                <time>{{ $lesson->formatted_duration }}</time>
                <span class="link-arrow">›</span>
            </a>
        @empty
            <p class="muted-text">No published lessons are available yet.</p>
        @endforelse
    </section>
</div>
@endsection
