
@extends('user.layouts.app')

@section('title', $lesson->title . ' | Education')
@section('page_heading', 'Education')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-education.css') }}">
@endpush

@section('content')
@php
    $mediaUrl = $lesson->media_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($lesson->media_path)
        : $lesson->external_media_url;

    $thumbnailUrl = $lesson->thumbnail_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($lesson->thumbnail_path)
        : null;

    $type = strtolower($lesson->content_type ?? 'article');
    $isVideo = in_array($type, ['video', 'reel'], true);
    $isReel = $type === 'reel';

    $currentIndex = $siblings->search(
        fn ($item) => (int) $item->id === (int) $lesson->id
    );

    $lessonNumber = $currentIndex === false ? 1 : $currentIndex + 1;
    $lessonTotal = $siblings->count();
    $progress = $lessonTotal > 0
        ? (int) round(($lessonNumber / $lessonTotal) * 100)
        : 0;

    $youtubeId = null;

    if ($isVideo && $lesson->external_media_url) {
        $host = strtolower(parse_url($lesson->external_media_url, PHP_URL_HOST) ?? '');
        $path = trim(parse_url($lesson->external_media_url, PHP_URL_PATH) ?? '', '/');

        if ($host === 'youtu.be' || str_ends_with($host, '.youtu.be')) {
            $youtubeId = explode('/', $path)[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str(parse_url($lesson->external_media_url, PHP_URL_QUERY) ?? '', $youtubeQuery);

            $youtubeId = $youtubeQuery['v'] ?? null;

            if (!$youtubeId && str_starts_with($path, 'embed/')) {
                $youtubeId = explode('/', substr($path, 6))[0] ?? null;
            }

            if (!$youtubeId && str_starts_with($path, 'shorts/')) {
                $youtubeId = explode('/', substr($path, 7))[0] ?? null;
            }
        }

        if ($youtubeId && !preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $youtubeId)) {
            $youtubeId = null;
        }
    }
@endphp

<div class="user-education-page lesson-page">

    <a class="education-back-link lesson-back-link"
       href="{{ route('user.education.module', $lesson->module) }}">
        <span aria-hidden="true">←</span>
        Back to {{ $lesson->module->title }}
    </a>

    <div class="lesson-workspace">

        <main class="lesson-main-column">

            <section class="lesson-heading-panel">
                <div class="lesson-heading-top">
                    <div class="lesson-heading-copy">
                        <div class="education-eyebrow">
                            {{ $lesson->module->category->name ?? 'Learning Centre' }}
                            <span aria-hidden="true">/</span>
                            {{ $lesson->module->title }}
                        </div>

                        <h2>{{ $lesson->title }}</h2>

                        <p>
                            {{ $lesson->description ?: 'Learn at your own pace with this lesson.' }}
                        </p>
                    </div>

                    <span class="lesson-type-badge">
                        @switch($type)
                            @case('video') Video lesson @break
                            @case('reel') Reel @break
                            @case('audio') Audio lesson @break
                            @default Article
                        @endswitch
                    </span>
                </div>

                <div class="lesson-meta-row">
                    @if($lesson->duration_seconds > 0)
                        <span class="lesson-meta-chip">
                            <span aria-hidden="true">◷</span>
                            {{ $lesson->formatted_duration }}
                        </span>
                    @endif

                    <span class="lesson-meta-chip">
                        Lesson {{ $lessonNumber }} of {{ $lessonTotal }}
                    </span>

                    <span class="lesson-meta-chip">
                        {{ ucfirst($type) }}
                    </span>
                </div>
            </section>

            <section class="lesson-player-panel" aria-label="Lesson media player">

                @if($mediaUrl && $isVideo && $youtubeId)
                    <div class="lesson-video-frame">
                        <iframe
                            src="https://www.youtube-nocookie.com/embed/{{ $youtubeId }}"
                            title="{{ $lesson->title }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                            allowfullscreen
                            loading="lazy">
                        </iframe>
                    </div>

                @elseif($mediaUrl && $isVideo && !$youtubeId)
                    <div class="{{ $isReel ? 'lesson-reel-frame' : 'lesson-video-frame' }}">
                        <video
                            controls
                            playsinline
                            preload="metadata"
                            @if($thumbnailUrl) poster="{{ $thumbnailUrl }}" @endif>
                            <source src="{{ $mediaUrl }}">
                            Your browser does not support video playback.
                        </video>
                    </div>

                @elseif($mediaUrl && $type === 'audio')
                    <div class="lesson-audio-player">
                        <div class="lesson-audio-art">
                            @if($thumbnailUrl)
                                <img src="{{ $thumbnailUrl }}"
                                     alt="{{ $lesson->title }}"
                                     onerror="this.style.display='none';">
                            @else
                                <span aria-hidden="true">♫</span>
                            @endif
                        </div>

                        <div class="lesson-audio-label">NOW PLAYING</div>
                        <h3>{{ $lesson->title }}</h3>

                        <p>
                            {{ $lesson->description ?: 'Listen to this lesson at your own pace.' }}
                        </p>

                        <audio controls preload="metadata">
                            <source src="{{ $mediaUrl }}">
                            Your browser does not support audio playback.
                        </audio>

                        <small class="lesson-audio-help">
                            Use the player controls to play, pause, seek and adjust volume.
                        </small>
                    </div>

                @elseif($type === 'article')
                    <div class="lesson-article-cover">
                        <div class="lesson-article-symbol">Aa</div>
                        <span>READING MATERIAL</span>
                        <h3>{{ $lesson->title }}</h3>
                        <p>{{ $lesson->description ?: 'Read the lesson below.' }}</p>
                    </div>

                @elseif($thumbnailUrl)
                    <div class="lesson-fallback-cover">
                        <img src="{{ $thumbnailUrl }}" alt="{{ $lesson->title }}">
                        <div class="lesson-fallback-caption">
                            <strong>{{ $lesson->title }}</strong>
                            <span>Media is not available to play.</span>
                        </div>
                    </div>

                @else
                    <div class="lesson-no-media">
                        <div class="lesson-article-symbol">
                            {{ $type === 'audio' ? '♫' : ($isReel ? '▷' : '▶') }}
                        </div>
                        <h3>Learning material</h3>
                        <p>The media file has not been added or is not available.</p>
                    </div>
                @endif

            </section>

            <section class="lesson-overview-panel">
                <div class="lesson-section-heading">
                    <span class="education-eyebrow">LESSON OVERVIEW</span>
                    <h3>About this lesson</h3>
                </div>

                <p class="lesson-overview-description">
                    {{ $lesson->description ?: 'Explore the lesson material and learn at your own pace.' }}
                </p>

                @if($lesson->content)
                    <div class="lesson-content-text">
                        {!! nl2br(e($lesson->content)) !!}
                    </div>
                @endif

                <div class="lesson-overview-stats">
                    @if($lesson->duration_seconds > 0)
                        <div>
                            <strong>{{ $lesson->formatted_duration }}</strong>
                            <span>Duration</span>
                        </div>
                    @endif

                    <div>
                        <strong>{{ $lessonNumber }} of {{ $lessonTotal }}</strong>
                        <span>Lesson</span>
                    </div>

                    <div>
                        <strong>{{ ucfirst($type) }}</strong>
                        <span>Content type</span>
                    </div>
                </div>
            </section>

            <nav class="lesson-navigation" aria-label="Lesson navigation">
                @if($previousLesson)
                    <a class="lesson-nav-button lesson-nav-previous"
                       href="{{ route('user.education.lesson', $previousLesson) }}">
                        <span aria-hidden="true">←</span>
                        <span>
                            <small>PREVIOUS</small>
                            <strong>Previous lesson</strong>
                        </span>
                    </a>
                @else
                    <span></span>
                @endif

                @if($nextLesson)
                    <a class="lesson-nav-button lesson-nav-next"
                       href="{{ route('user.education.lesson', $nextLesson) }}">
                        <span>
                            <small>NEXT LESSON</small>
                            <strong>Continue learning</strong>
                        </span>
                        <span aria-hidden="true">→</span>
                    </a>
                @else
                    <span class="lesson-complete-label">You reached the last lesson.</span>
                @endif
            </nav>

        </main>

        <aside class="lesson-playlist-panel">
            <div class="lesson-playlist-heading">
                <div>
                    <span class="education-eyebrow">YOUR LEARNING PATH</span>
                    <h3>Lesson contents</h3>
                </div>
                <span class="lesson-playlist-count">
                    {{ $lessonNumber }}/{{ $lessonTotal }}
                </span>
            </div>

            <div class="lesson-progress-track"
                 role="progressbar"
                 aria-label="Lesson progress"
                 aria-valuemin="0"
                 aria-valuemax="100"
                 aria-valuenow="{{ $progress }}">
                <span style="width: {{ $progress }}%"></span>
            </div>

            <div class="lesson-playlist-module">
                {{ $lesson->module->title }}
            </div>

            <div class="lesson-playlist-items">
                @foreach($siblings as $index => $item)
                    @php
                        $itemType = strtolower($item->content_type ?? 'article');

                        $itemIcon = match ($itemType) {
                            'video' => '▶',
                            'reel' => '▷',
                            'audio' => '♫',
                            default => 'Aa',
                        };
                    @endphp

                    <a class="lesson-playlist-item {{ (int) $item->id === (int) $lesson->id ? 'is-current' : '' }}"
                       href="{{ route('user.education.lesson', $item) }}"
                       @if((int) $item->id === (int) $lesson->id) aria-current="page" @endif>

                        <span class="lesson-playlist-icon" aria-hidden="true">
                            {{ $itemIcon }}
                        </span>

                        <span class="lesson-playlist-copy">
                            <strong>{{ $item->title }}</strong>
                            <small>
                                {{ ucfirst($itemType) }}
                                @if($item->duration_seconds > 0)
                                    · {{ $item->formatted_duration }}
                                @endif
                            </small>
                        </span>

                        <span class="lesson-playlist-status" aria-hidden="true">
                            {{ (int) $item->id === (int) $lesson->id ? '●' : '→' }}
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="lesson-playlist-footer">
                <span class="lesson-playlist-footer-icon" aria-hidden="true">L</span>
                <div>
                    <strong>Keep learning</strong>
                    <p>One lesson at a time. Continue whenever you are ready.</p>
                </div>
            </div>
        </aside>

    </div>
</div>
@endsection
