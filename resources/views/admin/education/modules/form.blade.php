
@php
    $editing = isset($module) && $module !== null;

    $currentImage = $editing && $module->thumbnail_path
        ? asset('storage/' . ltrim($module->thumbnail_path, '/'))
        : null;
@endphp

<div class="edu-modules-form-grid">

    {{-- Main Form --}}
    <div class="edu-modules-form-main">

        {{-- Basic Information --}}
        <section class="edu-modules-card edu-modules-form-card">
            <div class="edu-modules-card-heading">
                <div>
                    <h2>Basic Information</h2>
                    <p>Enter the main details for this learning module.</p>
                </div>
                <span class="edu-modules-section-number">01</span>
            </div>

            <div class="edu-modules-fields">

                <div class="edu-modules-field">
                    <label for="title">Module Title <span>*</span></label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $module->title ?? '') }}"
                        placeholder="e.g. Introduction to Finance"
                        required
                        maxlength="255"
                    >
                    @error('title')
                        <small class="edu-modules-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="edu-modules-field">
                    <label for="slug">Slug</label>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $module->slug ?? '') }}"
                        placeholder="introduction-to-finance"
                        maxlength="255"
                    >
                    <small class="edu-modules-field-help">
                        URL-friendly identifier for this module.
                    </small>
                    @error('slug')
                        <small class="edu-modules-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="edu-modules-field">
                    <label for="education_category_id">
                        Education Category <span>*</span>
                    </label>

                    <select
                        id="education_category_id"
                        name="education_category_id"
                        required
                    >
                        <option value="">Select a category</option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(
                                    (string) old(
                                        'education_category_id',
                                        $module->education_category_id ?? ''
                                    ) === (string) $category->id
                                )
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('education_category_id')
                        <small class="edu-modules-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="edu-modules-field">
                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Describe what students will learn in this module..."
                    >{{ old('description', $module->description ?? '') }}</textarea>

                    @error('description')
                        <small class="edu-modules-field-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>
        </section>

        {{-- Cover Image --}}
        <section class="edu-modules-card edu-modules-form-card">
            <div class="edu-modules-card-heading">
                <div>
                    <h2>Module Cover Image</h2>
                    <p>Upload a clear image to represent this module.</p>
                </div>
                <span class="edu-modules-section-number">02</span>
            </div>

            <div class="edu-modules-upload-area">
                <input
                    type="file"
                    id="thumbnail"
                    name="thumbnail"
                    accept="image/*"
                    class="edu-modules-file-input"
                >

                <label for="thumbnail" class="edu-modules-upload-label">
                    <span class="edu-modules-upload-icon">↑</span>
                    <strong>Click to upload an image</strong>
                    <span>PNG, JPG, JPEG or WEBP</span>
                </label>
            </div>

            @error('thumbnail')
                <small class="edu-modules-field-error">{{ $message }}</small>
            @enderror

            <div class="edu-modules-image-preview-box"
                 id="eduModuleFormPreviewBox"
                 @if (!$currentImage) hidden @endif>

                <div class="edu-modules-preview-heading">
                    <strong>Image Preview</strong>
                    <button type="button"
                            id="eduModuleRemovePreview"
                            class="edu-modules-remove-preview">
                        Remove preview
                    </button>
                </div>

                <img
                    id="eduModuleFormPreview"
                    src="{{ $currentImage ?? '' }}"
                    alt="Module cover preview"
                >

                <p id="eduModuleImageFileName">
                    {{ $currentImage ? 'Current module cover' : '' }}
                </p>
            </div>

            @if ($editing && $currentImage)
                <small class="edu-modules-field-help">
                    Leave the upload empty to keep the current image.
                </small>
            @endif
        </section>

    </div>

    {{-- Sidebar --}}
    <aside class="edu-modules-form-sidebar">

        {{-- Publishing --}}
        <section class="edu-modules-card edu-modules-form-card">
            <div class="edu-modules-card-heading">
                <div>
                    <h2>Publishing</h2>
                    <p>Control module visibility.</p>
                </div>
            </div>

            <div class="edu-modules-field">
                <label for="status">Module Status <span>*</span></label>

                <select id="status" name="status" required>
                    <option value="draft"
                        @selected(old('status', $module->status ?? 'draft') === 'draft')>
                        Draft
                    </option>

                    <option value="published"
                        @selected(old('status', $module->status ?? 'draft') === 'published')>
                        Published
                    </option>
                </select>

                @error('status')
                    <small class="edu-modules-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="edu-modules-field">
                <label for="sort_order">Display Order</label>

                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    value="{{ old('sort_order', $module->sort_order ?? 0) }}"
                    min="0"
                    step="1"
                >

                <small class="edu-modules-field-help">
                    Lower numbers appear first when ordering by this field.
                </small>

                @error('sort_order')
                    <small class="edu-modules-field-error">{{ $message }}</small>
                @enderror
            </div>
        </section>

        {{-- Live Card Preview --}}
        <section class="edu-modules-card edu-modules-live-card">
            <div class="edu-modules-card-heading">
                <div>
                    <h2>Live Preview</h2>
                    <p>Preview the module card.</p>
                </div>
            </div>

            <div class="edu-modules-live-image">
                <img
                    src="{{ $currentImage ?? '' }}"
                    alt="Module preview"
                    id="eduModuleLiveImage"
                    @if (!$currentImage) hidden @endif
                >

                <div id="eduModuleLiveImagePlaceholder"
                     @if ($currentImage) hidden @endif>
                    <span>▧</span>
                    <p>Cover image will appear here</p>
                </div>
            </div>

            <div class="edu-modules-live-content">
                <span class="edu-modules-live-category"
                      id="eduModuleLiveCategory">
                    Select a category
                </span>

                <h3 id="eduModuleLiveTitle">
                    {{ old('title', $module->title ?? 'Your module title') }}
                </h3>

                <p id="eduModuleLiveDescription">
                    {{ old('description', $module->description ?? 'Your module description will appear here.') }}
                </p>

                <span class="edu-modules-status edu-modules-status-{{ old('status', $module->status ?? 'draft') }}"
                      id="eduModuleLiveStatus">
                    <span></span>
                    {{ ucfirst(old('status', $module->status ?? 'draft')) }}
                </span>
            </div>
        </section>

    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const descriptionInput = document.getElementById('description');
    const categoryInput = document.getElementById('education_category_id');
    const statusInput = document.getElementById('status');
    const fileInput = document.getElementById('thumbnail');

    const previewBox = document.getElementById('eduModuleFormPreviewBox');
    const previewImage = document.getElementById('eduModuleFormPreview');
    const fileName = document.getElementById('eduModuleImageFileName');
    const removePreview = document.getElementById('eduModuleRemovePreview');

    const liveImage = document.getElementById('eduModuleLiveImage');
    const livePlaceholder = document.getElementById('eduModuleLiveImagePlaceholder');
    const liveTitle = document.getElementById('eduModuleLiveTitle');
    const liveDescription = document.getElementById('eduModuleLiveDescription');
    const liveCategory = document.getElementById('eduModuleLiveCategory');
    const liveStatus = document.getElementById('eduModuleLiveStatus');

    let uploadedImageUrl = null;

    // Generate a slug only while creating a module or when slug is empty.
    let slugManuallyEdited = slugInput.value.trim() !== '';

    slugInput.addEventListener('input', function () {
        slugManuallyEdited = this.value.trim() !== '';
    });

    titleInput.addEventListener('input', function () {
        liveTitle.textContent = this.value.trim() || 'Your module title';

        if (!slugManuallyEdited) {
            slugInput.value = this.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }
    });

    descriptionInput.addEventListener('input', function () {
        liveDescription.textContent =
            this.value.trim() || 'Your module description will appear here.';
    });

    categoryInput.addEventListener('change', function () {
        liveCategory.textContent =
            this.options[this.selectedIndex]?.text || 'Select a category';
    });

    statusInput.addEventListener('change', function () {
        const status = this.value;

        liveStatus.className =
            'edu-modules-status edu-modules-status-' + status;

        liveStatus.innerHTML = '<span></span>';

        liveStatus.appendChild(document.createTextNode(
            status.charAt(0).toUpperCase() + status.slice(1)
        ));
    });

    fileInput.addEventListener('change', function () {
        const file = this.files && this.files[0];

        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file.');
            this.value = '';
            return;
        }

        if (uploadedImageUrl) {
            URL.revokeObjectURL(uploadedImageUrl);
        }

        uploadedImageUrl = URL.createObjectURL(file);

        previewImage.src = uploadedImageUrl;
        previewBox.hidden = false;
        fileName.textContent = file.name;

        liveImage.src = uploadedImageUrl;
        liveImage.hidden = false;
        livePlaceholder.hidden = true;
    });

    removePreview.addEventListener('click', function () {
        // This removes the local preview only.
        // Existing server-side image is not deleted by this button.
        fileInput.value = '';

        if (uploadedImageUrl) {
            URL.revokeObjectURL(uploadedImageUrl);
            uploadedImageUrl = null;
        }

        previewBox.hidden = true;
        previewImage.src = '';
        fileName.textContent = '';

        liveImage.src = '';
        liveImage.hidden = true;
        livePlaceholder.hidden = false;
    });

    // Initialize preview from existing/old form values.
    liveTitle.textContent = titleInput.value.trim() || 'Your module title';

    liveDescription.textContent =
        descriptionInput.value.trim() || 'Your module description will appear here.';

    liveCategory.textContent =
        categoryInput.options[categoryInput.selectedIndex]?.text || 'Select a category';

    liveStatus.className =
        'edu-modules-status edu-modules-status-' + statusInput.value;

    liveStatus.innerHTML = '<span></span>';

    liveStatus.appendChild(document.createTextNode(
        statusInput.value.charAt(0).toUpperCase() + statusInput.value.slice(1)
    ));
});
</script>
