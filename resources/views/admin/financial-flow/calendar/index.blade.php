
@extends('layouts.admin')

@section('title', 'Financial Calendars')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/financial-calendar.css') }}">
@endpush

@section('content')
<div class="fc-page">
    <div class="fc-heading">
        <div>
            <span class="fc-eyebrow">
                <i class="fa-solid fa-wallet"></i>
                Financial Management
            </span>
            <h1>Financial Calendars</h1>
            <p>Manage users and explore their complete financial activity.</p>
        </div>

        <a href="{{ route('admin.financial-flow.index') }}" class="fc-btn fc-btn-primary">
            <i class="fa-solid fa-chart-line"></i>
            Overview
        </a>
    </div>

    <div class="fc-stats">
        <article class="fc-stat-card">
            <div class="fc-stat-icon"><i class="fa-solid fa-users"></i></div>
            <div>
                <span>Total Users</span>
                <strong>{{ number_format($totalUsers) }}</strong>
                <small>Registered accounts</small>
            </div>
        </article>

        <article class="fc-stat-card">
            <div class="fc-stat-icon"><i class="fa-solid fa-user-check"></i></div>
            <div>
                <span>Users With Events</span>
                <strong>{{ number_format($usersWithEvents) }}</strong>
                <small>Users with financial records</small>
            </div>
        </article>

        <article class="fc-stat-card">
            <div class="fc-stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
            <div>
                <span>Monthly Events</span>
                <strong>{{ number_format($monthlyEventCount) }}</strong>
                <small>Events in selected month</small>
            </div>
        </article>

        <article class="fc-stat-card">
            <div class="fc-stat-icon"><i class="fa-solid fa-calendar"></i></div>
            <div>
                <span>Reporting Period</span>
                <strong>{{ $month->format('M Y') }}</strong>
                <small>Selected reporting month</small>
            </div>
        </article>
    </div>

    <section class="fc-panel">
        <form method="GET"
              action="{{ route('admin.financial-flow.calendar.index') }}"
              class="fc-filters">

            <label class="fc-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search name, email or user ID..."
                >
            </label>

            <label class="fc-field">
                <span>Activity</span>
                <select name="activity">
                    <option value="all" @selected(($filters['activity'] ?? 'all') === 'all')>All users</option>
                    <option value="with_events" @selected(($filters['activity'] ?? '') === 'with_events')>With events</option>
                    <option value="no_events" @selected(($filters['activity'] ?? '') === 'no_events')>No events</option>
                </select>
            </label>

            <label class="fc-field">
                <span>Sort by</span>
                <select name="sort">
                    <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                    <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest</option>
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name</option>
                    <option value="events" @selected(($filters['sort'] ?? '') === 'events')>Most events</option>
                </select>
            </label>

            <label class="fc-field fc-month-field">
                <span>Reporting month</span>
                <input type="month" name="month" value="{{ $month->format('Y-m') }}">
            </label>

            <button type="submit" class="fc-btn fc-btn-primary">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <a href="{{ route('admin.financial-flow.calendar.index') }}"
               class="fc-btn fc-btn-secondary">
                Reset
            </a>
        </form>

        <div class="fc-section-heading">
            <div>
                <h2>Users Directory</h2>
                <p>Select a user to inspect their complete financial calendar.</p>
            </div>
            <span class="fc-count">{{ $users->total() }} users</span>
        </div>

        <div class="fc-table-wrap">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Total Events</th>
                        <th>{{ $month->format('M Y') }} Events</th>
                        <th>Last Event</th>
                        <th>Activity</th>
                        <th class="fc-center">Calendar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $listedUser)
                        @php
                            $hasEvents = $listedUser->financial_events_count > 0;
                            $calendarUrl = route(
                                'admin.financial-flow.calendar.show',
                                ['user' => $listedUser->id, 'month' => $month->format('Y-m')]
                            );
                        @endphp
                        <tr class="fc-user-row"
                            onclick="window.location.href='{{ $calendarUrl }}'"
                            style="cursor: pointer;">
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>
                                <a href="{{ $calendarUrl }}" class="fc-user-link">
                                    <span class="fc-avatar">
                                        {{ mb_strtoupper(mb_substr($listedUser->name ?? 'U', 0, 1)) }}
                                    </span>
                                    <span class="fc-user-details">
                                        <strong>{{ $listedUser->name }}</strong>
                                        <small>UID-{{ $listedUser->id }}</small>
                                    </span>
                                </a>
                            </td>
                            <td class="fc-email">{{ $listedUser->email }}</td>
                            <td><span class="fc-number">{{ number_format($listedUser->financial_events_count) }}</span></td>
                            <td><span class="fc-number">{{ number_format($listedUser->month_events_count) }}</span></td>
                            <td>
                                @if($listedUser->financial_events_max_event_date)
                                    {{ \Illuminate\Support\Carbon::parse($listedUser->financial_events_max_event_date)->format('d M Y') }}
                                @else
                                    <span class="fc-muted">No activity</span>
                                @endif
                            </td>
                            <td>
                                <span class="fc-status {{ $hasEvents ? 'fc-status-active' : 'fc-status-empty' }}">
                                    <span class="fc-status-dot"></span>
                                    {{ $hasEvents ? 'Has Events' : 'No Events' }}
                                </span>
                            </td>
                            <td class="fc-center">
                                <a href="{{ $calendarUrl }}"
                                   class="fc-icon-btn"
                                   aria-label="Open {{ $listedUser->name }} financial calendar"
                                   title="Open financial calendar">
                                    <i class="fa-regular fa-calendar-days"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="fc-empty">
                                    <i class="fa-solid fa-users-slash"></i>
                                    <strong>No users found</strong>
                                    <span>Try changing your search or filters.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="fc-pagination">{{ $users->links() }}</div>
        @endif
    </section>
</div>
@endsection
