


<?php $__env->startSection('title', 'Create Tool'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-tools.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="tools-page">
    <div class="tools-page-header">
        <div>
            <a href="<?php echo e(route('admin.tools.index')); ?>" class="tools-back-link">
                &larr; Back to Tools
            </a>
            <h1 class="tools-page-title">Create Tool</h1>
            <p class="tools-page-description">
                Add a tool and optionally associate it with existing journeys.
            </p>
        </div>
    </div>

    <?php if($errors->any()): ?>
        <div class="tools-alert tools-alert-error" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="tools-panel tools-form-panel">
        <div class="tools-panel-header">
            <div>
                <h2 class="tools-panel-title">Tool Details</h2>
                <p class="tools-panel-description">
                    Enter the tool information below.
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="<?php echo e(route('admin.tools.store')); ?>"
            class="tools-form"
        >
            <?php echo csrf_field(); ?>

            <?php echo $__env->make('admin.tools.partials.form', [
                'tool' => $tool,
                'journeys' => $journeys,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="tools-form-footer">
                <a
                    href="<?php echo e(route('admin.tools.index')); ?>"
                    class="tools-button tools-button-secondary"
                >
                    Cancel
                </a>

                <button type="submit" class="tools-button tools-button-primary">
                    Create Tool
                </button>
            </div>
        </form>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/admin/tools/create.blade.php ENDPATH**/ ?>