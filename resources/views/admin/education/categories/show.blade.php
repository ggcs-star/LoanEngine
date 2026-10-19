
@extends('layouts.admin')

@section('title', 'View Education Category')

@section('content')
<div class="dashboard-page education-page education-category-show-page">

    {{-- Page heading --}}
    <header class="education-heading">
        <div class="education-heading-copy">
            <span class="education-eyebrow">EDUCATION MANAGEMENT</span>
            <h1>Category Details</h1>
            <p>View category information and its associated learning modules.</p>
        </div>

        <div class="education-show-header-actions">
            <a href="{{ route('admin.education.categories.index') }}"
               class="education-button education-button-reset">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M19 12H5m7 7-7-7 7-7"/>
                </svg>
                Back to Categories
            </a>

            <a href="{{ route('admin.education.categories.edit', $category) }}"
               class="education-button education-button-primary">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="m14 5 5 5M4 20l4.5-1 10.8-10.8a2.1 2.1 0 0 0-3-3L5.5 16 4 20Z"/>
                </svg>
                Edit Category
            </a>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="education-alert education-alert-success" role="status">
            <span class="education-alert-symbol">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Category overview --}}
    <section class="education-show-hero">
        <div class="education-show-hero-main">

            <div class="education-show-image">
                @if($category->thumbnail_path)
                    <button type="button"
                            class="education-show-image-button js-image-popup"
                            data-image="{{ asset('storage/' . $category->thumbnail_path) }}"
                            data-title="{{ $category->name }}"
                            aria-label="Preview category image">
                        <img src="{{ asset('storage/' . $category->thumbnail_path) }}"
                             alt="{{ $category->name }}">
                        <span class="education-show-image-overlay">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="10.8" cy="10.8" r="6.8"/>
                                <path d="m16 16 4.2 4.2"/>
                            </svg>
                            Preview
                        </span>
                    </button>
                @else
                    <div class="education-show-no-image">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="4" y="4" width="16" height="16" rx="2"/>
                            <path d="m4 16 5-5 4 4 3-3 4 4"/>
                            <circle cx="9" cy="9" r="1.5"/>
                        </svg>
                        <span>No image</span>
                    </div>
                @endif
            </div>

            <div class="education-show-hero-copy">
                <div class="education-show-status-row">
                    <span class="education-status {{ $category->is_active ? 'education-status-active' : 'education-status-inactive' }}">
                        <span class="education-status-dot"></span>
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>

                    <span class="education-show-id">
                        ID: {{ $category->getKey() }}
                    </span>
                </div>

                <h2>{{ $category->name }}</h2>

                <p>
                    {{ $category->description ?: 'No description has been added for this category.' }}
                </p>

                <div class="education-show-slug">
                    <span>Slug</span>
                    <code>{{ $category->slug }}</code>
                </div>
            </div>
        </div>

        <div class="education-show-hero-footer">
            <span>Education Category</span>
            <span class="education-show-order">
                Display Order <strong>{{ $category->sort_order }}</strong>
            </span>
        </div>
    </section>

    {{-- Summary cards --}}
    <section class="education-show-stats">
        <article class="education-show-stat">
            <div class="education-show-stat-icon">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <rect x="4" y="4" width="7" height="7" rx="1.5"/>
                    <rect x="13" y="4" width="7" height="7" rx="1.5"/>
                    <rect x="4" y="13" width="7" height="7" rx="1.5"/>
                    <rect x="13" y="13" width="7" height="7" rx="1.5"/>
                </svg>
            </div>
            <div>
                <span>Total Modules</span>
                <strong>{{ $category->modules_count ?? $category->modules->count() }}</strong>
                <small>Modules linked to this category</small>
            </div>
        </article>

        <article class="education-show-stat">
            <div class="education-show-stat-icon education-show-stat-icon-blue">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21V5.5Z"/>
                    <path d="M4 5.5V21m5-13h7m-7 4h7"/>
                </svg>
            </div>
            <div>
                <span>Category Status</span>
                <strong>{{ $category->is_active ? 'Published' : 'Inactive' }}</strong>
                <small>
                    {{ $category->is_active
                        ? 'Available according to its active status'
                        : 'Currently marked as inactive' }}
                </small>
            </div>
        </article>

        <article class="education-show-stat">
            <div class="education-show-stat-icon education-show-stat-icon-grey">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3v18m-6-6 6 6 6-6M6 7l6-4 6 4"/>
                </svg>
            </div>
            <div>
                <span>Display Order</span>
                <strong>{{ $category->sort_order }}</strong>
                <small>Category listing position</small>
            </div>
        </article>
    </section>

    {{-- Category information --}}
    <section class="education-card education-show-details-card">
        <div class="education-show-section-header">
            <div>
                <h2>Category Information</h2>
                <p>Basic information and configuration for this category.</p>
            </div>

            <span class="education-show-section-icon">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 11v5m0-8h.01"/>
                </svg>
            </span>
        </div>

        <div class="education-show-information-grid">
            <div class="education-show-information-item">
                <span>Category Name</span>
                <strong>{{ $category->name }}</strong>
            </div>

            <div class="education-show-information-item">
                <span>URL Slug</span>
                <code>{{ $category->slug }}</code>
            </div>

            <div class="education-show-information-item">
                <span>Visibility</span>
                <strong>{{ $category->is_active ? 'Active' : 'Inactive' }}</strong>
            </div>

            <div class="education-show-information-item">
                <span>Display Order</span>
                <strong>{{ $category->sort_order }}</strong>
            </div>

            <div class="education-show-information-item education-show-information-full">
                <span>Description</span>
                <p>{{ $category->description ?: 'No description available.' }}</p>
            </div>
        </div>
    </section>

    {{-- Linked modules --}}
    <section class="education-card education-show-modules-card">
        <div class="education-show-section-header">
            <div>
                <h2>Linked Modules</h2>
                <p>Education modules associated with this category.</p>
            </div>

            <span class="education-result-label">
                {{ $category->modules->count() }}
                {{ $category->modules->count() === 1 ? 'Module' : 'Modules' }}
            </span>
        </div>

        @if($category->modules->isNotEmpty())
            <div class="education-table-wrap">
                <table class="education-table education-show-modules-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Module</th>
                            <th>Slug</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($category->modules as $module)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <strong class="education-show-module-name">
                                        {{ $module->name }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="education-slug">
                                        {{ $module->slug }}
                                    </span>
                                </td>

                                <td>
                                    <span class="education-status {{ $module->is_active ? 'education-status-active' : 'education-status-inactive' }}">
                                        <span class="education-status-dot"></span>
                                        {{ $module->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="education-empty education-show-modules-empty">
                <div class="education-empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="4" y="4" width="7" height="7" rx="1.5"/>
                        <rect x="13" y="4" width="7" height="7" rx="1.5"/>
                        <rect x="4" y="13" width="7" height="7" rx="1.5"/>
                        <rect x="13" y="13" width="7" height="7" rx="1.5"/>
                    </svg>
                </div>

                <h3>No modules linked yet</h3>
                <p>Modules associated with this category will appear here.</p>
            </div>
        @endif
    </section>

</div>

{{-- Image preview modal --}}
<div class="education-image-modal"
     id="educationImageModal"
     aria-hidden="true"
     inert>
    <button type="button"
            class="education-image-modal-backdrop"
            data-close-image
            aria-label="Close image preview"></button>

    <div class="education-image-modal-content"
         role="dialog"
         aria-modal="true"
         aria-labelledby="educationImageModalTitle">

        <button type="button"
                class="education-image-modal-close"
                data-close-image
                aria-label="Close image preview">&times;</button>

        <h3 id="educationImageModalTitle">Category Image</h3>
        <img id="educationImageModalPreview" src="" alt="">
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('educationImageModal');
    if (!modal || modal.dataset.initialized === 'true') return;
    modal.dataset.initialized = 'true';

    const image = document.getElementById('educationImageModalPreview');
    const title = document.getElementById('educationImageModalTitle');
    let lastFocused = null;

    function closeModal() {
        if (!modal.classList.contains('is-open')) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.inert = true;
        document.body.classList.remove('education-modal-open');
        image.removeAttribute('src');
        lastFocused?.focus();
    }

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('.education-show-image-button');

        if (trigger) {
            lastFocused = trigger;
            image.src = trigger.dataset.image;
            image.alt = trigger.dataset.title || 'Category image';
            title.textContent = trigger.dataset.title || 'Category Image';

            modal.inert = false;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('education-modal-open');
            modal.querySelector('.education-image-modal-close').focus();
            return;
        }

        if (event.target.closest('[data-close-image]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });
})();
</script>
@endsection
