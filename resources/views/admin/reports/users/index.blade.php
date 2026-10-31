
@extends('layouts.admin')

@section('title', 'User Activity Reports')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-reports.css') }}?v={{ filemtime(public_path('css/admin-reports.css')) }}">
@endpush

@section('content')
<div class="le-report-page">

    <section class="le-report-heading">
        <div class="le-heading-copy">
            <div class="le-breadcrumb">
                <span>Reports</span>
                <span class="le-breadcrumb-separator">›</span>
                <strong>User Activity</strong>
            </div>
            <h1>User Activity</h1>
            <p>Review every registered user and open their complete activity report.</p>
        </div>

        <div class="le-heading-actions">
            <a href="{{ route('admin.reports.export', request()->query()) }}" class="le-report-button le-report-button-primary">
                <span class="le-button-symbol">↓</span>
                Export All Users
            </a>
        </div>
    </section>

    <section class="le-analytics-grid">

        <article class="le-report-card le-trend-card">
            <div class="le-card-heading">
                <div>
                    <h2>User Activity Overview</h2>
                    <p>Total platform activities in the last 30 days</p>
                </div>
                <span class="le-period-label">Last 30 days</span>
            </div>

            <div class="le-chart-container">
                <canvas id="leActivityTrend"></canvas>
            </div>

            <div class="le-chart-legend">
                <span class="le-legend-item"><i class="le-legend-education"></i>Education</span>
                <span class="le-legend-item"><i class="le-legend-tools"></i>Tools</span>
                <span class="le-legend-item"><i class="le-legend-calculators"></i>Calculators</span>
                <span class="le-legend-item"><i class="le-legend-calendar"></i>Calendar</span>
            </div>
        </article>

        <article class="le-report-card le-distribution-card">
            <div class="le-card-heading">
                <div>
                    <h2>Activity Distribution</h2>
                    <p>Share of total recorded activities</p>
                </div>
            </div>

            <div class="le-distribution-content">
                <div class="le-donut-container">
                    <canvas id="leActivityDistribution"></canvas>
                    <div class="le-donut-center">
                        <strong>{{ number_format($stats['total_activities']) }}</strong>
                        <span>Total Activities</span>
                    </div>
                </div>

                <div class="le-distribution-legend">
                    @foreach($moduleBreakdown as $item)
                        @php
                            $percentage = $stats['total_activities'] > 0
                                ? round(($item['value'] / $stats['total_activities']) * 100)
                                : 0;
                        @endphp

                        <div class="le-distribution-row">
                            <span class="le-distribution-name">
                                <i class="le-distribution-dot le-dot-{{ $item['key'] }}"></i>
                                {{ $item['label'] }}
                            </span>
                            <strong>{{ number_format($item['value']) }}</strong>
                            <small>{{ $percentage }}%</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>

    </section>

    <section class="le-filter-card">
        <div class="le-filter-heading">
            <div class="le-filter-heading-left">
                <span class="le-filter-icon">⌕</span>
                <div>
                    <h2>Find Users</h2>
                    <p>Search and filter their activity summary.</p>
                </div>
            </div>

            <a href="{{ route('admin.reports.activities') }}" class="le-clear-button">Clear all</a>
        </div>

        <form method="GET" action="{{ route('admin.reports.activities') }}" class="le-report-filter-form">

            <label class="le-search-field">
                <span class="le-search-icon">⌕</span>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search name, email, mobile or user ID..."
                >
            </label>

            <div class="le-filter-fields">

                <label class="le-report-field">
                    <span>User Status</span>
                    <select name="user_status">
                        <option value="all" @selected(($filters['user_status'] ?? 'all') === 'all')>All Users</option>
                        <option value="active" @selected(($filters['user_status'] ?? '') === 'active')>Active Users</option>
                        <option value="inactive" @selected(($filters['user_status'] ?? '') === 'inactive')>Inactive Users</option>
                    </select>
                </label>

                <label class="le-report-field">
                    <span>Module</span>
                    <select name="module">
                        <option value="">All Modules</option>
                        @foreach(['education','tools','calculators','calendar'] as $module)
                            <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>
                                {{ ucfirst($module) }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="le-report-field">
                    <span>Activity Type</span>
                    <select name="activity_type">
                        <option value="">All Activities</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type }}" @selected(($filters['activity_type'] ?? '') === $type)>
                                {{ str($type)->replace('_', ' ')->title() }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="le-report-field">
                    <span>From Date</span>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                </label>

                <label class="le-report-field">
                    <span>To Date</span>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                </label>

                <label class="le-report-field">
                    <span>Sort By</span>
                    <select name="sort">
                        <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Last Active</option>
                        <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest Registered</option>
                        <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name</option>
                        <option value="most_active" @selected(($filters['sort'] ?? '') === 'most_active')>Most Active</option>
                    </select>
                </label>

            </div>

            <div class="le-filter-footer">
                <button type="submit" class="le-report-button le-report-button-primary">
                    <span class="le-button-symbol">⌕</span>
                    Apply Filters
                </button>

                <a href="{{ route('admin.reports.activities') }}" class="le-report-button le-report-button-secondary">
                    Reset
                </a>
            </div>

        </form>
    </section>

    <section class="le-report-card le-user-list-card">

        <div class="le-user-list-heading">
            <div>
                <h2>User Activity List</h2>
                <p>{{ number_format($users->total()) }} users found. Select any row to open the complete user report.</p>
            </div>

            <span class="le-result-count">{{ number_format($users->total()) }} entries</span>
        </div>

        <div class="le-table-scroll">
            <table class="le-report-table">
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
                    @forelse($users as $index => $user)
                        <tr class="le-report-user-row" data-href="{{ route('admin.reports.users.show', $user->id) }}" tabindex="0">
                            <td class="le-row-number">{{ $users->firstItem() + $index }}</td>

                            <td>
                                <div class="le-report-user">
                                    <span class="le-report-avatar">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                                    </span>
                                    <span class="le-report-user-info">
                                        <strong>{{ $user->name }}</strong>
                                        <small>{{ $user->email }}</small>
                                    </span>
                                </div>
                            </td>

                            <td>{{ number_format($user->education_count) }}</td>
                            <td>{{ number_format($user->tools_count) }}</td>
                            <td>{{ number_format($user->calculators_count) }}</td>
                            <td>{{ number_format($user->calendar_count) }}</td>

                            <td>
                                <strong class="le-activity-total">{{ number_format($user->total_activities) }}</strong>
                            </td>

                            <td>
                                <span class="le-last-active">
                                    {{ $user->last_active
                                        ? \Illuminate\Support\Carbon::parse($user->last_active)->format('d M Y, h:i A')
                                        : 'Never' }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('admin.reports.users.show', $user->id) }}" class="le-view-report-button">
                                    <span>▤</span> View Report
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="le-report-empty">
                                    <strong>No users found</strong>
                                    <span>Try changing your filters or search query.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="le-table-footer">
            <span>
                Showing {{ $users->firstItem() ?? 0 }}
                to {{ $users->lastItem() ?? 0 }}
                of {{ $users->total() }} entries
            </span>

            {{ $users->links() }}
        </div>

    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($dashboardChart);
    const breakdown = @json($moduleBreakdown);

    const rootStyles = getComputedStyle(document.documentElement);

    const primary = rootStyles.getPropertyValue('--primary').trim()
        || rootStyles.getPropertyValue('--button-bg').trim();

    const muted = rootStyles.getPropertyValue('--muted').trim();
    const border = rootStyles.getPropertyValue('--border').trim();
    const surface = rootStyles.getPropertyValue('--surface').trim();

    const shades = [
        primary,
        `color-mix(in srgb, ${primary} 72%, ${muted})`,
        `color-mix(in srgb, ${primary} 48%, ${muted})`,
        `color-mix(in srgb, ${primary} 25%, ${muted})`
    ];

    const modules = [
        { key: 'education', label: 'Education' },
        { key: 'tools', label: 'Tools' },
        { key: 'calculators', label: 'Calculators' },
        { key: 'calendar', label: 'Calendar' },
        { key: 'authentication', label: 'Authentication' }
    ];

    const trendCanvas = document.getElementById('leActivityTrend');

    if (trendCanvas && window.Chart) {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: modules.map(function (module, index) {
                    return {
                        label: module.label,
                        data: chartData.series[module.key],
                        borderColor: shades[index],
                        backgroundColor: shades[index],
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        fill: false
                    };
                })
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: muted,
                            maxTicksLimit: 10,
                            font: { size: 10 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: muted,
                            font: { size: 10 }
                        },
                        grid: { color: border }
                    }
                }
            }
        });
    }

    const donutCanvas = document.getElementById('leActivityDistribution');

    if (donutCanvas && window.Chart) {
        new Chart(donutCanvas, {
            type: 'doughnut',
            data: {
                labels: breakdown.map(item => item.label),
                datasets: [{
                    data: breakdown.map(item => item.value),
                    backgroundColor: shades,
                    borderColor: surface,
                    borderWidth: 3,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    document.querySelectorAll('.le-report-user-row').forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (event.target.closest('a,button')) return;
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
