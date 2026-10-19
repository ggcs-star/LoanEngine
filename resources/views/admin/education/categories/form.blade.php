
<div class="education-fields">

    <div class="education-field">
        <label for="category-name">
            Category Name <span>*</span>
        </label>

        <input
            class="form-control"
            id="category-name"
            type="text"
            name="name"
            required
            maxlength="150"
            value="{{ old('name', $category?->name) }}"
            placeholder="e.g. Home Loans"
        >

        <small>Enter a clear name for this education topic.</small>
    </div>

    <div class="education-field">
        <label for="category-slug">Slug</label>

        <input
            class="form-control"
            id="category-slug"
            type="text"
            name="slug"
            maxlength="180"
            value="{{ old('slug', $category?->slug) }}"
            placeholder="home-loans"
        >

        <small>Leave blank to generate it automatically from the name.</small>
    </div>

    <div class="education-field education-field-full">
        <label for="category-description">Description</label>

        <textarea
            class="form-control"
            id="category-description"
            name="description"
            rows="4"
            maxlength="5000"
            placeholder="Describe what users will learn in this category..."
        >{{ old('description', $category?->description) }}</textarea>

        <small>Provide a short overview of the learning topics.</small>
    </div>

    <div class="education-field education-field-full">
        <label for="category-thumbnail">Cover Image</label>

        <div class="education-upload">
            <input
                id="category-thumbnail"
                type="file"
                name="thumbnail"
                accept=".jpg,.jpeg,.png,.webp"
            >

            <small>JPG, PNG or WebP. Maximum file size: 3 MB.</small>
        </div>

        
        @if($category?->thumbnail_path)
            <div class="education-current-image">
                <img
                    src="{{ asset('storage/' . $category->thumbnail_path) }}"
                    alt="{{ $category->name }}"
                    class="education-current-image-preview"
                >

                <span>
                    Current cover image. Choose a new image only if you want to replace it.
                </span>
            </div>
        @endif

    </div>

    <div class="education-field">
        <label for="category-order">Display Order</label>

        <input
            class="form-control"
            id="category-order"
            type="number"
            min="0"
            max="100000"
            name="sort_order"
            value="{{ old('sort_order', $category?->sort_order ?? 0) }}"
        >

        <small>Categories with lower numbers can appear first.</small>
    </div>

    <div class="education-field education-status-field">
        <label>Visibility</label>

        <label class="education-switch-row" for="category-active">
            <input type="hidden" name="is_active" value="0">

            <input
                id="category-active"
                type="checkbox"
                name="is_active"
                value="1"
                @checked((bool) old('is_active', $category?->is_active ?? true))
            >

            <span class="education-switch-copy">
                <strong>Active category</strong>
                <small>Allow this category to be visible to users.</small>
            </span>
        </label>
    </div>

</div>
