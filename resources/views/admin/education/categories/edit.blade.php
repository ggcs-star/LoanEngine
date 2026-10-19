
@extends('layouts.admin')

@section('title', 'Edit Education Category')

@section('content')
<div class="dashboard-page education-page">

    {{-- Breadcrumb --}}
    <div class="education-breadcrumb">
        <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard">⌂</a>
        <span>›</span>
        <a href="{{ route('admin.education.categories.index') }}">
            Education Categories
        </a>
        <span>›</span>
        <strong>Edit Category</strong>
    </div>

    {{-- Page Header --}}
    <div class="welcome-row education-heading">
        <div>
            <span class="eyebrow">EDUCATION MANAGEMENT</span>
            <h1>Edit Education Category</h1>
            <p>
                Update category information, cover image and visibility.
            </p>
        </div>

        <a
            class="education-button"
            href="{{ route('admin.education.categories.index') }}"
        >
            <span aria-hidden="true">←</span>
            Back to Categories
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="education-flash" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="education-alert" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="education-alert" role="alert">
            <strong>Please correct the following errors:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Edit Form --}}
    <form
        method="POST"
        action="{{ route('admin.education.categories.update', $category) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        <div class="education-form-layout">

            {{-- Main Form --}}
            <div class="education-form-main">

                <section class="education-form-section">

                    <div class="education-section-heading">
                        <div class="education-section-icon" aria-hidden="true">
                            ✎
                        </div>

                        <div>
                            <h2>Basic Information</h2>
                            <p>
                                Update the details of this education category.
                            </p>
                        </div>
                    </div>

                    {{-- Shared fields include name, slug, description,
                         cover image, display order and visibility --}}
                    @include('admin.education.categories.form', [
                        'category' => $category
                    ])

                    {{-- Form Actions --}}
                    <div class="education-form-footer">
                        <a
                            class="education-button"
                            href="{{ route('admin.education.categories.index') }}"
                        >
                            Cancel
                        </a>

                        <button
                            class="education-button education-button-primary"
                            type="submit"
                        >
                            Save Changes
                        </button>
                    </div>

                </section>

            </div>

            {{-- Right Sidebar --}}
            <aside class="education-form-aside">

                <section class="education-form-section">

                    <div class="education-section-heading">
                        <div class="education-section-icon" aria-hidden="true">
                            ✓
                        </div>

                        <div>
                            <h2>Before You Save</h2>
                            <p>Review these category details.</p>
                        </div>
                    </div>

                    <ul class="education-tip-list">
                        <li>
                            <span class="education-tip-check">✓</span>
                            <span>Keep the category name clear and descriptive.</span>
                        </li>

                        <li>
                            <span class="education-tip-check">✓</span>
                            <span>Use a relevant cover image.</span>
                        </li>

                        <li>
                            <span class="education-tip-check">✓</span>
                            <span>Keep the slug consistent with the category.</span>
                        </li>

                        <li>
                            <span class="education-tip-check">✓</span>
                            <span>Verify display order and visibility.</span>
                        </li>
                    </ul>

                </section>

                {{-- Live Preview --}}
                <section class="education-form-section">

                    <div class="education-section-heading">
                        <div class="education-section-icon" aria-hidden="true">
                            ◉
                        </div>

                        <div>
                            <h2>Category Preview</h2>
                            <p>Preview your changes before saving.</p>
                        </div>
                    </div>

                    <div class="education-preview">
                    <div
                            id="education-preview-image"
                            class="education-thumbnail"
                        >
                            @if($category->thumbnail_path)
                                <img
                                    src="{{ asset('storage/' . $category->thumbnail_path) }}"
                                    alt="{{ $category->name }}"
                                    class="education-preview-image"
                                >
                            @else
                                <span class="education-preview-placeholder">▤</span>
                            @endif
                        </div>


                        <h3 id="education-preview-name">
                            {{ $category->name }}
                        </h3>

                        <p id="education-preview-description">
                            {{ $category->description ?: 'Your category description will appear here.' }}
                        </p>

                        <span class="education-status {{ $category->is_active ? 'education-status-active' : 'education-status-inactive' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>

                    </div>

                </section>

            </aside>

        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('category-name');
    const descriptionInput = document.getElementById('category-description');
    const activeInput = document.getElementById('category-active');
    const imageInput = document.getElementById('category-thumbnail');

    const previewName = document.getElementById('education-preview-name');
    const previewDescription = document.getElementById('education-preview-description');
    const previewImage = document.getElementById('education-preview-image');

    let previewObjectUrl = null;

    function updatePreview() {
        if (nameInput && previewName) {
            previewName.textContent =
                nameInput.value.trim() || 'Category Name';
        }

        if (descriptionInput && previewDescription) {
            previewDescription.textContent =
                descriptionInput.value.trim() ||
                'Your category description will appear here.';
        }

        const previewStatus = document.querySelector(
            '.education-preview .education-status'
        );

        if (activeInput && previewStatus) {
            previewStatus.textContent = activeInput.checked
                ? 'Active'
                : 'Inactive';

            previewStatus.classList.toggle(
                'education-status-active',
                activeInput.checked
            );

            previewStatus.classList.toggle(
                'education-status-inactive',
                !activeInput.checked
            );
        }
    }

    if (nameInput) {
        nameInput.addEventListener('input', updatePreview);
    }

    if (descriptionInput) {
        descriptionInput.addEventListener('input', updatePreview);
    }

    if (activeInput) {
        activeInput.addEventListener('change', updatePreview);
    }

    if (imageInput && previewImage) {
        imageInput.addEventListener('change', function () {
            const file = imageInput.files && imageInput.files[0];

            if (!file) {
                return;
            }

            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                return;
            }

            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
            }

            previewObjectUrl = URL.createObjectURL(file);

            const image = document.createElement('img');
            image.src = previewObjectUrl;
            image.alt = 'New category cover preview';
            image.style.width = '100%';
            image.style.height = '100%';
            image.style.objectFit = 'cover';

            previewImage.replaceChildren(image);
        });
    }

    updatePreview();
});
</script>
@endsection
