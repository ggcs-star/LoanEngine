@extends('user.layouts.app')

@section('title', 'Financial Flow Calendar | LoanEngine')
@section('page_heading', 'Financial Flow Calendar')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/financial-flow.css') }}">
@endpush

@section('content')
@php
    $monthStart = \Carbon\Carbon::create((int) $year, (int) $month, 1)->startOfMonth();
    $calendarStart = $monthStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $calendarEnd = $monthStart->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SATURDAY);
    $money = fn ($value) => '₹' . number_format((float) $value, 0, '.', ',');
    $eventsCollection = collect($events ?? []);
    $eventsByDate = $eventsCollection->groupBy(fn ($event) => $event->event_date->toDateString());
    $previousMonth = $monthStart->copy()->subMonth();
    $nextMonth = $monthStart->copy()->addMonth();
    $categoryColors = [
        'income' => 'income',
        'investment' => 'investment',
        'loan_emi' => 'loan_emi',
        'fixed_bill' => 'fixed_bill',
        'living_expense' => 'living_expense',
    ];

    $clientEvents = $eventsCollection->map(function ($event) {
        return [
            'id' => $event->id,
            'description' => $event->description,
            'amount' => (float) $event->amount,
            'category' => $event->category,
            'event_date' => $event->event_date->toDateString(),
            'reminder_time' => $event->reminder_time ? \Illuminate\Support\Carbon::parse($event->reminder_time)->format('H:i') : null,
            'reminder_sent_at' => $event->reminder_sent_at?->toIso8601String(),
            'is_reminder_sent' => $event->reminder_sent_at !== null,
            'payment_status' => $event->payment_status,
            'notes' => $event->notes,
            'is_recurring' => (bool) $event->is_recurring,
            'is_generated' => !empty($event->source_event_id),
            'is_locked' => !empty($event->source_event_id) || $event->reminder_sent_at !== null,
            'recurrence_frequency' => $event->recurrence_frequency,
            'recurrence_end_date' => $event->recurrence_end_date?->toDateString(),
            'edit_url' => route('user.financial-flow.events.update', $event),
            'delete_url' => route('user.financial-flow.events.destroy', $event),
            'status_url' => route('user.financial-flow.events.status', $event),
        ];
    })->values();

    // Presentation-only chart data derived from the already-loaded events collection.
    // This does not change the controller, saved data, summary calculations, or event actions.
    $ffcCategoryTotals = $eventsCollection->groupBy('category')->map(fn ($group) => (float) $group->sum('amount'));
    $ffcCategoryTotal = (float) $eventsCollection->sum('amount');
    $ffcChartCategoryColors = [
        'income' => '#111827',
        'investment' => '#4b5563',
        'loan_emi' => '#6b7280',
        'fixed_bill' => '#9ca3af',
        'living_expense' => '#d1d5db',
    ];
    $ffcDonutStops = [];
    $ffcDonutOffset = 0.0;
    foreach ($categories as $categoryKey => $categoryLabel) {
        $categoryAmount = (float) ($ffcCategoryTotals[$categoryKey] ?? 0);
        $categoryShare = $ffcCategoryTotal > 0 ? ($categoryAmount / $ffcCategoryTotal) * 100 : 0;
        $ffcColor = $ffcChartCategoryColors[$categoryKey] ?? '#d1d5db';
        $ffcDonutStops[] = $ffcColor . ' ' . round($ffcDonutOffset, 3) . '% ' . round($ffcDonutOffset + $categoryShare, 3) . '%';
        $ffcDonutOffset += $categoryShare;
    }
    $ffcDonutGradient = $ffcCategoryTotal > 0 ? implode(', ', $ffcDonutStops) : '#e5e7eb 0% 100%';

    $ffcWeeklyBuckets = collect(range(1, 5))->map(function ($week) use ($eventsCollection) {
        $firstDay = (($week - 1) * 7) + 1;
        $lastDay = $week * 7;
        $weekEvents = $eventsCollection->filter(function ($event) use ($firstDay, $lastDay) {
            $dayNumber = (int) $event->event_date->format('j');
            return $dayNumber >= $firstDay && $dayNumber <= $lastDay;
        });
        return [
            'label' => 'W' . $week,
            'income' => (float) $weekEvents->where('category', 'income')->sum('amount'),
            'outflow' => (float) $weekEvents->reject(fn ($event) => $event->category === 'income')->sum('amount'),
        ];
    });
    $ffcChartMax = max(1, (float) $ffcWeeklyBuckets->flatMap(fn ($bucket) => [$bucket['income'], $bucket['outflow']])->max());
    $ffcIncomePoints = [];
    $ffcOutflowPoints = [];
    foreach ($ffcWeeklyBuckets as $index => $bucket) {
        $x = 22 + ($index * 69);
        $incomeY = 92 - (($bucket['income'] / $ffcChartMax) * 68);
        $outflowY = 92 - (($bucket['outflow'] / $ffcChartMax) * 68);
        $ffcIncomePoints[] = $x . ',' . round($incomeY, 2);
        $ffcOutflowPoints[] = $x . ',' . round($outflowY, 2);
    }
@endphp

<div class="ffc">
    <header class="ffc-header ffc-hero">
        <div>
            <div class="ffc-eyebrow">Personal finance workspace</div>
            <h1>Financial Flow Calendar</h1>
            <p>Plan upcoming income, EMIs, bills and investments in one place.</p>
        </div>
        <button type="button" class="ffc-btn ffc-btn-primary" id="ffc-open-add">＋ Add Event</button>
    </header>

    @if (session('success')) <div class="ffc-alert ffc-alert-success" role="status">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="ffc-alert" role="alert">{{ session('error') }}</div> @endif
    @if ($errors->any())
        <div class="ffc-alert" role="alert">
            <strong>Please correct the following:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <nav class="ffc-tabs" aria-label="Financial flow sections">
        <button type="button" class="ffc-tab is-active" data-ffc-tab="calendar">Calendar</button>
        <button type="button" class="ffc-tab" data-ffc-tab="ledger">Ledger</button>
        <button type="button" class="ffc-tab" data-ffc-tab="health">Borrower Health</button>
    </nav>

    <section class="ffc-summary">
        <article class="ffc-card"><div class="ffc-metric-label">Planned Income</div><div class="ffc-metric-value ffc-positive">{{ $money($summary['inflow']) }}</div><div class="ffc-metric-note">Income events this month</div></article>
        <article class="ffc-card"><div class="ffc-metric-label">Planned Outflow</div><div class="ffc-metric-value">{{ $money($summary['outflow']) }}</div><div class="ffc-metric-note">Bills, EMIs and investments</div></article>
        <article class="ffc-card"><div class="ffc-metric-label">Loan EMI Obligations</div><div class="ffc-metric-value">{{ $money($summary['emi']) }}</div><div class="ffc-metric-note">Scheduled EMI amount</div></article>
        <article class="ffc-card"><div class="ffc-metric-label">Net Planned Flow</div><div class="ffc-metric-value {{ $summary['net_flow'] >= 0 ? 'ffc-positive' : 'ffc-negative' }}">{{ $money($summary['net_flow']) }}</div><div class="ffc-metric-note">Income minus planned outflow</div></article>
    </section>

    <section class="ffc-chart-grid" aria-label="Financial insights">
        <article class="ffc-chart-card">
            <div class="ffc-chart-head">
                <div><h2>Income vs Outflow</h2><p>Weekly comparison for {{ $monthStart->format('F Y') }}</p></div>
                <div class="ffc-chart-legend"><span><i class="ffc-legend-income"></i>Income</span><span><i class="ffc-legend-outflow"></i>Outflow</span></div>
            </div>
            <div class="ffc-bar-chart" role="img" aria-label="Weekly income and outflow comparison">
                <div class="ffc-chart-ylabels"><span>{{ $money($ffcChartMax) }}</span><span>{{ $money($ffcChartMax / 2) }}</span><span>₹0</span></div>
                <div class="ffc-bar-plot">
                    <div class="ffc-chart-gridline"></div><div class="ffc-chart-gridline"></div><div class="ffc-chart-gridline"></div>
                    @foreach ($ffcWeeklyBuckets as $bucket)
                        <div class="ffc-bar-group" title="{{ $bucket['label'] }} — Income {{ $money($bucket['income']) }}, Outflow {{ $money($bucket['outflow']) }}">
                            <div class="ffc-bar-pair">
                                <span class="ffc-bar ffc-bar-income" style="height: {{ max(2, ($bucket['income'] / $ffcChartMax) * 100) }}%"></span>
                                <span class="ffc-bar ffc-bar-outflow" style="height: {{ max(2, ($bucket['outflow'] / $ffcChartMax) * 100) }}%"></span>
                            </div>
                            <span class="ffc-bar-label">{{ $bucket['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>

        <article class="ffc-chart-card">
            <div class="ffc-chart-head"><div><h2>Category Breakdown</h2><p>Distribution of scheduled events</p></div><span class="ffc-chart-pill">{{ $eventsCollection->count() }} events</span></div>
            <div class="ffc-donut-layout">
                <div class="ffc-donut" role="img" aria-label="Category distribution" style="background: conic-gradient({{ $ffcDonutGradient }});">
                    <div class="ffc-donut-hole"><strong>{{ $money($ffcCategoryTotal) }}</strong><span>Total scheduled</span></div>
                </div>
                <ul class="ffc-donut-legend">
                    @foreach ($categories as $categoryKey => $categoryLabel)
                        @php
                            $categoryAmount = (float) ($ffcCategoryTotals[$categoryKey] ?? 0);
                            $categoryShare = $ffcCategoryTotal > 0 ? round(($categoryAmount / $ffcCategoryTotal) * 100) : 0;
                            $legendColor = $ffcChartCategoryColors[$categoryKey] ?? '#d1d5db';
                        @endphp
                        <li><span class="ffc-donut-label"><i style="background: {{ $legendColor }}"></i>{{ $categoryLabel }}</span><strong>{{ $categoryShare }}%</strong><small>{{ $money($categoryAmount) }}</small></li>
                    @endforeach
                </ul>
            </div>
        </article>

        <article class="ffc-chart-card">
            <div class="ffc-chart-head"><div><h2>Weekly Cash Flow Trend</h2><p>Income and outflow across this month</p></div><span class="ffc-chart-pill">{{ $monthStart->format('M Y') }}</span></div>
            <div class="ffc-line-chart" role="img" aria-label="Weekly cash flow trend for {{ $monthStart->format('F Y') }}">
                <div class="ffc-line-ylabels"><span>{{ $money($ffcChartMax) }}</span><span>{{ $money($ffcChartMax / 2) }}</span><span>₹0</span></div>
                <svg viewBox="0 0 320 118" preserveAspectRatio="none" aria-hidden="true">
                    <line x1="10" y1="24" x2="310" y2="24" class="ffc-svg-grid"/><line x1="10" y1="58" x2="310" y2="58" class="ffc-svg-grid"/><line x1="10" y1="92" x2="310" y2="92" class="ffc-svg-grid"/>
                    <polyline points="{{ implode(' ', $ffcIncomePoints) }}" class="ffc-svg-income"/><polyline points="{{ implode(' ', $ffcOutflowPoints) }}" class="ffc-svg-outflow"/>
                    @foreach ($ffcWeeklyBuckets as $index => $bucket)
                        <circle cx="{{ 22 + ($index * 69) }}" cy="{{ round(92 - (($bucket['income'] / $ffcChartMax) * 68), 2) }}" r="3" class="ffc-svg-dot-income"/>
                        <circle cx="{{ 22 + ($index * 69) }}" cy="{{ round(92 - (($bucket['outflow'] / $ffcChartMax) * 68), 2) }}" r="3" class="ffc-svg-dot-outflow"/>
                    @endforeach
                </svg>
                <div class="ffc-line-labels">@foreach ($ffcWeeklyBuckets as $bucket)<span>{{ $bucket['label'] }}</span>@endforeach</div>
            </div>
        </article>
    </section>

    <section class="ffc-section" data-ffc-panel="calendar">
        <div class="ffc-section-head">
            <div><h2>Monthly calendar</h2><p class="ffc-subtitle">Select a date to see events or schedule a new one.</p></div>
            <div class="ffc-toolbar">
                <a class="ffc-icon-btn" aria-label="Previous month" href="{{ route('user.financial-flow.index', ['year'=>$previousMonth->year,'month'=>$previousMonth->month]) }}">‹</a>
                <strong class="ffc-month">{{ $monthStart->format('F Y') }}</strong>
                <a class="ffc-icon-btn" aria-label="Next month" href="{{ route('user.financial-flow.index', ['year'=>$nextMonth->year,'month'=>$nextMonth->month]) }}">›</a>
                <a class="ffc-btn" href="{{ route('user.financial-flow.index') }}">Today</a>
            </div>
        </div>

        <div class="ffc-toolbar ffc-filters">
            <select class="ffc-select ffc-filter" id="ffc-category-filter" aria-label="Filter category">
                <option value="all">All categories</option>
                @foreach ($categories as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
            </select>
            <select class="ffc-select ffc-filter" id="ffc-status-filter" aria-label="Filter status">
                <option value="all">All statuses</option><option value="projected">Projected</option><option value="cleared">Cleared</option>
            </select>
        </div>

        <div class="ffc-calendar">
            <div class="ffc-weekdays">@foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $weekday)<div>{{ $weekday }}</div>@endforeach</div>
            <div class="ffc-days">
                @for ($day = $calendarStart->copy(); $day->lte($calendarEnd); $day->addDay())
                    @php
                        $dateKey = $day->toDateString();
                        $dayEvents = $eventsByDate->get($dateKey, collect());
                    @endphp
                    <button type="button" class="ffc-day {{ $day->month !== $monthStart->month ? 'is-outside' : '' }} {{ $day->isToday() ? 'is-today' : '' }} {{ $day->toDateString() === now()->toDateString() ? 'is-selected' : '' }}"
                        data-ffc-date="{{ $dateKey }}" aria-label="Show events on {{ $day->format('d F Y') }}">
                        <span class="ffc-day-number">{{ $day->day }}</span>
                        <span class="ffc-event-dots">
                            @foreach ($dayEvents->take(5) as $event)
                                <span class="ffc-event-dot ffc-event-dot--{{ $categoryColors[$event->category] ?? 'living_expense' }}"
                                    data-category="{{ $event->category }}" data-status="{{ $event->payment_status }}"
                                    title="{{ $event->description }} — {{ $money($event->amount) }}"></span>
                            @endforeach
                        </span>
                        @if ($dayEvents->isNotEmpty())
                            <span class="ffc-day-amount">{{ $money($dayEvents->sum('amount')) }}</span>
                            <span class="ffc-day-count">{{ $dayEvents->count() }} {{ $dayEvents->count() === 1 ? 'event' : 'events' }}</span>
                        @endif
                    </button>
                @endfor
            </div>
        </div>

        <section class="ffc-selected-date" id="ffc-selected-date-panel" aria-live="polite">
            <div class="ffc-selected-head">
                <div><div class="ffc-eyebrow">Selected date</div><h2 id="ffc-selected-date-title">{{ now()->format('D, d M') }}</h2><p id="ffc-selected-closing"></p></div>
                <button type="button" class="ffc-btn ffc-btn-primary" id="ffc-add-selected">＋ Add Event</button>
            </div>
            <div id="ffc-selected-events">
                @php $todayEvents = $eventsByDate->get(now()->toDateString(), collect()); @endphp
                @forelse ($todayEvents as $event)
                    <article class="ffc-event-card" data-event-card data-category="{{ $event->category }}" data-status="{{ $event->payment_status }}">
                        <span class="ffc-event-icon ffc-event-dot--{{ $categoryColors[$event->category] ?? 'living_expense' }}"></span>
                        <div class="ffc-event-main"><strong>{{ $event->description }}</strong><div class="ffc-event-meta">{{ $categories[$event->category] ?? $event->category }} · {{ ucfirst($event->payment_status) }}</div>@if($event->notes)<p>{{ $event->notes }}</p>@endif
                            @if($event->is_recurring)<div class="ffc-recurring-label">↻ Repeats {{ ucfirst($event->recurrence_frequency ?? 'scheduled') }} until {{ $event->recurrence_end_date?->format('d M Y') ?? 'stopped manually' }}</div>@endif
                            @if(!empty($event->source_event_id))<div class="ffc-recurring-label">🔒 Generated occurrence — editing and deletion are disabled.</div>@elseif($event->reminder_sent_at)<div class="ffc-recurring-label">🔒 Reminder sent — this event is locked.</div>@endif
                            @if($event->reminder_time)<div class="ffc-recurring-label">⏰ Reminder: {{ \Illuminate\Support\Carbon::parse($event->reminder_time)->format('h:i A') }}{{ $event->reminder_sent_at ? ' · Sent' : ' · Scheduled' }}</div>@endif
                        </div>
                        <strong class="ffc-event-price">{{ $money($event->amount) }}</strong>
                        <div class="ffc-event-actions">
                            @if(empty($event->source_event_id) && !$event->reminder_sent_at)
                                <button type="button" class="ffc-small-btn" data-edit-event
                                    data-id="{{ $event->id }}" data-description="{{ $event->description }}"
                                    data-amount="{{ $event->amount }}" data-category="{{ $event->category }}"
                                    data-date="{{ $event->event_date->format('Y-m-d') }}" data-reminder-time="{{ $event->reminder_time ? \Illuminate\Support\Carbon::parse($event->reminder_time)->format('H:i') : '' }}" data-status="{{ $event->payment_status }}"
                                    data-notes="{{ $event->notes }}" data-recurring="{{ (int)$event->is_recurring }}"
                                    data-frequency="{{ $event->recurrence_frequency }}" data-end-date="{{ $event->recurrence_end_date?->format('Y-m-d') }}">Edit</button>
                                <form method="POST" action="{{ route('user.financial-flow.events.destroy', $event) }}" data-delete-form>
                                    @csrf @method('DELETE')<button class="ffc-small-btn ffc-danger" type="submit">Delete</button>
                                </form>
                            @else
                                <span class="ffc-status">🔒 Locked</span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="ffc-empty" id="ffc-selected-empty">No events scheduled for this date. Select “Add Event” to create one.</div>
                @endforelse
            </div>
        </section>
    </section>

    <section class="ffc-section ffc-hidden" data-ffc-panel="ledger">
        <div class="ffc-section-head"><div><h2>Event ledger</h2><p class="ffc-subtitle">Search, edit, delete or update payment status.</p></div><input class="ffc-input ffc-ledger-search" id="ffc-ledger-search" type="search" placeholder="Search events…"></div>
        <div class="ffc-card ffc-table-card"><div class="ffc-table-wrap"><table class="ffc-table">
            <thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse ($eventsCollection as $event)
                <tr data-ledger-row data-search="{{ strtolower($event->description.' '.$event->category.' '.$event->event_date->format('Y-m-d')) }}">
                    <td>{{ $event->event_date->format('d M Y') }}</td><td><strong>{{ $event->description }}</strong>@if($event->notes)<div class="ffc-table-note">{{ $event->notes }}</div>@endif</td>
                    <td><span class="ffc-category-pill ffc-pill--{{ $categoryColors[$event->category] ?? 'living_expense' }}">{{ $categories[$event->category] ?? $event->category }}</span></td>
                    <td><strong>{{ $money($event->amount) }}</strong></td>
                    <td><span class="ffc-status {{ $event->payment_status === 'cleared' ? 'is-cleared' : 'is-projected' }}">{{ ucfirst($event->payment_status) }}</span></td>
                    <td><div class="ffc-action-row">
                        @if(empty($event->source_event_id) && !$event->reminder_sent_at)
                            <button type="button" class="ffc-small-btn" data-edit-event data-id="{{ $event->id }}" data-description="{{ $event->description }}" data-amount="{{ $event->amount }}" data-category="{{ $event->category }}" data-date="{{ $event->event_date->format('Y-m-d') }}" data-reminder-time="{{ $event->reminder_time ? \Illuminate\Support\Carbon::parse($event->reminder_time)->format('H:i') : '' }}" data-status="{{ $event->payment_status }}" data-notes="{{ $event->notes }}" data-recurring="{{ (int)$event->is_recurring }}" data-frequency="{{ $event->recurrence_frequency }}" data-end-date="{{ $event->recurrence_end_date?->format('Y-m-d') }}">✎ Edit</button>
                        @endif
                        @if(!$event->reminder_sent_at && empty($event->source_event_id))<form method="POST" action="{{ route('user.financial-flow.events.status', $event) }}">@csrf @method('PATCH')<input type="hidden" name="payment_status" value="{{ $event->payment_status === 'cleared' ? 'projected' : 'cleared' }}"><button class="ffc-small-btn" type="submit">Mark {{ $event->payment_status === 'cleared' ? 'Projected' : 'Cleared' }}</button></form>@endif
                        @if(empty($event->source_event_id) && !$event->reminder_sent_at)
                            <form method="POST" action="{{ route('user.financial-flow.events.destroy', $event) }}" data-delete-form>@csrf @method('DELETE')<button class="ffc-small-btn ffc-danger" type="submit">🗑 Delete</button></form>
                        @else
                            <span class="ffc-status">🔒 {{ $event->reminder_sent_at ? 'Reminder sent' : 'Locked' }}</span>
                        @endif
                    </div></td>
                </tr>
            @empty <tr><td colspan="6" class="ffc-empty">No events scheduled for this month.</td></tr> @endforelse
            </tbody>
        </table></div></div>
    </section>

    <section class="ffc-section ffc-hidden" data-ffc-panel="health">
        @php
            $monthlyIncome = $summary['inflow'];
            $monthlyEmi = $summary['emi'];
            $dti = $monthlyIncome > 0 ? ($monthlyEmi / $monthlyIncome) * 100 : null;
            $emiShare = $monthlyIncome > 0 ? min(100, ($monthlyEmi / $monthlyIncome) * 100) : 0;
            $running = 0; $lowestDip = 0;
            foreach ($eventsCollection as $healthEvent) {
                $amount = (float)$healthEvent->amount;
                $running += $healthEvent->category === 'income' ? $amount : -$amount;
                $lowestDip = min($lowestDip, $running);
            }
        @endphp
        <div class="ffc-health-grid">
            <article class="ffc-card"><div class="ffc-metric-label">Debt-to-Income (DTI)</div><div class="ffc-health-value">{{ $dti === null ? '—' : number_format($dti,1).'%' }}</div><p>{{ $dti === null ? 'Add income and EMI events to calculate DTI.' : 'Monthly EMI compared with planned monthly income.' }}</p><div class="ffc-progress"><span style="width:{{ min(100,$dti ?? 0) }}%"></span></div></article>
            <article class="ffc-card"><div class="ffc-metric-label">Lowest Running Net Flow</div><div class="ffc-health-value">{{ $money($lowestDip) }}</div><p>Estimate based on listed events in date order.</p></article>
            <article class="ffc-card"><div class="ffc-metric-label">Monthly Income Allocated to EMI</div><div class="ffc-health-value">{{ number_format($emiShare,1) }}%</div><p>Share of planned income allocated to loan EMIs.</p><div class="ffc-progress"><span style="width:{{ $emiShare }}%"></span></div></article>
            <article class="ffc-card"><div class="ffc-metric-label">Active EMI Obligations</div><div class="ffc-health-value">{{ $money($monthlyEmi) }}</div><p>Total EMI events for the selected month.</p></article>
        </div>
        <div class="ffc-alert ffc-health-note">These indicators are estimates based on events entered, not a credit assessment.</div>
    </section>
</div>

<dialog class="ffc-modal" id="ffc-event-modal">
    <div class="ffc-modal-head"><div><div class="ffc-eyebrow">Financial planning</div><h2 id="ffc-modal-title">Schedule event</h2><p class="ffc-subtitle">Keep your upcoming money movements organised.</p></div><button type="button" class="ffc-icon-btn" id="ffc-close-modal" aria-label="Close">×</button></div>
    <form method="POST" action="{{ route('user.financial-flow.events.store') }}" id="ffc-event-form" class="ffc-modal-body">
        @csrf
        <input type="hidden" name="_method" id="ffc-method" value="POST">
        <div class="ffc-form-grid">
            <div class="ffc-field ffc-field-full"><label for="ffc-description">Event name</label><input class="ffc-input" id="ffc-description" name="description" maxlength="180" required placeholder="e.g. Monthly salary"></div>
            <div class="ffc-field"><label for="ffc-amount">Amount (₹)</label><input class="ffc-input" id="ffc-amount" type="number" name="amount" min="0.01" step="0.01" required placeholder="25000"></div>
            <div class="ffc-field"><label for="ffc-date">Date</label><input class="ffc-input" id="ffc-date" type="date" name="event_date" value="{{ now()->toDateString() }}" required></div>
            <div class="ffc-field"><label for="ffc-reminder-time">Reminder Time</label><input class="ffc-input" id="ffc-reminder-time" type="time" name="reminder_time"><small>Leave blank if you don't need a reminder.</small></div>
            <div class="ffc-field"><label for="ffc-category">Category</label><select class="ffc-select" id="ffc-category" name="category" required>@foreach($categories as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
            <div class="ffc-field"><label for="ffc-status">Status</label><select class="ffc-select" id="ffc-status" name="payment_status" required><option value="projected">Projected</option><option value="cleared">Cleared</option></select></div>
            <div class="ffc-field ffc-field-full"><label class="ffc-check-row"><input type="checkbox" name="is_recurring" id="ffc-recurring" value="1"><span>Repeat this event</span></label></div>
            
            
            <div class="ffc-field ffc-recurring-field ffc-hidden"><label for="ffc-frequency">Repeat frequency</label><select class="ffc-select" name="recurrence_frequency" id="ffc-frequency"><option value="">Select frequency</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option><option value="yearly">Yearly</option></select></div>
            <div class="ffc-field ffc-recurring-field ffc-hidden"><label for="ffc-end-date">Repeat until</label><input class="ffc-input" type="date" name="recurrence_end_date" id="ffc-end-date"></div>
            <div class="ffc-field ffc-field-full"><label for="ffc-notes">Notes / reference (optional)</label><textarea class="ffc-textarea" id="ffc-notes" name="notes" rows="3" maxlength="5000" placeholder="Add a note or reference…"></textarea></div>
        </div>
        <div class="ffc-modal-actions"><button type="button" class="ffc-btn" id="ffc-cancel-modal">Cancel</button><button type="submit" class="ffc-btn ffc-btn-primary" id="ffc-submit-button">Schedule Event</button></div>
    </form>
</dialog>

<dialog id="ffc-delete-confirm" aria-labelledby="ffc-delete-title" style="width:min(420px,calc(100% - 32px));border:1px solid #e5e7eb;border-radius:18px;padding:0;box-shadow:0 24px 80px rgba(15,23,42,.28);color:#111827;background:#fff;">
    <div style="padding:26px 26px 22px;text-align:center;">
        <div style="width:58px;height:58px;margin:0 auto 15px;border-radius:50%;display:grid;place-items:center;background:#f3f4f6;color:#111827;font-size:26px;">🗑</div>
        <h2 id="ffc-delete-title" style="margin:0 0 9px;font-size:21px;font-weight:700;color:#111827;">Delete financial event?</h2>
        <p style="margin:0;color:#6b7280;line-height:1.6;font-size:14px;">Are you sure you want to delete this event? This action cannot be undone.</p>
        <div style="display:flex;justify-content:center;gap:10px;margin-top:24px;flex-wrap:wrap;">
            <button type="button" id="ffc-delete-cancel" class="ffc-btn" style="min-width:110px;justify-content:center;">Cancel</button>
            <button type="button" id="ffc-delete-confirm-button" class="ffc-btn ffc-danger" style="min-width:130px;justify-content:center;background:#111827;color:#fff;border-color:#111827;">Yes, Delete</button>
        </div>
    </div>
</dialog>

<style>
#ffc-delete-confirm::backdrop { background:rgba(15,23,42,.58); backdrop-filter:blur(3px); }
#ffc-delete-confirm[open] { margin:auto; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('.ffc');
    if (!root) return;

    const modal = document.getElementById('ffc-event-modal');
    const form = document.getElementById('ffc-event-form');
    const method = document.getElementById('ffc-method');
    const title = document.getElementById('ffc-modal-title');
    const submit = document.getElementById('ffc-submit-button');
    const dateInput = document.getElementById('ffc-date');
    const recurring = document.getElementById('ffc-recurring');
    const frequency = document.getElementById('ffc-frequency');
    const endDate = document.getElementById('ffc-end-date');
    const selectedDateTitle = document.getElementById('ffc-selected-date-title');
    const selectedEvents = document.getElementById('ffc-selected-events');
    const closing = document.getElementById('ffc-selected-closing');
    const allEvents = @json($clientEvents);
    const csrfToken = @json(csrf_token());
    const labels = @json($categories);
    const colorClass = {income:'income', investment:'investment', loan_emi:'loan_emi', fixed_bill:'fixed_bill', living_expense:'living_expense'};
    let selectedDate = @json(now()->toDateString());

    const money = value => '₹' + Number(value || 0).toLocaleString('en-IN', {maximumFractionDigits: 0});
    const safe = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const localDate = value => new Date(value + 'T00:00:00').toLocaleDateString('en-IN', {weekday:'short',day:'2-digit',month:'short',year:'numeric'});

    function openModal(date = selectedDate) {
        form.reset();
        form.action = @json(route('user.financial-flow.events.store'));
        method.value = 'POST';
        dateInput.value = date;
        document.getElementById('ffc-status').value = 'projected';
        title.textContent = 'Schedule event';
        submit.textContent = 'Schedule Event';
        recurring.checked = false;
        toggleRecurring();
        if (modal.showModal) modal.showModal(); else modal.setAttribute('open','');
    }
    function closeModal() { if (modal.close) modal.close(); else modal.removeAttribute('open'); }

    function toggleRecurring() {
        document.querySelectorAll('.ffc-recurring-field').forEach(el => el.classList.toggle('ffc-hidden', !recurring.checked));
        frequency.disabled = !recurring.checked;
        endDate.disabled = !recurring.checked;
        frequency.required = recurring.checked;
        endDate.required = false;
        if (!recurring.checked) { frequency.value = ''; endDate.value = ''; }
    }
    recurring.addEventListener('change', toggleRecurring);

    function editEvent(id) {
        const event = allEvents.find(item => String(item.id) === String(id));
        if (!event) return;
        if (event.is_locked) { alert(event.is_reminder_sent ? 'This event is locked because its reminder has already been sent.' : 'Generated events cannot be edited.'); return; }
        openModal(event.event_date);
        form.action = event.edit_url;
        method.value = 'PATCH';
        title.textContent = 'Edit financial event';
        submit.textContent = 'Save Changes';
        document.getElementById('ffc-description').value = event.description;
        document.getElementById('ffc-amount').value = event.amount;
        document.getElementById('ffc-category').value = event.category;
        document.getElementById('ffc-status').value = event.payment_status;
        document.getElementById('ffc-reminder-time').value = event.reminder_time || '';
        document.getElementById('ffc-notes').value = event.notes || '';
        recurring.checked = event.is_recurring;
        frequency.value = event.recurrence_frequency || '';
        endDate.value = event.recurrence_end_date || '';
        toggleRecurring();
    }

    root.addEventListener('click', e => {
        const edit = e.target.closest('[data-edit-event]');
        if (edit) { e.preventDefault(); editEvent(edit.dataset.id); return; }
        const day = e.target.closest('[data-ffc-date]');
        if (day) { selectDate(day.dataset.ffcDate); }
    });
    document.getElementById('ffc-open-add').addEventListener('click', () => openModal());
    document.getElementById('ffc-add-selected').addEventListener('click', () => openModal(selectedDate));
    document.getElementById('ffc-close-modal').addEventListener('click', closeModal);
    document.getElementById('ffc-cancel-modal').addEventListener('click', closeModal);

    function selectDate(date) {
        selectedDate = date;
        root.querySelectorAll('[data-ffc-date]').forEach(button => button.classList.toggle('is-selected', button.dataset.ffcDate === date));
        selectedDateTitle.textContent = localDate(date);
        const dayEvents = allEvents.filter(item => item.event_date === date);
        const totalIn = dayEvents.filter(e => e.category === 'income').reduce((s,e)=>s+e.amount,0);
        const totalOut = dayEvents.filter(e => e.category !== 'income').reduce((s,e)=>s+e.amount,0);
        closing.textContent = 'Income: ' + money(totalIn) + ' · Outflow: ' + money(totalOut) + ' · Net: ' + money(totalIn-totalOut);
        if (!dayEvents.length) {
            selectedEvents.innerHTML = '<div class="ffc-empty">No events scheduled for this date. Select “Add Event” to create one.</div>';
            return;
        }
        selectedEvents.innerHTML = dayEvents.map(event => `
            <article class="ffc-event-card" data-event-card data-category="${safe(event.category)}" data-status="${safe(event.payment_status)}">
                <span class="ffc-event-icon ffc-event-dot--${colorClass[event.category] || 'living_expense'}"></span>
                <div class="ffc-event-main"><strong>${safe(event.description)}</strong><div class="ffc-event-meta">${safe(labels[event.category] || event.category)} · ${safe(event.payment_status[0].toUpperCase()+event.payment_status.slice(1))}</div>
                ${event.notes ? `<p>${safe(event.notes)}</p>` : ''}
                ${event.reminder_time ? `<div class="ffc-recurring-label">⏰ Reminder: ${safe(event.reminder_time)}${event.is_reminder_sent ? ' · Sent' : ' · Scheduled'}</div>` : ''}
                ${event.is_reminder_sent ? '<div class="ffc-recurring-label">🔒 Reminder sent — this event is locked.</div>' : ''}
                ${event.is_recurring ? `<div class="ffc-recurring-label">↻ Repeats ${safe(event.recurrence_frequency)}${event.recurrence_end_date ? ' until '+safe(localDate(event.recurrence_end_date)) : ''}</div>` : ''}</div>
                <strong class="ffc-event-price">${money(event.amount)}</strong>
                <div class="ffc-event-actions">${event.is_locked ? `<span class="ffc-status">🔒 ${event.is_reminder_sent ? 'Reminder sent' : 'Locked'}</span>` : `<button type="button" class="ffc-small-btn" data-edit-event data-id="${event.id}">✎ Edit</button><form method="POST" action="${event.delete_url}" data-delete-form><input type="hidden" name="_token" value="${safe(csrfToken)}"><input type="hidden" name="_method" value="DELETE"><button class="ffc-small-btn ffc-danger" type="submit">🗑 Delete</button></form>`}</div>
            </article>`).join('');
    }

    const deleteDialog = document.getElementById('ffc-delete-confirm');
    const deleteCancel = document.getElementById('ffc-delete-cancel');
    const deleteConfirm = document.getElementById('ffc-delete-confirm-button');
    let pendingDeleteForm = null;

    // Show a centered in-page confirmation dialog for every delete form,
    // including forms added dynamically to the selected-date event list.
    document.addEventListener('submit', e => {
        const deleteForm = e.target.closest('form[data-delete-form]');
        if (!deleteForm) return;
        if (deleteDialog.dataset.confirmed === '1') {
            deleteDialog.dataset.confirmed = '0';
            return;
        }
        e.preventDefault();
        pendingDeleteForm = deleteForm;
        if (typeof deleteDialog.showModal === 'function') {
            if (!deleteDialog.open) deleteDialog.showModal();
        } else {
            const accepted = window.confirm('Delete this financial event? This action cannot be undone.');
            if (accepted) deleteForm.submit();
        }
    });

    deleteCancel.addEventListener('click', () => {
        pendingDeleteForm = null;
        deleteDialog.close();
    });

    deleteConfirm.addEventListener('click', () => {
        if (!pendingDeleteForm) {
            deleteDialog.close();
            return;
        }
        const formToSubmit = pendingDeleteForm;
        pendingDeleteForm = null;
        deleteDialog.close();
        // Native submit sends the original CSRF token and DELETE method fields.
        formToSubmit.submit();
    });

    deleteDialog.addEventListener('click', e => {
        if (e.target === deleteDialog) {
            pendingDeleteForm = null;
            deleteDialog.close();
        }
    });

    root.querySelectorAll('[data-ffc-tab]').forEach(tab => tab.addEventListener('click', () => {
        root.querySelectorAll('[data-ffc-tab]').forEach(item => item.classList.toggle('is-active', item === tab));
        root.querySelectorAll('[data-ffc-panel]').forEach(panel => panel.classList.toggle('ffc-hidden', panel.dataset.ffcPanel !== tab.dataset.ffcTab));
    }));

    const categoryFilter = document.getElementById('ffc-category-filter');
    const statusFilter = document.getElementById('ffc-status-filter');
    function applyFilters() {
        const category = categoryFilter.value;
        const status = statusFilter.value;
        root.querySelectorAll('.ffc-event-dot').forEach(dot => {
            dot.classList.toggle('ffc-filtered-out', (category !== 'all' && dot.dataset.category !== category) || (status !== 'all' && dot.dataset.status !== status));
        });
        root.querySelectorAll('[data-event-card]').forEach(card => {
            card.classList.toggle('ffc-hidden', (category !== 'all' && card.dataset.category !== category) || (status !== 'all' && card.dataset.status !== status));
        });
    }
    categoryFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    document.getElementById('ffc-ledger-search').addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        root.querySelectorAll('[data-ledger-row]').forEach(row => row.classList.toggle('ffc-hidden', !row.dataset.search.includes(query)));
    });

    selectDate(selectedDate);
});
</script>
@endsection
