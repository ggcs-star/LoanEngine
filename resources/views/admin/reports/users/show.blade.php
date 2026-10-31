
@extends('layouts.admin')

@section('title', 'User Analytics')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-reports.css') }}">
@endpush

@section('content')
@php
    $tabs = [
        'overview' => 'Overview',
        'education' => 'Education',
        'tools' => 'Tools',
        'calculators' => 'Calculators',
        'calendar' => 'Calendar',
        'timeline' => 'Activity Timeline',
    ];

    $recentActivities = $recentActivities ?? collect();
    $summary = $summary ?? [];
    $records = $records ?? null;
    $tab = $tab ?? 'overview';
@endphp

<div class="le-report-page">

    <a class="le-back-link" href="{{ route('admin.reports.activities') }}">
        ← Back to User Activity
    </a>

    <section class="le-profile-card">
        <div class="le-profile-avatar">
            {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
        </div>

        <div class="le-profile-copy">
            <h1>{{ $user->name }}</h1>
            <p>{{ $user->email }}</p>
            <small>
                User ID: {{ $user->id }}
                <span>•</span>
                Joined: {{ $user->created_at?->format('d M Y') ?? '—' }}
            </small>
        </div>

        <a class="le-button le-button-secondary"
           href="{{ url('/admin/users/'.$user->id) }}">
            View Profile ↗
        </a>

        <a class="le-button le-button-primary"
           href="{{ route('admin.reports.users.export', array_merge(
               ['user' => $user->id, 'tab' => $tab],
               request()->query()
           )) }}">
            ↓ Export Report
        </a>
    </section>

    <nav class="le-tabs" aria-label="User analytics sections">
        @foreach($tabs as $key => $label)
            <a
                class="{{ $tab === $key ? 'active' : '' }}"
                href="{{ route('admin.reports.users.show', array_merge(
                    ['user' => $user->id, 'tab' => $key],
                    request()->except('tab', 'page')
                )) }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <div class="le-kpi-grid le-kpi-six le-kpi-nine">

        <article class="le-kpi">
            <div>
                <small>Total Activities</small>
                <strong>{{ number_format($summary['total'] ?? 0) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Education Views</small>
                <strong>{{ number_format($summary['education'] ?? 0) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Tools Usage</small>
                <strong>{{ number_format($summary['tools'] ?? 0) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Calculator Usage</small>
                <strong>{{ number_format($summary['calculators'] ?? 0) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Calendar Events</small>
                <strong>{{ number_format($summary['calendar_total'] ?? 0) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Total Logins</small>
                <strong>{{ number_format($summary['total_logins'] ?? 0) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Failed Logins</small>
                <strong>{{ number_format($summary['failed_logins'] ?? 0) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Last Login</small>
                <strong class="le-kpi-last">
                    {{ !empty($summary['last_login'])
                        ? \Illuminate\Support\Carbon::parse($summary['last_login'])->diffForHumans()
                        : '—' }}
                </strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Last Active</small>
                <strong class="le-kpi-last">
                    {{ !empty($summary['last_active'])
                        ? \Illuminate\Support\Carbon::parse($summary['last_active'])->diffForHumans()
                        : '—' }}
                </strong>
            </div>
        </article>

    </div>

    @if($tab === 'overview')

        <div class="le-chart-grid">

            <section class="le-data-panel">
                <div class="le-panel-heading">
                    <div>
                        <h2>Activity Trend</h2>
                        <p>Daily tracked activity · Last 30 days</p>
                    </div>
                    <span class="le-chart-caption">Daily</span>
                </div>

                <div class="le-chart-area">
                    <canvas id="activityTrend"></canvas>
                </div>
            </section>

            <section class="le-data-panel">
                <div class="le-panel-heading">
                    <div>
                        <h2>Module Usage</h2>
                        <p>Activity distribution</p>
                    </div>
                </div>

                <div class="le-donut-layout">
                    <div class="le-donut-area">
                        <canvas id="moduleUsage"></canvas>
                    </div>

                    <div class="le-legend">
                        @foreach($breakdown as $item)
                            <div>
                                <i></i>
                                <span>{{ $item['label'] }}</span>
                                <b>{{ number_format($item['value']) }}</b>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

        </div>

        <div class="le-overview-grid">

            <section class="le-data-panel">
                <div class="le-panel-heading">
                    <div>
                        <h2>Recent Activity</h2>
                        <p>Latest tracked user events</p>
                    </div>
                    <a class="le-text-link"
                       href="{{ route('admin.reports.users.show', [
                           'user' => $user->id,
                           'tab' => 'timeline'
                       ]) }}">
                        View timeline →
                    </a>
                </div>

                <div class="le-overview-list">
                    @forelse($recentActivities as $activity)
                        <div class="le-activity-row">
                            <span class="le-activity-dot"></span>

                            <div>
                                <b>
                                    {{ str($activity->activity_type ?? 'Activity')->replace('_', ' ')->title() }}
                                </b>

                                <small>
                                    {{ str($activity->module ?? '')->replace('_', ' ')->title() }}
                                    @if(!empty($activity->subject_name))
                                        · {{ $activity->subject_name }}
                                    @elseif(!empty($activity->detail))
                                        · {{ $activity->detail }}
                                    @endif
                                </small>
                            </div>

                            <time>
                                {{ $activity->happened_at
                                    ? \Illuminate\Support\Carbon::parse($activity->happened_at)->diffForHumans()
                                    : '—' }}
                            </time>
                        </div>
                    @empty
                        <div class="le-empty">No activity recorded yet.</div>
                    @endforelse
                </div>
            </section>

            <section class="le-data-panel">
                <div class="le-panel-heading">
                    <div>
                        <h2>Account Security Activity</h2>
                        <p>Authentication summary</p>
                    </div>
                </div>

                <div class="le-summary-list">
                    <div>
                        <span>Successful logins</span>
                        <b>{{ number_format($summary['total_logins'] ?? 0) }}</b>
                    </div>

                    <div>
                        <span>Failed login attempts</span>
                        <b>{{ number_format($summary['failed_logins'] ?? 0) }}</b>
                    </div>

                    <div>
                        <span>Last successful login</span>
                        <b>
                            {{ !empty($summary['last_login'])
                                ? \Illuminate\Support\Carbon::parse($summary['last_login'])->format('d M Y, h:i A')
                                : '—' }}
                        </b>
                    </div>

                    <div>
                        <span>Completed lessons</span>
                        <b>{{ number_format($summary['completed_lessons'] ?? 0) }}</b>
                    </div>

                    <div>
                        <span>Cleared calendar events</span>
                        <b>{{ number_format($summary['calendar_cleared'] ?? 0) }}</b>
                    </div>

                    <div>
                        <span>Projected calendar events</span>
                        <b>{{ number_format($summary['calendar_projected'] ?? 0) }}</b>
                    </div>
                </div>
            </section>

        </div>

    @else

        <section class="le-filter-panel">
            <div class="le-filter-title">
                <div>
                    <h2>{{ $tabs[$tab] ?? 'Activity' }} Search</h2>
                    <p>Search and filter this user's records.</p>
                </div>
<!-- 
                <a class="le-button le-button-primary"
                   href="{{ route('admin.reports.users.export', array_merge(
                       ['user' => $user->id, 'tab' => $tab],
                       request()->query()
                   )) }}">
                    ↓ Export Report
                </a> -->
            </div>

            <form method="GET"
                  action="{{ route('admin.reports.users.show', ['user' => $user->id]) }}">

                <input type="hidden" name="tab" value="{{ $tab }}">

                <label class="le-search-field">
                    <span>⌕</span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search {{ strtolower($tabs[$tab] ?? 'activity') }}..."
                    >
                </label>

                <div class="le-filter-grid">

                    @if(in_array($tab, ['education', 'tools', 'timeline']))
                        <label>
                            Activity Type
                            <input
                                name="activity_type"
                                value="{{ $filters['activity_type'] ?? '' }}"
                                placeholder="e.g. lesson_viewed"
                            >
                        </label>
                    @endif

                    @if($tab === 'calendar')
                        <label>
                            Category
                            <select name="category">
                                <option value="">All Categories</option>

                                @foreach(['income', 'investment', 'loan_emi', 'fixed_bill', 'living_expense'] as $value)
                                    <option
                                        value="{{ $value }}"
                                        @selected(($filters['category'] ?? '') === $value)
                                    >
                                        {{ str($value)->replace('_', ' ')->title() }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            Payment Status
                            <select name="status">
                                <option value="">All Statuses</option>
                                <option value="projected" @selected(($filters['status'] ?? '') === 'projected')>
                                    Projected
                                </option>
                                <option value="cleared" @selected(($filters['status'] ?? '') === 'cleared')>
                                    Cleared
                                </option>
                            </select>
                        </label>
                    @endif

                    @if($tab === 'calculators')
                        <label>
                            Event Type
                            <input
                                name="activity_type"
                                value="{{ $filters['activity_type'] ?? '' }}"
                                placeholder="calculated"
                            >
                        </label>
                    @endif

                    <label>
                        From Date
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                    </label>

                    <label>
                        To Date
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                    </label>

                </div>

                <div class="le-filter-actions">
                    <button type="submit" class="le-button le-button-primary">
                        Apply Filters
                    </button>

                    <a class="le-button le-button-secondary"
                       href="{{ route('admin.reports.users.show', [
                           'user' => $user->id,
                           'tab' => $tab
                       ]) }}">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="le-data-panel">
            <div class="le-panel-heading">
                <div>
                    <h2>{{ $tabs[$tab] ?? 'Activity' }} Records</h2>
                    <p>{{ number_format($records?->total() ?? 0) }} records found</p>
                </div>
                <span class="le-live-label">● Report Data</span>
            </div>

            <div class="le-table-wrap">

                @if($tab === 'calendar')

                    <table class="le-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Event Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($records ?? [] as $record)
                                <tr>
                                    <td>{{ $record->description ?: '—' }}</td>
                                    <td>{{ str($record->category ?? '—')->replace('_', ' ')->title() }}</td>
                                    <td>{{ number_format((float) ($record->amount ?? 0), 2) }}</td>
                                    <td>{{ str($record->payment_status ?? '—')->title() }}</td>
                                    <td>{{ $record->event_date ? \Illuminate\Support\Carbon::parse($record->event_date)->format('d M Y') : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="le-empty">No calendar records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($tab === 'calculators')

                    <table class="le-table">
                        <thead>
                            <tr>
                                <th>Calculator</th>
                                <th>Event Type</th>
                                <th>Status</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($records ?? [] as $record)
                                <tr>
                                    <td>{{ $record->calculator?->name ?? ('Calculator #' . $record->calculator_id) }}</td>
                                    <td>{{ str($record->event_type ?? 'calculated')->replace('_', ' ')->title() }}</td>
                                    <td>{{ $record->status ?? '—' }}</td>
                                    <td>{{ $record->created_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="le-empty">No calculator records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                @else

                    <table class="le-table">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Module</th>
                                <th>Subject Type</th>
                                <th>Subject Name / Details</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($records ?? [] as $record)
                                <tr>
                                    <td>{{ str($record->activity_type ?? 'Activity')->replace('_', ' ')->title() }}</td>
                                    <td>
                                        <span class="le-module-pill">
                                            {{ str($record->module ?? $tab)->replace('_', ' ')->title() }}
                                        </span>
                                    </td>
                                    <td>{{ str($record->subject_type ?? '—')->replace('_', ' ')->title() }}</td>
                                    <td class="le-subject-name">
                                        {{ $record->subject_name ?? $record->detail ?? '—' }}

                                        @if(($record->module ?? '') === 'authentication')
                                            <small class="le-auth-details">
                                                @if(!empty($record->ip_address))
                                                    IP: {{ $record->ip_address }}
                                                @endif
                                                @if(!empty($record->browser))
                                                    · {{ $record->browser }}
                                                @endif
                                                @if(!empty($record->operating_system))
                                                    · {{ $record->operating_system }}
                                                @endif
                                                @if(!empty($record->device_type))
                                                    · {{ $record->device_type }}
                                                @endif
                                                @if(!empty($record->status_code))
                                                    · Status: {{ $record->status_code }}
                                                @endif
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $record->happened_at
                                            ? \Illuminate\Support\Carbon::parse($record->happened_at)->format('d M Y, h:i A')
                                            : ($record->created_at?->format('d M Y, h:i A') ?? '—') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="le-empty">No activity records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @endif

            </div>

            @if($records instanceof \Illuminate\Contracts\Pagination\Paginator)
                <div class="le-pagination">
                    <span class="le-pagination-info">
                        Showing
                        <strong>{{ $records->firstItem() ?? 0 }}</strong>
                        to
                        <strong>{{ $records->lastItem() ?? 0 }}</strong>
                        of
                        <strong>{{ $records->total() }}</strong>
                    </span>

                    <div class="le-pagination-controls">
                        @if($records->onFirstPage())
                            <span class="le-page-control disabled">Previous</span>
                        @else
                            <a class="le-page-control" href="{{ $records->previousPageUrl() }}">Previous</a>
                        @endif

                        @foreach($records->getUrlRange(
                            max(1, $records->currentPage() - 2),
                            min($records->lastPage(), $records->currentPage() + 2)
                        ) as $page => $url)
                            <a
                                class="le-page-number {{ $records->currentPage() === $page ? 'active' : '' }}"
                                href="{{ $url }}"
                            >
                                {{ $page }}
                            </a>
                        @endforeach

                        @if($records->hasMorePages())
                            <a class="le-page-control" href="{{ $records->nextPageUrl() }}">Next</a>
                        @else
                            <span class="le-page-control disabled">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>

    @endif

</div>
@endsection

@if($tab === 'overview')
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const trendCanvas = document.getElementById('activityTrend');
                const moduleCanvas = document.getElementById('moduleUsage');

                if (typeof Chart === 'undefined') {
                    return;
                }

                const chartData = @json($chart);
                const breakdown = @json($breakdown);

                if (trendCanvas) {
                    new Chart(trendCanvas, {
                        type: 'line',
                        data: {
                            labels: chartData.labels || [],
                            datasets: [
                                {
                                    label: 'Education',
                                    data: chartData.series?.education || [],
                                    tension: 0.35
                                },
                                {
                                    label: 'Tools',
                                    data: chartData.series?.tools || [],
                                    tension: 0.35
                                },
                                {
                                    label: 'Calculators',
                                    data: chartData.series?.calculators || [],
                                    tension: 0.35
                                },
                                {
                                    label: 'Calendar',
                                    data: chartData.series?.calendar || [],
                                    tension: 0.35
                                },
                                {
                                    label: 'Authentication',
                                    data: chartData.series?.authentication || [],
                                    tension: 0.35
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }

                if (moduleCanvas) {
                    new Chart(moduleCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: breakdown.map(item => item.label),
                            datasets: [{
                                data: breakdown.map(item => item.value),
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endif
