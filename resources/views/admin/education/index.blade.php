
@extends('layouts.admin')

@section('title', 'Education')

@section('content')
<div class="dashboard-page">
    <div class="welcome-row">
        <div>
            <span class="eyebrow">LEARN & GROW</span>
            <h1>Education</h1>
            <p>Explore guides, videos, audio and lessons for your loan journey.</p>
        </div>
    </div>

    @forelse($categories as $category)
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>{{ $category->name }}</h2>
                    @if($category->description)
                        <p class="muted-text">{{ $category->description }}</p>
                    @endif
                </div>
            </div>

            <div class="feature-grid" style="margin-top:18px">
                @foreach($category->modules as $module)
                    <a class="feature-card"
                       href="{{ route('education.show', $module->slug) }}">
                        @if($module->thumbnail_path)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($module->thumbnail_path) }}"
                                alt="{{ $module->title }}"
                                loading="lazy"
                                style="width:100%;height:120px;object-fit:cover;border-radius:10px">
                        @else
                            <span class="feature-icon blue"><icon name="book-open" /></span>
                        @endif

                        <strong>{{ $module->title }}</strong>
                        <p>{{ \Illuminate\Support\Str::limit($module->description, 110) }}</p>
                        <span class="muted-text">
                            {{ $module->published_lessons_count }} lessons
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @empty
        <section class="panel">
            <h2>No published education content yet</h2>
            <p class="muted-text">Published categories and modules will appear here.</p>
        </section>
    @endforelse
</div>
@endsection
