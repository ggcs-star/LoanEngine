


<?php $__env->startSection('title', 'Edit Journey Step'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-journey-steps.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="journey-step-page">
    <div class="journey-step-page-header">
        <div>
            <a href="<?php echo e(route('admin.journey-steps.index', ['journey_id' => $step->journey_id])); ?>" class="journey-step-back-link">
                &larr; Back to Steps
            </a>
            <h1 class="journey-step-page-title">Edit Journey Step</h1>
            <p class="journey-step-page-description">
                Update this step and its relevant tool associations.
            </p>
        </div>

        <span class="journey-step-status journey-step-status-<?php echo e($step->status); ?>">
            <?php echo e(ucfirst($step->status)); ?>

        </span>
    </div>

    <?php if(session('error')): ?>
        <div class="journey-step-alert" role="alert"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="journey-step-alert" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="journey-step-panel">
        <div class="journey-step-panel-header">
            <h2>Step Details</h2>
            <p>Changes will apply to this step only.</p>
        </div>

        <form method="POST" action="<?php echo e(route('admin.journey-steps.update', $step)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <?php echo $__env->make('admin.journey-steps.partials.form', [
                'step' => $step,
                'journeys' => $journeys,
                'selectedJourney' => $selectedJourney,
                'tools' => $tools,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="journey-step-form-footer">
                <a href="<?php echo e(route('admin.journey-steps.index', ['journey_id' => $step->journey_id])); ?>" class="journey-step-button journey-step-button-secondary">
                    Cancel
                </a>
                <button type="submit" class="journey-step-button journey-step-button-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/admin/journey-steps/edit.blade.php ENDPATH**/ ?>