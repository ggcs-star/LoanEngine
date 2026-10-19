 @extends('user.layouts.app')

@section('title', $category->name . ' | Education')
@section('page_heading', 'Education')

@section('content')
<div class="user-education-page ec-page">

    <section class="ec-hero">
        <div class="ec-hero-copy">
            <div class="ec-eyebrow">
                <span>LEARNING CENTRE</span>
                <span class="ec-dot"></span>
                <span>{{ strtoupper($category->name) }}</span>
            </div>

            <h2>{{ $category->name }}</h2>

            <p>
                {{ $category->description ?: 'Explore practical learning resources and improve your financial knowledge at your own pace.' }}
            </p>

            <form class="ec-search-form" id="ecModuleSearchForm" action="#" role="search">
                <label class="ec-search-field">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m16 16 4 4"></path>
                    </svg>
                    <input
                        type="search"
                        id="ecModuleSearch"
                        placeholder="Search {{ strtolower($category->name) }} modules..."
                        aria-label="Search modules"
                    >
                </label>
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="ec-hero-art" aria-hidden="true">
            <div class="ec-orbit ec-orbit-one"></div>
            <div class="ec-orbit ec-orbit-two"></div>

            <div class="ec-floating ec-floating-top">
                <span class="ec-floating-icon">✦</span>
                <span>Practical<br>Guidance</span>
            </div>

            <div class="ec-learning-card">
                <div class="ec-play-icon">
                    <svg viewBox="0 0 24 24"><path d="m9 5 10 7-10 7z"></path></svg>
                </div>
                <strong>Keep learning</strong>
                <span>One module at a time</span>
                <div class="ec-progress"><span></span></div>
            </div>

            <div class="ec-floating ec-floating-right">
                <span class="ec-floating-icon">↗</span>
                <span>Build<br>Your Future</span>
            </div>
        </div>
    </section>

    <section class="ec-modules-section">
        <div class="ec-section-heading">
            <div>
                <span class="ec-section-eyebrow">EXPLORE CONTENT</span>
                <h2>Available {{ strtolower($category->name) }} modules</h2>
                <p>Choose a module to explore its lessons and learning material.</p>
            </div>

            <span class="ec-total-badge">
                <span class="ec-total-dot"></span>
                {{ $modules->total() }}
                {{ \Illuminate\Support\Str::plural('module', $modules->total()) }}
            </span>
        </div>

        <div class="ec-toolbar">
            <select id="ecModuleSort" aria-label="Sort modules">
                <option value="latest">Latest first</option>
                <option value="oldest">Oldest first</option>
                <option value="az">Title A–Z</option>
                <option value="za">Title Z–A</option>
                <option value="lessons-high">Most lessons</option>
                <option value="lessons-low">Fewest lessons</option>
            </select>

            <div class="ec-view-toggle" aria-label="Module layout">
                <button type="button" class="is-active" data-ec-view="grid" aria-label="Grid view" aria-pressed="true">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                    </svg>
                </button>
                <button type="button" data-ec-view="list" aria-label="List view" aria-pressed="false">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 5h12M9 12h12M9 19h12"></path>
                        <circle cx="4" cy="5" r="1"></circle>
                        <circle cx="4" cy="12" r="1"></circle>
                        <circle cx="4" cy="19" r="1"></circle>
                    </svg>
                </button>
            </div>
        </div>

        <div class="ec-module-grid" id="ecModuleGrid">
            @forelse($modules as $module)
                @php
                    $mediaService = app(\App\Services\Education\EducationMediaService::class);
                    $thumbnailPath = $module->thumbnail_path;
                    $thumbnailUrl = $mediaService->url($thumbnailPath);
                    $thumbnailExists = $mediaService->exists($thumbnailPath);
                    $lessonCount = (int) ($module->published_lessons_count ?? 0);
                @endphp

                <a
                    class="ec-module-card"
                    href="{{ route('user.education.module', $module) }}"
                    data-title="{{ strtolower($module->title) }}"
                    data-created="{{ $module->created_at?->timestamp ?? 0 }}"
                    data-lessons="{{ $lessonCount }}"
                >
                    <div class="ec-module-thumbnail">
                        @php
                            $totalSeconds = (int) ($module->published_duration_seconds ?? 0);

                            $durationLabel = $totalSeconds >= 3600
                                ? floor($totalSeconds / 3600) . ' hr ' . floor(($totalSeconds % 3600) / 60) . ' min'
                                : ($totalSeconds >= 60
                                    ? floor($totalSeconds / 60) . ' min'
                                    : $totalSeconds . ' sec');
                        @endphp

                        @if($totalSeconds > 0)
                            <span class="ec-module-duration">
                                <span aria-hidden="true">◷</span>
                                {{ $durationLabel }}
                            </span>
                        @endif
                        @if($thumbnailUrl && $thumbnailExists)
                            <img
                                src="{{ $thumbnailUrl }}"
                                alt="{{ $module->title }}"
                                loading="lazy"
                                onerror="this.hidden=true;this.parentElement.querySelector('.ec-thumbnail-fallback').hidden=false;"
                            >
                        @endif

                        <div class="ec-thumbnail-fallback" @if($thumbnailUrl && $thumbnailExists) hidden @endif>
                            {{ strtoupper(substr($module->title, 0, 1)) }}
                        </div>

                        <span class="ec-module-label">
                            {{ strtoupper($category->name) }} MODULE
                        </span>

                        <span class="ec-thumbnail-arrow" aria-hidden="true">↗</span>
                    </div>

                    <div class="ec-module-body">
                        <span class="ec-module-type">{{ strtoupper($category->name) }} MODULE</span>
                        <h3>{{ $module->title }}</h3>

                        <p class="ec-module-description">
                            {{ $module->description ?: 'Explore this module and learn at your own pace.' }}
                        </p>

                        <div class="ec-module-footer">
                            <span class="ec-lesson-count">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="5" y="3" width="14" height="18" rx="2"></rect>
                                    <path d="M8 8h8M8 12h8M8 16h5"></path>
                                </svg>
                                {{ $lessonCount }}
                                {{ \Illuminate\Support\Str::plural('lesson', $lessonCount) }}
                            </span>

                            <span class="ec-module-action">
                                Explore Module <span aria-hidden="true">→</span>
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="ec-empty-state">
                    <div class="ec-empty-icon" aria-hidden="true">▤</div>
                    <h3>No published modules yet</h3>
                    <p>Modules with published lessons will appear here when available.</p>
                    <a href="{{ route('user.education.index') }}">Browse categories →</a>
                </div>
            @endforelse

            <div class="ec-no-results" id="ecModuleNoResults" hidden>
                <h3>No matching modules</h3>
                <p>Try another search term.</p>
            </div>
        </div>

        <div class="ec-pagination" id="ecModulePagination">
            {{ $modules->links() }}
        </div>

        <a class="ec-back-link" href="{{ route('user.education.index') }}">
            <span aria-hidden="true">←</span> Back to all categories
        </a>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('ecModuleGrid');
    const searchInput = document.getElementById('ecModuleSearch');
    const searchForm = document.getElementById('ecModuleSearchForm');
    const sort = document.getElementById('ecModuleSort');
    const pagination = document.getElementById('ecModulePagination');
    const noResults = document.getElementById('ecModuleNoResults');
    const viewButtons = document.querySelectorAll('[data-ec-view]');

    if (!grid || !searchInput || !sort) return;

    const cards = Array.from(grid.querySelectorAll('.ec-module-card'));

    function applyFilters() {
        const query = searchInput.value.trim().toLowerCase();
        let visible = 0;

        cards.forEach(function (card) {
            const title = (card.dataset.title || '').toLowerCase();
            const text = card.textContent.toLowerCase();
            const matches = !query || title.includes(query) || text.includes(query);
            card.hidden = !matches;
            if (matches) visible++;
        });

        if (noResults) noResults.hidden = visible > 0;
        if (pagination) pagination.hidden = query.length > 0;
    }

    function sortCards() {
        const mode = sort.value;
        const sorted = [...cards].sort(function (a, b) {
            const titleA = a.dataset.title || '';
            const titleB = b.dataset.title || '';
            const createdA = Number(a.dataset.created || 0);
            const createdB = Number(b.dataset.created || 0);
            const lessonsA = Number(a.dataset.lessons || 0);
            const lessonsB = Number(b.dataset.lessons || 0);

            switch (mode) {
                case 'oldest': return createdA - createdB;
                case 'az': return titleA.localeCompare(titleB);
                case 'za': return titleB.localeCompare(titleA);
                case 'lessons-high': return lessonsB - lessonsA;
                case 'lessons-low': return lessonsA - lessonsB;
                default: return createdB - createdA;
            }
        });

        sorted.forEach(function (card) { grid.insertBefore(card, noResults); });
        applyFilters();
    }

    searchInput.addEventListener('input', applyFilters);
    if (searchForm) {
        searchForm.addEventListener('submit', function (event) {
            event.preventDefault();
            applyFilters();
            document.querySelector('.ec-modules-section')?.scrollIntoView({
                behavior: 'smooth', block: 'start'
            });
        });
    }
    sort.addEventListener('change', sortCards);

    viewButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const isList = button.dataset.ecView === 'list';
            grid.classList.toggle('is-list-view', isList);
            viewButtons.forEach(function (item) {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', String(active));
            });
        });
    });
});
</script>
@endpush