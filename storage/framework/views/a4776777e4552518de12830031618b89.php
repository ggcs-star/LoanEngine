

<?php $__env->startSection('title', 'Journey Steps Management'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/admin-journey-steps.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="journey-step-page">
    <div class="journey-step-page-header">
        <div>
            <h1 class="journey-step-page-title">Journey Steps</h1>
            <p class="journey-step-page-description">Manage steps within their journeys and assign relevant tools.</p>
        </div>
        <a href="<?php echo e(route('admin.journey-steps.create', !empty($filters['journey_id']) ? ['journey_id' => $filters['journey_id']] : [])); ?>" class="journey-step-button journey-step-button-primary">
            <span aria-hidden="true">+</span> Add Step
        </a>
    </div>

    
    <?php if(session('success')): ?>
        <div class="journey-step-alert journey-step-alert-success" role="status">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="journey-step-alert journey-step-alert-error" role="alert">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="journey-step-alert journey-step-alert-error" role="alert">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="journey-step-stats">
        <article class="journey-step-stat-card"><span>Total Steps</span><strong><?php echo e(number_format($statistics['total'] ?? 0)); ?></strong></article>
        <article class="journey-step-stat-card"><span>Active Steps</span><strong><?php echo e(number_format($statistics['active'] ?? 0)); ?></strong></article>
        <article class="journey-step-stat-card"><span>Inactive Steps</span><strong><?php echo e(number_format($statistics['inactive'] ?? 0)); ?></strong></article>
    </div>

    <section class="journey-step-panel">
        <div class="journey-step-panel-header">
            <h2>All Journey Steps</h2>
            <p>Search steps, filter by journey or status, and manage tool assignments.</p>
        </div>

        <form method="GET" action="<?php echo e(route('admin.journey-steps.index')); ?>" class="journey-step-filters">
            <div class="journey-step-field">
                <label for="journey-step-search">Search</label>
                <input id="journey-step-search" type="search" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search title or description">
            </div>
            <div class="journey-step-field">
                <label for="journey-step-journey">Journey</label>
                <select id="journey-step-journey" name="journey_id">
                    <option value="">All journeys</option>
                    <?php $__currentLoopData = $journeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $journey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($journey->id); ?>" <?php if((string) ($filters['journey_id'] ?? '') === (string) $journey->id): echo 'selected'; endif; ?>><?php echo e($journey->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="journey-step-field">
                <label for="journey-step-status">Status</label>
                <select id="journey-step-status" name="status">
                    <option value="">All statuses</option>
                    <option value="active" <?php if(($filters['status'] ?? '') === 'active'): echo 'selected'; endif; ?>>Active</option>
                    <option value="inactive" <?php if(($filters['status'] ?? '') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                </select>
            </div>
            <div class="journey-step-filter-actions">
                <button type="submit" class="journey-step-button journey-step-button-primary">Apply Filters</button>
                <a href="<?php echo e(route('admin.journey-steps.index')); ?>" class="journey-step-button journey-step-button-secondary">Reset</a>
            </div>
        </form>

        <form id="journeyStepBulkDeleteForm" method="POST" action="<?php echo e(route('admin.journey-steps.bulk-destroy')); ?>">
            <?php echo csrf_field(); ?>
        </form>
        <div class="journey-step-bulk-toolbar">
            <span id="journeyStepSelectionCount" class="journey-step-selection-count" aria-live="polite">0 selected</span>
            <button type="button" id="journeyStepDeleteSelected" class="journey-step-button journey-step-button-secondary" disabled>Delete Selected</button>
        </div>

        <div class="journey-step-table-wrapper">
            <table class="journey-step-table">
                <thead><tr>
                    <th class="journey-step-select-column"><input type="checkbox" id="journeyStepSelectAll" class="journey-step-select-checkbox" aria-label="Select all steps on this page"></th>
                    <th>Step</th><th>Journey</th><th>Tools</th><th>Status</th><th>Order</th><th class="journey-step-actions-heading">Actions</th>
                </tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                   <tr
                        class="journey-step-clickable-row"
                        data-href="<?php echo e(route('admin.journey-steps.show', $item)); ?>"
                        tabindex="0"
                        role="link"
                        aria-label="View <?php echo e($item->title); ?>"
                    >
                        <td class="journey-step-select-column"><input type="checkbox" class="journey-step-select-checkbox journey-step-row-checkbox" name="ids[]" value="<?php echo e($item->id); ?>" form="journeyStepBulkDeleteForm" aria-label="Select step <?php echo e($item->title); ?>"></td>
                        <td>
                            <strong class="journey-step-name"><?php echo e($item->step_number); ?>. <?php echo e($item->title); ?></strong>
                            <?php if($item->description): ?><span class="journey-step-meta"><?php echo e(\Illuminate\Support\Str::limit($item->description, 100)); ?></span><?php endif; ?>
                        </td>
                        <td><?php echo e($item->journey?->name ?? 'Journey unavailable'); ?></td>
                        <td><div class="journey-step-tool-tags">
                            <?php $__empty_2 = true; $__currentLoopData = $item->tools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><span class="journey-step-tag"><?php echo e($tool->name); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><span class="journey-step-muted">No tools assigned</span><?php endif; ?>
                        </div></td>
                        <td><span class="journey-step-status journey-step-status-<?php echo e($item->status); ?>"><?php echo e(ucfirst($item->status)); ?></span></td>
                        <td><?php echo e($item->display_order); ?></td>
                        <td><div class="journey-step-row-actions">
                            <a href="<?php echo e(route('admin.journey-steps.edit', $item)); ?>" class="journey-step-icon-button" title="Edit step" aria-label="Edit <?php echo e($item->title); ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                            </a>
                            <form method="POST" action="<?php echo e(route('admin.journey-steps.destroy', $item)); ?>" class="journey-step-single-delete-form" data-delete-name="<?php echo e($item->title); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="journey-step-icon-button" title="Delete step" aria-label="Delete <?php echo e($item->title); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                </button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7"><div class="journey-step-empty-state"><strong>No journey steps found</strong><p>Create a step or adjust your filters.</p><a href="<?php echo e(route('admin.journey-steps.create', !empty($filters['journey_id']) ? ['journey_id' => $filters['journey_id']] : [])); ?>" class="journey-step-button journey-step-button-primary">Create First Step</a></div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="journey-step-results-footer">
    <div class="journey-step-results-count">
        Showing <?php echo e($steps->firstItem() ?? 0); ?> to <?php echo e($steps->lastItem() ?? 0); ?>

        of <?php echo e($steps->total()); ?> results
    </div>

    <?php if($steps->hasPages()): ?>
        <div class="journey-step-pagination">
            <?php echo e($steps->withQueryString()->links()); ?>

        </div>
    <?php endif; ?>
</div>
    </section>
</div>

<div class="journey-step-delete-modal" id="journeyStepDeleteModal" hidden>
    <div class="journey-step-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="journeyStepDeleteTitle" aria-describedby="journeyStepDeleteMessage" tabindex="-1">
        <h2 id="journeyStepDeleteTitle">Delete journey step?</h2>
        <p id="journeyStepDeleteMessage">Are you sure you want to delete this step?</p>
        <div class="journey-step-delete-dialog-actions">
            <button type="button" id="journeyStepCancelDelete">Cancel</button>
            <button type="button" id="journeyStepConfirmDelete" class="journey-step-confirm-delete">Delete</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('journeyStepSelectAll');
    const checkboxes = Array.from(document.querySelectorAll('.journey-step-row-checkbox'));
    const countLabel = document.getElementById('journeyStepSelectionCount');
    const deleteSelected = document.getElementById('journeyStepDeleteSelected');
    const bulkForm = document.getElementById('journeyStepBulkDeleteForm');
    const modal = document.getElementById('journeyStepDeleteModal');
    const title = document.getElementById('journeyStepDeleteTitle');
    const message = document.getElementById('journeyStepDeleteMessage');
    const cancel = document.getElementById('journeyStepCancelDelete');
    const confirmButton = document.getElementById('journeyStepConfirmDelete');
    let pendingForm = null;
    let previousFocus = null;

    function selectedCheckboxes() { return checkboxes.filter(function (box) { return box.checked; }); }
    function updateSelection() {
        const count = selectedCheckboxes().length;
        countLabel.textContent = count + ' selected';
        deleteSelected.disabled = count === 0;
        selectAll.checked = checkboxes.length > 0 && count === checkboxes.length;
        selectAll.indeterminate = count > 0 && count < checkboxes.length;
        selectAll.disabled = checkboxes.length === 0;
    }
    selectAll.addEventListener('change', function () {
        checkboxes.forEach(function (box) { box.checked = selectAll.checked; });
        updateSelection();
    });
    checkboxes.forEach(function (box) { box.addEventListener('change', updateSelection); });

    function openModal(form, isBulk) {
        pendingForm = form;
        previousFocus = document.activeElement;
        if (isBulk) {
            const count = selectedCheckboxes().length;
            title.textContent = 'Delete selected journey steps?';
            message.textContent = 'You are about to delete ' + count + (count === 1 ? ' selected step.' : ' selected steps') + ' Their tool associations will also be removed.';
        } else {
            title.textContent = 'Delete journey step?';
            message.textContent = 'Are you sure you want to delete "' + (form.dataset.deleteName || 'this step') + '" and its tool associations?';
        }
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        cancel.focus();
    }
    function closeModal() {
        modal.hidden = true;
        document.body.style.overflow = '';
        pendingForm = null;
        confirmButton.disabled = false;
        cancel.disabled = false;
        confirmButton.textContent = 'Delete';
        if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
    }
    document.querySelectorAll('.journey-step-single-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) { event.preventDefault(); openModal(form, false); });
    });
    deleteSelected.addEventListener('click', function () { if (selectedCheckboxes().length) openModal(bulkForm, true); });
    cancel.addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', function (event) {
        if (modal.hidden) return;
        if (event.key === 'Escape') closeModal();
        if (event.key === 'Tab') {
            const focusable = [cancel, confirmButton];
            if (event.shiftKey && document.activeElement === focusable[0]) { event.preventDefault(); focusable[1].focus(); }
            else if (!event.shiftKey && document.activeElement === focusable[1]) { event.preventDefault(); focusable[0].focus(); }
        }
    });
    confirmButton.addEventListener('click', function () {
        if (!pendingForm) return;
        if (pendingForm === bulkForm && selectedCheckboxes().length === 0) return closeModal();
        confirmButton.disabled = true;
        cancel.disabled = true;
        confirmButton.textContent = 'Deleting...';
        HTMLFormElement.prototype.submit.call(pendingForm);
    });
        // Open the Show page when clicking anywhere on a table row.
    document.querySelectorAll('.journey-step-clickable-row').forEach(function (row) {
        function openShowPage() {
            const url = row.dataset.href;

            if (url) {
                window.location.href = url;
            }
        }

        row.addEventListener('click', function (event) {
            if (event.target.closest(
                'a, button, form, input, select, textarea, label'
            )) {
                return;
            }

            openShowPage();
        });

        row.addEventListener('keydown', function (event) {
            if (
                (event.key === 'Enter' || event.key === ' ') &&
                event.target === row
            ) {
                event.preventDefault();
                openShowPage();
            }
        });
    });
    updateSelection();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/admin/journey-steps/index.blade.php ENDPATH**/ ?>