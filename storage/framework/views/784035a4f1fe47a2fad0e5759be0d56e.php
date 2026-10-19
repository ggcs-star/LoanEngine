

<?php $__env->startSection('title', 'Journey Management'); ?>

<?php $__env->startPush('styles'); ?>

<link rel="stylesheet" href="<?php echo e(asset('css/admin-journeys.css')); ?>">

<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="journey-page">

    <div class="journey-page-header">

        <div>

            <h1 class="journey-page-title">Journey Management</h1>

            <p class="journey-page-description">

                Manage journeys before adding their individual steps and tools.

            </p>

        </div>

        <a href="<?php echo e(route('admin.tool-journeys.create')); ?>"

           class="journey-button journey-button-primary">

            <span aria-hidden="true">+</span> Add Journey

        </a>

    </div>

    
    <?php if(session('success')): ?>
        <div class="journey-alert journey-alert-success" role="status">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="journey-alert journey-alert-error" role="alert">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="journey-alert journey-alert-error" role="alert">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p><?php echo e($error); ?></p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div class="journey-stats">

        <article class="journey-stat-card">

            <span>Total Journeys</span>

            <strong><?php echo e(number_format($statistics['total'] ?? 0)); ?></strong>

        </article>

        <article class="journey-stat-card">

            <span>Active Journeys</span>

            <strong><?php echo e(number_format($statistics['active'] ?? 0)); ?></strong>

        </article>

        <article class="journey-stat-card">

            <span>Inactive Journeys</span>

            <strong><?php echo e(number_format($statistics['inactive'] ?? 0)); ?></strong>

        </article>

    </div>

    <section class="journey-panel">

        <div class="journey-panel-header">

            <div>

                <h2>All Journeys</h2>

                <p>Search and manage the journeys stored in your database.</p>

            </div>

        </div>

        <form method="GET"

              action="<?php echo e(route('admin.tool-journeys.index')); ?>"

              class="journey-filters">

            <div class="journey-field">

                <label for="journey-search">Search journeys</label>

                <input

                    id="journey-search"

                    type="search"

                    name="search"

                    value="<?php echo e($filters['search'] ?? ''); ?>"

                    placeholder="Search name, slug or short label"

                >

            </div>

            <div class="journey-field">

                <label for="journey-status">Status</label>

                <select id="journey-status" name="status">

                    <option value="">All statuses</option>

                    <option value="active"

                        <?php if(($filters['status'] ?? '') === 'active'): echo 'selected'; endif; ?>>

                        Active

                    </option>

                    <option value="inactive"

                        <?php if(($filters['status'] ?? '') === 'inactive'): echo 'selected'; endif; ?>>

                        Inactive

                    </option>

                </select>

            </div>

            <div class="journey-filter-actions">

                <button type="submit"

                        class="journey-button journey-button-primary">

                    Apply Filters

                </button>

                <a href="<?php echo e(route('admin.tool-journeys.index')); ?>"

                   class="journey-button journey-button-secondary">

                    Reset

                </a>

            </div>

        </form>

        <form id="journeyBulkDeleteForm"

              method="POST"

              action="<?php echo e(route('admin.tool-journeys.bulk-destroy')); ?>">

            <?php echo csrf_field(); ?>

        </form>

        <div class="journey-bulk-toolbar">

            <span class="journey-selection-count"

                  id="journeySelectionCount"

                  aria-live="polite">0 selected</span>

            <button type="button"

                    id="journeyDeleteSelected"

                    class="journey-button journey-button-secondary"

                    disabled>

                Delete Selected

            </button>

        </div>

        <div class="journey-table-wrapper">

            <table class="journey-table">

                <thead>

                    <tr>

                        <th class="journey-select-column">

                            <input type="checkbox"

                                   id="journeySelectAll"

                                   class="journey-select-checkbox"

                                   aria-label="Select all journeys on this page">

                        </th>

                        <th>Journey</th>

                        <th>Steps</th>

                        <th>Tools</th>

                        <th>Status</th>

                        <th>Order</th>

                        <th class="journey-actions-heading">Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php $__empty_1 = true; $__currentLoopData = $journeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr class="journey-clickable-row"

                            data-url="<?php echo e(route('admin.tool-journeys.show', $item)); ?>"

                            tabindex="0"

                            aria-label="Open details for <?php echo e($item->name); ?>">

                            <td class="journey-select-column">

                                <input type="checkbox"

                                       class="journey-select-checkbox journey-row-checkbox"

                                       name="ids[]"

                                       value="<?php echo e($item->id); ?>"

                                       form="journeyBulkDeleteForm"

                                       aria-label="Select <?php echo e($item->name); ?>">

                            </td>

                            <td>

                                <strong class="journey-name"><?php echo e($item->name); ?></strong>

                                <span class="journey-slug"><?php echo e($item->slug); ?></span>

                                <?php if($item->short_label): ?>

                                    <span class="journey-short-label">

                                        <?php echo e($item->short_label); ?>


                                    </span>

                                <?php endif; ?>

                                <?php if($item->description): ?>

                                    <p class="journey-description-cell">

                                        <?php echo e(\Illuminate\Support\Str::limit($item->description, 120)); ?>


                                    </p>

                                <?php endif; ?>

                            </td>

                            <td><?php echo e(number_format($item->steps_count)); ?></td>

                            <td><?php echo e(number_format($item->tools_count)); ?></td>

                            <td>

                                <span class="journey-status journey-status-<?php echo e($item->status); ?>">

                                    <?php echo e(ucfirst($item->status)); ?>


                                </span>

                            </td>

                            <td><?php echo e($item->display_order); ?></td>

                            <td>

                                <div class="journey-row-actions">

                                    <a href="<?php echo e(route('admin.tool-journeys.edit', $item)); ?>"

                                       class="journey-icon-button"

                                       title="Edit journey"

                                       aria-label="Edit <?php echo e($item->name); ?>">

                                        <svg width="16" height="16"

                                             viewBox="0 0 24 24"

                                             fill="none" stroke="currentColor"

                                             stroke-width="1.8"

                                             stroke-linecap="round"

                                             stroke-linejoin="round"

                                             aria-hidden="true">

                                            <path d="M12 20h9"/>

                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>

                                        </svg>

                                    </a>

                                    <form method="POST"

                                          action="<?php echo e(route('admin.tool-journeys.destroy', $item)); ?>"

                                          class="journey-single-delete-form"

                                          data-delete-name="<?php echo e($item->name); ?>">

                                        <?php echo csrf_field(); ?>

                                        <?php echo method_field('DELETE'); ?>

                                        <button type="submit"

                                                class="journey-icon-button"

                                                title="Delete journey"

                                                aria-label="Delete <?php echo e($item->name); ?>">

                                            <svg width="16" height="16"

                                                 viewBox="0 0 24 24"

                                                 fill="none" stroke="currentColor"

                                                 stroke-width="1.8"

                                                 stroke-linecap="round"

                                                 stroke-linejoin="round"

                                                 aria-hidden="true">

                                                <path d="M3 6h18"/>

                                                <path d="M8 6V4h8v2"/>

                                                <path d="m19 6-1 14H6L5 6"/>

                                                <path d="M10 11v5M14 11v5"/>

                                            </svg>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>

                            <td colspan="7">

                                <div class="journey-empty-state">

                                    <strong>No journeys found</strong>

                                    <p>Create a journey or adjust your search filters.</p>

                                    <a href="<?php echo e(route('admin.tool-journeys.create')); ?>"

                                       class="journey-button journey-button-primary">

                                        Create First Journey

                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <?php if($journeys->hasPages()): ?>

            <div class="journey-pagination">

                <?php echo e($journeys->links()); ?>


            </div>

        <?php endif; ?>

    </section>

</div>

<div class="journey-delete-modal" id="journeyDeleteModal" hidden>

    <div class="journey-delete-dialog"

         role="dialog"

         aria-modal="true"

         aria-labelledby="journeyDeleteTitle"

         aria-describedby="journeyDeleteMessage"

         tabindex="-1">

        <h2 id="journeyDeleteTitle">Delete journey?</h2>

        <p id="journeyDeleteMessage">

            Are you sure you want to delete this journey?

        </p>

        <div class="journey-delete-dialog-actions">

            <button type="button" id="journeyCancelDelete">

                Cancel

            </button>

            <button type="button"

                    class="journey-confirm-delete"

                    id="journeyConfirmDelete">

                Delete

            </button>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const selectAll = document.getElementById('journeySelectAll');

    const checkboxes = Array.from(

        document.querySelectorAll('.journey-row-checkbox')

    );

    const countLabel = document.getElementById('journeySelectionCount');

    const deleteSelected = document.getElementById('journeyDeleteSelected');

    const bulkForm = document.getElementById('journeyBulkDeleteForm');

    const modal = document.getElementById('journeyDeleteModal');

    const title = document.getElementById('journeyDeleteTitle');

    const message = document.getElementById('journeyDeleteMessage');

    const cancel = document.getElementById('journeyCancelDelete');

    const confirm = document.getElementById('journeyConfirmDelete');

    let pendingForm = null;

    let previousFocus = null;

    function selectedCheckboxes() {

        return checkboxes.filter(function (checkbox) {

            return checkbox.checked;

        });

    }

    function updateSelection() {

        const selectedCount = selectedCheckboxes().length;

        countLabel.textContent = selectedCount + ' selected';

        deleteSelected.disabled = selectedCount === 0;

        selectAll.checked =

            checkboxes.length > 0 && selectedCount === checkboxes.length;

        selectAll.indeterminate =

            selectedCount > 0 && selectedCount < checkboxes.length;

        selectAll.disabled = checkboxes.length === 0;

    }

    selectAll.addEventListener('change', function () {

        checkboxes.forEach(function (checkbox) {

            checkbox.checked = selectAll.checked;

        });

        updateSelection();

    });

    checkboxes.forEach(function (checkbox) {

        checkbox.addEventListener('change', updateSelection);

    });

    function openModal(form, isBulk) {

        pendingForm = form;

        previousFocus = document.activeElement;

        if (isBulk) {

            const count = selectedCheckboxes().length;

            title.textContent = 'Delete selected journeys?';

            message.textContent =

                'You are about to delete ' + count +

                (count === 1 ? ' selected journey.' : ' selected journeys.') +

                ' Journeys with linked steps or tools may not be deletable.';

        } else {

            title.textContent = 'Delete journey?';

            message.textContent =

                'Are you sure you want to delete "' +

                (form.dataset.deleteName || 'this journey') +

                '"? Journeys with linked steps or tools may not be deletable.';

        }

        modal.hidden = false;

        document.body.style.overflow = 'hidden';

        cancel.focus();

    }

    function closeModal() {

        modal.hidden = true;

        document.body.style.overflow = '';

        pendingForm = null;

        if (previousFocus && previousFocus.focus) {

            previousFocus.focus();

        }

    }

    document.querySelectorAll('.journey-single-delete-form').forEach(function (form) {

        form.addEventListener('submit', function (event) {

            event.preventDefault();

            openModal(form, false);

        });

    });

    deleteSelected.addEventListener('click', function () {

        if (selectedCheckboxes().length) {

            openModal(bulkForm, true);

        }

    });

    cancel.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {

        if (event.target === modal) {

            closeModal();

        }

    });

    document.addEventListener('keydown', function (event) {

        if (modal.hidden) {

            return;

        }

        if (event.key === 'Escape') {

            closeModal();

        }

        if (event.key === 'Tab') {

            const focusable = [cancel, confirm];

            if (event.shiftKey && document.activeElement === focusable[0]) {

                event.preventDefault();

                focusable[1].focus();

            } else if (!event.shiftKey && document.activeElement === focusable[1]) {

                event.preventDefault();

                focusable[0].focus();

            }

        }

    });

    confirm.addEventListener('click', function () {

        if (!pendingForm) {

            return;

        }

        confirm.disabled = true;

        cancel.disabled = true;

        confirm.textContent = 'Deleting...';

        HTMLFormElement.prototype.submit.call(pendingForm);

    });

    document.querySelectorAll('.journey-clickable-row').forEach(function (row) {

        function navigate() {

            if (row.dataset.url) {

                window.location.href = row.dataset.url;

            }

        }

        row.addEventListener('click', function (event) {

            if (event.target.closest(

                'a, button, form, input, select, textarea, label'

            )) {

                return;

            }

            navigate();

        });

        row.addEventListener('keydown', function (event) {

            if (

                (event.key === 'Enter' || event.key === ' ') &&

                event.target === row

            ) {

                event.preventDefault();

                navigate();

            }

        });

    });

    updateSelection();

});

</script>

<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/admin/tool-journeys/index.blade.php ENDPATH**/ ?>