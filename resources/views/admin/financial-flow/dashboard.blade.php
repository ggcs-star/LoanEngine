
@extends('layouts.admin')

@section('content')
@php
    $money = fn ($value) => '₹' . number_format((float) $value, 0);
    $label = fn ($value) => filled($value)
        ? ucwords(str_replace(['_', '-'], ' ', $value))
        : 'Uncategorized';

    $monthUrl = fn ($target) => route('admin.financial-flow.index', array_merge(
        request()->except(['year', 'month', 'date', 'page']),
        ['year' => $target->year, 'month' => $target->month]
    ));
@endphp

<div class="ff-page">
    @if(session('error'))
        <div class="ff-alert">{{ session('error') }}</div>
    @endif

    <header class="ff-heading">
        <div class="ff-title-block">
            <span class="ff-eyebrow">FINANCIAL MANAGEMENT</span>
            <h1>Financial Flow Overview</h1>
            <p>
                {{ $selectedUser ? 'Financial overview for ' . $selectedUser->name : 'Monitor financial events across all users.' }}
            </p>
        </div>

        <div class="ff-heading-actions">
            <form method="GET" action="{{ route('admin.financial-flow.index') }}"
                  class="ff-period-form" id="ffPeriodForm">
                <div class="ff-field">
                    <label for="ffUser">Financial data</label>
                    <select id="ffUser" name="user_id">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                @selected((string)($filters['user_id'] ?? '') === (string)$user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ff-field">
                    <label for="ffMonth">Reporting month</label>
                    <input id="ffMonth" type="month"
                           value="{{ $month->format('Y-m') }}" required>
                </div>

                <button class="ff-button" type="submit">Apply</button>

                <a class="ff-button ff-button-light"
                   href="{{ route('admin.financial-flow.export', request()->query()) }}">
                    Export CSV
                </a>
            </form>
        </div>
    </header>

    <section class="ff-kpis">
        <article class="ff-kpi">
            <div class="ff-kpi-top">
                <span>Total Income</span>
                <span class="ff-kpi-icon ff-icon-primary">↗</span>
            </div>
            <strong>{{ $money($summary['income']) }}</strong>
            <small>Category: income</small>
        </article>

        <article class="ff-kpi">
            <div class="ff-kpi-top">
                <span>Total Expenses</span>
                <span class="ff-kpi-icon ff-icon-secondary">↘</span>
            </div>
            <strong>{{ $money($summary['expenses']) }}</strong>
            <small>Identified expense categories</small>
        </article>

        <article class="ff-kpi">
            <div class="ff-kpi-top">
                <span>EMI Obligations</span>
                <span class="ff-kpi-icon ff-icon-accent">▤</span>
            </div>
            <strong>{{ $money($summary['emi']) }}</strong>
            <small>Category: loan_emi</small>
        </article>

        <article class="ff-kpi">
            <div class="ff-kpi-top">
                <span>Total Events</span>
                <span class="ff-kpi-icon ff-icon-neutral">◷</span>
            </div>
            <strong>{{ number_format($summary['total_events']) }}</strong>
            <small>{{ number_format($summary['unique_users']) }} users with events</small>
        </article>
    </section>

    <section class="ff-charts">
        <article class="ff-card">
            <div class="ff-card-heading">
                <div>
                    <h2>Weekly Activity</h2>
                    <p>Income, expenses and EMI</p>
                </div>
            </div>
            <div class="ff-legend">
                <span><i class="ff-dot ff-dot-primary"></i>Income</span>
                <span><i class="ff-dot ff-dot-secondary"></i>Expenses</span>
                <span><i class="ff-dot ff-dot-accent"></i>EMI</span>
            </div>
            <div id="ffWeeklyChart" class="ff-bar-chart"></div>
        </article>

        <article class="ff-card">
            <div class="ff-card-heading">
                <div>
                    <h2>Events by Category</h2>
                    <p>Actual database categories</p>
                </div>
            </div>

            <div class="ff-donut-wrap">
                <svg id="ffCategoryDonut" class="ff-donut-svg"
                     viewBox="0 0 180 180" role="img"
                     aria-label="Events by category">
                    <circle class="ff-donut-track" cx="90" cy="90" r="67"></circle>
                    <g id="ffDonutSegments"></g>
                </svg>
                <div class="ff-donut-center">
                    <strong id="ffCategoryTotal">₹0</strong>
                    <span>Total amount</span>
                </div>
            </div>
            <div id="ffCategoryLegend" class="ff-category-legend"></div>
        </article>

        <article class="ff-card">
            <div class="ff-card-heading">
                <div>
                    <h2>Monthly Trend</h2>
                    <p>Year-to-date totals</p>
                </div>
            </div>
            <div class="ff-legend">
                <span><i class="ff-dot ff-dot-primary"></i>Income</span>
                <span><i class="ff-dot ff-dot-secondary"></i>Expenses</span>
                <span><i class="ff-dot ff-dot-accent"></i>EMI</span>
            </div>
            <div id="ffTrendChart" class="ff-trend-chart"></div>
        </article>
    </section>

    <section class="ff-calendar-layout">
        <article class="ff-card ff-calendar-card">
            <div class="ff-card-heading ff-calendar-heading">
                <div>
                    <h2>Financial Calendar</h2>
                    <p>Choose a date to view its events.</p>
                </div>
                <div class="ff-calendar-nav">
                    <a href="{{ $monthUrl($month->copy()->subMonth()) }}"
                       aria-label="Previous month">‹</a>
                    <strong>{{ $month->format('F Y') }}</strong>
                    <a href="{{ $monthUrl($month->copy()->addMonth()) }}"
                       aria-label="Next month">›</a>
                </div>
            </div>

            <div class="ff-weekdays">
                @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                    <span>{{ $day }}</span>
                @endforeach
            </div>

            <div id="ffCalendarGrid" class="ff-calendar-grid"></div>

            <div class="ff-calendar-key">
                <span><i class="ff-dot ff-dot-primary"></i>Income</span>
                <span><i class="ff-dot ff-dot-secondary"></i>Expenses</span>
                <span><i class="ff-dot ff-dot-accent"></i>EMI / Other</span>
            </div>
        </article>

        <article class="ff-card ff-selected-card">
            <div class="ff-card-heading">
                <div>
                    <h2>Selected Date</h2>
                    <p id="ffSelectedLabel"></p>
                </div>
            </div>
            <div id="ffSelectedEvents" class="ff-selected-events"></div>
        </article>
    </section>

    <section class="ff-card ff-table-card" id="financial-events">
        <div class="ff-card-heading ff-table-heading">
            <div>
                <h2>Financial Events</h2>
                <p>
                    {{ $selectedUser ? 'Records for ' . $selectedUser->name : 'Records for all users' }}
                </p>
            </div>
            <span class="ff-record-count">{{ $events->total() }} records</span>
        </div>

        <form method="GET" action="{{ route('admin.financial-flow.index') }}"
              class="ff-filters">
            <input type="hidden" name="year" value="{{ $month->year }}">
            <input type="hidden" name="month" value="{{ $month->month }}">

            <input type="search" name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="Search events or users…" aria-label="Search events">

            <select name="category" aria-label="Category filter">
                <option value="">All categories</option>
                @foreach($categories as $key => $categoryName)
                    <option value="{{ $key }}"
                        @selected(($filters['category'] ?? '') === $key)>
                        {{ $categoryName }}
                    </option>
                @endforeach
            </select>

            <select name="status" aria-label="Status filter">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}"
                        @selected(($filters['status'] ?? '') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>

            <select name="user_id" aria-label="User filter">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        @selected((string)($filters['user_id'] ?? '') === (string)$user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>

            <button class="ff-button" type="submit">Apply filters</button>

            <a class="ff-button ff-button-light"
               href="{{ route('admin.financial-flow.index', [
                   'year' => $month->year,
                   'month' => $month->month,
                   'user_id' => $filters['user_id'] ?? null,
               ]) }}">Reset filters</a>
        </form>

        <div class="ff-table-scroll">
            <table class="ff-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>User</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Event Date</th>
                        <th>Status</th>
                        <th>Recurrence</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>
                                <div class="ff-event-name">{{ $event->description }}</div>
                                @if($event->notes)
                                    <small class="ff-notes">
                                        {{ \Illuminate\Support\Str::limit($event->notes, 65) }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $event->user?->name ?? 'Unknown user' }}</td>
                            <td>
                                <span class="ff-category-badge">
                                    {{ $categories[$event->category] ?? $label($event->category) }}
                                </span>
                            </td>
                            <td class="ff-amount">{{ $money($event->amount) }}</td>
                            <td>{{ $event->event_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <span class="ff-status ff-status-{{ $event->payment_status }}">
                                    {{ ucfirst($event->payment_status ?? 'Unknown') }}
                                </span>
                            </td>
                            <td>
                                @if($event->is_recurring)
                                    <span class="ff-recurring">
                                        ↻ {{ ucfirst($event->recurrence_frequency ?? 'Recurring') }}
                                    </span>
                                @else
                                    <span class="ff-muted">One-time</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ff-empty">
                                No events found for this selection.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ff-pagination">{{ $events->links() }}</div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const events = @json($calendarEvents);
    const categories = @json($categoryBreakdown);
    const weekly = @json($weeklyBreakdown);
    const trend = @json($monthlyTrend);
    const initialDate = @json($selectedDate);
    const year = @json($month->year);
    const month = @json($month->month);

    const money = value => new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    }).format(Number(value || 0));

    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[char]);

    const kind = category => {
        const value = String(category || '').toLowerCase();
        if (value === 'income') return 'income';
        if (value === 'loan_emi') return 'emi';
        if (value.includes('expense')) return 'expense';
        return 'other';
    };

    const categoryLabel = category => category
        ? String(category).replace(/[_-]+/g, ' ')
            .replace(/\b\w/g, char => char.toUpperCase())
        : 'Uncategorized';

    const css = getComputedStyle(document.documentElement);
    const palette = [
        css.getPropertyValue('--ff-chart-primary').trim(),
        css.getPropertyValue('--ff-chart-secondary').trim(),
        css.getPropertyValue('--ff-chart-accent').trim(),
        css.getPropertyValue('--ff-chart-neutral').trim()
    ];

    // Weekly bar chart
    const weeklyRoot = document.getElementById('ffWeeklyChart');
    const maxWeekly = Math.max(1, ...weekly.flatMap(item => [
        Number(item.income || 0),
        Number(item.expenses || 0),
        Number(item.emi || 0)
    ]));

    const barHeight = 140;
    const groupWidth = 66;
    const weeklyWidth = Math.max(300, weekly.length * groupWidth);

    const weeklyMarkup = weekly.map((item, index) => {
        const x = index * groupWidth + 5;

        const rect = (value, offset, cssClass, seriesLabel) => {
            const amount = Number(value || 0);
            const height = amount > 0 ? Math.max(2, amount / maxWeekly * barHeight) : 0;
            const y = barHeight - height;

            return `<rect class="${cssClass}" x="${x + offset}" y="${y}"
                width="14" height="${height}" rx="3">
                <title>${esc(seriesLabel)}: ${money(amount)}</title>
            </rect>`;
        };

        return `${rect(item.income, 0, 'ff-svg-primary', 'Income')}
            ${rect(item.expenses, 17, 'ff-svg-secondary', 'Expenses')}
            ${rect(item.emi, 34, 'ff-svg-accent', 'EMI')}
            <text class="ff-svg-label" x="${x + 24}" y="${barHeight + 20}"
                text-anchor="middle">${esc(item.label)}</text>`;
    }).join('');

    weeklyRoot.innerHTML = `<svg class="ff-chart-svg ff-weekly-svg"
        viewBox="0 0 ${weeklyWidth} 185" role="img" aria-label="Weekly totals">
        <line class="ff-svg-grid" x1="0" y1="35" x2="${weeklyWidth}" y2="35"/>
        <line class="ff-svg-grid" x1="0" y1="70" x2="${weeklyWidth}" y2="70"/>
        <line class="ff-svg-grid" x1="0" y1="105" x2="${weeklyWidth}" y2="105"/>
        <line class="ff-svg-grid" x1="0" y1="${barHeight}" x2="${weeklyWidth}" y2="${barHeight}"/>
        ${weeklyMarkup}
    </svg>`;

    // Donut chart: segment color is controlled by CSS classes.
    const radius = 67;
    const circumference = 2 * Math.PI * radius;
    const total = categories.reduce((sum, item) => sum + Number(item.amount || 0), 0);
    document.getElementById('ffCategoryTotal').textContent = money(total);

    let dashOffset = 0;
    document.getElementById('ffDonutSegments').innerHTML = categories.map((item, index) => {
        const length = total > 0 ? Number(item.amount || 0) / total * circumference : 0;
        const offset = dashOffset;
        dashOffset += length;

        return `<circle class="ff-donut-segment ff-segment-${index % 8}"
            cx="90" cy="90" r="${radius}"
            stroke-dasharray="${length} ${circumference - length}"
            stroke-dashoffset="${-offset}">
            <title>${esc(item.label)}: ${money(item.amount)}</title>
        </circle>`;
    }).join('');

    document.getElementById('ffCategoryLegend').innerHTML = categories.length
        ? categories.map((item, index) => `
            <div class="ff-category-row">
                <span class="ff-category-name">
                    <i class="ff-category-color ff-segment-${index % 8}"></i>
                    <span>${esc(item.label)}
                        <small>${Number(item.count)} event${Number(item.count) === 1 ? '' : 's'}</small>
                    </span>
                </span>
                <strong>${money(item.amount)}</strong>
            </div>
        `).join('')
        : '<p class="ff-chart-empty">No categories recorded.</p>';

    // Monthly trend chart
    const root = document.getElementById('ffTrendChart');
    const width = 340, height = 175, left = 14, right = 10, top = 12, bottom = 27;
    const plotWidth = width - left - right;
    const plotHeight = height - top - bottom;

    const maxTrend = Math.max(1, ...trend.flatMap(item => [
        Number(item.income || 0),
        Number(item.expenses || 0),
        Number(item.emi || 0)
    ]));

    const px = index => trend.length <= 1
        ? left + plotWidth / 2
        : left + index * plotWidth / (trend.length - 1);

    const py = value => top + plotHeight - Number(value || 0) / maxTrend * plotHeight;

    const points = key => trend.map((item, index) => `${px(index)},${py(item[key])}`).join(' ');

    const dots = key => trend.map((item, index) =>
        `<circle class="ff-point-${key}" cx="${px(index)}" cy="${py(item[key])}" r="2.8"/>`
    ).join('');

    const labels = trend.map((item, index) =>
        `<text class="ff-svg-label" x="${px(index)}" y="${height - 6}"
            text-anchor="middle">${esc(item.label)}</text>`
    ).join('');

    const gridLines = [0, 1, 2, 3].map(index => {
        const y = top + plotHeight * index / 3;
        return `<line class="ff-svg-grid" x1="${left}" y1="${y}" x2="${width-right}" y2="${y}"/>`;
    }).join('');

    root.innerHTML = `<svg class="ff-chart-svg" viewBox="0 0 ${width} ${height}"
        role="img" aria-label="Monthly income, expenses and EMI trend">
        ${gridLines}
        <polyline class="ff-line-primary" points="${points('income')}"/>
        <polyline class="ff-line-secondary" points="${points('expenses')}"/>
        <polyline class="ff-line-accent" points="${points('emi')}"/>
        ${dots('income')}${dots('expenses')}${dots('emi')}${labels}
    </svg>`;

    // Calendar
    const grouped = {};
    events.forEach(event => {
        if (event.date) (grouped[event.date] ||= []).push(event);
    });

    const calendar = document.getElementById('ffCalendarGrid');
    const selectedLabel = document.getElementById('ffSelectedLabel');
    const selectedPanel = document.getElementById('ffSelectedEvents');

    const first = new Date(year, month - 1, 1);
    const days = new Date(year, month, 0).getDate();
    const leading = (first.getDay() + 6) % 7;

    for (let index = 0; index < leading; index++) {
        const blank = document.createElement('div');
        blank.className = 'ff-day ff-day-empty';
        calendar.appendChild(blank);
    }

    function showDate(date) {
        calendar.querySelectorAll('.ff-day[data-date]').forEach(button => {
            const active = button.dataset.date === date;
            button.classList.toggle('is-selected', active);
            button.setAttribute('aria-pressed', String(active));
        });

        selectedLabel.textContent = new Date(`${date}T00:00:00`)
            .toLocaleDateString('en-IN', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
            });

        const items = grouped[date] || [];

        selectedPanel.innerHTML = items.length
            ? items.map(event => `
                <div class="ff-selected-event">
                    <div class="ff-selected-event-top">
                        <span class="ff-category-badge">${esc(categoryLabel(event.category))}</span>
                        <strong>${money(event.amount)}</strong>
                    </div>
                    <div class="ff-selected-event-title">${esc(event.description)}</div>
                    <div class="ff-selected-event-meta">${esc(event.user)}</div>
                    <div class="ff-selected-event-meta">
                        ${esc(event.status || 'Unknown')}
                        ${event.is_recurring ? ' · Recurring' : ''}
                    </div>
                </div>
            `).join('')
            : '<div class="ff-no-events">No events scheduled for this date.</div>';
    }

    for (let day = 1; day <= days; day++) {
        const date = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const items = grouped[date] || [];
        const button = document.createElement('button');

        button.type = 'button';
        button.className = 'ff-day';
        button.dataset.date = date;
        button.setAttribute('aria-label', `${date}, ${items.length} events`);

        const number = document.createElement('span');
        number.className = 'ff-day-number';
        number.textContent = day;
        button.appendChild(number);

        const markers = document.createElement('span');
        markers.className = 'ff-day-markers';

        [...new Set(items.map(event => kind(event.category)))].slice(0, 3).forEach(type => {
            const dot = document.createElement('i');
            dot.className = `ff-day-dot ff-day-${type}`;
            markers.appendChild(dot);
        });

        button.appendChild(markers);

        if (items.length) {
            const count = document.createElement('small');
            count.textContent = `${items.length} event${items.length === 1 ? '' : 's'}`;
            button.appendChild(count);
        }

        button.addEventListener('click', () => showDate(date));
        calendar.appendChild(button);
    }

    showDate(initialDate);

    // Keep month and selected user in the same GET request.
    document.getElementById('ffPeriodForm').addEventListener('submit', event => {
        const value = document.getElementById('ffMonth').value;

        if (!value) {
            event.preventDefault();
            return;
        }

        const [selectedYear, selectedMonth] = value.split('-');
        const form = event.currentTarget;

        ['year', 'month'].forEach(name => {
            const existing = form.querySelector(`[name="${name}"]`);
            if (existing) existing.remove();
        });

        [['year', selectedYear], ['month', selectedMonth]].forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        });
    });
});
</script>
@endsection
