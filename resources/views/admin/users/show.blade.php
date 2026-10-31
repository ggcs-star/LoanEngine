
@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}?v=20261029">



<div class="ud-page">

    {{-- Page heading --}}
    <header class="ud-topbar">
        <div>
            <p class="ud-eyebrow">User Management</p>
            <h1 class="ud-title">User Details</h1>
            <p class="ud-description">
                View account information and calculator usage.
            </p>
        </div>

        <a href="{{ route('admin.users.index') }}" class="ud-back">
            ← Back to Users
        </a>
    </header>

    {{-- User profile --}}
    <section class="ud-card ud-profile">

        <div class="ud-identity">
            <div class="ud-avatar">
                {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}
            </div>

            <h2 class="ud-name">{{ $user->name ?: 'Unnamed User' }}</h2>

            <span class="ud-role">
                {{ ucfirst($user->role ?? 'User') }}
            </span>

            {{-- Connect this link to your actual edit route if available. --}}
            @if(\Illuminate\Support\Facades\Route::has('admin.users.edit'))
                <a href="{{ route('admin.users.edit', $user) }}" class="ud-edit">
                    ✎ &nbsp; Edit User
                </a>
            @endif
        </div>

        <div class="ud-profile-column">
            <h3 class="ud-column-heading">
                <span class="ud-column-icon">♟</span>
                Personal Information
            </h3>

            <div class="ud-info">
                <span class="ud-info-icon">♟</span>
                <div class="ud-info-content">
                    <span class="ud-info-label">Full Name</span>
                    <strong class="ud-info-value">{{ $user->name ?: '—' }}</strong>
                </div>
            </div>

            <div class="ud-info">
                <span class="ud-info-icon">✉</span>
                <div class="ud-info-content">
                    <span class="ud-info-label">Email Address</span>
                    <strong class="ud-info-value">{{ $user->email ?: '—' }}</strong>
                </div>
            </div>

            @if($hasMobile ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">☎</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Mobile Number</span>
                        <strong class="ud-info-value">{{ $user->mobile ?: '—' }}</strong>
                    </div>
                </div>
            @endif
        </div>

        <div class="ud-profile-column">
            <h3 class="ud-column-heading">
                <span class="ud-column-icon">⚙</span>
                Account Information
            </h3>

            @if($hasRole ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">♙</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Role</span>
                        <strong class="ud-info-value">
                            {{ ucfirst($user->role ?? 'User') }}
                        </strong>
                    </div>
                </div>
            @endif

            @if($hasVerified ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">✓</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Email Verification</span>
                        <span class="ud-verification {{ $user->email_verified_at ? 'ud-verified' : 'ud-unverified' }}">
                            {{ $user->email_verified_at ? 'Verified' : 'Not verified' }}
                        </span>
                    </div>
                </div>
            @endif

            <div class="ud-info">
                <span class="ud-info-icon">▦</span>
                <div class="ud-info-content">
                    <span class="ud-info-label">Account Created</span>
                    <strong class="ud-info-value">
                        {{ $user->created_at?->format('d M Y, h:i A') ?? '—' }}
                    </strong>
                </div>
            </div>

            <div class="ud-info">
                <span class="ud-info-icon">◷</span>
                <div class="ud-info-content">
                    <span class="ud-info-label">Last Login</span>
                    <strong class="ud-info-value">
                        {{ $user->last_login_at?->format('d M Y, h:i A') ?? '—' }}
                    </strong>
                </div>
            </div>
        </div>

        <div class="ud-profile-column">
            <h3 class="ud-column-heading">
                <span class="ud-column-icon">♦</span>
                Address Information
            </h3>

            @if($hasAddress ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">⌖</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Address</span>
                        <strong class="ud-info-value">{{ $user->address ?: '—' }}</strong>
                    </div>
                </div>
            @endif

            @if($hasCity ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">⌖</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">City</span>
                        <strong class="ud-info-value">{{ $user->city ?: '—' }}</strong>
                    </div>
                </div>
            @endif

            @if($hasEmploymentType ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">▤</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Employment Type</span>
                        <strong class="ud-info-value">
                            {{ $user->employment_type ?: '—' }}
                        </strong>
                    </div>
                </div>
            @endif

            @if($hasAnnualIncome ?? false)
                <div class="ud-info">
                    <span class="ud-info-icon">₹</span>
                    <div class="ud-info-content">
                        <span class="ud-info-label">Annual Income</span>
                        <strong class="ud-info-value">
                            {{ $user->annual_income !== null
                                ? '₹ ' . number_format((float) $user->annual_income)
                                : '—' }}
                        </strong>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Calculator activity statistics --}}
    <section class="ud-card ud-activity">
        <div class="ud-section-heading">
            <span class="ud-section-icon">▦</span>
            <div>
                <h3 class="ud-section-title">Calculator Activity</h3>
                <p class="ud-section-description">
                    Overview of calculator usage and results.
                </p>
            </div>
        </div>

        <div class="ud-stats">
            <div class="ud-stat ud-stat-total">
                <span class="ud-stat-icon">▦</span>
                <div>
                    <span class="ud-stat-label">Total Calculations</span>
                    <strong class="ud-stat-number">
                        {{ number_format($totalCalculations) }}
                    </strong>
                </div>
            </div>

            <div class="ud-stat ud-stat-completed">
                <span class="ud-stat-icon">✓</span>
                <div>
                    <span class="ud-stat-label">Completed Successfully</span>
                    <strong class="ud-stat-number">
                        {{ number_format($completedCalculations) }}
                    </strong>
                </div>
            </div>

            <div class="ud-stat ud-stat-failed">
                <span class="ud-stat-icon">×</span>
                <div>
                    <span class="ud-stat-label">Failed Calculations</span>
                    <strong class="ud-stat-number">
                        {{ number_format($failedCalculations) }}
                    </strong>
                </div>
            </div>
        </div>
    </section>

    {{-- Calculation history --}}
    <section class="ud-card ud-history">
        <div class="ud-history-header">
            <div class="ud-section-heading ud-section-heading-compact">
                <span class="ud-section-icon">◷</span>
                <div>
                    <h3 class="ud-section-title">Calculation History</h3>
                    <p class="ud-section-description">
                        View the calculator activities performed by this user.
                    </p>
                </div>
            </div>

            <div class="ud-filters">
                <label class="ud-search">
                    <span class="ud-search-icon">⌕</span>
                    <input
                        type="search"
                        id="udHistorySearch"
                        placeholder="Search calculator or event type..."
                        aria-label="Search calculation history"
                    >
                </label>

                <input
                    type="date"
                    id="udHistoryDate"
                    class="ud-date-input"
                    aria-label="Filter by date"
                    title="Filter by date"
                >

                <select id="udRowsPerPage" class="ud-filter-select" aria-label="Rows per page">
                    <option value="10">10 rows</option>
                    <option value="15" selected>15 rows</option>
                    <option value="25">25 rows</option>
                    <option value="50">50 rows</option>
                </select>
            </div>
        </div>

        <div class="ud-table-wrap">
            <table class="ud-table" id="udHistoryTable">
                <thead>
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Calculator</th>
                        <th>Event Type</th>
                        <th>Status</th>
                        <th>Date &amp; Time</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentCalculations as $usage)
                        @php
                            $status = strtolower((string) ($usage->status ?? 'unknown'));

                            $statusClass = match ($status) {
                                'completed' => 'ud-status-completed',
                                'failed' => 'ud-status-failed',
                                default => 'ud-status-other',
                            };

                            $calculatorName = $usage->calculator?->name
                                ?? 'Calculator #' . ($usage->calculator_id ?? '—');

                            $usageDate = $usage->created_at;

                            $inputData = is_array($usage->inputs)
                                ? $usage->inputs
                                : (is_object($usage->inputs)
                                    ? (array) $usage->inputs
                                    : $usage->inputs);

                            $resultData = is_array($usage->result)
                                ? $usage->result
                                : (is_object($usage->result)
                                    ? (array) $usage->result
                                    : $usage->result);
                        @endphp

                        <tr class="ud-data-row"
                            data-calculator="{{ strtolower($calculatorName) }}"
                            data-event="{{ strtolower((string) $usage->event_type) }}"
                            data-status="{{ $status }}"
                            data-date="{{ $usageDate?->format('Y-m-d') ?? '' }}">

                            <td class="ud-expand">
                                <details>
                                    <summary aria-label="Expand calculation details"></summary>
                                </details>
                            </td>

                            <td class="ud-row-id">#{{ $usage->id }}</td>

                            <td class="ud-calculator-name">
                                {{ $calculatorName }}
                            </td>

                            <td>
                                <span class="ud-event">
                                    {{ ucfirst($usage->event_type ?: '—') }}
                                </span>
                            </td>

                            <td>
                                <span class="ud-status {{ $statusClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td class="ud-date">
                                {{ $usageDate?->format('d M Y, h:i A') ?? '—' }}
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="ud-view-link ud-toggle-details"
                                >
                                    View Details →
                                </button>
                            </td>
                        </tr>

                        <tr class="ud-detail-row" hidden>
                            <td colspan="7">
                                <div class="ud-detail-panels">
                                    <div class="ud-detail-panel">
                                        <div class="ud-detail-panel-heading">
                                            ▤ &nbsp; Input Data
                                        </div>
                                        <div class="ud-detail-panel-body">
                                            <pre class="ud-json">{{ is_string($inputData)
                                                ? $inputData
                                                : (json_encode($inputData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'No input data available') }}</pre>
                                        </div>
                                    </div>

                                    <div class="ud-detail-panel">
                                        <div class="ud-detail-panel-heading">
                                            ▥ &nbsp; Result Data
                                        </div>
                                        <div class="ud-detail-panel-body">
                                            <pre class="ud-json">{{ is_string($resultData)
                                                ? $resultData
                                                : (json_encode($resultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'No result data available') }}</pre>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ud-no-data">
                                No calculator activity found for this user.
                            </td>
                        </tr>
                    @endforelse

                    <!-- @if($recentCalculations->isNotEmpty())
                        <tr id="udNoMatches" hidden>
                            <td colspan="7" class="ud-no-data">
                                No calculations match your search or date filter.
                            </td>
                        </tr>
                    @endif -->
                </tbody>
            </table>
        </div>

        <div class="ud-pagination">
            <span class="ud-pagination-info" id="udVisibleCount">
                Showing {{ $recentCalculations->firstItem() ?? 0 }}
                to {{ $recentCalculations->lastItem() ?? 0 }}
                of {{ $recentCalculations->total() }} results
            </span>

            {{ $recentCalculations->links() }}
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('udHistoryTable');

    if (!table) return;

    const search = document.getElementById('udHistorySearch');
    const dateFilter = document.getElementById('udHistoryDate');
    const rowsSelect = document.getElementById('udRowsPerPage');
    // const noMatches = document.getElementById('udNoMatches');
    const visibleCount = document.getElementById('udVisibleCount');

    const dataRows = Array.from(table.querySelectorAll('.ud-data-row'));
    const detailRows = dataRows.map(row => row.nextElementSibling);
    let expandedIds = new Set();

    function updateHistory() {
        const query = search.value.trim().toLowerCase();
        const selectedDate = dateFilter.value;
        const limit = Number(rowsSelect.value) || 15;

        const matchingRows = dataRows.filter(row => {
            const matchesSearch =
                row.dataset.calculator.includes(query) ||
                row.dataset.event.includes(query) ||
                row.dataset.status.includes(query) ||
                row.querySelector('.ud-row-id').textContent.toLowerCase().includes(query);

            const matchesDate =
                !selectedDate || row.dataset.date === selectedDate;

            return matchesSearch && matchesDate;
        });

        const visibleRows = new Set(matchingRows.slice(0, limit));

        dataRows.forEach((row, index) => {
            const detailRow = detailRows[index];
            const isVisible = visibleRows.has(row);

            row.hidden = !isVisible;
            detailRow.hidden = !isVisible || !expandedIds.has(row);

            const details = row.querySelector('.ud-expand details');
            if (details) details.open = expandedIds.has(row) && isVisible;
        });

        // if (noMatches) noMatches.hidden = matchingRows.length !== 0;

        const displayed = Math.min(matchingRows.length, limit);

        visibleCount.textContent = matchingRows.length
            ? `Showing ${displayed} of ${matchingRows.length} matching records on this page`
            : 'No matching records on this page';
    }

    dataRows.forEach((row, index) => {
        const detailRow = detailRows[index];
        const expandDetails = row.querySelector('.ud-expand details');
        const button = row.querySelector('.ud-toggle-details');

        function toggleDetails() {
            if (expandedIds.has(row)) {
                expandedIds.delete(row);
            } else {
                expandedIds.add(row);
            }

            updateHistory();
        }

        expandDetails?.addEventListener('toggle', function () {
            if (expandDetails.open) {
                expandedIds.add(row);
            } else {
                expandedIds.delete(row);
            }

            updateHistory();
        });

        button?.addEventListener('click', toggleDetails);
    });

    search.addEventListener('input', updateHistory);
    dateFilter.addEventListener('change', updateHistory);
    rowsSelect.addEventListener('change', updateHistory);

    updateHistory();
});
</script>
@endsection
