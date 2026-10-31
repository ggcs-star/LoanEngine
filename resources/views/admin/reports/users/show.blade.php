
@extends('layouts.admin')

@section('title', 'User Analytics')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-reports.css') }}?v={{ filemtime(public_path('css/admin-reports.css')) }}">
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

    $tab = $tab ?? 'overview';
    $summary = $summary ?? [];
@endphp

<div class="le-report-page">

    <a class="le-back-link" href="{{ route('admin.reports.activities') }}">← Back to User Activity</a>

    <section class="le-profile-card">
        <div class="le-profile-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</div>
        <div class="le-profile-copy">
            <span class="le-eyebrow">USER REPORT / PROFILE</span>
            <h1>{{ $user->name }}</h1>
            <p>{{ $user->email }}</p>
            <small>User ID: {{ $user->id }} <span>•</span> Joined {{ $user->created_at?->format('d M Y') ?? '—' }}</small>
        </div>
        <div class="le-profile-actions">
            <a class="le-button le-button-secondary" href="{{ url('/admin/users/'.$user->id) }}">View Profile ↗</a>
            <a href="{{ route('admin.reports.users.export', array_merge(['user' => $user->id, 'tab' => $tab], request()->query())) }}" class="le-button le-button-primary">↓ Export Report</a>
        </div>
    </section>

    <nav class="le-tabs" aria-label="User analytics sections">
        @foreach($tabs as $key => $label)
            <a class="{{ $tab === $key ? 'active' : '' }}"
               href="{{ route('admin.reports.users.show', array_merge(['user' => $user->id, 'tab' => $key], request()->except('tab', 'page'))) }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <section class="le-kpi-grid le-kpi-six">
        <article class="le-kpi"><span class="le-kpi-icon">◉</span><div><small>Total Activities</small><strong>{{ number_format($summary['total'] ?? 0) }}</strong></div></article>
        <article class="le-kpi"><span class="le-kpi-icon">▤</span><div><small>Education Views</small><strong>{{ number_format($summary['education'] ?? 0) }}</strong></div></article>
        <article class="le-kpi"><span class="le-kpi-icon">⌘</span><div><small>Tools Usage</small><strong>{{ number_format($summary['tools'] ?? 0) }}</strong></div></article>
        <article class="le-kpi"><span class="le-kpi-icon">∑</span><div><small>Calculator Usage</small><strong>{{ number_format($summary['calculators'] ?? 0) }}</strong></div></article>
        <article class="le-kpi"><span class="le-kpi-icon">▦</span><div><small>Calendar Events</small><strong>{{ number_format($summary['calendar_total'] ?? 0) }}</strong></div></article>
        <article class="le-kpi"><span class="le-kpi-icon">◷</span><div><small>Last Active</small><strong class="le-kpi-last">{{ !empty($summary['last_active']) ? \Illuminate\Support\Carbon::parse($summary['last_active'])->diffForHumans() : '—' }}</strong></div></article>
    </section>

    @if($tab === 'overview')

        <div class="le-chart-grid">
            <section class="le-data-panel">
                <div class="le-panel-heading">
                    <div><h2>Activity Trend</h2><p>Daily tracked activity · Last 30 days</p></div>
                    <span class="le-chart-caption">Last 30 days</span>
                </div>
                <div class="le-chart-area"><canvas id="activityTrend"></canvas></div>
            </section>

            <section class="le-data-panel">
                <div class="le-panel-heading"><div><h2>Module Usage</h2><p>Share of recorded activities</p></div></div>
                <div class="le-donut-layout">
                    <div class="le-donut-area"><canvas id="moduleDonut"></canvas></div>
                    <div class="le-legend">
                        @foreach($breakdown as $item)
                            <div><i></i><span>{{ $item['label'] }}</span><b>{{ number_format($item['value']) }}</b></div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>

        <div class="le-overview-grid">
            <section class="le-data-panel">
                <div class="le-panel-heading">
                    <div><h2>Recent Activity</h2><p>Latest activity across available modules</p></div>
                    <a href="{{ route('admin.reports.users.show', ['user' => $user->id, 'tab' => 'timeline']) }}" class="le-text-link">View all →</a>
                </div>
                <div class="le-overview-list">
                    @php
                        $recent = \App\Models\UserActivityLog::where('user_id', $user->id)->latest('created_at')->limit(6)->get();
                    @endphp
                    @forelse($recent as $r)
                        <div class="le-activity-row">
                            <span class="le-activity-dot"></span>
                            <div>
                                <b>{{ str($r->activity_type)->replace('_', ' ')->title() }}</b>
                                <small>{{ ucfirst($r->module) }} · {{ str($r->subject_type ?? 'Activity')->replace('_', ' ')->title() }}</small>
                            </div>
                            <time>{{ $r->created_at?->format('d M, h:i A') }}</time>
                        </div>
                    @empty
                        <div class="le-empty-state"><strong>No activity yet</strong><span>No activity log entries found.</span></div>
                    @endforelse
                </div>
            </section>

            <section class="le-data-panel">
                <div class="le-panel-heading">
                    <div><h2>Calendar Summary</h2><p>Financial events recorded for this user</p></div>
                    <a href="{{ route('admin.reports.users.show', ['user' => $user->id, 'tab' => 'calendar']) }}" class="le-text-link">View all →</a>
                </div>
                @php($calendar = \App\Models\FinancialEvent::where('user_id', $user->id)->get())
                <div class="le-summary-list">
                    <div><span>Total Events</span><b>{{ $calendar->count() }}</b></div>
                    <div><span>Projected</span><b>{{ $calendar->where('payment_status', 'projected')->count() }}</b></div>
                    <div><span>Cleared</span><b>{{ $calendar->where('payment_status', 'cleared')->count() }}</b></div>
                    <div><span>Total Amount</span><b>₹ {{ number_format((float) $calendar->sum('amount'), 2) }}</b></div>
                </div>
            </section>
        </div>

        <div class="le-three-grid">
            <section class="le-data-panel le-mini-panel">
                <div class="le-panel-heading"><h2>Education Progress</h2><a href="{{ route('admin.reports.users.show', ['user' => $user->id, 'tab' => 'education']) }}" class="le-text-link">View all →</a></div>
                <strong>{{ number_format($summary['completed_lessons'] ?? 0) }}</strong>
                <small>Lessons completed</small>
            </section>
            <section class="le-data-panel le-mini-panel">
                <div class="le-panel-heading"><h2>Tools Usage</h2><a href="{{ route('admin.reports.users.show', ['user' => $user->id, 'tab' => 'tools']) }}" class="le-text-link">View all →</a></div>
                <strong>{{ number_format($summary['tools'] ?? 0) }}</strong>
                <small>Recorded tool and journey activity</small>
            </section>
            <section class="le-data-panel le-mini-panel">
                <div class="le-panel-heading"><h2>Calculator Usage</h2><a href="{{ route('admin.reports.users.show', ['user' => $user->id, 'tab' => 'calculators']) }}" class="le-text-link">View all →</a></div>
                <strong>{{ number_format($summary['calculators'] ?? 0) }}</strong>
                <small>Recorded calculations</small>
            </section>
        </div>

    @else

        <section class="le-filter-panel">
            <div class="le-panel-heading">
                <div><h2>{{ $tabs[$tab] ?? 'Activity' }} Records</h2><p>Search and filter this user's records.</p></div>
                <a class="le-button le-button-primary" href="{{ route('admin.reports.users.export', array_merge(['user' => $user->id, 'tab' => $tab], request()->query())) }}">↓ Export Excel</a>
            </div>

            <form method="GET" action="{{ route('admin.reports.users.show', ['user' => $user->id]) }}">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <div class="le-filter-grid">
                    <label class="le-field le-field-search"><span>Search</span><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search records..."></label>

                    @if(in_array($tab, ['education', 'tools', 'timeline']))
                        <label class="le-field"><span>Activity Type</span><input name="activity_type" value="{{ $filters['activity_type'] ?? '' }}" placeholder="e.g. lesson_viewed"></label>
                    @endif

                    @if($tab === 'calendar')
                        <label class="le-field"><span>Category</span>
                            <select name="category">
                                <option value="">All Categories</option>
                                @foreach(['income','investment','loan_emi','fixed_bill','living_expense'] as $v)
                                    <option value="{{ $v }}" @selected(($filters['category'] ?? '') === $v)>{{ str($v)->replace('_', ' ')->title() }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="le-field"><span>Payment Status</span>
                            <select name="status">
                                <option value="">All Statuses</option>
                                <option value="projected" @selected(($filters['status'] ?? '') === 'projected')>Projected</option>
                                <option value="cleared" @selected(($filters['status'] ?? '') === 'cleared')>Cleared</option>
                            </select>
                        </label>
                    @endif

                    @if($tab === 'calculators')
                        <label class="le-field"><span>Event Type</span><input name="activity_type" value="{{ $filters['activity_type'] ?? '' }}" placeholder="calculated"></label>
                    @endif

                    <label class="le-field"><span>From Date</span><input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
                    <label class="le-field"><span>To Date</span><input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
                </div>

                <div class="le-filter-actions">
                    <button class="le-button le-button-primary" type="submit">Apply Filters</button>
                    <a class="le-button le-button-secondary" href="{{ route('admin.reports.users.show', ['user' => $user->id, 'tab' => $tab]) }}">Reset</a>
                </div>
            </form>
        </section>

        @if($tab === 'timeline')
            @include('admin.reports.users.partials.timeline', ['timeline' => $records, 'user' => $user])
        @else
            <section class="le-data-panel">
                <div class="le-panel-heading">
                    <div><h2>{{ $tabs[$tab] ?? 'Activity' }} Records</h2><p>{{ number_format($records->total()) }} matching records</p></div>
                </div>

                <div class="le-table-wrap">
                    <table class="le-table">
                        @if($tab === 'calendar')
                            <thead><tr><th>Description</th><th>Category</th><th>Event Date</th><th>Amount</th><th>Status</th><th>Recurring</th><th>Reminder</th></tr></thead>
                            <tbody>
                            @forelse($records as $r)
                                <tr>
                                    <td>{{ $r->description }}</td>
                                    <td>{{ str($r->category)->replace('_', ' ')->title() }}</td>
                                    <td>{{ $r->event_date?->format('d M Y') }}</td>
                                    <td>₹ {{ number_format((float) $r->amount, 2) }}</td>
                                    <td><span class="le-module-pill">{{ ucfirst($r->payment_status) }}</span></td>
                                    <td>{{ $r->is_recurring ? 'Yes' : 'No' }}</td>
                                    <td>{{ $r->reminder_sent_at ? 'Sent' : ($r->reminder_time ?: '—') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="le-empty-cell">No matching calendar records.</td></tr>
                            @endforelse
                            </tbody>
                        @elseif($tab === 'calculators')
                            <thead><tr><th>Calculator</th><th>Event</th><th>Status</th><th>Inputs</th><th>Result</th><th>Date & Time</th></tr></thead>
                            <tbody>
                            @forelse($records as $r)
                                <tr>
                                    <td>{{ $r->calculator?->name ?? '—' }}</td>
                                    <td>{{ $r->event_type }}</td>
                                    <td>{{ $r->status }}</td>
                                    <td><code>{{ \Illuminate\Support\Str::limit(json_encode($r->inputs), 70) }}</code></td>
                                    <td><code>{{ \Illuminate\Support\Str::limit(json_encode($r->result), 70) }}</code></td>
                                    <td>{{ $r->created_at?->format('d M Y, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="le-empty-cell">No calculator records.</td></tr>
                            @endforelse
                            </tbody>
                        @else
                            <thead><tr><th>Activity</th><th>Module</th><th>Subject Type</th><th>Subject Name</th><th>Date & Time</th></tr></thead>
                            <tbody>
                            @forelse($records as $r)
                                <tr>
                                    <td>{{ str($r->activity_type)->replace('_', ' ')->title() }}</td>
                                    <td>{{ ucfirst($r->module) }}</td>
                                    <td>{{ str($r->subject_type ?? 'Activity')->replace('_', ' ')->title() }}</td>
                                    <td>{{ $r->subject_name ?? '—' }}</td>
                                    <td>{{ isset($r->happened_at) ? date('d M Y, h:i A', strtotime($r->happened_at)) : ($r->created_at?->format('d M Y, h:i A') ?? '—') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="le-empty-cell">No matching records.</td></tr>
                            @endforelse
                            </tbody>
                        @endif
                    </table>
                </div>

                @include('admin.reports.users.partials.pagination', ['records' => $records])
            </section>
        @endif
    @endif
</div>

@if($tab === 'overview')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) return;

    const labels = @json($chart['labels']);
    const series = @json($chart['series']);
    const theme = getComputedStyle(document.documentElement);
    const primary = theme.getPropertyValue('--primary').trim() || theme.getPropertyValue('--button-bg').trim();
    const muted = theme.getPropertyValue('--muted').trim();
    const border = theme.getPropertyValue('--border').trim();

    const trend = document.getElementById('activityTrend');
    if (trend) {
        new Chart(trend, {
            type: 'line',
            data: {
                labels,
                datasets: Object.keys(series).map(function (key) {
                    return {
                        label: key.charAt(0).toUpperCase() + key.slice(1),
                        data: series[key],
                        borderColor: primary,
                        backgroundColor: primary,
                        tension: 0.35,
                        fill: false,
                        pointRadius: 2,
                        borderWidth: 2
                    };
                })
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, color: muted } } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: muted }, grid: { color: border } },
                    x: { ticks: { color: muted }, grid: { display: false } }
                }
            }
        });
    }

    const donut = document.getElementById('moduleDonut');
    if (donut) {
        new Chart(donut, {
            type: 'doughnut',
            data: {
                labels: @json(collect($breakdown)->pluck('label')),
                datasets: [{
                    data: @json(collect($breakdown)->pluck('value')),
                    backgroundColor: [primary, muted, border, theme.getPropertyValue('--surface-hover').trim() || border],
                    borderWidth: 2,
                    borderColor: theme.getPropertyValue('--surface').trim()
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
@endif
@endsection
