
@extends('layouts.admin')

@section('title', 'User Activity')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-reports.css') }}">
@endpush

@section('content')

<div class="le-report-page">

    <div class="le-page-heading">
        <div>
            <div class="le-breadcrumb">Reports <span>›</span> User Activity</div>
            <h1>User Activity</h1>
            <p>Review every registered user and open their complete activity report.</p>
        </div>

        <a href="{{ route('admin.reports.export', request()->query()) }}"
            class="le-button le-button-primary">
                ↓ Export All Users
            </a>
    </div>

    <div class="le-kpi-grid">

        <article class="le-kpi">
            <span class="le-kpi-icon">♙</span>
            <div>
                <small>Total Users</small>
                <strong>{{ number_format($stats['total_users']) }}</strong>
                <em>All registered users</em>
            </div>
        </article>

        <article class="le-kpi">
            <span class="le-kpi-icon">◉</span>
            <div>
                <small>Active Users</small>
                <strong>{{ number_format($stats['active_users']) }}</strong>
                <em>Users with tracked activity</em>
            </div>
        </article>

        <article class="le-kpi">
            <span class="le-kpi-icon">⌁</span>
            <div>
                <small>Total Activities</small>
                <strong>{{ number_format($stats['total_activities']) }}</strong>
                <em>Recorded platform events</em>
            </div>
        </article>

        <article class="le-kpi">
            <span class="le-kpi-icon">◷</span>
            <div>
                <small>Avg. Activities / User</small>
                <strong>{{ number_format($stats['average'], 2) }}</strong>
                <em>Across all registered users</em>
            </div>
        </article>

    </div>

    <form class="le-filter-panel"
          method="GET"
          action="{{ route('admin.reports.activities') }}">

        <div class="le-filter-title">
            <div>
                <h2>Find Users</h2>
                <p>Search users and filter their activity summary.</p>
            </div>

            <a href="{{ route('admin.reports.activities') }}"
               class="le-text-link">
                Clear all
            </a>
        </div>

        <label class="le-search-field">
            <span>⌕</span>
            <input
                type="search"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="Search name, email, mobile or user ID..."
            >
        </label>

        <div class="le-filter-grid">

            <label>
                User Status
                <select name="user_status">
                    <option value="all"
                        @selected(($filters['user_status'] ?? 'all') === 'all')>
                        All Users
                    </option>

                    <option value="active"
                        @selected(($filters['user_status'] ?? '') === 'active')>
                        With Tracked Activity
                    </option>

                    <option value="inactive"
                        @selected(($filters['user_status'] ?? '') === 'inactive')>
                        No Tracked Activity
                    </option>
                </select>
            </label>

            <label>
                Module
                <select name="module">
                    <option value="">All Modules</option>

                    @foreach([
                        'education'=>'Education',
                        'tools'=>'Tools',
                        'calculators'=>'Calculators',
                        'calendar'=>'Calendar'
                    ] as $value=>$label)

                        <option value="{{ $value }}"
                            @selected(($filters['module'] ?? '') === $value)>
                            {{ $label }}
                        </option>

                    @endforeach
                </select>
            </label>

            <label>
                Activity Type
                <select name="activity_type">
                    <option value="">All Activities</option>

                    @foreach($activityTypes as $type)
                        <option value="{{ $type }}"
                            @selected(($filters['activity_type'] ?? '') === $type)>
                            {{ str($type)->replace('_',' ')->title() }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                From Date
                <input type="date"
                       name="from"
                       value="{{ $filters['from'] ?? '' }}">
            </label>

            <label>
                To Date
                <input type="date"
                       name="to"
                       value="{{ $filters['to'] ?? '' }}">
            </label>

            <label>
                Sort By
                <select name="sort">
                    <option value="latest"
                        @selected(($filters['sort'] ?? 'latest') === 'latest')>
                        Last Active
                    </option>

                    <option value="most_active"
                        @selected(($filters['sort'] ?? '') === 'most_active')>
                        Most Activities
                    </option>

                    <option value="name"
                        @selected(($filters['sort'] ?? '') === 'name')>
                        Name A–Z
                    </option>

                    <option value="oldest"
                        @selected(($filters['sort'] ?? '') === 'oldest')>
                        Oldest Joined
                    </option>
                </select>
            </label>

        </div>

        <div class="le-filter-actions">
            <button class="le-button le-button-primary" type="submit">
                Apply Filters
            </button>

            <a class="le-button le-button-secondary"
               href="{{ route('admin.reports.activities') }}">
                Reset
            </a>
        </div>

    </form>

    <section class="le-data-panel">

        <div class="le-panel-heading">
            <div>
                <h2>User Activity List</h2>
                <p>
                    {{ number_format($users->total()) }} users found.
                    Select any row to open the complete user report.
                </p>
            </div>

            <span class="le-live-label">● User Directory</span>
        </div>

        <div class="le-table-wrap">

            <table class="le-table le-user-directory-table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Education</th>
                        <th>Tools</th>
                        <th>Calculators</th>
                        <th>Calendar</th>
                        <th>Total Activities</th>
                        <th>Last Active</th>
                        <th>Report</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($users as $user)

                        <tr
                            class="le-clickable-user-row"
                            data-href="{{ route('admin.reports.users.show', ['user'=>$user->id]) }}"
                            tabindex="0"
                            role="link"
                            aria-label="Open report for {{ $user->name }}"
                        >

                            <td>
                                {{ $users->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="le-user-cell">

                                    <span class="le-avatar">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </span>

                                    <span>
                                        <b>{{ $user->name }}</b>
                                        <small>{{ $user->email }}</small>
                                        <small>{{ $user->mobile ?? '—' }}</small>
                                        <small class="le-user-id">
                                            User ID: {{ $user->id }}
                                        </small>
                                    </span>

                                </div>
                            </td>

                            <td>
                                <span class="le-count-pill">
                                    {{ number_format($user->education_count) }}
                                </span>
                            </td>

                            <td>
                                <span class="le-count-pill">
                                    {{ number_format($user->tools_count) }}
                                </span>
                            </td>

                            <td>
                                <span class="le-count-pill">
                                    {{ number_format($user->calculators_count) }}
                                </span>
                            </td>

                            <td>
                                <span class="le-count-pill">
                                    {{ number_format($user->calendar_count) }}
                                </span>
                            </td>

                            <td>
                                <strong>
                                    {{ number_format($user->total_activities) }}
                                </strong>
                            </td>

                            <td>
                                {{ $user->last_active
                                    ? \Illuminate\Support\Carbon::parse($user->last_active)->format('d M Y, h:i A')
                                    : 'Never tracked'
                                }}
                            </td>

                            <td>
                                <a class="le-button le-button-small le-button-secondary"
                                   href="{{ route('admin.reports.users.show', ['user'=>$user->id]) }}">
                                    View Report →
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="le-empty">
                                No users match the selected filters.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="le-pagination">
            <span>
                Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}
                of {{ number_format($users->total()) }} users
            </span>

            {{ $users->links() }}
        </div>

    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.le-clickable-user-row').forEach(function (row) {

        row.addEventListener('click', function (event) {

            if (event.target.closest('a, button, input, select, label')) {
                return;
            }

            window.location.href = row.dataset.href;
        });

        row.addEventListener('keydown', function (event) {

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                window.location.href = row.dataset.href;
            }

        });

    });

});
</script>

@endsection
