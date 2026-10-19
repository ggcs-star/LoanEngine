
<?php
    $selectedJourneyIds = collect(
        old(
            'journey_ids',
            $tool->exists
                ? $tool->journeys->pluck('id')->all()
                : []
        )
    )->map(fn ($id) => (string) $id)->all();
?>

<div class="tools-form-grid">
    <div class="tools-field">
        <label for="name">Tool Name <span class="tools-required">*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="<?php echo e(old('name', $tool->name)); ?>"
            maxlength="120"
            required
            autocomplete="off"
            placeholder="Enter tool name"
        >
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="tools-field">
        <label for="slug">Slug</label>
        <input
            id="slug"
            type="text"
            name="slug"
            value="<?php echo e(old('slug', $tool->slug)); ?>"
            maxlength="140"
            placeholder="e.g. home-loan-calculator"
        >
        <p class="tools-field-help">
            Leave empty to generate it from the tool name.
        </p>
        <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
        ><?php echo e(old('short_description', $tool->short_description)); ?></textarea>
        <?php $__errorArgs = ['short_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="tools-field">
        <label for="category">Category <span class="tools-required">*</span></label>
        <input
            id="category"
            type="text"
            name="category"
            value="<?php echo e(old('category', $tool->category)); ?>"
            maxlength="40"
            required
            placeholder="Enter category"
        >
        <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="tools-field">
        <label for="type">Type <span class="tools-required">*</span></label>
        <input
            id="type"
            type="text"
            name="type"
            value="<?php echo e(old('type', $tool->type)); ?>"
            maxlength="30"
            required
            placeholder="Enter tool type"
        >
        <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="tools-field">
        <label for="icon">Icon Identifier <span class="tools-required">*</span></label>
        <input
            id="icon"
            type="text"
            name="icon"
            value="<?php echo e(old('icon', $tool->icon)); ?>"
            maxlength="60"
            required
            placeholder="Enter icon identifier"
        >
        <p class="tools-field-help">
            Enter the identifier supported by your frontend icon library.
        </p>
        <?php $__errorArgs = ['icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="tools-field">
        <label for="status">Status <span class="tools-required">*</span></label>
        <select id="status" name="status" required>
            <?php $__currentLoopData = [
                'active' => 'Active',
                'planned' => 'Planned',
                'inactive' => 'Inactive',
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option
                    value="<?php echo e($value); ?>"
                    <?php if(old('status', $tool->status ?: 'active') === $value): echo 'selected'; endif; ?>
                >
                    <?php echo e($label); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="tools-field">
        <label for="page_route">Page Route <span class="tools-required">*</span></label>
        <input
            id="page_route"
            type="text"
            name="page_route"
            value="<?php echo e(old('page_route', $tool->page_route)); ?>"
            maxlength="255"
            required
            placeholder="Enter route name or path"
        >
        <p class="tools-field-help">
            Use the format expected by your frontend.
        </p>
        <?php $__errorArgs = ['page_route'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="tools-field">
        <label for="display_order">Display Order</label>
        <input
            id="display_order"
            type="number"
            name="display_order"
            value="<?php echo e(old('display_order', $tool->display_order ?? 1)); ?>"
            min="1"
            max="4294967295"
        >
        <?php $__errorArgs = ['display_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="tools-field tools-field-full">
        <div class="tools-section-heading">
            <h3>Journey Associations</h3>
            <p>
                Select the journeys where this tool is relevant.
                Journey options are loaded from the database.
            </p>
        </div>

        <?php if($journeys->isNotEmpty()): ?>
            <div class="tools-journey-list">
                <?php $__currentLoopData = $journeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $journey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="tools-journey-option" for="journey-<?php echo e($journey->id); ?>">
                        <input
                            id="journey-<?php echo e($journey->id); ?>"
                            type="checkbox"
                            name="journey_ids[]"
                            value="<?php echo e($journey->id); ?>"
                            <?php if(in_array((string) $journey->id, $selectedJourneyIds, true)): echo 'checked'; endif; ?>
                        >

                        <span>
                            <strong><?php echo e($journey->name); ?></strong>
                            <?php if($journey->short_label): ?>
                                <small><?php echo e($journey->short_label); ?></small>
                            <?php endif; ?>
                        </span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <p class="tools-empty-note">
                No active journeys are available yet. You can create journeys
                in Journey Management.
            </p>
        <?php endif; ?>

        <?php $__errorArgs = ['journey_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        <?php $__errorArgs = ['journey_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="tools-field-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="tools-field tools-field-full">
        <label class="tools-checkbox-option" for="show_on_toolkit_home">
            <input
                id="show_on_toolkit_home"
                type="checkbox"
                name="show_on_toolkit_home"
                value="1"
                <?php if(old('show_on_toolkit_home', $tool->show_on_toolkit_home ?? true)): echo 'checked'; endif; ?>
            >
            <span>
                <strong>Show on Toolkit Home</strong>
                <small>Display this tool on the public toolkit landing page.</small>
            </span>
        </label>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/admin/tools/partials/form.blade.php ENDPATH**/ ?>