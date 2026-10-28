
@extends('layouts.admin')

@section('title', 'User Financial Calendar')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/financial-calendar.css') }}">
@endpush

@section('content')
@php
    $daysInMonth = $month->daysInMonth;
    // Monday-first calendar: Monday = 0, Sunday = 6.
    $leadingDays = $month->dayOfWeekIso - 1;

    $eventsByDate = $events->groupBy(
        fn ($event) => $event->event_date?->format('Y-m-d')
    );

    $selectedCarbonDate = \Illuminate\Support\Carbon::parse($selectedDate);
    $previousMonth = $month->copy()->subMonth()->format('Y-m');
    $nextMonth = $month->copy()->addMonth()->format('Y-m');

    $calendarUrl = route(
        'admin.financial-flow.calendar.show',
        ['user' => $user->id]
    );

    $monthUrl = fn ($value) => $calendarUrl . '?' . http_build_query([
        'month' => $value,
        'date' => $value === $month->format('Y-m')
            ? $selectedDate
            : $value . '-01',
    ]);
@endphp

<div class="fc-page">
    <div class="fc-breadcrumb">
        <a href="{{ route('admin.financial-flow.calendar.index') }}">
            <i class="fa-solid fa-arrow-left"></i> Financial Calendars
        </a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>{{ $user->name }}</span>
    </div>

    <section class="fc-profile-card">
        <span class="fc-avatar fc-avatar-large">
            {{ mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1)) }}
        </span>

        <div class="fc-profile-info">
            <span class="fc-eyebrow">USER FINANCIAL CALENDAR</span>
            <h1>{{ $user->name }}</h1>
            <p>{{ $user->email }} <span>·</span> User ID {{ $user->id }}</p>
        </div>

        <div class="fc-profile-actions">
            <a href="{{ route('admin.financial-flow.calendar.index') }}"
               class="fc-btn fc-btn-secondary">
                <i class="fa-solid fa-users"></i> All Users
            </a>
            <a href="{{ route('admin.financial-flow.index') }}"
               class="fc-btn fc-btn-primary">
                <i class="fa-solid fa-chart-pie"></i> Analytics
            </a>
        </div>
    </section>

    <div class="fc-stats fc-stats-user">
        <article class="fc-stat-card">
            <div class="fc-stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <div>
                <span>Total Events</span>
                <strong>{{ number_format($allUserEventsCount) }}</strong>
                <small>Across all dates</small>
            </div>
        </article>

        <article class="fc-stat-card">
            <div class="fc-stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
            <div>
                <span>{{ $month->format('M Y') }} Events</span>
                <strong>{{ number_format($monthEventsCount) }}</strong>
                <small>Selected month only</small>
            </div>
        </article>

        <article class="fc-stat-card">
            <div class="fc-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <span>Cleared Events</span>
                <strong>{{ number_format($summary['cleared']) }}</strong>
                <small>In selected month</small>
            </div>
        </article>

        <article class="fc-stat-card">
            <div class="fc-stat-icon"><i class="fa-solid fa-clock"></i></div>
            <div>
                <span>Projected Events</span>
                <strong>{{ number_format($summary['projected']) }}</strong>
                <small>In selected month</small>
            </div>
        </article>
    </div>

    <section class="fc-calendar-section">
        <div class="fc-calendar-heading">
            <div>
                <span class="fc-eyebrow">FINANCIAL PLANNER</span>
                <h2>Monthly Calendar</h2>
                <p>Select any date to inspect this user's financial events.</p>
            </div>

            <div class="fc-month-navigation">
                <a href="{{ $monthUrl($previousMonth) }}"
                   class="fc-icon-btn"
                   aria-label="Previous month"
                   title="Previous month">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>

                <strong>{{ $month->format('F Y') }}</strong>

                <a href="{{ $monthUrl($nextMonth) }}"
                   class="fc-icon-btn"
                   aria-label="Next month"
                   title="Next month">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
        </div>

        <div class="fc-calendar-layout">
            <section class="fc-calendar-card">
                <div class="fc-weekdays">
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                        <span>{{ $weekday }}</span>
                    @endforeach
                </div>

                <div class="fc-calendar-grid">
                    @for($blank = 0; $blank < $leadingDays; $blank++)
                        <div class="fc-day fc-day-blank" aria-hidden="true"></div>
                    @endfor

                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $dateKey = $month->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
                            $dayEvents = $eventsByDate->get($dateKey, collect());
                            $isSelected = $dateKey === $selectedDate;
                            $isToday = $dateKey === now()->toDateString();
                        @endphp

                        <a href="{{ $calendarUrl . '?' . http_build_query([
                                'month' => $month->format('Y-m'),
                                'date' => $dateKey,
                                'search' => $filters['search'] ?? null,
                                'category' => $filters['category'] ?? null,
                                'status' => $filters['status'] ?? null,
                            ]) }}"
                           class="fc-day {{ $isSelected ? 'is-selected' : '' }} {{ $isToday ? 'is-today' : '' }} {{ $dayEvents->isNotEmpty() ? 'has-events' : '' }}"
                           aria-label="{{ $selectedCarbonDate->copy()->setDay($day)->format('l, d F Y') }}: {{ $dayEvents->count() }} events">

                            <span class="fc-day-number">{{ $day }}</span>

                            @if($dayEvents->isNotEmpty())
                                <span class="fc-day-dots" aria-hidden="true">
                                    @foreach($dayEvents->take(3) as $event)
                                        <i class="fc-event-dot {{ strtolower($event->category ?? '') === 'income' ? 'is-income' : (str_contains(strtolower($event->category ?? ''), 'emi') ? 'is-emi' : 'is-other') }}"></i>
                                    @endforeach
                                </span>
                                <small>{{ $dayEvents->count() }} {{ \Illuminate\Support\Str::plural('event', $dayEvents->count()) }}</small>
                            @endif
                        </a>
                    @endfor
                </div>

                <div class="fc-legend">
                    <span><i class="fc-event-dot is-income"></i> Income</span>
                    <span><i class="fc-event-dot is-other"></i> Other categories</span>
                    <span><i class="fc-event-dot is-emi"></i> EMI</span>
                </div>
            </section>

            <aside class="fc-selected-panel">
                <div class="fc-selected-heading">
                    <div class="fc-selected-icon">
                        <i class="fa-regular fa-calendar-check"></i>
                    </div>
                    <div>
                        <h3>{{ $selectedCarbonDate->format('l, d F Y') }}</h3>
                        <p>Financial events scheduled for this date</p>
                    </div>
                    <span class="fc-count">{{ $selectedDateEvents->count() }} events</span>
                </div>

                <div class="fc-selected-events">
                    @forelse($selectedDateEvents as $event)
                        <article class="fc-event-row">
                            <span class="fc-event-symbol {{ strtolower($event->category ?? '') === 'income' ? 'is-income' : (str_contains(strtolower($event->category ?? ''), 'emi') ? 'is-emi' : 'is-other') }}">
                                @if(strtolower($event->category ?? '') === 'income')
                                    <i class="fa-solid fa-arrow-down"></i>
                                @elseif(str_contains(strtolower($event->category ?? ''), 'emi'))
                                    <i class="fa-solid fa-building-columns"></i>
                                @else
                                    <i class="fa-solid fa-receipt"></i>
                                @endif
                            </span>

                            <div class="fc-event-main">
                                <strong>{{ $event->description }}</strong>
                                <span>{{ \Illuminate\Support\Str::headline($event->category ?: 'Uncategorized') }}</span>
                                <div class="fc-event-meta">
                                    <span class="fc-status {{ $event->payment_status === 'cleared' ? 'fc-status-active' : 'fc-status-pending' }}">
                                        {{ \Illuminate\Support\Str::headline($event->payment_status ?? 'Unknown') }}
                                    </span>
                                    <small>
                                        {{ $event->is_recurring
                                            ? \Illuminate\Support\Str::headline($event->recurrence_frequency ?? 'Recurring')
                                            : 'One-time' }}
                                    </small>
                                </div>
                                @if(filled($event->notes))
                                    <p class="fc-event-notes">{{ $event->notes }}</p>
                                @endif
                            </div>

                            <strong class="fc-event-amount">
                                ₹{{ number_format((float) $event->amount, 0) }}
                            </strong>
                        </article>
                    @empty
                        <div class="fc-empty fc-empty-events">
                            <span class="fc-empty-icon"><i class="fa-regular fa-calendar-xmark"></i></span>
                            <strong>No events on this date</strong>
                            <span>Select another date to view its records.</span>
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    </section>

    <section class="fc-panel fc-ledger-panel">
        <div class="fc-section-heading">
            <div>
                <h2>Financial Events</h2>
                <p>Search and filter this user's events for {{ $month->format('F Y') }}.</p>
            </div>
            <span class="fc-count">{{ $events->count() }} events</span>
        </div>

        <form method="GET" action="{{ $calendarUrl }}" class="fc-filters fc-event-filters">
            <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
            <input type="hidden" name="date" value="{{ $selectedDate }}">

            <label class="fc-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Search description or notes...">
            </label>

            <label class="fc-field">
                <span>Category</span>
                <select name="category">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>
                            {{ \Illuminate\Support\Str::headline($category) }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="fc-field">
                <span>Payment Status</span>
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="cleared" @selected(($filters['status'] ?? '') === 'cleared')>Cleared</option>
                    <option value="projected" @selected(($filters['status'] ?? '') === 'projected')>Projected</option>
                </select>
            </label>

            <button class="fc-btn fc-btn-primary" type="submit">
                <i class="fa-solid fa-filter"></i> Apply Filters
            </button>

            <a class="fc-btn fc-btn-secondary"
               href="{{ $calendarUrl . '?' . http_build_query([
                   'month' => $month->format('Y-m'),
                   'date' => $selectedDate,
               ]) }}">Reset</a>
        </form>

        <div class="fc-table-wrap">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Recurrence</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>{{ $event->event_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <div class="fc-description">
                                    <span class="fc-event-symbol {{ strtolower($event->category ?? '') === 'income' ? 'is-income' : (str_contains(strtolower($event->category ?? ''), 'emi') ? 'is-emi' : 'is-other') }}">
                                        <i class="fa-solid fa-receipt"></i>
                                    </span>
                                    <div>
                                        <strong>{{ $event->description }}</strong>
                                        @if(filled($event->notes))
                                            <small>{{ \Illuminate\Support\Str::limit($event->notes, 70) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ \Illuminate\Support\Str::headline($event->category ?: 'Uncategorized') }}</td>
                            <td class="fc-amount">₹{{ number_format((float) $event->amount, 0) }}</td>
                            <td>
                                <span class="fc-status {{ $event->payment_status === 'cleared' ? 'fc-status-active' : 'fc-status-pending' }}">
                                    {{ \Illuminate\Support\Str::headline($event->payment_status ?? 'Unknown') }}
                                </span>
                            </td>
                            <td>
                                @if($event->is_recurring)
                                    <strong>{{ \Illuminate\Support\Str::headline($event->recurrence_frequency ?? 'Recurring') }}</strong>
                                    @if($event->recurrence_end_date)
                                        <small class="fc-table-subtext">Until {{ $event->recurrence_end_date->format('d M Y') }}</small>
                                    @endif
                                @else
                                    One-time
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="fc-empty">
                                    <i class="fa-solid fa-receipt"></i>
                                    <strong>No financial events found</strong>
                                    <span>There are no matching records for this month and filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
