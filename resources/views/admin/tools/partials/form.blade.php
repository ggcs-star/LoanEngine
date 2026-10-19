
@php
    $selectedJourneyIds = collect(
        old(
            'journey_ids',
            $tool->exists
                ? $tool->journeys->pluck('id')->all()
                : []
        )
    )->map(fn ($id) => (string) $id)->all();
@endphp

<div class="tools-form-grid">
    <div class="tools-field">
        <label for="name">Tool Name <span class="tools-required">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $tool->name) }}"
            maxlength="120"
            required
            autocomplete="off"
            placeholder="Enter tool name"
        >
        @error('name')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field">
        <label for="slug">Slug</label>
        <input
            id="slug"
            type="text"
            name="slug"
            value="{{ old('slug', $tool->slug) }}"
            maxlength="140"
            placeholder="e.g. home-loan-calculator"
        >
        <p class="tools-field-help">
            Leave empty to generate it from the tool name.
        </p>
        @error('slug')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field tools-field-full">
        <label for="short_description">
            Short Description <span class="tools-required">*</span>
        </label>
        <textarea
            id="short_description"
            name="short_description"
            rows="3"
            maxlength="200"
            required
            placeholder="Describe what this tool does"
        >{{ old('short_description', $tool->short_description) }}</textarea>
        @error('short_description')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field">
        <label for="category">Category <span class="tools-required">*</span></label>
        <input
            id="category"
            type="text"
            name="category"
            value="{{ old('category', $tool->category) }}"
            maxlength="40"
            required
            placeholder="Enter category"
        >
        @error('category')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field">
        <label for="type">Type <span class="tools-required">*</span></label>
        <input
            id="type"
            type="text"
            name="type"
            value="{{ old('type', $tool->type) }}"
            maxlength="30"
            required
            placeholder="Enter tool type"
        >
        @error('type')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field">
        <label for="icon">Icon Identifier <span class="tools-required">*</span></label>
        <input
            id="icon"
            type="text"
            name="icon"
            value="{{ old('icon', $tool->icon) }}"
            maxlength="60"
            required
            placeholder="Enter icon identifier"
        >
        <p class="tools-field-help">
            Enter the identifier supported by your frontend icon library.
        </p>
        @error('icon')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field">
        <label for="status">Status <span class="tools-required">*</span></label>
        <select id="status" name="status" required>
            @foreach ([
                'active' => 'Active',
                'planned' => 'Planned',
                'inactive' => 'Inactive',
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('status', $tool->status ?: 'active') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field">
        <label for="page_route">Page Route <span class="tools-required">*</span></label>
        <input
            id="page_route"
            type="text"
            name="page_route"
            value="{{ old('page_route', $tool->page_route) }}"
            maxlength="255"
            required
            placeholder="Enter route name or path"
        >
        <p class="tools-field-help">
            Use the format expected by your frontend.
        </p>
        @error('page_route')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field">
        <label for="display_order">Display Order</label>
        <input
            id="display_order"
            type="number"
            name="display_order"
            value="{{ old('display_order', $tool->display_order ?? 1) }}"
            min="1"
            max="4294967295"
        >
        @error('display_order')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field tools-field-full">
        <div class="tools-section-heading">
            <h3>Journey Associations</h3>
            <p>
                Select the journeys where this tool is relevant.
                Journey options are loaded from the database.
            </p>
        </div>

        @if ($journeys->isNotEmpty())
            <div class="tools-journey-list">
                @foreach ($journeys as $journey)
                    <label class="tools-journey-option" for="journey-{{ $journey->id }}">
                        <input
                            id="journey-{{ $journey->id }}"
                            type="checkbox"
                            name="journey_ids[]"
                            value="{{ $journey->id }}"
                            @checked(in_array((string) $journey->id, $selectedJourneyIds, true))
                        >

                        <span>
                            <strong>{{ $journey->name }}</strong>
                            @if ($journey->short_label)
                                <small>{{ $journey->short_label }}</small>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        @else
            <p class="tools-empty-note">
                No active journeys are available yet. You can create journeys
                in Journey Management.
            </p>
        @endif

        @error('journey_ids')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror

        @error('journey_ids.*')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field tools-field-full">
        <label class="tools-checkbox-option" for="show_on_toolkit_home">
            <input
                id="show_on_toolkit_home"
                type="checkbox"
                name="show_on_toolkit_home"
                value="1"
                @checked(old('show_on_toolkit_home', $tool->show_on_toolkit_home ?? true))
            >
            <span>
                <strong>Show on Toolkit Home</strong>
                <small>Display this tool on the public toolkit landing page.</small>
            </span>
        </label>
    </div>
</div>
