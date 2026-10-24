
@extends('layouts.admin')

@section('title', 'Calculator Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calculators.css') }}?v={{ filemtime(public_path('css/calculators.css')) }}">
@endpush

@section('content')
<div class="calculator-page">

    
    <div class="page-heading">
        <div>
            <h1>Calculator Management</h1>
            <p>Manage calculators separately from Resources and Planning.</p>
        </div>

        <a href="{{ route('admin.calculators.create') }}"
           class="btn-primary-dark">
            + Add Calculator
        </a>
    </div>

    
    @if(session('success'))
        <div class="calculator-alert calculator-alert-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="calculator-alert calculator-alert-error" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="calculator-alert calculator-alert-error" role="alert">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    
    <div class="calculator-card calculator-filter-card">
        <form method="GET"
              action="{{ route('admin.calculators.index') }}"
              class="calculator-filter-form">

            <div class="calculator-filter-search">
                <label for="calculator-search" class="form-label">
                    Search Calculators
                </label>

                <input
                    type="text"
                    id="calculator-search"
                    name="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Search by calculator name or slug..."
                >
            </div>

            <div class="calculator-filter-status">
                <label for="calculator-status" class="form-label">
                    Status
                </label>

                <select name="status"
                        id="calculator-status"
                        class="form-select">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>
                        Active
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="calculator-filter-actions">
                <button type="submit" class="btn-primary-dark">
                    Apply Filters
                </button>

                <a href="{{ route('admin.calculators.index') }}"
                   class="btn-secondary-light">
                    Reset
                </a>
            </div>
        </form>
    </div>

    
    <div class="calculator-card calculator-table-card">

        <div class="calculator-table-heading">
            <div>
                <h2>All Calculators</h2>
                <p>Select records to delete multiple calculators.</p>
            </div>

            <span class="calculator-count">
                {{ $calculators->total() }} total
            </span>
        </div>

        <form id="bulk-delete-form"
              method="POST"
              action="{{ route('admin.calculators.bulk-destroy') }}">
            @csrf
            @method('DELETE')

            <div class="calculator-bulk-toolbar">
                <label class="calculator-select-all">
                    <input type="checkbox" id="select-all-calculators">
                    Select all on this page
                </label>

                <button type="submit"
                        id="bulk-delete-button"
                        class="btn-danger-light"
                        disabled>
                    Delete Selected
                </button>
            </div>

            <div class="calculator-table-wrap">
                <table class="calculator-table">
                    <thead>
                        <tr>
                            <th class="checkbox-column">Select</th>
                            <th>Calculator</th>
                            <th>Slug</th>
                            <th>Journeys</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($calculators as $calculator)
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        class="calculator-checkbox"
                                        name="ids[]"
                                        value="{{ $calculator->id }}"
                                        aria-label="Select {{ $calculator->name }}"
                                    >
                                </td>

                                <td>
                                    <div class="calculator-name">
                                        {{ $calculator->name }}
                                    </div>

                                    @if($calculator->short_description)
                                        <div class="calculator-description">
                                            {{ \Illuminate\Support\Str::limit($calculator->short_description, 75) }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <code class="calculator-slug">
                                        {{ $calculator->slug }}
                                    </code>
                                </td>

                                <td>
                                    <div class="calculator-journeys">
                                        @forelse($calculator->journeys as $journey)
                                            <span class="journey-badge">
                                                {{ $journey->title ?? $journey->name ?? 'Journey' }}
                                            </span>
                                        @empty
                                            <span class="muted-text">Not assigned</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td>
                                    @if($calculator->status === 'active')
                                        <span class="status-badge status-active">
                                            Active
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>{{ $calculator->display_order }}</td>

                                <td>
                                    <div class="calculator-row-actions">
                                        <a
                                            href="{{ route('admin.calculators.edit', $calculator) }}"
                                            class="btn-table-edit"
                                        >
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            class="btn-table-delete"
                                            data-delete-url="{{ route('admin.calculators.destroy', $calculator) }}"
                                            data-calculator-name="{{ $calculator->name }}"
                                            onclick="confirmCalculatorDelete(this)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="calculator-empty">
                                    <div class="empty-state">
                                        <h3>No calculators found</h3>
                                        <p>Try changing your filters or create a new calculator.</p>

                                        <a href="{{ route('admin.calculators.create') }}"
                                           class="btn-primary-dark">
                                            + Add Calculator
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        
        @if($calculators->hasPages())
            <div class="calculator-pagination">
                {{ $calculators->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>


<form id="single-delete-form" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>


<div id="calculator-delete-modal"
     class="calculator-modal"
     aria-hidden="true">

    <div class="calculator-modal-backdrop"
         onclick="closeCalculatorDeleteModal()"></div>

    <div class="calculator-modal-dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="calculator-delete-title">

        <div class="calculator-modal-icon">!</div>

        <h2 id="calculator-delete-title">Delete Calculator?</h2>

        <p id="calculator-delete-message">
            Are you sure you want to delete this calculator?
        </p>

        <p class="calculator-modal-warning">
            This action cannot be undone.
        </p>

        <div class="calculator-modal-actions">
            <button type="button"
                    class="btn-secondary-light"
                    onclick="closeCalculatorDeleteModal()">
                Cancel
            </button>

            <button type="button"
                    class="btn-danger"
                    id="confirm-single-delete">
                Yes, Delete
            </button>
        </div>
    </div>
</div>


<div id="calculator-bulk-modal"
     class="calculator-modal"
     aria-hidden="true">

    <div class="calculator-modal-backdrop"
         onclick="closeCalculatorBulkModal()"></div>

    <div class="calculator-modal-dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="calculator-bulk-title">

        <div class="calculator-modal-icon">!</div>

        <h2 id="calculator-bulk-title">Delete Selected Calculators?</h2>

        <p id="calculator-bulk-message">
            Are you sure you want to delete the selected calculators?
        </p>

        <p class="calculator-modal-warning">
            This action cannot be undone.
        </p>

        <div class="calculator-modal-actions">
            <button type="button"
                    class="btn-secondary-light"
                    onclick="closeCalculatorBulkModal()">
                Cancel
            </button>

            <button type="button"
                    class="btn-danger"
                    id="confirm-bulk-delete">
                Yes, Delete Selected
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all-calculators');
    const checkboxes = Array.from(
        document.querySelectorAll('.calculator-checkbox')
    );
    const bulkButton = document.getElementById('bulk-delete-button');
    const bulkForm = document.getElementById('bulk-delete-form');

    const singleModal = document.getElementById('calculator-delete-modal');
    const bulkModal = document.getElementById('calculator-bulk-modal');
    const singleForm = document.getElementById('single-delete-form');

    let pendingDeleteUrl = null;

    function selectedCheckboxes() {
        return checkboxes.filter(checkbox => checkbox.checked);
    }

    function updateSelection() {
        const selected = selectedCheckboxes();

        bulkButton.disabled = selected.length === 0;

        selectAll.checked =
            checkboxes.length > 0 &&
            selected.length === checkboxes.length;

        selectAll.indeterminate =
            selected.length > 0 &&
            selected.length < checkboxes.length;
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });

        updateSelection();
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelection);
    });

    window.confirmCalculatorDelete = function (button) {
        pendingDeleteUrl = button.dataset.deleteUrl;

        const calculatorName = button.dataset.calculatorName || 'this calculator';

        document.getElementById('calculator-delete-message').textContent =
            'Are you sure you want to delete "' + calculatorName + '"?';

        singleModal.classList.add('is-open');
        singleModal.setAttribute('aria-hidden', 'false');
    };

    window.closeCalculatorDeleteModal = function () {
        singleModal.classList.remove('is-open');
        singleModal.setAttribute('aria-hidden', 'true');
        pendingDeleteUrl = null;
    };

    document.getElementById('confirm-single-delete')
        .addEventListener('click', function () {
            if (!pendingDeleteUrl) return;

            singleForm.action = pendingDeleteUrl;
            singleForm.submit();
        });

    bulkForm.addEventListener('submit', function (event) {
        const selected = selectedCheckboxes();

        if (selected.length === 0) {
            event.preventDefault();
            return;
        }

        event.preventDefault();

        document.getElementById('calculator-bulk-message').textContent =
            'You have selected ' + selected.length +
            ' calculator(s). Do you want to delete them?';

        bulkModal.classList.add('is-open');
        bulkModal.setAttribute('aria-hidden', 'false');
    });

    window.closeCalculatorBulkModal = function () {
        bulkModal.classList.remove('is-open');
        bulkModal.setAttribute('aria-hidden', 'true');
    };

    document.getElementById('confirm-bulk-delete')
        .addEventListener('click', function () {
            bulkForm.submit();
        });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            window.closeCalculatorDeleteModal();
            window.closeCalculatorBulkModal();
        }
    });

    updateSelection();
});
</script>
@endsection
