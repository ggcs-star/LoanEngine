
@php
    $selectedJourneys = old(
        'journey_ids',
        isset($calculator)
            ? $calculator->journeys->pluck('id')->map(fn ($id) => (string) $id)->toArray()
            : []
    );

    $selectedJourneys = array_map('strval', (array) $selectedJourneys);
@endphp

<div class="calculator-form">

    {{-- Calculator Name --}}
    <div class="form-field">
        <label for="calculator-name" class="form-label">
            Calculator Name <span class="required-mark">*</span>
        </label>

        <input
            type="text"
            id="calculator-name"
            name="name"
            class="form-control"
            value="{{ old('name', $calculator->name ?? '') }}"
            placeholder="e.g. Home Loan EMI Calculator"
            required
            maxlength="150"
        >

        @error('name')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    {{-- Slug --}}
    <div class="form-field">
        <label for="calculator-slug" class="form-label">
            Calculator Slug <span class="required-mark">*</span>
        </label>

        <input
            type="text"
            id="calculator-slug"
            name="slug"
            class="form-control"
            value="{{ old('slug', $calculator->slug ?? '') }}"
            placeholder="home-loan-emi-calculator"
            required
            maxlength="180"
        >

        <small class="form-help">
            Unique URL identifier. Use lowercase letters and hyphens.
        </small>

        @error('slug')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    {{-- Short Description: required database column --}}
    <div class="form-field full-width">
        <label for="calculator-short-description" class="form-label">
            Short Description <span class="form-help">(Optional)</span>
        </label>

        <textarea
            id="calculator-short-description"
            name="short_description"
            class="form-control"
            rows="3"
            maxlength="1000"
            placeholder="Briefly explain what this calculator does..."
        >{{ old('short_description', $calculator->short_description ?? '') }}</textarea>

        @error('short_description')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-divider"></div>

    {{-- Status --}}
    <div class="form-field">
        <label for="calculator-status" class="form-label">
            Publication Status <span class="required-mark">*</span>
        </label>

        <select
            id="calculator-status"
            name="status"
            class="form-select"
            required
        >
            <option value="active"
                @selected(old('status', $calculator->status ?? 'active') === 'active')>
                Active
            </option>

            <option value="inactive"
                @selected(old('status', $calculator->status ?? 'active') === 'inactive')>
                Inactive
            </option>
        </select>

        <small class="form-help">
            Only active calculators should be offered to users.
        </small>

        @error('status')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    {{-- Display Order --}}
    <div class="form-field">
        <label for="calculator-display-order" class="form-label">
            Display Order <span class="required-mark">*</span>
        </label>

        <input
            type="number"
            id="calculator-display-order"
            name="display_order"
            class="form-control"
            value="{{ old('display_order', $calculator->display_order ?? 1) }}"
            min="0"
            required
        >

        <small class="form-help">
            Lower numbers appear first.
        </small>

        @error('display_order')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    {{-- Icon --}}
    <div class="form-field">
        <label for="calculator-icon" class="form-label">
            Icon
        </label>

        <input
            type="text"
            id="calculator-icon"
            name="icon"
            class="form-control"
            value="{{ old('icon', $calculator->icon ?? 'calculator') }}"
            placeholder="calculator"
            maxlength="100"
        >

        @error('icon')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    {{-- Existing Route Name --}}
    <div class="form-field">
        <label for="calculator-route-name" class="form-label">
            Existing Calculator Route Name
        </label>

        <input
            type="text"
            id="calculator-route-name"
            name="route_name"
            class="form-control"
            value="{{ old('route_name', $calculator->route_name ?? '') }}"
            placeholder="e.g. calculators.show"
            maxlength="180"
        >

        <small class="form-help">
            Enter a valid route name if this calculator uses an existing route.
        </small>

        @error('route_name')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-divider"></div>

    {{-- Journey Recommendations --}}
    <div class="form-field full-width">
        <div class="journey-section-heading">
            <div>
                <h3>Recommended Journeys</h3>
                <p>
                    Select the journeys where this calculator should be recommended.
                </p>
            </div>

            <span
                class="journey-selection-count"
                id="journey-selection-count"
            >
                {{ count($selectedJourneys) }} selected
            </span>
        </div>

        @error('journey_ids')
            <small class="field-error">{{ $message }}</small>
        @enderror

        @if(isset($journeys) && $journeys->isNotEmpty())
            <div class="journey-options">
                @foreach($journeys as $journey)
                    @php
                        $journeyId = (string) $journey->id;
                        $isSelected = in_array($journeyId, $selectedJourneys, true);
                    @endphp

                    <label
                        class="journey-option {{ $isSelected ? 'is-selected' : '' }}"
                        for="journey-{{ $journey->id }}"
                    >
                        <input
                            type="checkbox"
                            id="journey-{{ $journey->id }}"
                            name="journey_ids[]"
                            value="{{ $journey->id }}"
                            @checked($isSelected)
                        >

                        <span class="journey-option-content">
                            <span class="journey-option-title">
                                {{ $journey->name }}
                            </span>

                            @if($journey->short_label)
                                <span class="journey-option-description">
                                    {{ $journey->short_label }}
                                </span>
                            @endif
                        </span>

                        <span class="journey-checkmark" aria-hidden="true">✓</span>
                    </label>
                @endforeach
            </div>
        @else
            <div class="journey-empty-state">
                <strong>No journeys available</strong>
                <p>
                    Create or activate a journey before assigning recommendations.
                </p>
            </div>
        @endif

        @error('journey_ids.*')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const journeyCheckboxes = document.querySelectorAll(
        '.journey-option input[type="checkbox"]'
    );

    const selectionCount = document.getElementById(
        'journey-selection-count'
    );

    function updateJourneySelection() {
        let selectedCount = 0;

        journeyCheckboxes.forEach(function (checkbox) {
            const card = checkbox.closest('.journey-option');

            if (checkbox.checked) {
                selectedCount++;
                card.classList.add('is-selected');
            } else {
                card.classList.remove('is-selected');
            }
        });

        if (selectionCount) {
            selectionCount.textContent = selectedCount + ' selected';
        }
    }

    journeyCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateJourneySelection);
    });

    updateJourneySelection();
});
</script>
