@php
    $selectedFormula = old('formula_type', $calculator->formula_type ?? '');
    $formulaDefinitions = $formulaDefinitions ?? [];
    $formulaTypes = $formulaTypes ?? [];
    $journeyIds = collect(old('journey_ids', $calculator->exists ? $calculator->journeys->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

<div class="calculator-editor">
    <section class="calculator-card calculator-editor-card">
        <div class="calculator-editor-heading">
            <div>
                <h2>Basic Information</h2>
                <p>Set the calculator name, URL, visibility and display order.</p>
            </div>
        </div>
        <div class="calculator-editor-grid">
            <div class="calculator-editor-field">
                <label for="name">Calculator Name <span class="required">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', $calculator->name) }}" maxlength="120" autocomplete="off" required>
                @error('name')<p class="calculator-editor-error">{{ $message }}</p>@enderror
            </div>
            <div class="calculator-editor-field">
                <label for="slug">URL Slug <span class="required">*</span></label>
                <input id="slug" type="text" name="slug" value="{{ old('slug', $calculator->slug) }}" maxlength="140" pattern="[A-Za-z0-9_-]+" autocomplete="off" required>
                <p class="calculator-editor-help">Used in the URL. Formula selection is controlled separately.</p>
                @error('slug')<p class="calculator-editor-error">{{ $message }}</p>@enderror
            </div>
            <div class="calculator-editor-field is-full">
                <label for="short_description">Short Description</label>
<textarea
    id="short_description"
    name="short_description"
    rows="3"
    maxlength="200"
    placeholder="Enter a short description (optional)"
>{{ old('short_description', $calculator->short_description) }}</textarea>
                @error('short_description')<p class="calculator-editor-error">{{ $message }}</p>@enderror
            </div>
            <div class="calculator-editor-field">
                <label for="icon">Icon</label>
                <select id="icon" name="icon">
                    @foreach (['calculator' => 'Calculator', 'home' => 'Home', 'chart' => 'Chart', 'wallet' => 'Wallet', 'percent' => 'Percentage'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('icon', $calculator->icon ?: 'calculator') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('icon')<p class="calculator-editor-error">{{ $message }}</p>@enderror
            </div>
            <div class="calculator-editor-field">
                <label for="display_order">Display Order <span class="required">*</span></label>
                <input id="display_order" type="number" name="display_order" value="{{ old('display_order', $calculator->display_order ?? 1) }}" min="0" max="4294967295" required>
                @error('display_order')<p class="calculator-editor-error">{{ $message }}</p>@enderror
            </div>
            <div class="calculator-editor-field">
                <label for="status">Status <span class="required">*</span></label>
                <select id="status" name="status" required>
                    <option value="active" @selected(old('status', $calculator->status ?: 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $calculator->status ?: 'active') === 'inactive')>Inactive</option>
                </select>
                @error('status')<p class="calculator-editor-error">{{ $message }}</p>@enderror
            </div>
            <div class="calculator-editor-field">
                <label for="route_name">Named Route</label>
                <input id="route_name" type="text" name="route_name" value="{{ old('route_name', $calculator->route_name) }}" maxlength="180" placeholder="Optional">
                <p class="calculator-editor-help">Only fill this when an existing integration requires it.</p>
                @error('route_name')<p class="calculator-editor-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="calculator-card calculator-editor-card">
        <div class="calculator-editor-heading">
            <div>
                <h2>Formula Configuration</h2>
                <p>Choose a supported application formula to review its predefined inputs and outputs.</p>
            </div>
        </div>
        <div class="calculator-editor-field">
            <label for="formula_type">Formula Type <span class="required">*</span></label>
            <select id="formula_type" name="formula_type" required>
                <option value="">Select a formula</option>
                @foreach ($formulaTypes as $key => $label)
                    <option value="{{ $key }}" @selected($selectedFormula === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="calculator-editor-help">Formula code is managed by the application and cannot be entered from this form.</p>
            @error('formula_type')<p class="calculator-editor-error">{{ $message }}</p>@enderror
        </div>
        <div id="formula-description" class="calculator-formula-summary" hidden>
            <span class="calculator-formula-mark">ƒx</span>
            <div>
                <strong id="formula-title"></strong>
                <p id="formula-help">Predefined application formula</p>
            </div>
        </div>
        <div class="calculator-config-grid">
            <div class="calculator-config-column">
                <h3>Input Fields</h3>
                <p>Inputs expected by the selected formula.</p>
                <div id="formula-inputs" class="calculator-config-list">
                    <div class="calculator-config-empty">Select a formula to review its input fields.</div>
                </div>
            </div>
            <div class="calculator-config-column">
                <h3>Result Fields</h3>
                <p>Outputs defined for the selected formula.</p>
                <div id="formula-results" class="calculator-config-list">
                    <div class="calculator-config-empty">Select a formula to review its result fields.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="calculator-card calculator-editor-card">
        <div class="calculator-editor-heading">
            <div>
                <h2>Journey Associations</h2>
                <p>Select the journeys where this calculator should be available.</p>
            </div>
            <span class="journey-selection-count" id="journey-selection-count">0 selected</span>
        </div>
        @if ($journeys->isNotEmpty())
            <div class="calculator-journey-grid">
                @foreach ($journeys as $journey)
                    <label class="calculator-journey-choice {{ in_array((string) $journey->id, $journeyIds, true) ? 'is-selected' : '' }}" for="journey-{{ $journey->id }}">
                        <input id="journey-{{ $journey->id }}" type="checkbox" name="journey_ids[]" value="{{ $journey->id }}" @checked(in_array((string) $journey->id, $journeyIds, true))>
                        <span class="calculator-journey-choice-content">
                            <strong>{{ $journey->name }}</strong>
                            @if ($journey->short_label)
                                <small>{{ $journey->short_label }}</small>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        @else
            <div class="journey-empty-state">
                <strong>No active journeys available</strong>
                <p>Create or activate a journey before associating it with this calculator.</p>
            </div>
        @endif
        @error('journey_ids')<p class="calculator-editor-error">{{ $message }}</p>@enderror
        @error('journey_ids.*')<p class="calculator-editor-error">{{ $message }}</p>@enderror
    </section>

    <div class="calculator-editor-footer">
        <a href="{{ route('admin.calculators.index') }}" class="btn-secondary-light">Cancel</a>
        <button type="submit" class="btn-primary-dark">{{ $calculator->exists ? 'Save Changes' : 'Create Calculator' }}</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formulaSelect = document.getElementById('formula_type');
    const definitions = @json($formulaDefinitions);
    const description = document.getElementById('formula-description');
    const title = document.getElementById('formula-title');
    const inputsContainer = document.getElementById('formula-inputs');
    const resultsContainer = document.getElementById('formula-results');
    const journeyCheckboxes = Array.from(document.querySelectorAll('.calculator-journey-choice input[type="checkbox"]'));
    const journeyCount = document.getElementById('journey-selection-count');

    function addText(parent, tag, value, className) {
        const node = document.createElement(tag);
        node.textContent = String(value ?? '');
        if (className) node.className = className;
        parent.appendChild(node);
        return node;
    }

    function addEmpty(parent, message) {
        addText(parent, 'div', message, 'calculator-config-empty');
    }

    function renderEntries(container, entries, kind) {
        container.replaceChildren();
        const values = Object.entries(entries || {});
        if (!values.length) {
            addEmpty(container, kind === 'input'
                ? 'No input metadata is registered for this formula yet.'
                : 'No result metadata is registered for this formula yet.');
            return;
        }
        values.forEach(([key, value]) => {
            const item = document.createElement('div');
            item.className = 'calculator-config-item';
            const label = typeof value === 'object' && value !== null ? (value.label || value.name || key) : value;
            addText(item, 'strong', label);
            addText(item, 'small', 'Key: ' + key);
            if (kind === 'input' && typeof value === 'object' && value !== null) {
                const details = [];
                if (value.type) details.push('Type: ' + value.type);
                if (value.default !== undefined && value.default !== null) details.push('Default: ' + value.default);
                if (value.min !== undefined && value.min !== null) details.push('Min: ' + value.min);
                if (value.max !== undefined && value.max !== null) details.push('Max: ' + value.max);
                if (details.length) addText(item, 'small', details.join(' · '));
            }
            container.appendChild(item);
        });
    }

    function renderFormula() {
        const formula = definitions[formulaSelect.value];
        inputsContainer.replaceChildren();
        resultsContainer.replaceChildren();
        if (!formula) {
            description.hidden = true;
            addEmpty(inputsContainer, 'Select a formula to review its input fields.');
            addEmpty(resultsContainer, 'Select a formula to review its result fields.');
            return;
        }
        description.hidden = false;
        title.textContent = formula.label || formula.name || formulaSelect.value;
        renderEntries(inputsContainer, formula.inputs, 'input');
        renderEntries(resultsContainer, formula.results, 'result');
    }

    function updateJourneySelection() {
        const selected = journeyCheckboxes.filter(input => input.checked).length;
        journeyCount.textContent = selected + ' selected';
        journeyCheckboxes.forEach(input => {
            input.closest('.calculator-journey-choice')?.classList.toggle('is-selected', input.checked);
        });
    }

    formulaSelect.addEventListener('change', renderFormula);
    journeyCheckboxes.forEach(input => input.addEventListener('change', updateJourneySelection));
    renderFormula();
    updateJourneySelection();
});
</script>
