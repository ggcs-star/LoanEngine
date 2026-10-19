
@extends('layouts.admin')

@section('title', 'Education Categories')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/education.css') }}">
@endpush

@section('content')
<div class="education-categories-page">

    {{-- Page Header --}}
    <div class="ec-page-header">
        <div>
            <h1>Education Categories</h1>
            <p>Manage categories and organize learning content.</p>
        </div>

        <a href="{{ route('admin.education.categories.create') }}"
           class="ec-btn ec-btn-primary">
            <span aria-hidden="true">+</span>
            Add Category
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="ec-alert ec-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="ec-alert ec-alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="ec-alert ec-alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="ec-card">

        {{-- Filters --}}
        <form method="GET"
              action="{{ route('admin.education.categories.index') }}"
              class="ec-filter-form">

            <div class="ec-search-wrap">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m16 16 4 4"></path>
                </svg>

                <input type="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by category name or slug..."
                       aria-label="Search categories">
            </div>

            <div class="ec-filter-field">
                <label for="ec-status">Status</label>
                <select name="status" id="ec-status">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>
                        Active
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="ec-filter-field">
                <label for="ec-sort">Sort By</label>
                <select name="sort" id="ec-sort">
                    <option value="latest" @selected(request('sort', 'latest') === 'latest')>
                        Latest
                    </option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>
                        Oldest
                    </option>
                    <option value="name" @selected(request('sort') === 'name')>
                        Name A–Z
                    </option>
                    <option value="order" @selected(request('sort') === 'order')>
                        Sort Order
                    </option>
                </select>
            </div>

            <button type="submit" class="ec-btn ec-btn-filter">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     aria-hidden="true">
                    <path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5Z"></path>
                </svg>
                Filter
            </button>

            @if (request()->hasAny(['search', 'status', 'sort']))
                <a href="{{ route('admin.education.categories.index') }}"
                   class="ec-reset-link">
                    Reset
                </a>
            @endif
        </form>

        {{-- Bulk Delete Form --}}
        <form id="ec-bulk-delete-form"
              method="POST"
              action="{{ route('admin.education.categories.bulk-destroy') }}">
            @csrf
            @method('DELETE')
        </form>

        {{-- Bulk Actions --}}
        <div class="ec-bulk-toolbar">
            <label class="ec-bulk-select-label">
                <input type="checkbox"
                       id="ec-select-all-toolbar"
                       aria-label="Select all visible categories">

                <span>Select all</span>
            </label>

            <span id="ec-selected-count" aria-live="polite">
                0 selected
            </span>

            <button type="button"
                    id="ec-delete-selected"
                    class="ec-bulk-delete-btn"
                    disabled>
                Delete Selected
            </button>
        </div>

        {{-- Categories Table --}}
        <div class="ec-table-wrap">
            <table class="ec-table">
                <thead>
                    <tr>
                        <th class="ec-checkbox-column">
                            <input type="checkbox"
                                   id="ec-select-all-table"
                                   aria-label="Select all categories">
                        </th>
                        <th>#</th>
                        <th>Category</th>
                        <th>Slug</th>
                        <th>Modules</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th class="ec-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($categories as $category)
                        <tr class="ec-category-row"
                            data-view-url="{{ route('admin.education.categories.show', $category) }}"
                            tabindex="0"
                            role="link"
                            aria-label="View category {{ $category->name }}">

                            <td class="ec-checkbox-column">
                                <input type="checkbox"
                                       class="ec-row-checkbox"
                                       name="ids[]"
                                       value="{{ $category->getKey() }}"
                                       form="ec-bulk-delete-form"
                                       aria-label="Select {{ $category->name }}">
                            </td>

                            <td class="ec-number">
                                {{ $categories->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="ec-category-info">
                                    @if ($category->thumbnail_path)
                                        <img
                                            src="{{ asset('storage/' . $category->thumbnail_path) }}"
                                            alt="{{ $category->name }}"
                                            class="ec-category-thumbnail"
                                            data-image-preview
                                            data-image-url="{{ asset('storage/' . $category->thumbnail_path) }}"
                                            data-image-name="{{ $category->name }}"
                                            tabindex="0"
                                            role="button"
                                            aria-label="Preview {{ $category->name }} image"
                                            loading="lazy">
                                    @else
                                        <div class="ec-category-placeholder"
                                             aria-label="No category image">
                                            <svg viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.7"
                                                 aria-hidden="true">
                                                <rect x="3" y="3" width="18" height="18" rx="3"></rect>
                                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                <path d="m21 15-5-5L5 21"></path>
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="ec-category-text">
                                        <span class="ec-category-name">
                                            {{ $category->name }}
                                        </span>

                                        <span class="ec-category-description">
                                            {{ $category->description ?: 'No description added' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="ec-slug">{{ $category->slug }}</span>
                            </td>

                            <td>
                                <span class="ec-module-count">
                                    {{ $category->modules_count ?? $category->modules()->count() }}
                                </span>
                            </td>

                            <td>
                                @if ($category->is_active)
                                    <span class="ec-status ec-status-active">
                                        <span></span> Active
                                    </span>
                                @else
                                    <span class="ec-status ec-status-inactive">
                                        <span></span> Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="ec-order">
                                {{ $category->sort_order }}
                            </td>

                            <td>
                                <div class="ec-actions">
                                    <a href="{{ route('admin.education.categories.edit', $category) }}"
                                       class="ec-action-btn ec-edit-btn"
                                       title="Edit category"
                                       aria-label="Edit {{ $category->name }}"
                                       data-row-action>
                                        <svg viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="1.8"
                                             aria-hidden="true">
                                            <path d="m14 6 4 4"></path>
                                            <path d="M4 20l4.5-1 11-11a2.1 2.1 0 0 0-3-3l-11 11L4 20Z"></path>
                                        </svg>
                                    </a>

                                    <form action="{{ route('admin.education.categories.destroy', $category) }}"
                                          method="POST"
                                          class="ec-delete-form"
                                          data-row-action>
                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                                class="ec-action-btn ec-delete-btn"
                                                data-delete-category
                                                data-category-name="{{ $category->name }}"
                                                aria-label="Delete {{ $category->name }}"
                                                title="Delete category">
                                            <svg viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 aria-hidden="true">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4h8v2"></path>
                                                <path d="m19 6-1 14H6L5 6"></path>
                                                <path d="M10 10v6M14 10v6"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="ec-empty-state">
                                    <div class="ec-empty-icon">
                                        <svg viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="1.6"
                                             aria-hidden="true">
                                            <rect x="3" y="4" width="18" height="16" rx="3"></rect>
                                            <path d="M8 9h8M8 13h5"></path>
                                        </svg>
                                    </div>

                                    <h3>No categories found</h3>
                                    <p>Try changing your search or filters, or create a new category.</p>

                                    <a href="{{ route('admin.education.categories.create') }}"
                                       class="ec-btn ec-btn-primary">
                                        <span aria-hidden="true">+</span>
                                        Add Category
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($categories->count())
            <div class="ec-table-footer">
                <p>
                    Showing <strong>{{ $categories->firstItem() }}</strong>
                    to <strong>{{ $categories->lastItem() }}</strong>
                    of <strong>{{ $categories->total() }}</strong>
                    {{ \Illuminate\Support\Str::plural('category', $categories->total()) }}
                </p>

                <div class="ec-pagination">
                    {{ $categories->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Image Preview Modal --}}
<div class="ec-image-modal" id="ec-image-modal" aria-hidden="true">
    <div class="ec-image-backdrop" data-close-image></div>

    <div class="ec-image-dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="ec-image-title"
         tabindex="-1">

        <button type="button"
                class="ec-image-close"
                data-close-image
                aria-label="Close image preview">
            &times;
        </button>

        <h3 id="ec-image-title">Category Image</h3>
        <img id="ec-preview-image" src="" alt="">
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="ec-delete-modal" id="ec-delete-modal" aria-hidden="true">
    <div class="ec-modal-backdrop" data-close-delete-modal></div>

    <div class="ec-modal-dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="ec-modal-title"
         aria-describedby="ec-modal-message"
         tabindex="-1">

        <div class="ec-modal-icon">
            <svg viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 aria-hidden="true">
                <path d="M3 6h18"></path>
                <path d="M8 6V4h8v2"></path>
                <path d="m19 6-1 14H6L5 6"></path>
                <path d="M10 10v6M14 10v6"></path>
            </svg>
        </div>

        <h2 id="ec-modal-title">Delete Category?</h2>

        <p id="ec-modal-message">
            Are you sure you want to delete this category? This action cannot be undone.
        </p>

        <div class="ec-modal-actions">
            <button type="button"
                    class="ec-modal-cancel"
                    data-close-delete-modal>
                Cancel
            </button>

            <button type="button"
                    class="ec-modal-confirm"
                    id="ec-confirm-delete">
                Delete Category
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('ec-delete-modal');
    const deleteConfirm = document.getElementById('ec-confirm-delete');
    const deleteMessage = document.getElementById('ec-modal-message');

    let activeDeleteForm = null;
    let deleteTrigger = null;
    let isBulkDelete = false;

    const bulkForm = document.getElementById('ec-bulk-delete-form');
    const bulkDeleteButton = document.getElementById('ec-delete-selected');
    const selectedCount = document.getElementById('ec-selected-count');

    const selectAllToolbar = document.getElementById('ec-select-all-toolbar');
    const selectAllTable = document.getElementById('ec-select-all-table');
    const rowCheckboxes = Array.from(
        document.querySelectorAll('.ec-row-checkbox')
    );

    function getSelectedCheckboxes() {
        return rowCheckboxes.filter(function (checkbox) {
            return checkbox.checked;
        });
    }

    function updateSelection() {
        const selected = getSelectedCheckboxes();
        const count = selected.length;
        const total = rowCheckboxes.length;

        selectedCount.textContent = count + (count === 1 ? ' selected' : ' selected');
        bulkDeleteButton.disabled = count === 0;

        [selectAllToolbar, selectAllTable].forEach(function (checkbox) {
            if (!checkbox) return;

            checkbox.checked = total > 0 && count === total;
            checkbox.indeterminate = count > 0 && count < total;
        });
    }

    function setAllCheckboxes(checked) {
        rowCheckboxes.forEach(function (checkbox) {
            checkbox.checked = checked;
        });

        updateSelection();
    }

    if (selectAllToolbar) {
        selectAllToolbar.addEventListener('change', function () {
            setAllCheckboxes(selectAllToolbar.checked);
        });
    }

    if (selectAllTable) {
        selectAllTable.addEventListener('change', function () {
            setAllCheckboxes(selectAllTable.checked);
        });
    }

    rowCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateSelection);
    });

    function openDeleteModal(form, name, trigger, bulk = false) {
        activeDeleteForm = form;
        deleteTrigger = trigger;
        isBulkDelete = bulk;

        if (bulk) {
            const count = getSelectedCheckboxes().length;

            deleteMessage.textContent =
                'Are you sure you want to delete ' + count +
                ' selected categor' + (count === 1 ? 'y' : 'ies') +
                '? Categories with linked modules will be skipped.';
            deleteConfirm.textContent = 'Delete Selected';
        } else {
            deleteMessage.textContent = name
                ? 'Are you sure you want to delete "' + name +
                  '"? This action cannot be undone.'
                : 'Are you sure you want to delete this category? This action cannot be undone.';

            deleteConfirm.textContent = 'Delete Category';
        }

        deleteModal.classList.add('is-open');
        deleteModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        deleteConfirm.disabled = false;

        deleteModal.querySelector('.ec-modal-cancel').focus();
    }

    function closeDeleteModal() {
        deleteModal.classList.remove('is-open');
        deleteModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        activeDeleteForm = null;
        isBulkDelete = false;

        if (deleteTrigger && document.contains(deleteTrigger)) {
            deleteTrigger.focus();
        }

        deleteTrigger = null;
    }

    document.querySelectorAll('[data-delete-category]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const form = button.closest('form');

            if (form) {
                openDeleteModal(
                    form,
                    button.dataset.categoryName,
                    button,
                    false
                );
            }
        });
    });

    bulkDeleteButton.addEventListener('click', function () {
        if (getSelectedCheckboxes().length === 0) return;

        openDeleteModal(
            bulkForm,
            null,
            bulkDeleteButton,
            true
        );
    });

    deleteModal.querySelectorAll('[data-close-delete-modal]').forEach(function (element) {
        element.addEventListener('click', closeDeleteModal);
    });

    deleteConfirm.addEventListener('click', function () {
        if (!activeDeleteForm) return;

        if (isBulkDelete && getSelectedCheckboxes().length === 0) {
            closeDeleteModal();
            return;
        }

        deleteConfirm.disabled = true;
        deleteConfirm.textContent = 'Deleting...';

        activeDeleteForm.submit();
    });

    // Image preview modal
    const imageModal = document.getElementById('ec-image-modal');
    const previewImage = document.getElementById('ec-preview-image');
    const imageTitle = document.getElementById('ec-image-title');

    let imageTrigger = null;

    function openImageModal(image) {
        imageTrigger = image;
        previewImage.src = image.dataset.imageUrl;
        previewImage.alt = image.dataset.imageName || 'Category image';
        imageTitle.textContent = image.dataset.imageName || 'Category Image';

        imageModal.classList.add('is-open');
        imageModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        imageModal.querySelector('.ec-image-close').focus();
    }

    function closeImageModal() {
        imageModal.classList.remove('is-open');
        imageModal.setAttribute('aria-hidden', 'true');
        previewImage.src = '';
        document.body.style.overflow = '';

        if (imageTrigger && document.contains(imageTrigger)) {
            imageTrigger.focus();
        }

        imageTrigger = null;
    }

    document.querySelectorAll('[data-image-preview]').forEach(function (image) {
        image.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            openImageModal(image);
        });

        image.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                event.stopPropagation();
                openImageModal(image);
            }
        });
    });

    imageModal.querySelectorAll('[data-close-image]').forEach(function (element) {
        element.addEventListener('click', closeImageModal);
    });

    // Row click navigates to category details.
    document.querySelectorAll('.ec-category-row[data-view-url]').forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (event.target.closest(
                'a, button, input, select, textarea, form, label, [data-row-action], [data-image-preview]'
            )) {
                return;
            }

            window.location.href = row.dataset.viewUrl;
        });

        row.addEventListener('keydown', function (event) {
            if (event.target !== row) return;

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                window.location.href = row.dataset.viewUrl;
            }
        });
    });

    // Close modals with Escape.
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        if (deleteModal.classList.contains('is-open')) {
            closeDeleteModal();
        } else if (imageModal.classList.contains('is-open')) {
            closeImageModal();
        }
    });

    updateSelection();
});
</script>
@endsection
