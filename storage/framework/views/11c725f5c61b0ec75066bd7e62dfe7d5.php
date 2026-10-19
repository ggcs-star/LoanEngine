<?php $__env->startSection('title', 'Tools Management'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/admin-tools.css')); ?>">
<style>
    .tools-select-column {
        width: 44px;
        text-align: center;
    }

    .tools-select-checkbox {
        width: 16px;
        height: 16px;
        accent-color: var(--primary, #222);
        cursor: pointer;
    }

    .tools-clickable-row {
        cursor: pointer;
    }

    .tools-clickable-row:focus-visible {
        outline: 2px solid var(--primary, #222);
        outline-offset: -2px;
    }

    .tools-bulk-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border, #e2e2e2);
        background: var(--surface, #fff);
    }

    .tools-bulk-left,
    .tools-bulk-right {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tools-selection-count {
        color: var(--muted, #777);
        font-size: 13px;
    }

    .tools-icon-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: 1px solid var(--border, #e2e2e2);
        border-radius: 7px;
        background: var(--surface, #fff);
        color: var(--text, #222);
        text-decoration: none;
        cursor: pointer;
        font-size: 16px;
    }

    .tools-icon-button:hover {
        background: var(--surface-hover, #f5f5f5);
    }

    .tools-icon-button-danger {
        color: var(--danger, #333);
    }

    .tools-delete-modal[hidden] {
        display: none !important;
    }

    .tools-delete-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgb(0 0 0 / 45%);
    }

    .tools-delete-dialog {
        width: 100%;
        max-width: 440px;
        padding: 26px;
        border: 1px solid var(--border, #e2e2e2);
        border-radius: 14px;
        background: var(--surface, #fff);
        color: var(--text, #222);
    }

    .tools-delete-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        margin-bottom: 18px;
        border: 1px solid var(--border, #e2e2e2);
        border-radius: 12px;
        font-size: 23px;
    }

    .tools-delete-dialog h2 {
        margin: 0 0 9px;
        color: var(--text, #222);
        font-size: 20px;
    }

    .tools-delete-dialog p {
        margin: 0;
        color: var(--muted, #777);
        font-size: 14px;
        line-height: 1.65;
    }

    .tools-delete-dialog-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 25px;
    }

    .tools-delete-dialog-actions button {
        min-height: 40px;
        padding: 9px 17px;
        border: 1px solid var(--border, #e2e2e2);
        border-radius: 8px;
        background: var(--surface, #fff);
        color: var(--text, #222);
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .tools-delete-dialog-actions .tools-confirm-delete {
        border-color: var(--primary, #222);
        background: var(--primary, #222);
        color: #fff;
    }

    .tools-delete-dialog-actions button:disabled {
        cursor: wait;
        opacity: .65;
    }

    @media (max-width: 640px) {
        .tools-delete-dialog {
            padding: 21px;
        }

        .tools-bulk-toolbar {
            padding: 12px;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="tools-page">

    <div class="tools-page-header">
        <div>
            <h1 class="tools-page-title">Tools Management</h1>
            <p class="tools-page-description">
                Manage tools and connect them to relevant journeys.
            </p>
        </div>

        <a href="<?php echo e(route('admin.tools.create')); ?>"
           class="tools-button tools-button-primary">
            <span aria-hidden="true">+</span>
            Add Tool
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="tools-alert tools-alert-success" role="status">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="tools-alert tools-alert-error" role="alert">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="tools-alert tools-alert-error" role="alert">
            <strong>Please check the following:</strong>
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="tools-stats">
        <div class="tools-stat-card">
            <span class="tools-stat-label">Total Tools</span>
            <strong class="tools-stat-value">
                <?php echo e(number_format($statistics['total'] ?? 0)); ?>

            </strong>
        </div>

        <div class="tools-stat-card">
            <span class="tools-stat-label">Active</span>
            <strong class="tools-stat-value">
                <?php echo e(number_format($statistics['active'] ?? 0)); ?>

            </strong>
        </div>

        <div class="tools-stat-card">
            <span class="tools-stat-label">Planned</span>
            <strong class="tools-stat-value">
                <?php echo e(number_format($statistics['planned'] ?? 0)); ?>

            </strong>
        </div>

        <div class="tools-stat-card">
            <span class="tools-stat-label">Inactive</span>
            <strong class="tools-stat-value">
                <?php echo e(number_format($statistics['inactive'] ?? 0)); ?>

            </strong>
        </div>
    </div>

    <section class="tools-panel">
        <div class="tools-panel-header">
            <div>
                <h2 class="tools-panel-title">All Tools</h2>
                <p class="tools-panel-description">
                    Search and manage your existing tools.
                </p>
            </div>
        </div>

        <form method="GET"
              action="<?php echo e(route('admin.tools.index')); ?>"
              class="tools-filters">

            <div class="tools-field tools-filter-search">
                <label for="search">Search</label>
                <input
                    id="search"
                    type="search"
                    name="search"
                    value="<?php echo e($filters['search'] ?? ''); ?>"
                    placeholder="Search by name, slug or description"
                >
            </div>

            <div class="tools-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    <?php $__currentLoopData = [
                        'active' => 'Active',
                        'planned' => 'Planned',
                        'inactive' => 'Inactive'
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>"
                            <?php if(($filters['status'] ?? '') === $value): echo 'selected'; endif; ?>>
                            <?php echo e($label); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="tools-field">
                <label for="journey_id">Journey</label>
                <select id="journey_id" name="journey_id">
                    <option value="">All journeys</option>
                    <?php $__currentLoopData = $journeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $journey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($journey->id); ?>"
                            <?php if(
                                (string) ($filters['journey_id'] ?? '') ===
                                (string) $journey->id
                            ): echo 'selected'; endif; ?>>
                            <?php echo e($journey->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="tools-filter-actions">
                <button type="submit"
                        class="tools-button tools-button-primary">
                    Apply Filters
                </button>

                <a href="<?php echo e(route('admin.tools.index')); ?>"
                   class="tools-button tools-button-secondary">
                    Reset
                </a>
            </div>
        </form>

        
        <form id="bulkDeleteForm"
              method="POST"
              action="<?php echo e(route('admin.tools.bulk-destroy')); ?>">
            <?php echo csrf_field(); ?>
        </form>

        <div class="tools-bulk-toolbar">
            <div class="tools-bulk-left">
                <span class="tools-selection-count"
                      id="toolsSelectionCount"
                      aria-live="polite">
                    0 selected
                </span>
            </div>

            <div class="tools-bulk-right">
                <button type="button"
                        id="toolsDeleteSelected"
                        class="tools-button tools-button-secondary"
                        disabled>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="tools-table-wrapper">
            <table class="tools-table">
                <thead>
                    <tr>
                        <th scope="col" class="tools-select-column">
                            <input type="checkbox"
                                   id="toolsSelectAll"
                                   class="tools-select-checkbox"
                                   aria-label="Select all tools on this page">
                        </th>
                        <th scope="col">Tool</th>
                        <th scope="col">Category / Type</th>
                        <th scope="col">Journeys</th>
                        <th scope="col">Status</th>
                        <th scope="col">Toolkit Home</th>
                        <th scope="col">Order</th>
                        <th scope="col" class="tools-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="tools-clickable-row"
                            data-url="<?php echo e(route('admin.tools.show', $tool)); ?>"
                            tabindex="0"
                            aria-label="Open details for <?php echo e($tool->name); ?>">

                            <td class="tools-select-column">
                                <input type="checkbox"
                                       class="tools-select-checkbox tools-row-checkbox"
                                       name="ids[]"
                                       value="<?php echo e($tool->id); ?>"
                                       form="bulkDeleteForm"
                                       aria-label="Select <?php echo e($tool->name); ?>">
                            </td>

                            <td>
                                <div class="tools-name-cell">
                                    <strong><?php echo e($tool->name); ?></strong>
                                    <span><?php echo e($tool->slug); ?></span>
                                    <p><?php echo e($tool->short_description); ?></p>
                                </div>
                            </td>

                            <td>
                                <div><?php echo e($tool->category); ?></div>
                                <span class="tools-muted"><?php echo e($tool->type); ?></span>
                            </td>

                            <td>
                                <?php $__empty_2 = true; $__currentLoopData = $tool->journeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $journey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                    <span class="tools-tag">
                                        <?php echo e($journey->name); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                    <span class="tools-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="tools-status tools-status-<?php echo e($tool->status); ?>">
                                    <?php echo e(ucfirst($tool->status)); ?>

                                </span>
                            </td>

                            <td>
                                <?php echo e($tool->show_on_toolkit_home ? 'Yes' : 'No'); ?>

                            </td>

                            <td><?php echo e($tool->display_order); ?></td>

                            <td>
                                <div class="tools-row-actions">
                                    <a href="<?php echo e(route('admin.tools.edit', $tool)); ?>"
                                       class="tools-icon-button"
                                       title="Edit tool"
                                       aria-label="Edit <?php echo e($tool->name); ?>">
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
                                          action="<?php echo e(route('admin.tools.destroy', $tool)); ?>"
                                          class="tools-single-delete-form"
                                          data-delete-name="<?php echo e($tool->name); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>

                                        <button type="submit"
                                                class="tools-icon-button tools-icon-button-danger"
                                                title="Delete tool"
                                                aria-label="Delete <?php echo e($tool->name); ?>">
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
                            <td colspan="8">
                                <div class="tools-empty-state">
                                    <strong>No tools found</strong>
                                    <p>
                                        Add a tool or adjust your filters to see results.
                                    </p>
                                    <a href="<?php echo e(route('admin.tools.create')); ?>"
                                       class="tools-button tools-button-primary">
                                        Add First Tool
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($tools->hasPages()): ?>
            <div class="tools-pagination">
                <?php echo e($tools->links()); ?>

            </div>
        <?php endif; ?>
    </section>
</div>


<div class="tools-delete-modal"
     id="toolsDeleteModal"
     hidden>
    <div class="tools-delete-dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="toolsDeleteTitle"
         aria-describedby="toolsDeleteMessage"
         tabindex="-1">

        <div class="tools-delete-icon" aria-hidden="true">
            <svg width="23" height="23"
                 viewBox="0 0 24 24"
                 fill="none" stroke="currentColor"
                 stroke-width="1.8"
                 stroke-linecap="round"
                 stroke-linejoin="round">
                <path d="M12 9v4"/>
                <path d="M12 17h.01"/>
                <path d="M10.3 3.9 1.9 18.2A2 2 0 0 0 3.6 21h16.8a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
            </svg>
        </div>

        <h2 id="toolsDeleteTitle">Delete tool?</h2>

        <p id="toolsDeleteMessage">
            Are you sure you want to delete this tool? This action cannot be undone.
        </p>

        <div class="tools-delete-dialog-actions">
            <button type="button" id="toolsCancelDelete">
                Cancel
            </button>

            <button type="button"
                    class="tools-confirm-delete"
                    id="toolsConfirmDelete">
                Delete
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('toolsSelectAll');
    const rowCheckboxes = Array.from(
        document.querySelectorAll('.tools-row-checkbox')
    );
    const selectedCount = document.getElementById('toolsSelectionCount');
    const deleteSelectedButton = document.getElementById('toolsDeleteSelected');
    const bulkForm = document.getElementById('bulkDeleteForm');

    const modal = document.getElementById('toolsDeleteModal');
    const modalDialog = modal.querySelector('.tools-delete-dialog');
    const modalTitle = document.getElementById('toolsDeleteTitle');
    const modalMessage = document.getElementById('toolsDeleteMessage');
    const cancelButton = document.getElementById('toolsCancelDelete');
    const confirmButton = document.getElementById('toolsConfirmDelete');

    let pendingForm = null;
    let previousFocus = null;

    function selectedCheckboxes() {
        return rowCheckboxes.filter(function (checkbox) {
            return checkbox.checked;
        });
    }

    function updateSelection() {
        const selected = selectedCheckboxes();
        const count = selected.length;

        selectedCount.textContent = count + (count === 1 ? ' selected' : ' selected');
        deleteSelectedButton.disabled = count === 0;

        if (selectAll) {
            selectAll.checked = count > 0 && count === rowCheckboxes.length;
            selectAll.indeterminate = count > 0 && count < rowCheckboxes.length;
            selectAll.disabled = rowCheckboxes.length === 0;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowCheckboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });

            updateSelection();
        });
    }

    rowCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateSelection);
    });

    function openDeleteModal(form, isBulk) {
        pendingForm = form;
        previousFocus = document.activeElement;

        if (isBulk) {
            const count = selectedCheckboxes().length;

            modalTitle.textContent = 'Delete selected tools?';
            modalMessage.textContent =
                'You are about to delete ' + count +
                (count === 1 ? ' selected tool.' : ' selected tools.') +
                ' This action cannot be undone.';
        } else {
            const toolName = form.dataset.deleteName || 'this tool';

            modalTitle.textContent = 'Delete tool?';
            modalMessage.textContent =
                'Are you sure you want to delete "' + toolName +
                '"? This action cannot be undone.';
        }

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        cancelButton.focus();
    }

    function closeDeleteModal() {
        modal.hidden = true;
        document.body.style.overflow = '';
        pendingForm = null;

        if (previousFocus && typeof previousFocus.focus === 'function') {
            previousFocus.focus();
        }
    }

    document.querySelectorAll('.tools-single-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            openDeleteModal(form, false);
        });
    });

    deleteSelectedButton.addEventListener('click', function () {
        if (selectedCheckboxes().length === 0) {
            return;
        }

        openDeleteModal(bulkForm, true);
    });

    cancelButton.addEventListener('click', closeDeleteModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (modal.hidden) {
            return;
        }

        if (event.key === 'Escape') {
            closeDeleteModal();
        }

        if (event.key === 'Tab') {
            const focusable = [cancelButton, confirmButton];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    confirmButton.addEventListener('click', function () {
        if (!pendingForm) {
            return;
        }

        const formToSubmit = pendingForm;

        confirmButton.disabled = true;
        cancelButton.disabled = true;
        confirmButton.textContent = 'Deleting...';

        // Use the browser's native form submission without re-triggering
        // the confirmation submit listener.
        HTMLFormElement.prototype.submit.call(formToSubmit);
    });

    document.querySelectorAll('.tools-clickable-row').forEach(function (row) {
        function navigateToDetails() {
            const url = row.dataset.url;

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

            navigateToDetails();
        });

        row.addEventListener('keydown', function (event) {
            if (
                (event.key === 'Enter' || event.key === ' ') &&
                event.target === row
            ) {
                event.preventDefault();
                navigateToDetails();
            }
        });
    });

    updateSelection();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\LoanEngine\resources\views/admin/tools/index.blade.php ENDPATH**/ ?>