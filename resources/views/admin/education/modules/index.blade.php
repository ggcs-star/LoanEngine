@extends('layouts.admin')

@section('title', 'Education Modules')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/education-modules.css') }}">
@endpush
@section('content')
<div class="edu-modules-page">
    <div class="edu-modules-heading">
        <div>
            <h1>Education Modules</h1>
            <p>Manage learning modules, organize categories and control published content.</p>
        </div>
        <a href="{{ route('admin.education.modules.create') }}" class="edu-modules-primary-btn">
            <span aria-hidden="true">＋</span> Add Module
        </a>
    </div>

    @if (session('success'))
        <div class="edu-modules-alert edu-modules-alert-success" role="status">
            <span aria-hidden="true">✓</span> {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="edu-modules-alert edu-modules-alert-warning" role="status">
            <span aria-hidden="true">!</span> {{ session('warning') }}
        </div>
    @endif

    @if (session('error'))
        <div class="edu-modules-alert edu-modules-alert-error" role="alert">
            <span aria-hidden="true">!</span> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="edu-modules-alert edu-modules-alert-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="edu-modules-card" aria-label="Education modules list">
        <form method="GET" action="{{ route('admin.education.modules.index') }}" class="edu-modules-filter-form">
            <div class="edu-modules-search">
                <span class="edu-modules-search-icon" aria-hidden="true">⌕</span>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by module name or slug..."
                    aria-label="Search modules">
            </div>

            <div class="edu-modules-filter-field">
                <label for="module-category">Category</label>
                <select id="module-category" name="category">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="edu-modules-filter-field">
                <label for="module-status">Status</label>
                <select id="module-status" name="status">
                    <option value="">All Statuses</option>
                    <option value="published" @selected(request('status') === 'published')>Published</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                </select>
            </div>

            <div class="edu-modules-filter-field">
                <label for="module-sort">Sort By</label>
                <select id="module-sort" name="sort">
                    <option value="default" @selected(request('sort', 'default') === 'default')>Default Order</option>
                    <option value="latest" @selected(request('sort') === 'latest')>Latest</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                    <option value="title" @selected(request('sort') === 'title')>Module Name</option>
                </select>
            </div>

            <button type="submit" class="edu-modules-filter-btn">
                <span aria-hidden="true">⚲</span> Filter
            </button>

            @if (request()->hasAny(['search', 'category', 'status', 'sort']))
                <a href="{{ route('admin.education.modules.index') }}" class="edu-modules-reset-btn">Reset</a>
            @endif
        </form>

        <div class="edu-modules-bulk-toolbar">
            <div class="edu-modules-selection-info">
                <span class="edu-modules-selection-count" id="eduModulesSelectedCount">0</span>
                <span id="eduModulesSelectionText">modules selected</span>
            </div>

            <form id="eduModulesBulkDeleteForm" method="POST" action="{{ route('admin.education.modules.bulk-destroy') }}" class="edu-modules-bulk-form">
                @csrf
                @method('DELETE')
                <div id="eduModulesBulkIds"></div>
                <button type="submit" id="eduModulesBulkDeleteButton" class="edu-modules-bulk-delete-btn" disabled>
                    <span aria-hidden="true">▤</span> Delete Selected
                </button>
            </form>
        </div>

        <div class="edu-modules-table-wrap">
            <table class="edu-modules-table">
                <thead>
                    <tr>
                        <th class="edu-modules-check-cell">
                            <input type="checkbox" id="eduModulesSelectAll" aria-label="Select all modules on this page">
                        </th>
                        <th class="edu-modules-number-cell">#</th>
                        <th>MODULE</th>
                        <th>CATEGORY</th>
                        <th>SLUG</th>
                        <th>LESSONS</th>
                        <th>STATUS</th>
                        <th>ORDER</th>
                        <th class="edu-modules-actions-heading">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($modules as $module)
                        @php
                            $thumbnailUrl = $module->thumbnail_path
                                ? asset('storage/' . ltrim($module->thumbnail_path, '/'))
                                : null;
                            $showUrl = route('admin.education.modules.show', $module);
                        @endphp
                        <tr class="edu-modules-row" data-view-url="{{ $showUrl }}" tabindex="0" aria-label="View module {{ $module->title }}">
                            <td class="edu-modules-check-cell">
                                <input type="checkbox" class="edu-modules-row-checkbox" value="{{ $module->id }}" aria-label="Select {{ $module->title }}">
                            </td>
                            <td class="edu-modules-number-cell">{{ $modules->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="edu-modules-module-info">
                                    <button
                                        type="button"
                                        class="edu-modules-thumbnail-button"
                                        @if ($thumbnailUrl)
                                            data-image-url="{{ $thumbnailUrl }}"
                                        @else
                                            data-image-url=""
                                        @endif
                                        data-image-title="{{ $module->title }}"
                                        aria-label="Preview image for {{ $module->title }}"
                                        title="Preview thumbnail">
                                        @if ($thumbnailUrl)
                                            <img
                                                class="edu-modules-thumbnail"
                                                src="{{ $thumbnailUrl }}"
                                                alt="{{ $module->title }}"
                                                loading="lazy"
                                                onerror="this.style.display='none';"
                                            />
                                            <span class="edu-modules-thumbnail-placeholder" hidden>{{ strtoupper(mb_substr($module->title, 0, 1)) }}</span>
                                        @else
                                            <span class="edu-modules-thumbnail-placeholder">{{ strtoupper(mb_substr($module->title, 0, 1)) }}</span>
                                        @endif
                                    </button>
                                    <div class="edu-modules-module-text">
                                        <a href="{{ $showUrl }}" class="edu-modules-module-title edu-module-view-link">
                                            {{ $module->title }}
                                        </a>
                                        <span class="edu-modules-module-description">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($module->description ?? 'No description added'), 55) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="edu-modules-category-name">{{ $module->category?->name ?? 'Uncategorized' }}</span></td>
                            <td><span class="edu-modules-slug">{{ $module->slug }}</span></td>
                            <td><span class="edu-modules-count-badge">{{ $module->lessons_count }}</span></td>
                            <td>
                                <span class="edu-modules-status edu-modules-status-{{ $module->status }}">
                                    <span class="edu-modules-status-dot"></span>
                                    {{ ucfirst($module->status) }}
                                </span>
                            </td>
                            <td><span class="edu-modules-order">{{ $module->sort_order }}</span></td>
                            <td>
                                <div class="edu-modules-actions">
                                    <!-- <a href="{{ $showUrl }}" class="edu-modules-action-btn edu-modules-view-btn" title="View module" aria-label="View {{ $module->title }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                                    </a> -->
                                    <a href="{{ route('admin.education.modules.edit', $module) }}" class="edu-modules-action-btn edu-modules-edit-btn" title="Edit module" aria-label="Edit {{ $module->title }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"></path></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.education.modules.destroy', $module) }}" class="edu-modules-single-delete-form" data-module-title="{{ $module->title }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="edu-modules-action-btn edu-modules-delete-btn" title="Delete module" aria-label="Delete {{ $module->title }}">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 14H6L5 6"></path><path d="M10 11v5M14 11v5"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="edu-modules-empty-cell">
                                <div class="edu-modules-empty-state">
                                    <div class="edu-modules-empty-icon">▤</div>
                                    <h3>No modules found</h3>
                                    <p>
                                        @if (request()->hasAny(['search', 'category', 'status', 'sort']))
                                            Try changing your search or filter options.
                                        @else
                                            Start by creating your first education module.
                                        @endif
                                    </p>
                                    <a href="{{ route('admin.education.modules.create') }}" class="edu-modules-primary-btn">＋ Add Module</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="edu-modules-table-footer">
            <div class="edu-modules-results-text">
                @if ($modules->total() > 0)
                    Showing <strong>{{ $modules->firstItem() }}–{{ $modules->lastItem() }}</strong>
                    of <strong>{{ $modules->total() }}</strong>
                    {{ \Illuminate\Support\Str::plural('module', $modules->total()) }}
                @else
                    Showing 0 modules
                @endif
            </div>
            @if ($modules->hasPages())
                <div class="edu-modules-pagination">{{ $modules->onEachSide(1)->links() }}</div>
            @endif
        </div>
    </section>
</div>

{{-- Thumbnail preview modal --}}
<div class="edu-module-image-modal" id="eduModuleImageModal" aria-hidden="true" hidden>
    <button type="button" class="edu-module-image-modal__backdrop" data-close-image-modal aria-label="Close image preview"></button>
    <div class="edu-module-image-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="eduModuleImageModalTitle">
        <div class="edu-module-image-modal__header">
            <h2 id="eduModuleImageModalTitle">Module thumbnail</h2>
            <button type="button" class="edu-module-image-modal__close" data-close-image-modal aria-label="Close preview">&times;</button>
        </div>
        <div class="edu-module-image-modal__body">
            <img id="eduModuleImageModalImage" src="" alt="">
        </div>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div class="edu-modules-modal" id="eduModulesDeleteModal" aria-hidden="true">
    <div class="edu-modules-modal-backdrop" data-close-module-modal></div>
    <div class="edu-modules-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="eduModulesModalTitle">
        <div class="edu-modules-modal-icon">!</div>
        <h2 id="eduModulesModalTitle">Delete selected modules?</h2>
        <p id="eduModulesModalMessage">This action cannot be undone.</p>
        <div class="edu-modules-modal-actions">
            <button type="button" class="edu-modules-modal-cancel" id="eduModulesCancelDelete">Cancel</button>
            <button type="button" class="edu-modules-modal-confirm" id="eduModulesConfirmDelete">Yes, Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('eduModulesSelectAll');
    const rowCheckboxes = Array.from(document.querySelectorAll('.edu-modules-row-checkbox'));
    const selectedCount = document.getElementById('eduModulesSelectedCount');
    const selectionText = document.getElementById('eduModulesSelectionText');
    const bulkDeleteButton = document.getElementById('eduModulesBulkDeleteButton');
    const bulkDeleteForm = document.getElementById('eduModulesBulkDeleteForm');
    const bulkIdsContainer = document.getElementById('eduModulesBulkIds');

    const deleteModal = document.getElementById('eduModulesDeleteModal');
    const modalTitle = document.getElementById('eduModulesModalTitle');
    const modalMessage = document.getElementById('eduModulesModalMessage');
    const cancelButton = document.getElementById('eduModulesCancelDelete');
    const confirmButton = document.getElementById('eduModulesConfirmDelete');
    let pendingForm = null;
    let lastFocusedElement = null;

    function getSelectedCheckboxes() {
        return rowCheckboxes.filter((checkbox) => checkbox.checked);
    }

    function updateSelection() {
        const count = getSelectedCheckboxes().length;
        if (selectedCount) selectedCount.textContent = String(count);
        if (selectionText) selectionText.textContent = count === 1 ? 'module selected' : 'modules selected';
        if (bulkDeleteButton) bulkDeleteButton.disabled = count === 0;
        if (selectAll) {
            selectAll.checked = rowCheckboxes.length > 0 && rowCheckboxes.every((checkbox) => checkbox.checked);
            selectAll.indeterminate = count > 0 && count < rowCheckboxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowCheckboxes.forEach((checkbox) => { checkbox.checked = selectAll.checked; });
            updateSelection();
        });
    }
    rowCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelection));

    function openDeleteModal(title, message, form) {
        pendingForm = form;
        lastFocusedElement = document.activeElement;
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        deleteModal.classList.add('is-open');
        deleteModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        confirmButton.focus();
    }

    function closeDeleteModal() {
        deleteModal.classList.remove('is-open');
        deleteModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        pendingForm = null;
        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') lastFocusedElement.focus();
    }

    if (bulkDeleteForm) {
        bulkDeleteForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const selected = getSelectedCheckboxes();
            if (!selected.length) return;

            bulkIdsContainer.replaceChildren();
            selected.forEach((checkbox) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'module_ids[]';
                input.value = checkbox.value;
                bulkIdsContainer.appendChild(input);
            });

            const count = selected.length;
            openDeleteModal(
                'Delete ' + count + ' selected module' + (count === 1 ? '' : 's') + '?',
                'Modules containing lessons will be skipped. Other selected modules and their thumbnails will be deleted.',
                bulkDeleteForm
            );
        });
    }

    document.querySelectorAll('.edu-modules-single-delete-form').forEach((form) => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const title = form.dataset.moduleTitle || 'this module';
            openDeleteModal(
                'Delete this module?',
                'Are you sure you want to delete "' + title + '"? Modules containing lessons cannot be deleted.',
                form
            );
        });
    });

    if (cancelButton) cancelButton.addEventListener('click', closeDeleteModal);
    if (deleteModal) {
        deleteModal.querySelectorAll('[data-close-module-modal]').forEach((element) => {
            element.addEventListener('click', closeDeleteModal);
        });
    }
    if (confirmButton) {
        confirmButton.addEventListener('click', function () {
            if (pendingForm) HTMLFormElement.prototype.submit.call(pendingForm);
        });
    }

    // Open module detail page when clicking a non-interactive area of its row.
    document.querySelectorAll('.edu-modules-row[data-view-url]').forEach((row) => {
        function navigateToModule(event) {
            if (event.target.closest('a, button, input, select, textarea, form, label')) return;
            window.location.href = row.dataset.viewUrl;
        }
        row.addEventListener('click', navigateToModule);
        row.addEventListener('keydown', function (event) {
            if ((event.key === 'Enter' || event.key === ' ') && event.target === row) {
                event.preventDefault();
                window.location.href = row.dataset.viewUrl;
            }
        });
    });

    // Thumbnail preview modal.
    const imageModal = document.getElementById('eduModuleImageModal');
    const imageModalTitle = document.getElementById('eduModuleImageModalTitle');
    const imageModalImage = document.getElementById('eduModuleImageModalImage');
    let imageTrigger = null;

    function closeImageModal() {
        if (!imageModal) return;
        imageModal.hidden = true;
        imageModal.setAttribute('aria-hidden', 'true');
        imageModalImage.src = '';
        document.body.style.overflow = '';
        if (imageTrigger) imageTrigger.focus();
    }

    document.querySelectorAll('.edu-modules-thumbnail-button').forEach((button) => {
        button.addEventListener('click', function () {
            const imageUrl = button.dataset.imageUrl;
            if (!imageUrl) {
                window.location.href = button.closest('.edu-modules-row').dataset.viewUrl;
                return;
            }
            imageTrigger = button;
            imageModalTitle.textContent = button.dataset.imageTitle || 'Module thumbnail';
            imageModalImage.src = imageUrl;
            imageModalImage.alt = (button.dataset.imageTitle || 'Module') + ' thumbnail';
            imageModal.hidden = false;
            imageModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            imageModal.querySelector('[data-close-image-modal]').focus();
        });
    });

    if (imageModal) {
        imageModal.querySelectorAll('[data-close-image-modal]').forEach((element) => {
            element.addEventListener('click', closeImageModal);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            if (deleteModal && deleteModal.classList.contains('is-open')) closeDeleteModal();
            if (imageModal && !imageModal.hidden) closeImageModal();
        }
    });

    updateSelection();
});
</script>
@endpush
