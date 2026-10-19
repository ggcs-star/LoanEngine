
@php
    $selectedToolIds = collect(old(
        'tool_ids',
        $step->exists ? $step->tools->pluck('id')->all() : []
    ))->map(fn ($id) => (string) $id)->all();

    $currentJourneyId = (string) old(
        'journey_id',
        $selectedJourney?->id ?? $step->journey_id ?? ''
    );
@endphp

<div
    class="journey-step-form"
    data-tools-url="{{ url('/admin/tool-journeys') }}"
>
    <div class="journey-step-form-grid">
        <div class="journey-step-field journey-step-field-full">
            <label for="journey_id">Journey <span>*</span></label>

            <select id="journey_id" name="journey_id" required>
                <option value="">Select a journey</option>

                @foreach ($journeys as $journey)
                    <option
                        value="{{ $journey->id }}"
                        @selected($currentJourneyId === (string) $journey->id)
                    >
                        {{ $journey->name }}
                    </option>
                @endforeach
            </select>

            @error('journey_id')
                <p class="journey-step-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="journey-step-field">
            <label for="step_number">Step Number <span>*</span></label>
            <input
                id="step_number"
                name="step_number"
                type="number"
                min="1"
                max="4294967295"
                value="{{ old('step_number', $step->step_number ?? 1) }}"
                required
            >
            @error('step_number')
                <p class="journey-step-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="journey-step-field">
            <label for="display_order">Display Order <span>*</span></label>
            <input
                id="display_order"
                name="display_order"
                type="number"
                min="1"
                max="4294967295"
                value="{{ old('display_order', $step->display_order ?? 1) }}"
                required
            >
            @error('display_order')
                <p class="journey-step-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="journey-step-field journey-step-field-full">
            <label for="title">Step Title <span>*</span></label>
            <input
                id="title"
                name="title"
                type="text"
                maxlength="160"
                value="{{ old('title', $step->title) }}"
                placeholder="Enter step title"
                required
            >
            @error('title')
                <p class="journey-step-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="journey-step-field journey-step-field-full">
            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
                rows="4"
                maxlength="10000"
                placeholder="Explain this step"
            >{{ old('description', $step->description) }}</textarea>
            @error('description')
                <p class="journey-step-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="journey-step-field journey-step-field-full">
            <label for="what_you_need">What You Need</label>
            <textarea
                id="what_you_need"
                name="what_you_need"
                rows="4"
                maxlength="10000"
                placeholder="List the information or documents needed"
            >{{ old('what_you_need', $step->what_you_need) }}</textarea>
            @error('what_you_need')
                <p class="journey-step-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="journey-step-field journey-step-field-full">
            <label for="watch_out_for">Watch Out For</label>
            <textarea
                id="watch_out_for"
                name="watch_out_for"
                rows="4"
                maxlength="10000"
                placeholder="Mention important cautions or common mistakes"
            >{{ old('watch_out_for', $step->watch_out_for) }}</textarea>
            @error('watch_out_for')
                <p class="journey-step-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="journey-step-field">
            <label for="status">Status <span>*</span></label>
            <select id="status" name="status" required>
                <option value="active" @selected(old('status', $step->status ?: 'active') === 'active')>
                    Active
                </option>
                <option value="inactive" @selected(old('status', $step->status ?: 'active') === 'inactive')>
                    Inactive
                </option>
            </select>
            @error('status')
                <p class="journey-step-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="journey-step-field journey-step-field-full">
            <div class="journey-step-section-heading">
                <h3>Relevant Tools</h3>
                <p>Only tools mapped to the selected journey can be assigned to this step.</p>
            </div>

            <input type="hidden" name="tool_ids_present" value="1">

            <div
                class="journey-step-tool-list"
                id="journey-step-tool-list"
                aria-live="polite"
            >
                @forelse ($tools as $tool)
                    <label class="journey-step-tool-option">
                        <input
                            type="checkbox"
                            name="tool_ids[]"
                            value="{{ $tool->id }}"
                            @checked(in_array((string) $tool->id, $selectedToolIds, true))
                        >
                        <span>
                            <strong>{{ $tool->name }}</strong>
                            <small>{{ $tool->slug }}</small>
                        </span>
                    </label>
                @empty
                    <p class="journey-step-empty-tools">
                        Select a journey to load its associated tools.
                    </p>
                @endforelse
            </div>

            @error('tool_ids')
                <p class="journey-step-error">{{ $message }}</p>
            @enderror

            @error('tool_ids.*')
                <p class="journey-step-error">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const root = document.querySelector('.journey-step-form');
    const journeySelect = document.getElementById('journey_id');
    const toolList = document.getElementById('journey-step-tool-list');

    if (!root || !journeySelect || !toolList) {
        return;
    }

    let activeController = null;

    function renderTools(tools, selectedIds = []) {
        toolList.replaceChildren();

        if (!tools.length) {
            const message = document.createElement('p');
            message.className = 'journey-step-empty-tools';
            message.textContent = 'No tools are associated with this journey yet.';
            toolList.appendChild(message);
            return;
        }

        tools.forEach((tool) => {
            const label = document.createElement('label');
            label.className = 'journey-step-tool-option';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'tool_ids[]';
            checkbox.value = String(tool.id);
            checkbox.checked = selectedIds.includes(String(tool.id));

            const text = document.createElement('span');
            const name = document.createElement('strong');
            name.textContent = tool.name;

            const slug = document.createElement('small');
            slug.textContent = tool.slug;

            text.append(name, slug);
            label.append(checkbox, text);
            toolList.appendChild(label);
        });
    }

    async function loadTools(journeyId, preserveSelection = false) {
        if (activeController) {
            activeController.abort();
        }

        if (!journeyId) {
            renderTools([]);
            return;
        }

        const selectedIds = preserveSelection
            ? Array.from(toolList.querySelectorAll('input:checked'))
                .map((input) => input.value)
            : [];

        activeController = new AbortController();

        const loading = document.createElement('p');
        loading.className = 'journey-step-empty-tools';
        loading.textContent = 'Loading associated tools…';
        toolList.replaceChildren(loading);

        try {
            const baseUrl = root.dataset.toolsUrl.replace(/\/$/, '');
            const response = await fetch(
                `${baseUrl}/${encodeURIComponent(journeyId)}/tools`,
                {
                    headers: { Accept: 'application/json' },
                    signal: activeController.signal
                }
            );

            if (!response.ok) {
                throw new Error('Unable to load tools.');
            }

            const result = await response.json();
            renderTools(result.data || [], selectedIds);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            const message = document.createElement('p');
            message.className = 'journey-step-empty-tools';
            message.textContent = 'Could not load tools. Please try selecting the journey again.';
            toolList.replaceChildren(message);
        }
    }

    journeySelect.addEventListener('change', () => {
        loadTools(journeySelect.value, false);
    });

    // Initial load keeps existing selections when editing or returning
    // after validation errors.
    if (journeySelect.value) {
        loadTools(journeySelect.value, true);
    }
})();
</script>
@endpush
