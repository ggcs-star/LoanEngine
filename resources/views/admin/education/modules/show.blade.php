
@extends('layouts.admin')

@section('title', 'View Education Module')

@section('content')
<div class="dashboard-page edu-modules-page">

    {{-- Header --}}
    <div class="edu-modules-header">
        <div>
            <a href="{{ route('admin.education.modules.index') }}"
               class="edu-modules-back-link">
                <span>&larr;</span> Back to Modules
            </a>

            <span class="edu-modules-eyebrow">EDUCATION MANAGEMENT</span>
            <h1>Module Details</h1>
            <p>View the information and content of this learning module.</p>
        </div>

        <div class="edu-modules-show-actions">
            <a href="{{ route('admin.education.modules.edit', $module) }}"
               class="edu-modules-primary-btn">
                Edit Module <span>&rarr;</span>
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="edu-modules-alert edu-modules-alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Module Overview --}}
    <section class="edu-modules-card edu-modules-show-card">

        <div class="edu-modules-show-cover">
            @if ($module->thumbnail_path)
                <button
                    type="button"
                    class="edu-modules-show-image-button"
                    id="eduModuleShowImageButton"
                    aria-label="Enlarge module cover image"
                >
                    <img
                        src="{{ asset('storage/' . ltrim($module->thumbnail_path, '/')) }}"
                        alt="{{ $module->title }} cover image"
                        id="eduModuleShowCoverImage"
                    >
                    <span class="edu-modules-image-zoom">View image ↗</span>
                </button>
            @else
                <div class="edu-modules-show-no-cover">
                    <span>▧</span>
                    <p>No cover image available</p>
                </div>
            @endif
        </div>

        <div class="edu-modules-show-main">

            <div class="edu-modules-show-labels">
                <span class="edu-modules-category">
                    {{ $module->category?->name ?? 'Uncategorized' }}
                </span>

                <span class="edu-modules-status edu-modules-status-{{ $module->status }}">
                    <span></span>
                    {{ ucfirst($module->status) }}
                </span>
            </div>

            <h2>{{ $module->title }}</h2>

            <p class="edu-modules-show-slug">
                <span>Slug</span>
                <code>{{ $module->slug }}</code>
            </p>

            <div class="edu-modules-show-description">
                <h3>About this module</h3>

                @if ($module->description)
                    <p>{{ $module->description }}</p>
                @else
                    <p class="edu-modules-muted">
                        No description has been added for this module yet.
                    </p>
                @endif
            </div>

            <div class="edu-modules-show-meta">
                <div>
                    <span>Display Order</span>
                    <strong>{{ $module->sort_order ?? 0 }}</strong>
                </div>

                <div>
                    <span>Total Lessons</span>
                    <strong>{{ $module->lessons_count ?? 0 }}</strong>
                </div>

                <div>
                    <span>Module ID</span>
                    <strong>{{ $module->id }}</strong>
                </div>
            </div>

        </div>
    </section>

    {{-- Lessons Section --}}
    <section class="edu-modules-card edu-modules-show-lessons">
        <div class="edu-modules-card-heading">
            <div>
                <h2>Module Lessons</h2>
                <p>Lessons associated with this education module.</p>
            </div>

            <span class="edu-modules-count">
                {{ $module->lessons_count ?? 0 }} lessons
            </span>
        </div>

        <div class="edu-modules-lessons-placeholder">
            <div class="edu-modules-empty-icon">▤</div>
            <h3>Lesson overview</h3>
            <p>
                This module has
                {{ $module->lessons_count ?? 0 }}
                {{ ($module->lessons_count ?? 0) == 1 ? 'lesson' : 'lessons' }}.
            </p>
        </div>
    </section>

    <div class="edu-modules-show-bottom">
        <a href="{{ route('admin.education.modules.index') }}"
           class="edu-modules-secondary-btn">
            &larr; Back to Modules
        </a>

        <a href="{{ route('admin.education.modules.edit', $module) }}"
           class="edu-modules-primary-btn">
            Edit Module
        </a>
    </div>

</div>

{{-- Cover Image Modal --}}
@if ($module->thumbnail_path)
    <div class="edu-modules-modal" id="eduModuleShowImageModal" aria-hidden="true">
        <div class="edu-modules-modal-backdrop"
             data-close-show-image></div>

        <div class="edu-modules-modal-content"
             role="dialog"
             aria-modal="true"
             aria-label="Module cover image preview">

            <div class="edu-modules-modal-header">
                <h3>{{ $module->title }}</h3>

                <button type="button"
                        data-close-show-image
                        aria-label="Close image preview">×</button>
            </div>

            <div class="edu-modules-modal-image-wrap">
                <img
                    src="{{ asset('storage/' . ltrim($module->thumbnail_path, '/')) }}"
                    alt="{{ $module->title }} cover image"
                >
            </div>
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageButton = document.getElementById('eduModuleShowImageButton');
    const modal = document.getElementById('eduModuleShowImageModal');

    if (!imageButton || !modal) return;

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('edu-modules-modal-open');
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('edu-modules-modal-open');
    }

    imageButton.addEventListener('click', openModal);

    modal.querySelectorAll('[data-close-show-image]').forEach(function (element) {
        element.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });
});
</script>
@endsection
