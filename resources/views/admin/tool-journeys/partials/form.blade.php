
<div class="journey-form-grid">
    <div class="journey-field">
        <label for="name">Journey Name <span>*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $journey->name) }}"
            maxlength="120"
            required
            placeholder="Enter journey name"
        >
        @error('name')
            <p class="journey-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="journey-field">
        <label for="slug">Slug</label>
        <input
            id="slug"
            type="text"
            name="slug"
            value="{{ old('slug', $journey->slug) }}"
            maxlength="140"
            placeholder="Auto-generated if empty"
        >
        <small>Use lowercase letters, numbers and hyphens.</small>
        @error('slug')
            <p class="journey-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="journey-field">
        <label for="short_label">Short Label</label>
        <input
            id="short_label"
            type="text"
            name="short_label"
            value="{{ old('short_label', $journey->short_label) }}"
            maxlength="80"
            placeholder="Optional short label"
        >
        @error('short_label')
            <p class="journey-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="journey-field">
        <label for="display_order">Display Order <span>*</span></label>
        <input
            id="display_order"
            type="number"
            name="display_order"
            value="{{ old('display_order', $journey->display_order ?? 1) }}"
            min="1"
            max="4294967295"
            required
        >
        @error('display_order')
            <p class="journey-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="journey-field journey-field-full">
        <label for="description">Description</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            maxlength="10000"
            placeholder="Describe the purpose of this journey"
        >{{ old('description', $journey->description) }}</textarea>
        @error('description')
            <p class="journey-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="journey-field">
        <label for="status">Status <span>*</span></label>
        <select id="status" name="status" required>
            <option
                value="active"
                @selected(old('status', $journey->status ?: 'active') === 'active')
            >
                Active
            </option>
            <option
                value="inactive"
                @selected(old('status', $journey->status ?: 'active') === 'inactive')
            >
                Inactive
            </option>
        </select>
        @error('status')
            <p class="journey-error">{{ $message }}</p>
        @enderror
    </div>
</div>
