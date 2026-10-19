
@extends('layouts.admin')

@section('title', 'Create Education Category')

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
        <strong>Create Category</strong>
    </div>

    {{-- Page Header --}}
    <div class="welcome-row education-heading">
        <div>
            <span class="eyebrow">EDUCATION MANAGEMENT</span>
            <h1>Create Education Category</h1>
            <p>Add a new category to organize your learning content.</p>
        </div>

        <a
            class="education-button"
            href="{{ route('admin.education.categories.index') }}"
        >
            <span aria-hidden="true">←</span>
            Back to Categories
        </a>
    </div>

    {{-- Success / Error Messages --}}
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

    {{-- Category Form --}}
    <form
        method="POST"
        action="{{ route('admin.education.categories.store') }}"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="education-form-layout">

            {{-- Main Form Column --}}
            <div class="education-form-main">

                <section class="education-form-section">

                    <div class="education-section-heading">
                        <div class="education-section-icon" aria-hidden="true">
                            ▣
                        </div>

                        <div>
                            <h2>Basic Information</h2>
                            <p>
                                Enter the main details for this education category.
                            </p>
                        </div>
                    </div>

                    {{-- All fields, including Display Order and Visibility --}}
                    @include('admin.education.categories.form', [
                        'category' => null
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
                            <span aria-hidden="true">＋</span>
                            Save Category
                        </button>
                    </div>

                </section>

            </div>

            {{-- Sidebar Information --}}
            <aside class="education-form-aside">

                <section class="education-form-section">

                    <div class="education-section-heading">
                        <div class="education-section-icon" aria-hidden="true">
                            ♧
                        </div>

                        <div>
                            <h2>Tips for a Good Category</h2>
                            <p>Follow these guidelines for better organization.</p>
                        </div>
                    </div>

                    <ul class="education-tip-list">
                        <li>
                            <span class="education-tip-check">✓</span>
                            <span>Use a clear and concise category name.</span>
                        </li>

                        <li>
                            <span class="education-tip-check">✓</span>
                            <span>Add a relevant cover image.</span>
                        </li>

                        <li>
                            <span class="education-tip-check">✓</span>
                            <span>Write a short, informative description.</span>
                        </li>

                        <li>
                            <span class="education-tip-check">✓</span>
                            <span>Keep the slug simple and SEO-friendly.</span>
                        </li>

                        <li>
                            <span class="education-tip-check">✓</span>
                            <span>Choose the correct display order.</span>
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
                            <h2>Live Preview</h2>
                            <p>Preview the category information as you type.</p>
                        </div>
                    </div>

                    <div class="education-preview">

                        <div
                            id="education-preview-image"
                            class="education-thumbnail"
                        >
                            ▤
                        </div>

                        <h3 id="education-preview-name">
                            Category Name
                        </h3>

                        <p id="education-preview-description">
                            Your category description will appear here.
                        </p>

                        <span class="education-status education-status-active">
                            Active
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
                previewImage.replaceChildren();
                previewImage.textContent = '▤';
                return;
            }

            if (!file.type.startsWith('image/')) {
                return;
            }

            const image = document.createElement('img');
            image.alt = 'Selected category cover preview';
            image.src = URL.createObjectURL(file);
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
