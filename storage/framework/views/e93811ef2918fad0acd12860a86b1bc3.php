

<?php $__env->startSection('title', 'Edit Journey'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-journeys.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="journey-page">
    <div class="journey-page-header">
        <div>
            <a href="<?php echo e(route('admin.tool-journeys.index')); ?>" class="journey-back-link">
                &larr; Back to Journeys
            </a>
            <h1 class="journey-page-title">Edit Journey</h1>
            <p class="journey-page-description">
                Update the journey details without changing its existing steps or tool mappings.
            </p>
        </div>
    </div>

    <?php if(session('error')): ?>
        <div class="journey-alert" role="alert"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="journey-alert" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="journey-panel">
        <div class="journey-panel-header">
            <h2>Journey Details</h2>
            <p>Update the information below and save your changes.</p>
        </div>

        <form method="POST" action="<?php echo e(route('admin.tool-journeys.update', $journey)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <?php echo $__env->make('admin.tool-journeys.partials.form', ['journey' => $journey], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="journey-form-footer">
                <a href="<?php echo e(route('admin.tool-journeys.index')); ?>" class="journey-button journey-button-secondary">
                    Cancel
                </a>
                <button type="submit" class="journey-button journey-button-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/admin/tool-journeys/edit.blade.php ENDPATH**/ ?>