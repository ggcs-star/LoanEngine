
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
@endphp

<div class="le-report-page">

    <a class="le-back-link" href="{{ route('admin.reports.activities') }}">
        ← Back to User Activity
    </a>

    {{-- User Profile --}}
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
                Joined: {{ $user->created_at?->format('d M Y') }}
            </small>
        </div>

        <a class="le-button le-button-secondary"
           href="{{ url('/admin/users/'.$user->id) }}">
            View Profile ↗
        </a>

        <a href="{{ route('admin.reports.users.export', $user->id) }}"
           class="le-button le-button-primary">
            ↓ Export User Report
        </a>

    </section>

    {{-- Tabs --}}
    <nav class="le-tabs" aria-label="User analytics sections">
        @foreach($tabs as $key => $label)
            <a
                class="{{ $tab === $key ? 'active' : '' }}"
                href="{{ route(
                    'admin.reports.users.show',
                    array_merge(
                        ['user' => $user->id, 'tab' => $key],
                        request()->except('tab', 'page')
                    )
                ) }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </nav>

    {{-- KPI Cards --}}
    <div class="le-kpi-grid le-kpi-six">

        <article class="le-kpi">
            <div>
                <small>Total Activities</small>
                <strong>{{ number_format($summary['total']) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Education Views</small>
                <strong>{{ number_format($summary['education']) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Tools Usage</small>
                <strong>{{ number_format($summary['tools']) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Calculator Usage</small>
                <strong>{{ number_format($summary['calculators']) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Calendar Events</small>
                <strong>{{ number_format($summary['calendar_total']) }}</strong>
            </div>
        </article>

        <article class="le-kpi">
            <div>
                <small>Last Active</small>
                <strong class="le-kpi-last">
                    {{ $summary['last_active']
                        ? \Illuminate\Support\Carbon::parse($summary['last_active'])->diffForHumans()
                        : '—' }}
                </strong>
            </div>
        </article>

    </div>

    {{-- OVERVIEW TAB --}}
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
                        <p>Share of recorded activities</p>
                    </div>
                </div>

                <div class="le-donut-layout">
                    <div class="le-donut-area">
                        <canvas id="moduleDonut"></canvas>
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

            {{-- Recent Activity --}}
            <section class="le-data-panel">

                <div class="le-panel-heading">
                    <div>
                        <h2>Recent Activity</h2>
                        <p>Latest activity across available modules</p>
                    </div>

                    <a href="{{ route('admin.reports.users.show', [
                        'user' => $user->id,
                        'tab' => 'timeline'
                    ]) }}" class="le-text-link">
                        View all →
                    </a>
                </div>

                <div class="le-overview-list">

                    @php
                        $recent = \App\Models\UserActivityLog::where(
                            'user_id',
                            $user->id
                        )->latest()->limit(6)->get();
                    @endphp

                    @forelse($recent as $r)
                        <div class="le-activity-row">
                            <span class="le-activity-dot"></span>

                            <div>
                                <b>
                                    {{ str($r->activity_type)->replace('_', ' ')->title() }}
                                </b>

                                <small>
                                    {{ ucfirst($r->module) }}
                                    ·
                                    {{ str($r->subject_type ?? 'Activity')->replace('_', ' ')->title() }}
                                </small>
                            </div>

                            <time>
                                {{ $r->created_at?->format('d M, h:i A') }}
                            </time>
                        </div>
                    @empty
                        <div class="le-empty">
                            No activity log entries yet.
                        </div>
                    @endforelse

                </div>
            </section>

            {{-- Calendar Summary --}}
            <section class="le-data-panel">

                <div class="le-panel-heading">
                    <div>
                        <h2>Calendar Summary</h2>
                        <p>Financial events recorded for this user</p>
                    </div>

                    <a href="{{ route('admin.reports.users.show', [
                        'user' => $user->id,
                        'tab' => 'calendar'
                    ]) }}" class="le-text-link">
                        View all →
                    </a>
                </div>

                @php
                    $calendar = \App\Models\FinancialEvent::where(
                        'user_id',
                        $user->id
                    )->get();
                @endphp

                <div class="le-summary-list">
                    <div>
                        <span>Total Events</span>
                        <b>{{ $calendar->count() }}</b>
                    </div>

                    <div>
                        <span>Projected</span>
                        <b>{{ $calendar->where('payment_status', 'projected')->count() }}</b>
                    </div>

                    <div>
                        <span>Cleared</span>
                        <b>{{ $calendar->where('payment_status', 'cleared')->count() }}</b>
                    </div>

                    <div>
                        <span>Total Amount</span>
                        <b>₹ {{ number_format((float) $calendar->sum('amount'), 2) }}</b>
                    </div>
                </div>

            </section>

        </div>

        {{-- Overview Mini Cards --}}
        <div class="le-three-grid">

            <section class="le-data-panel le-mini-panel">
                <div class="le-panel-heading">
                    <h2>Education Progress</h2>
                    <a href="{{ route('admin.reports.users.show', [
                        'user' => $user->id,
                        'tab' => 'education'
                    ]) }}" class="le-text-link">
                        View all →
                    </a>
                </div>

                <strong>{{ number_format($summary['completed_lessons']) }}</strong>
                <small>Lessons completed</small>

                <div class="le-mini-bars">
                    <i style="height:35%"></i>
                    <i style="height:52%"></i>
                    <i style="height:43%"></i>
                    <i style="height:78%"></i>
                    <i style="height:64%"></i>
                    <i style="height:90%"></i>
                    <i style="height:72%"></i>
                </div>
            </section>

            <section class="le-data-panel le-mini-panel">
                <div class="le-panel-heading">
                    <h2>Tools Usage</h2>
                    <a href="{{ route('admin.reports.users.show', [
                        'user' => $user->id,
                        'tab' => 'tools'
                    ]) }}" class="le-text-link">
                        View all →
                    </a>
                </div>

                <strong>{{ number_format($summary['tools']) }}</strong>
                <small>Recorded tool and journey activity</small>

                <div class="le-mini-bars">
                    <i style="height:42%"></i>
                    <i style="height:68%"></i>
                    <i style="height:55%"></i>
                    <i style="height:82%"></i>
                    <i style="height:48%"></i>
                    <i style="height:74%"></i>
                    <i style="height:90%"></i>
                </div>
            </section>

            <section class="le-data-panel le-mini-panel">
                <div class="le-panel-heading">
                    <h2>Calculator Usage</h2>
                    <a href="{{ route('admin.reports.users.show', [
                        'user' => $user->id,
                        'tab' => 'calculators'
                    ]) }}" class="le-text-link">
                        View all →
                    </a>
                </div>

                <strong>{{ number_format($summary['calculators']) }}</strong>
                <small>Recorded calculations</small>

                <div class="le-mini-bars">
                    <i style="height:48%"></i>
                    <i style="height:72%"></i>
                    <i style="height:60%"></i>
                    <i style="height:88%"></i>
                    <i style="height:53%"></i>
                    <i style="height:78%"></i>
                    <i style="height:95%"></i>
                </div>
            </section>

        </div>

    {{-- ALL NON-OVERVIEW TABS --}}
    @else

        {{-- Filters --}}
        <section class="le-filter-panel">

            <div class="le-filter-title">
                <div>
                    <h2>{{ $tabs[$tab] }} Search</h2>
                    <p>
                        Search and filter this user's
                        {{ strtolower($tabs[$tab]) }} records.
                    </p>
                </div>

                <a class="le-button le-button-primary"
                   href="{{ route('admin.reports.users.export', array_merge(
                       ['user' => $user->id, 'tab' => $tab],
                       request()->query()
                   )) }}">
                    ↓ Export Excel
                </a>
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
                        placeholder="Search {{ strtolower($tabs[$tab]) }} records..."
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

                                @foreach(['income', 'investment', 'loan_emi', 'fixed_bill', 'living_expense'] as $v)
                                    <option
                                        value="{{ $v }}"
                                        @selected(($filters['category'] ?? '') === $v)
                                    >
                                        {{ str($v)->replace('_', ' ')->title() }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            Payment Status
                            <select name="status">
                                <option value="">All Statuses</option>

                                <option value="projected"
                                    @selected(($filters['status'] ?? '') === 'projected')>
                                    Projected
                                </option>

                                <option value="cleared"
                                    @selected(($filters['status'] ?? '') === 'cleared')>
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
                        <input
                            type="date"
                            name="from"
                            value="{{ $filters['from'] ?? '' }}"
                        >
                    </label>

                    <label>
                        To Date
                        <input
                            type="date"
                            name="to"
                            value="{{ $filters['to'] ?? '' }}"
                        >
                    </label>

                </div>

                <div class="le-filter-actions">
                    <button class="le-button le-button-primary">
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

        {{-- Records --}}
        <section class="le-data-panel">

            <div class="le-panel-heading">
                <div>
                    <h2>{{ $tabs[$tab] }} Records</h2>
                    <p>
                        {{ number_format($records->total()) }} matching records
                    </p>
                </div>
            </div>

            <div class="le-table-wrap">
                <table class="le-table">

                    @if($tab === 'calendar')

                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Event Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Recurring</th>
                                <th>Reminder</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($records as $r)
                                <tr>
                                    <td>{{ $r->description }}</td>
                                    <td>{{ str($r->category)->replace('_', ' ')->title() }}</td>
                                    <td>{{ $r->event_date?->format('d M Y') }}</td>
                                    <td>₹ {{ number_format((float) $r->amount, 2) }}</td>
                                    <td>
                                        <span class="le-module-pill">
                                            {{ ucfirst($r->payment_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $r->is_recurring ? 'Yes' : 'No' }}</td>
                                    <td>
                                        {{ $r->reminder_sent_at ? 'Sent' : ($r->reminder_time ?: '—') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="le-empty">
                                        No matching calendar records.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @elseif($tab === 'calculators')

                        <thead>
                            <tr>
                                <th>Calculator</th>
                                <th>Event</th>
                                <th>Status</th>
                                <th>Inputs</th>
                                <th>Result</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($records as $r)
                                <tr>
                                    <td>{{ $r->calculator?->name ?? '—' }}</td>
                                    <td>{{ $r->event_type }}</td>
                                    <td>{{ $r->status }}</td>
                                    <td>
                                        <code>{{ \Illuminate\Support\Str::limit(json_encode($r->inputs), 70) }}</code>
                                    </td>
                                    <td>
                                        <code>{{ \Illuminate\Support\Str::limit(json_encode($r->result), 70) }}</code>
                                    </td>
                                    <td>{{ $r->created_at?->format('d M Y, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="le-empty">
                                        No calculator records.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @else

                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Module</th>
                                <th>Subject Type</th>
                                <th>Subject Name</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($records as $r)
                                <tr>
                                    <td>
                                        {{ str($r->activity_type)->replace('_', ' ')->title() }}
                                    </td>

                                    <td>{{ ucfirst($r->module) }}</td>

                                    <td>
                                        {{ str($r->subject_type ?? 'Activity')->replace('_', ' ')->title() }}
                                    </td>

                                    <td>
                                        {{ $r->subject_name ?? '—' }}
                                    </td>

                                    <td>
                                        {{ isset($r->happened_at)
                                            ? \Illuminate\Support\Carbon::parse($r->happened_at)->format('d M Y, h:i A')
                                            : ($r->created_at?->format('d M Y, h:i A') ?? '—') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="le-empty">
                                        No matching records.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    @endif

                </table>
            </div>

            {{-- SINGLE PAGINATION: NON-OVERVIEW TABS ONLY --}}
            <div class="le-pagination">

                <div class="le-pagination-info">
                    Showing
                    <strong>
                        {{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }}
                    </strong>
                    of
                    <strong>{{ $records->total() }}</strong>
                    records
                </div>

                @if($records->hasPages())

                    @php
                        $currentPage = $records->currentPage();
                        $lastPage = $records->lastPage();
                        $startPage = max(1, $currentPage - 1);
                        $endPage = min($lastPage, $currentPage + 1);
                    @endphp

                    <nav class="le-pagination-controls"
                         aria-label="Records pagination">

                        @if($records->onFirstPage())
                            <span class="le-page-control disabled">
                                ‹ Previous
                            </span>
                        @else
                            <a class="le-page-control"
                               href="{{ $records->previousPageUrl() }}">
                                ‹ Previous
                            </a>
                        @endif

                        @if($startPage > 1)

                            <a class="le-page-number"
                               href="{{ $records->url(1) }}">
                                1
                            </a>

                            @if($startPage > 2)
                                <span class="le-page-ellipsis">…</span>
                            @endif

                        @endif

                        @foreach($records->getUrlRange($startPage, $endPage) as $page => $url)

                            <a href="{{ $url }}"
                               class="le-page-number {{ $currentPage == $page ? 'active' : '' }}"
                               @if($currentPage == $page) aria-current="page" @endif>
                                {{ $page }}
                            </a>

                        @endforeach

                        @if($endPage < $lastPage)

                            @if($endPage < $lastPage - 1)
                                <span class="le-page-ellipsis">…</span>
                            @endif

                            <a class="le-page-number"
                               href="{{ $records->url($lastPage) }}">
                                {{ $lastPage }}
                            </a>

                        @endif

                        @if($records->hasMorePages())
                            <a class="le-page-control"
                               href="{{ $records->nextPageUrl() }}">
                                Next ›
                            </a>
                        @else
                            <span class="le-page-control disabled">
                                Next ›
                            </span>
                        @endif

                    </nav>

                @endif

            </div>

        </section>

    @endif

</div>

{{-- OVERVIEW CHARTS --}}
@if($tab === 'overview')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            if (!window.Chart) return;

            const labels = @json($chart['labels']);
            const series = @json($chart['series']);

            const colors = {
                education: getComputedStyle(document.documentElement)
                    .getPropertyValue('--primary').trim() || '#222',
                tools: '#777777',
                calculators: '#a0a0a0',
                calendar: '#c4c4c4'
            };

            const ctx = document.getElementById('activityTrend');

            if (ctx) {
                new Chart(ctx, {
                    type: 'line',

                    data: {
                        labels,
                        datasets: Object.keys(series).map(key => ({
                            label: key.charAt(0).toUpperCase() + key.slice(1),
                            data: series[key],
                            borderColor: colors[key],
                            backgroundColor: colors[key] + '18',
                            tension: .35,
                            fill: false,
                            pointRadius: 2,
                            borderWidth: 2
                        }))
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 7
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            },
                            x: {
                                grid: { display: false }
                            }
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
                            backgroundColor: [
                                '#222222',
                                '#666666',
                                '#999999',
                                '#cccccc'
                            ],
                            borderWidth: 2,
                            borderColor: getComputedStyle(document.documentElement)
                                .getPropertyValue('--surface').trim() || '#fff'
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

        });
    </script>

@endif

@endsection
