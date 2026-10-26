
@extends('layouts.admin')

@section('title', 'Calculator Insights')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-calculator-insights.css') }}">
@endpush

@php
    $dateLabel = static fn ($date, $format = 'd M Y, h:i A') =>
        $date ? \Illuminate\Support\Carbon::parse($date)->format($format) : '—';

    $normalizeValue = function ($value, int $depth = 0) use (&$normalizeValue) {
        if ($depth > 10) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $normalizeValue($item, $depth + 1);
            }
        }

        return $value;
    };

    $toArray = static function ($value) use ($normalizeValue): array {
        $value = $normalizeValue($value);

        return is_array($value) ? $value : [];
    };

    $displayValue = static function ($value) use ($normalizeValue): string {
        $value = $normalizeValue($value);

        if (is_array($value) || is_object($value)) {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) ?: '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return $value === null || $value === '' ? '—' : (string) $value;
    };

    $selectedInputs = $selectedUsage ? $toArray($selectedUsage->inputs) : [];
    $selectedResults = $selectedUsage ? $toArray($selectedUsage->result) : [];

    $detailTab = request('detail_tab', 'result');

    if (! in_array($detailTab, ['inputs', 'result', 'raw'], true)) {
        $detailTab = 'result';
    }

    $chartMaximum = max(1, (int) collect($chartData)->max('count'));

    $donutItems = collect($topCalculators)
        ->map(fn ($item) => [
            'name' => $item->name,
            'count' => (int) $item->total_calculations,
        ])
        ->filter(fn ($item) => $item['count'] > 0)
        ->values();

    $donutTotal = (int) $donutItems->sum('count');

    $donutColors = [
        'var(--primary)',
        'var(--text)',
        'var(--muted)',
        'var(--border)',
        'var(--button-bg)',
    ];

    $donutOffset = 0;

    $donutItems = $donutItems->map(function ($item, $index) use (&$donutOffset, $donutTotal, $donutColors) {
        $item['percentage'] = $donutTotal > 0 ? ($item['count'] / $donutTotal) * 100 : 0;
        $item['offset'] = $donutOffset;
        $item['color'] = $donutColors[$index % count($donutColors)];
        $donutOffset += $item['percentage'];

        return $item;
    });

    $resultLabels = [
        'property_value' => 'Property Value',
        'loan_amount' => 'Loan Amount',
        'monthly_emi' => 'Monthly EMI',
        'down_payment' => 'Down Payment',
        'stamp_duty' => 'Stamp Duty',
        'registration_charges' => 'Registration Charges',
        'total_upfront_investment' => 'Total Upfront Investment',
        'buying_wealth' => 'Buying Wealth',
        'buying_wealth_15_years' => 'Buying Wealth (15 yrs)',
        'renting_wealth' => 'Renting Wealth',
        'renting_wealth_15_years' => 'Renting Wealth (15 yrs)',
        'difference' => 'Difference',
        'recommendation' => 'Recommendation',
        'analysis_years' => 'Analysis Years',
        'break_even_year' => 'Break Even Year',
        'wealth_progression' => 'Year-wise Wealth Progression',
        'schedule' => 'Repayment Schedule',
    ];

    $moneyKeys = [
        'property_value',
        'loan_amount',
        'monthly_emi',
        'down_payment',
        'stamp_duty',
        'registration_charges',
        'total_upfront_investment',
        'buying_wealth',
        'buying_wealth_15_years',
        'renting_wealth',
        'renting_wealth_15_years',
        'difference',
        'emi',
        'principal',
        'interest',
        'balance',
        'total_principal',
        'total_interest',
        'total_payment',
        'total_cost_with_fee',
    ];

    $summaryValues = collect($selectedResults)
        ->filter(fn ($value) => ! is_array($value) && ! is_object($value));

    $structuredValues = collect($selectedResults)
        ->filter(fn ($value) => is_array($value) || is_object($value));
@endphp

@section('content')
<div class="ci-workspace">
    <header class="ci-heading">
        <div>
            <div class="ci-breadcrumb">
                <span>Admin</span>
                <span>/</span>
                <span>Calculator Insights</span>
            </div>
            <h1>Calculator Insights</h1>
            <p>Track calculator usage and understand user activity across LoanEngine.</p>
        </div>

        <form method="GET" action="{{ route('admin.calculator-insights.index') }}" class="ci-date-filter">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @foreach (['q', 'calculator_id', 'user_id', 'status'] as $field)
                @if (!empty($filters[$field]))
                    <input type="hidden" name="{{ $field }}" value="{{ $filters[$field] }}">
                @endif
            @endforeach

            <label class="ci-date-control">
                <span>From</span>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="Start date">
            </label>

            <label class="ci-date-control">
                <span>To</span>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="End date">
            </label>

            <button type="submit">Apply</button>
        </form>
    </header>

    <section class="ci-stat-grid">
        <article class="ci-stat-card">
            <span class="ci-stat-icon" aria-hidden="true">▤</span>
            <div>
                <span class="ci-stat-label">Total Calculations</span>
                <strong>{{ number_format($totalCalculations) }}</strong>
                <small>Matching recorded calculations</small>
            </div>
        </article>

        <article class="ci-stat-card">
            <span class="ci-stat-icon" aria-hidden="true">♙</span>
            <div>
                <span class="ci-stat-label">Unique Users</span>
                <strong>{{ number_format($uniqueUsers) }}</strong>
                <small>Users with matching activity</small>
            </div>
        </article>

        <article class="ci-stat-card">
            <span class="ci-stat-icon" aria-hidden="true">▥</span>
            <div>
                <span class="ci-stat-label">Calculators Used</span>
                <strong>{{ number_format($calculatorsUsed) }}</strong>
                <small>Calculators with matching records</small>
            </div>
        </article>

        <article class="ci-stat-card">
            <span class="ci-stat-icon" aria-hidden="true">✓</span>
            <div>
                <span class="ci-stat-label">Completed Calculations</span>
                <strong>{{ number_format($completedCalculations) }}</strong>
                <small>Successfully recorded calculations</small>
            </div>
        </article>
    </section>

    <section class="ci-chart-grid">
        <article class="ci-panel ci-chart-panel">
            <div class="ci-panel-heading">
                <div>
                    <h2>Calculations Trend</h2>
                    <p>Daily recorded activity · Last 7 days</p>
                </div>
                <span class="ci-panel-icon" aria-hidden="true">↗</span>
            </div>

            <div class="ci-trend-chart">
                <div class="ci-y-axis">
                    <span>{{ $chartMaximum }}</span>
                    <span>{{ number_format($chartMaximum * .75, 0) }}</span>
                    <span>{{ number_format($chartMaximum * .5, 0) }}</span>
                    <span>{{ number_format($chartMaximum * .25, 0) }}</span>
                    <span>0</span>
                </div>

                <div class="ci-plot-area">
                    <div class="ci-grid-lines">
                        @for ($i = 0; $i < 5; $i++)
                            <span></span>
                        @endfor
                    </div>

                    <div class="ci-bars">
                        @foreach ($chartData as $point)
                            @php
                                $height = $point['count'] > 0
                                    ? max(3, ($point['count'] / $chartMaximum) * 100)
                                    : 1;
                            @endphp
                            <div class="ci-bar-column">
                                <span class="ci-bar-value">{{ $point['count'] }}</span>
                                <div class="ci-bar-track">
                                    <div class="ci-bar" style="height: {{ $height }}%"></div>
                                </div>
                                <span class="ci-bar-label">{{ $point['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </article>

        <article class="ci-panel ci-chart-panel">
            <div class="ci-panel-heading">
                <div>
                    <h2>Calculator Usage</h2>
                    <p>Completed calculations by calculator</p>
                </div>
                <span class="ci-panel-icon" aria-hidden="true">◉</span>
            </div>

            @if ($donutTotal > 0)
                <div class="ci-donut-layout">
                    <div class="ci-donut">
                        <svg viewBox="0 0 120 120" role="img" aria-label="Top calculator usage distribution">
                            <circle cx="60" cy="60" r="44" fill="none" stroke="var(--background)" stroke-width="17"/>
                            @foreach ($donutItems as $item)
                                <circle
                                    cx="60"
                                    cy="60"
                                    r="44"
                                    fill="none"
                                    stroke="{{ $item['color'] }}"
                                    stroke-width="17"
                                    stroke-dasharray="{{ $item['percentage'] * 2.7646 }} {{ 276.46 - ($item['percentage'] * 2.7646) }}"
                                    stroke-dashoffset="{{ -$item['offset'] * 2.7646 }}"
                                    transform="rotate(-90 60 60)"
                                />
                            @endforeach
                        </svg>
                        <div class="ci-donut-center">
                            <strong>{{ number_format($donutTotal) }}</strong>
                            <span>Top calculators</span>
                        </div>
                    </div>

                    <div class="ci-legend">
                        @foreach ($donutItems as $item)
                            <div class="ci-legend-item">
                                <span class="ci-legend-dot" style="background: {{ $item['color'] }}"></span>
                                <span class="ci-legend-name" title="{{ $item['name'] }}">{{ $item['name'] }}</span>
                                <strong>{{ number_format($item['count']) }}</strong>
                                <small>{{ number_format($item['percentage'], 1) }}%</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="ci-empty-chart">
                    <strong>No calculator usage recorded</strong>
                    <span>Completed calculations will appear here.</span>
                </div>
            @endif
        </article>
    </section>

    <section class="ci-content-grid">
        <article class="ci-panel ci-history-panel">
            <nav class="ci-tabs" aria-label="Calculator insights views">
                @foreach (['recent' => 'Recent Calculations', 'users' => 'User-wise Insights', 'calculators' => 'Calculator-wise Insights'] as $key => $label)
                    <a
                        class="{{ $tab === $key ? 'is-active' : '' }}"
                        href="{{ route('admin.calculator-insights.index', array_merge(request()->except(['users_page', 'calculators_page', 'detail', 'detail_tab']), ['tab' => $key])) }}"
                    >{{ $label }}</a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('admin.calculator-insights.index') }}" class="ci-filters">
                <input type="hidden" name="tab" value="{{ $tab }}">

                @foreach (['from', 'to'] as $dateField)
                    @if (!empty($filters[$dateField]))
                        <input type="hidden" name="{{ $dateField }}" value="{{ $filters[$dateField] }}">
                    @endif
                @endforeach

                <label class="ci-search">
                    <span aria-hidden="true">⌕</span>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search users, calculators or records">
                </label>

                <select name="calculator_id" aria-label="Filter by calculator">
                    <option value="">All Calculators</option>
                    @foreach ($calculatorOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['calculator_id'] ?? '') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>

                <select name="user_id" aria-label="Filter by user">
                    <option value="">All Users</option>
                    @foreach ($userOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $option->id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status" aria-label="Filter by status">
                    <option value="">All Status</option>
                    <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                    <option value="failed" @selected(($filters['status'] ?? '') === 'failed')>Failed</option>
                </select>

                <button class="ci-filter-button" type="submit">Filter</button>
                <a class="ci-reset-button" href="{{ route('admin.calculator-insights.index', ['tab' => $tab]) }}">Reset</a>
            </form>

            @if ($tab === 'recent')
                <div class="ci-table-scroll">
                    <table class="ci-table ci-recent-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Calculator</th>
                                <th>Inputs</th>
                                <th>Result</th>
                                <th>Status</th>
                                <th>Date &amp; Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentCalculations as $usage)
                                @php
                                    $rowInputs = $toArray($usage->inputs);
                                    $rowResults = $toArray($usage->result);

                                    $inputPreview = collect($rowInputs)
                                        ->map(fn ($value, $key) => \Illuminate\Support\Str::headline((string) $key) . ': ' . $displayValue($value))
                                        ->first();

                                    $resultPreview = collect($rowResults)
                                        ->filter(fn ($value) => !is_array($value) && !is_object($value))
                                        ->map(fn ($value, $key) => \Illuminate\Support\Str::headline((string) $key) . ': ' . $displayValue($value))
                                        ->first();
                                @endphp

                                <tr class="{{ (int) request('detail') === (int) $usage->id ? 'ci-selected-row' : '' }}">
                                    <td>{{ $usage->id }}</td>
                                    <td>
                                        <strong>{{ $usage->user?->name ?? 'Deleted user' }}</strong>
                                        <small>{{ $usage->user?->email ?? 'No email' }}</small>
                                    </td>
                                    <td>{{ $usage->calculator?->name ?? 'Deleted calculator' }}</td>
                                    <td class="ci-preview" title="{{ $inputPreview ?: 'No inputs saved' }}">
                                        {{ \Illuminate\Support\Str::limit($inputPreview ?: '—', 34) }}
                                    </td>
                                    <td class="ci-preview" title="{{ $resultPreview ?: 'No result saved' }}">
                                        {{ \Illuminate\Support\Str::limit($resultPreview ?: '—', 34) }}
                                    </td>
                                    <td>
                                        <span class="ci-status-pill {{ ($usage->status ?? '') === 'completed' ? 'is-completed' : (($usage->status ?? '') === 'failed' ? 'is-failed' : '') }}">
                                            <span></span>{{ ucfirst($usage->status ?? 'Unknown') }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $dateLabel($usage->created_at, 'd M Y') }}
                                        <small>{{ $dateLabel($usage->created_at, 'h:i A') }}</small>
                                    </td>
                                    <td>
                                        <a
                                            class="ci-view-button"
                                            href="{{ route('admin.calculator-insights.index', array_merge(request()->except(['users_page', 'calculators_page']), ['tab' => 'recent', 'detail' => $usage->id, 'detail_tab' => 'result'])) }}"
                                            aria-label="View calculation {{ $usage->id }}"
                                            title="View calculation details"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                <path d="M2.5 12s3.4-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.4 6.5-9.5 6.5S2.5 12 2.5 12Z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="ci-no-records">No calculation records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="ci-table-footer">
                    <span>Showing {{ $recentCalculations->firstItem() ?? 0 }}–{{ $recentCalculations->lastItem() ?? 0 }} of {{ number_format($recentCalculations->total()) }} records</span>
                    {{ $recentCalculations->links() }}
                </div>

            @elseif ($tab === 'users')
                <div class="ci-table-scroll">
                    <table class="ci-table">
                        <thead>
                            <tr><th>User</th><th>Email</th><th>Total Calculations</th><th>Last Activity</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td><strong>{{ $user->name }}</strong><small>User #{{ $user->id }}</small></td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="ci-count-pill">{{ number_format($user->total_calculations) }}</span></td>
                                    <td>{{ $dateLabel($user->last_calculation_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="ci-no-records">No user activity found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="ci-table-footer">
                    <span>{{ number_format($users->total()) }} users</span>
                    {{ $users->links() }}
                </div>

            @else
                <div class="ci-table-scroll">
                    <table class="ci-table">
                        <thead>
                            <tr><th>Calculator</th><th>Slug</th><th>Formula Type</th><th>Total Calculations</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($calculators as $calculator)
                                <tr>
                                    <td><strong>{{ $calculator->name }}</strong><small>Calculator #{{ $calculator->id }}</small></td>
                                    <td>{{ $calculator->slug }}</td>
                                    <td>{{ $calculator->formula_type ?: '—' }}</td>
                                    <td><span class="ci-count-pill">{{ number_format($calculator->total_calculations) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="ci-no-records">No calculator records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="ci-table-footer">
                    <span>{{ number_format($calculators->total()) }} calculators</span>
                    {{ $calculators->links() }}
                </div>
            @endif
        </article>

        <aside class="ci-panel ci-details-panel" id="calculation-details">
            <div class="ci-details-heading">
                <div>
                    <h2>Calculation Details</h2>
                    <p>{{ $selectedUsage ? 'Record #' . $selectedUsage->id : 'Select a calculation to inspect its data' }}</p>
                </div>

                @if ($selectedUsage)
                    <a
                        class="ci-close-details"
                        href="{{ route('admin.calculator-insights.index', request()->except(['detail', 'detail_tab'])) }}"
                        aria-label="Close calculation details"
                    >×</a>
                @endif
            </div>

            @if ($selectedUsage)
                <div class="ci-detail-identity">
                    <span class="ci-detail-icon" aria-hidden="true">▤</span>
                    <div>
                        <strong>{{ $selectedUsage->calculator?->name ?? 'Deleted calculator' }}</strong>
                        <small>{{ $selectedUsage->user?->name ?? 'Deleted user' }} ({{ $selectedUsage->user?->email ?? 'No email' }})</small>
                        <small>Date: {{ $dateLabel($selectedUsage->created_at) }}</small>
                    </div>
                    <span class="ci-status-pill {{ ($selectedUsage->status ?? '') === 'completed' ? 'is-completed' : (($selectedUsage->status ?? '') === 'failed' ? 'is-failed' : '') }}">
                        <span></span>{{ ucfirst($selectedUsage->status ?? 'Unknown') }}
                    </span>
                </div>

                <nav class="ci-detail-tabs" aria-label="Calculation detail sections">
                    @foreach (['inputs' => 'Inputs', 'result' => 'Result', 'raw' => 'Raw Data'] as $key => $label)
                        <a
                            class="{{ $detailTab === $key ? 'is-active' : '' }}"
                            href="{{ route('admin.calculator-insights.index', array_merge(request()->except('detail_tab'), ['detail' => $selectedUsage->id, 'detail_tab' => $key])) }}"
                        >{{ $label }}</a>
                    @endforeach
                </nav>

                <div class="ci-detail-body">
                    @if ($detailTab === 'inputs')
                        <div class="ci-data-heading">
                            <div><h3>Calculation Inputs</h3><p>Values supplied to the calculator</p></div>
                            <span>{{ count($selectedInputs) }} fields</span>
                        </div>

                        @forelse ($selectedInputs as $key => $value)
                            <div class="ci-detail-row">
                                <span>{{ \Illuminate\Support\Str::headline((string) $key) }}</span>
                                <strong>
                                    @if (is_array($value) || is_object($value))
                                        @include('admin.calculator-insights.partials.result-renderer',['value' => $value, 'depth' => 0])
                                    @else
                                        {{ $displayValue($value) }}
                                    @endif
                                </strong>
                            </div>
                        @empty
                            <p class="ci-detail-empty">No input data was saved for this calculation.</p>
                        @endforelse

                    @elseif ($detailTab === 'result')
                        <div class="ci-data-heading">
                            <div><h3>Calculation Summary</h3><p>Key results from this calculation</p></div>
                            <span>{{ count($selectedResults) }} metrics</span>
                        </div>

                        @if ($summaryValues->isNotEmpty())
                            <div class="ci-result-table-wrap">
                                <table class="ci-result-table">
                                    <tbody>
                                        @foreach ($summaryValues as $key => $value)
                                            <tr>
                                                <th scope="row">{{ $resultLabels[$key] ?? \Illuminate\Support\Str::headline((string) $key) }}</th>
                                                <td>
                                                    @if ($key === 'recommendation')
                                                        <span class="ci-recommendation">{{ strtoupper((string) $value) }}</span>
                                                    @elseif (in_array($key, $moneyKeys, true) && is_numeric($value))
                                                        ₹{{ number_format((float) $value, 2, '.', ',') }}
                                                    @else
                                                        {{ $displayValue($value) }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @foreach ($structuredValues as $key => $value)
                            <section class="ci-wealth-section">
                                <div class="ci-data-heading">
                                    <div>
                                        <h3>{{ $resultLabels[$key] ?? \Illuminate\Support\Str::headline((string) $key) }}</h3>
                                        <p>Detailed result data</p>
                                    </div>
                                </div>

                                @include('admin.calculator-insights.partials.result-renderer', [
                                    'value' => $value,
                                    'depth' => 0,
                                ])
                            </section>
                        @endforeach

                        @if ($selectedResults === [])
                            <p class="ci-detail-empty">No result data was saved for this calculation.</p>
                        @endif

                    @else
                        <div class="ci-data-heading">
                            <div><h3>Raw Calculation Data</h3><p>Saved input and result payload</p></div>
                        </div>
                        <pre class="ci-raw-data">{{ json_encode(['inputs' => $selectedInputs, 'result' => $selectedResults], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'No data available' }}</pre>
                    @endif
                </div>

                <div class="ci-detail-meta">
                    <span>Event type</span>
                    <strong>{{ $selectedUsage->event_type ?: '—' }}</strong>
                    <span>Session ID</span>
                    <strong>{{ $selectedUsage->session_id ?: '—' }}</strong>
                </div>
            @else
                <div class="ci-detail-placeholder">
                    <span aria-hidden="true">◎</span>
                    <strong>No calculation selected</strong>
                    <p>Use the eye icon in the recent calculations table to view inputs, results, and raw data here.</p>
                </div>
            @endif
        </aside>
    </section>
</div>
@endsection
