
<?php
    $selectedToolIds = collect(old(
        'tool_ids',
        $step->exists ? $step->tools->pluck('id')->all() : []
    ))->map(fn ($id) => (string) $id)->all();

    $currentJourneyId = (string) old(
        'journey_id',
        $selectedJourney?->id ?? $step->journey_id ?? ''
    );
?>

<div
    class="journey-step-form"
    data-tools-url="<?php echo e(url('/admin/tool-journeys')); ?>"
>
    <div class="journey-step-form-grid">
        <div class="journey-step-field journey-step-field-full">
            <label for="journey_id">Journey <span>*</span></label>

            <select id="journey_id" name="journey_id" required>
                <option value="">Select a journey</option>

                <?php $__currentLoopData = $journeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $journey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option
                        value="<?php echo e($journey->id); ?>"
                        <?php if($currentJourneyId === (string) $journey->id): echo 'selected'; endif; ?>
                    >
                        <?php echo e($journey->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <?php $__errorArgs = ['journey_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="journey-step-error"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="journey-step-field">
            <label for="step_number">Step Number <span>*</span></label>
            <input
                id="step_number"
                name="step_number"
                type="number"
                min="1"
                max="4294967295"
                value="<?php echo e(old('step_number', $step->step_number ?? 1)); ?>"
                required
            >
            <?php $__errorArgs = ['step_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="journey-step-error"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="journey-step-field">
            <label for="display_order">Display Order <span>*</span></label>
            <input
                id="display_order"
                name="display_order"
                type="number"
                min="1"
                max="4294967295"
                value="<?php echo e(old('display_order', $step->display_order ?? 1)); ?>"
                required
            >
            <?php $__errorArgs = ['display_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="journey-step-error"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="journey-step-field journey-step-field-full">
            <label for="title">Step Title <span>*</span></label>
            <input
                id="title"
                name="title"
                type="text"
                maxlength="160"
                value="<?php echo e(old('title', $step->title)); ?>"
                placeholder="Enter step title"
                required
            >
            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="journey-step-error"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="journey-step-field journey-step-field-full">
            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
                rows="4"
                maxlength="10000"
                placeholder="Explain this step"
            ><?php echo e(old('description', $step->description)); ?></textarea>
            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="journey-step-error"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="journey-step-field journey-step-field-full">
            <label for="what_you_need">What You Need</label>
            <textarea
                id="what_you_need"
                name="what_you_need"
                rows="4"
                maxlength="10000"
                placeholder="List the information or documents needed"
            ><?php echo e(old('what_you_need', $step->what_you_need)); ?></textarea>
            <?php $__errorArgs = ['what_you_need'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="journey-step-error"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="journey-step-field journey-step-field-full">
            <label for="watch_out_for">Watch Out For</label>
            <textarea
                id="watch_out_for"
                name="watch_out_for"
                rows="4"
                maxlength="10000"
                placeholder="Mention important cautions or common mistakes"
            ><?php echo e(old('watch_out_for', $step->watch_out_for)); ?></textarea>
            <?php $__errorArgs = ['watch_out_for'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="journey-step-error"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="journey-step-field">
            <label for="status">Status <span>*</span></label>
            <select id="status" name="status" required>
                <option value="active" <?php if(old('status', $step->status ?: 'active') === 'active'): echo 'selected'; endif; ?>>
                    Active
                </option>
                <option value="inactive" <?php if(old('status', $step->status ?: 'active') === 'inactive'): echo 'selected'; endif; ?>>
                    Inactive
                </option>
            </select>
            <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="journey-step-error"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $tools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <label class="journey-step-tool-option">
                        <input
                            type="checkbox"
                            name="tool_ids[]"
                            value="<?php echo e($tool->id); ?>"
                            <?php if(in_array((string) $tool->id, $selectedToolIds, true)): echo 'checked'; endif; ?>
                        >
                        <span>
                            <strong><?php echo e($tool->name); ?></strong>
                            <small><?php echo e($tool->slug); ?></small>
                        </span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="journey-step-empty-tools">
                        Select a journey to load its associated tools.
                    </p>
                <?php endif; ?>
            </div>

            <?php $__errorArgs = ['tool_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="journey-step-error"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <?php $__errorArgs = ['tool_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="journey-step-error"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>
<?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/admin/journey-steps/partials/form.blade.php ENDPATH**/ ?>