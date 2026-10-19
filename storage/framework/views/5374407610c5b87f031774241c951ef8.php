
<div class="journey-form-grid">
    <div class="journey-field">
        <label for="name">Journey Name <span>*</span></label>
        <input
            id="name"
            type="text"
            name="name"
            value="<?php echo e(old('name', $journey->name)); ?>"
            maxlength="120"
            required
            placeholder="Enter journey name"
        >
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="journey-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="journey-field">
        <label for="slug">Slug</label>
        <input
            id="slug"
            type="text"
            name="slug"
            value="<?php echo e(old('slug', $journey->slug)); ?>"
            maxlength="140"
            placeholder="Auto-generated if empty"
        >
        <small>Use lowercase letters, numbers and hyphens.</small>
        <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="journey-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="journey-field">
        <label for="short_label">Short Label</label>
        <input
            id="short_label"
            type="text"
            name="short_label"
            value="<?php echo e(old('short_label', $journey->short_label)); ?>"
            maxlength="80"
            placeholder="Optional short label"
        >
        <?php $__errorArgs = ['short_label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="journey-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="journey-field">
        <label for="display_order">Display Order <span>*</span></label>
        <input
            id="display_order"
            type="number"
            name="display_order"
            value="<?php echo e(old('display_order', $journey->display_order ?? 1)); ?>"
            min="1"
            max="4294967295"
            required
        >
        <?php $__errorArgs = ['display_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="journey-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="journey-field journey-field-full">
        <label for="description">Description</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            maxlength="10000"
            placeholder="Describe the purpose of this journey"
        ><?php echo e(old('description', $journey->description)); ?></textarea>
        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="journey-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="journey-field">
        <label for="status">Status <span>*</span></label>
        <select id="status" name="status" required>
            <option
                value="active"
                <?php if(old('status', $journey->status ?: 'active') === 'active'): echo 'selected'; endif; ?>
            >
                Active
            </option>
            <option
                value="inactive"
                <?php if(old('status', $journey->status ?: 'active') === 'inactive'): echo 'selected'; endif; ?>
            >
                Inactive
            </option>
        </select>
        <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="journey-error"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/admin/tool-journeys/partials/form.blade.php ENDPATH**/ ?>