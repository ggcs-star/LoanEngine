
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
            Short Description <span class="tools-field-help">(Optional)</span>
        </label>
        <textarea
            id="short_description"
            name="short_description"
            rows="3"
            maxlength="200"
            placeholder="Describe what this tool does"
        >{{ old('short_description', $tool->short_description) }}</textarea>
        @error('short_description')
            <p class="tools-field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="tools-field">
        <label for="category">Category <span class="tools-required">*</span></label>
        <select id="category" name="category" required>
            <option value="">Choose a category</option>
            @foreach (['Resources', 'Planning', 'Calculators'] as $category)
                <option value="{{ $category }}" @selected(old('category', $tool->category) === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <p class="tools-field-help">Choose where users will find this tool.</p>
        @error('category')<p class="tools-field-error">{{ $message }}</p>@enderror
    </div>

    <div class="tools-field">
        <label for="type">Tool Type <span class="tools-required">*</span></label>
        <select id="type" name="type" required>
            <option value="">Choose a type</option>
            @foreach (['Resource', 'Planning Tool', 'Calculator'] as $type)
                <option value="{{ $type }}" @selected(old('type', $tool->type) === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <p class="tools-field-help">Use Resource for Resources, Planning Tool for Planning, and Calculator for Calculators.</p>
        @error('type')<p class="tools-field-error">{{ $message }}</p>@enderror
    </div>

    <div class="tools-field">
        <label for="icon">Icon</label>
        <select id="icon" name="icon">
            @foreach (['house', 'home', 'calendar', 'book', 'chart', 'calculator'] as $icon)
                <option value="{{ $icon }}" @selected(old('icon', $tool->icon ?: 'book') === $icon)>{{ ucfirst($icon) }}</option>
            @endforeach
        </select>
        @error('icon')<p class="tools-field-error">{{ $message }}</p>@enderror
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
        <label>Page Link</label>
        <p class="tools-field-help">LoanEngine opens the correct page automatically based on the category. You do not need to enter a URL.</p>
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

@push('scripts')
<script>
(() => {
    const category = document.getElementById('category');
    const type = document.getElementById('type');
    if (!category || !type) return;
    const mapping = { Resources: 'Resource', Planning: 'Planning Tool', Calculators: 'Calculator' };
    category.addEventListener('change', () => {
        const expected = mapping[category.value];
        if (expected) type.value = expected;
    });
})();
</script>
@endpush
