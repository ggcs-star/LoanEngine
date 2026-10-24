
@extends('user.layouts.app')

@section('title', $tool->name . ' | LoanEngine')
@section('page_heading', $tool->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calculators/home-affordability.css') }}">
@endpush

@section('content')
@php
    $defaults = [
        'property_price' => 10000000,
        'down_payment_percent' => 40,
        'interest_rate' => 6.9,
        'tenure_years' => 20,
        'monthly_rent' => 15000,
        'appreciation_rate' => 6,
        'rent_increase_rate' => 7,
        'investment_return' => 12,
        'inflation_rate' => 6,
        'maintenance_percent' => 1,
        'analysis_years' => 15,
    ];

    $values = array_merge($defaults, $inputs ?? [], old());
    $money = fn ($v) => '₹' . number_format((float) $v, 0, '.', ',');
    $hasResult = is_array($result ?? null) && isset($result['buying_wealth']);
    $buying = (float) ($result['buying_wealth'] ?? 0);
    $renting = (float) ($result['renting_wealth'] ?? 0);
    $maxWealth = max(1, $buying, $renting);
    $progression = $result['wealth_progression'] ?? [];
@endphp

<div class="ha-calculator" id="home-affordability">

    <a class="ha-calculator__back" href="{{ route('user.tools.index') }}">
        ← Back to all tools
    </a>

    <header class="ha-calculator__header ha-calculator__header--banner">
        <div class="ha-calculator__header-content">
            <span class="ha-calculator__eyebrow">LOANENGINE · CALCULATOR</span>
            <h1 class="ha-calculator__title">{{ $tool->name }}</h1>
            <p class="ha-calculator__description">
                Compare the estimated cost of buying a property against renting and investing.
            </p>
            <a class="ha-calculator__hero-action" href="#home-affordability-form">
                Start calculating <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="ha-calculator__hero-note" aria-hidden="true">
            <span class="ha-calculator__hero-mark">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6"
                          stroke="currentColor" stroke-width="1.7"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="ha-calculator__hero-note-label">YOUR NEXT FINANCIAL MOVE</span>
            <strong>Plan with confidence.</strong>
            <span class="ha-calculator__hero-note-copy">Smarter calculations. Clearer decisions.</span>
            <span class="ha-calculator__hero-rule"></span>
        </div>

        <div class="ha-calculator__hero-footer">
            <span>LOANENGINE</span>
            <span>PLAN SMARTER · MOVE FORWARD</span>
        </div>
    </header>

    @if ($errors->any())
        <div class="ha-calculator__errors" role="alert">
            <strong>Please check your inputs.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('calculators.calculate', ['tool' => $tool->slug]) }}"
          id="home-affordability-form">
        @csrf

        {{-- BUYING DETAILS --}}
        <section class="ha-calculator__panel">
            <h2 class="ha-calculator__section-title">
                <span class="ha-calculator__section-icon">⌂</span>
                Buying Details
            </h2>

            <div class="ha-calculator__field">
                <div class="ha-calculator__field-header">
                    <label class="ha-calculator__label" for="property_price_number">
                        Property Price
                    </label>
                    <input class="ha-calculator__value"
                           id="property_price_number" type="number"
                           min="1000000" max="50000000" step="100000"
                           value="{{ $values['property_price'] }}"
                           data-sync="property_price" required>
                </div>
                <input class="ha-calculator__range" type="range"
                       min="1000000" max="50000000" step="100000"
                       value="{{ $values['property_price'] }}"
                       data-sync="property_price" aria-label="Property price">
                <div class="ha-calculator__range-labels">
                    <span>₹10L</span><span>₹5Cr</span>
                </div>
                <input type="hidden" name="property_price" id="property_price"
                       value="{{ $values['property_price'] }}">
            </div>

            <div class="ha-calculator__field" style="margin-top:24px">
                <div class="ha-calculator__field-header">
                    <label class="ha-calculator__label" for="down_payment_percent_number">
                        Down Payment
                    </label>
                    <strong class="ha-calculator__value">
                        <span id="down_payment_percent_display">{{ $values['down_payment_percent'] }}</span>%
                    </strong>
                </div>
                <input class="ha-calculator__range" type="range"
                       min="10" max="90" step="1"
                       value="{{ $values['down_payment_percent'] }}"
                       data-sync="down_payment_percent"
                       aria-label="Down payment">
                <div class="ha-calculator__range-labels">
                    <span>10%</span><span>90%</span>
                </div>
                <p class="ha-calculator__muted">
                    Down payment amount:
                    <strong id="down_payment_amount">
                        {{ $money($values['property_price'] * $values['down_payment_percent'] / 100) }}
                    </strong>
                </p>
                <input type="hidden" name="down_payment_percent" id="down_payment_percent"
                       value="{{ $values['down_payment_percent'] }}">
            </div>

            <div class="ha-calculator__compact-grid">
                <div class="ha-calculator__compact-field">
                    <div class="ha-calculator__field-header">
                        <label class="ha-calculator__label" for="interest_rate_number">
                            Interest (%)
                        </label>
                        <input class="ha-calculator__value" id="interest_rate_number"
                               type="number" min="0" max="30" step="0.1"
                               value="{{ $values['interest_rate'] }}"
                               data-sync="interest_rate" required>
                    </div>
                    <input class="ha-calculator__range" type="range"
                           min="0" max="30" step="0.1"
                           value="{{ $values['interest_rate'] }}"
                           data-sync="interest_rate" aria-label="Interest rate">
                    <div class="ha-calculator__range-labels"><span>0%</span><span>30%</span></div>
                    <input type="hidden" name="interest_rate" id="interest_rate"
                           value="{{ $values['interest_rate'] }}">
                </div>

                <div class="ha-calculator__compact-field">
                    <div class="ha-calculator__field-header">
                        <label class="ha-calculator__label" for="tenure_years_number">
                            Tenure (Yrs)
                        </label>
                        <input class="ha-calculator__value" id="tenure_years_number"
                               type="number" min="1" max="40" step="1"
                               value="{{ $values['tenure_years'] }}"
                               data-sync="tenure_years" required>
                    </div>
                    <input class="ha-calculator__range" type="range"
                           min="1" max="40" step="1"
                           value="{{ $values['tenure_years'] }}"
                           data-sync="tenure_years" aria-label="Loan tenure">
                    <div class="ha-calculator__range-labels"><span>1 year</span><span>40 years</span></div>
                    <input type="hidden" name="tenure_years" id="tenure_years"
                           value="{{ $values['tenure_years'] }}">
                </div>
            </div>

            <div class="ha-calculator__recommendation" style="margin-top:24px;margin-bottom:0">
                <div class="ha-calculator__recommendation-label">Projected Monthly EMI</div>
                <h3 class="ha-calculator__recommendation-title" id="emi_preview">—</h3>
                <p class="ha-calculator__muted">Estimated EMI updates as you adjust the loan details.</p>
            </div>
        </section>

        {{-- RENTING & ECONOMICS --}}
        <section class="ha-calculator__panel">
            <h2 class="ha-calculator__section-title">
                <span class="ha-calculator__section-icon">▦</span>
                Renting &amp; Economics
            </h2>

            @php
                $economicFields = [
                    ['monthly_rent', 'Current Monthly Rent', 5000, 150000, 500, '₹5k', '₹1.5L'],
                    ['appreciation_rate', 'Property Appreciation', -10, 20, 0.5, '-10%', '20%'],
                    ['rent_increase_rate', 'Annual Rent Increase', -10, 20, 0.5, '-10%', '20%'],
                    ['investment_return', 'Investment Returns (Equity)', -10, 30, 0.5, '-10%', '30%'],
                    ['inflation_rate', 'Inflation Rate', -10, 30, 0.5, '-10%', '30%'],
                    ['maintenance_percent', 'Maintenance & Tax', 0, 10, 0.5, '0%', '10%'],
                ];
            @endphp

            <div class="ha-calculator__grid">
                @foreach ($economicFields as [$key, $label, $min, $max, $step, $minLabel, $maxLabel])
                    <div class="ha-calculator__field">
                        <div class="ha-calculator__field-header">
                            <label class="ha-calculator__label" for="{{ $key }}_number">
                                {{ $label }}
                            </label>
                            <input class="ha-calculator__value"
                                   id="{{ $key }}_number" type="number"
                                   min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
                                   value="{{ $values[$key] }}" data-sync="{{ $key }}" required>
                        </div>
                        <input class="ha-calculator__range" type="range"
                               min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
                               value="{{ max($min, min($max, (float) $values[$key])) }}"
                               data-sync="{{ $key }}" aria-label="{{ $label }}">
                        <div class="ha-calculator__range-labels">
                            <span>{{ $minLabel }}</span><span>{{ $maxLabel }}</span>
                        </div>
                        <input type="hidden" name="{{ $key }}" id="{{ $key }}"
                               value="{{ $values[$key] }}">
                    </div>
                @endforeach
            </div>

            <div class="ha-calculator__field" style="margin-top:28px">
                <h3 class="ha-calculator__section-title">Analysis Period</h3>
                <div class="ha-calculator__field-header">
                    <label class="ha-calculator__label" for="analysis_years_number">
                        Simulating your net worth over time
                    </label>
                    <strong class="ha-calculator__value">
                        <span id="analysis_years_display">{{ $values['analysis_years'] }}</span> years
                    </strong>
                </div>
                <input class="ha-calculator__range" id="analysis_years_number"
                       type="range" min="5" max="30" step="1"
                       value="{{ $values['analysis_years'] }}"
                       data-sync="analysis_years" aria-label="Analysis period">
                <div class="ha-calculator__range-labels">
                    <span>5 Years</span><span>30 Years</span>
                </div>
                <input type="hidden" name="analysis_years" id="analysis_years"
                       value="{{ $values['analysis_years'] }}">
            </div>

            <button class="ha-calculator__submit" type="submit">
                Calculate Rent vs Buy →
            </button>
        </section>
    </form>

    {{-- CALCULATED RESULTS --}}
    @if ($hasResult)
        <section id="calculator-results">
            <h2 class="ha-calculator__section-title">Wealth Comparison</h2>

            <div class="ha-calculator__recommendation">
                <div class="ha-calculator__recommendation-label">
                    Recommendation: {{ $result['recommendation'] }}
                </div>
                <h3 class="ha-calculator__recommendation-title">
                    {{ $result['recommendation'] === 'BUY' ? 'Buying' : 'Renting' }}
                    is ahead by {{ $money($result['difference']) }}
                </h3>
                <p class="ha-calculator__recommendation-description">
                    Estimated comparison over {{ $result['analysis_years'] }} years.
                </p>
            </div>

            <article class="ha-calculator__result" style="margin-bottom:16px">
                <div class="ha-calculator__result-label">Total Wealth: Buying</div>
                <div class="ha-calculator__result-value">{{ $money($buying) }}</div>
                <div style="height:8px;margin-top:16px;background:var(--u-surface-muted);border-radius:999px;overflow:hidden">
                    <div style="height:100%;width:{{ $buying / $maxWealth * 100 }}%;background:var(--u-primary);border-radius:999px"></div>
                </div>
            </article>

            <article class="ha-calculator__result" style="margin-bottom:24px">
                <div class="ha-calculator__result-label">Total Wealth: Renting</div>
                <div class="ha-calculator__result-value">{{ $money($renting) }}</div>
                <div style="height:8px;margin-top:16px;background:var(--u-surface-muted);border-radius:999px;overflow:hidden">
                    <div style="height:100%;width:{{ $renting / $maxWealth * 100 }}%;background:var(--u-primary);border-radius:999px"></div>
                </div>
            </article>

            <section class="ha-calculator__panel">
                <h3 class="ha-calculator__section-title">Wealth Progression</h3>

                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px">
                    @foreach ([1, 5, 10, 20] as $chartYears)
                        <button type="button"
                                class="ha-calculator__chart-filter"
                                data-chart-years="{{ $chartYears }}">
                            {{ $chartYears }}Y
                        </button>
                    @endforeach
                </div>

                <canvas id="wealth-progression-chart"
                        role="img"
                        aria-label="Buying and renting wealth progression chart"
                        style="display:block;width:100%;height:280px"></canvas>

                <div class="ha-calculator__range-labels" style="margin-top:14px">
                    <span>Buying Net Worth</span>
                    <span>Renting Net Worth</span>
                </div>

                <details style="margin-top:20px">
                    <summary style="cursor:pointer;font-weight:700">View yearly values</summary>
                    <div class="ha-calculator__table-wrap" style="margin-top:12px">
                        <table class="ha-calculator__table">
                            <thead>
                                <tr><th>Year</th><th>Buying Net Worth</th><th>Renting Net Worth</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($progression as $point)
                                    <tr>
                                        <td>{{ $point['year'] }}</td>
                                        <td>{{ $money($point['buying']) }}</td>
                                        <td>{{ $money($point['renting']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </section>

            <section class="ha-calculator__panel">
                <h3 class="ha-calculator__section-title">
                    Upfront Investment {{ $money($result['upfront_investment']) }}
                </h3>

                @foreach ([
                    'Down Payment' => $result['down_payment'],
                    'Stamp Duty & Registration' => $result['stamp_duty'],
                    'Processing Fees / Brokerage' => $result['fees'],
                    'Renovation & Moving Costs' => $result['renovation'],
                ] as $label => $amount)
                    <div style="display:flex;justify-content:space-between;gap:16px;padding:12px 0;border-bottom:1px solid var(--u-border)">
                        <span class="ha-calculator__muted">{{ $label }}</span>
                        <strong>{{ $money($amount) }}</strong>
                    </div>
                @endforeach
            </section>

            <section>
                <h3 class="ha-calculator__section-title">Smart Tips</h3>

                <article class="ha-calculator__result" style="margin-bottom:12px">
                    <strong>Understanding Appreciation</strong>
                    <p class="ha-calculator__muted">
                        Your estimate assumes {{ $values['appreciation_rate'] }}% annual appreciation.
                        Higher or lower growth can significantly change the outcome.
                    </p>
                </article>

                <article class="ha-calculator__result" style="margin-bottom:12px">
                    <strong>The Opportunity Cost</strong>
                    <p class="ha-calculator__muted">
                        Renting assumes the upfront purchase funds can earn an estimated
                        {{ $values['investment_return'] }}% annual investment return.
                    </p>
                </article>

                <article class="ha-calculator__result">
                    <strong>Maintenance Buffer</strong>
                    <p class="ha-calculator__muted">
                        Maintenance is estimated at {{ $values['maintenance_percent'] }}%
                        annually, adjusted using the selected inflation assumption.
                    </p>
                </article>
            </section>

            <p class="ha-calculator__muted" style="margin-top:20px">
                Estimates only. Actual loan costs, taxes, rent, property appreciation,
                maintenance and investment returns may differ.
            </p>
        </section>
    @endif
</div>

<script>
(function () {
    const form = document.getElementById('home-affordability-form');
    const root = document.getElementById('home-affordability');
    const formatMoney = value =>
        '₹' + Math.round(Number(value) || 0).toLocaleString('en-IN');

    function syncAll(key, value) {
        const controls = form.querySelectorAll('[data-sync="' + key + '"]');
        const hidden = document.getElementById(key);

        controls.forEach(control => {
            if (control.type === 'range' || control.type === 'number') {
                control.value = value;
            }
        });

        if (hidden) hidden.value = value;
    }

    function updatePreview() {
        const get = key => Number(document.getElementById(key)?.value) || 0;

        const price = get('property_price');
        const downPercent = get('down_payment_percent');
        const principal = price * (1 - downPercent / 100);
        const rate = get('interest_rate') / 1200;
        const months = get('tenure_years') * 12;

        let emi = 0;

        if (months > 0) {
            if (rate === 0) {
                emi = principal / months;
            } else {
                const factor = Math.pow(1 + rate, months);
                emi = principal * rate * factor / (factor - 1);
            }
        }

        const emiNode = document.getElementById('emi_preview');
        const downNode = document.getElementById('down_payment_amount');
        const downDisplay = document.getElementById('down_payment_percent_display');
        const analysisDisplay = document.getElementById('analysis_years_display');

        if (emiNode) emiNode.textContent = formatMoney(emi);
        if (downNode) downNode.textContent = formatMoney(price * downPercent / 100);
        if (downDisplay) downDisplay.textContent = downPercent;
        if (analysisDisplay) analysisDisplay.textContent = get('analysis_years');
    }

    if (form) {
        form.querySelectorAll('[data-sync]').forEach(control => {
            control.addEventListener('input', function () {
                const key = control.dataset.sync;
                const value = control.value;

                if (value !== '') syncAll(key, value);
                updatePreview();
            });

            control.addEventListener('change', function () {
                if (control.value === '') return;

                const min = Number(control.min);
                const max = Number(control.max);
                const value = Math.min(max, Math.max(min, Number(control.value)));

                syncAll(control.dataset.sync, value);
                updatePreview();
            });
        });

        form.addEventListener('submit', function () {
            form.querySelectorAll('input[type="hidden"][name]').forEach(hidden => {
                const source = form.querySelector('[data-sync="' + hidden.id + '"]');
                if (source) hidden.value = source.value;
            });
        });

        updatePreview();
    }

    const canvas = document.getElementById('wealth-progression-chart');

    if (canvas) {
        const points = @json($progression);
        let selectedYears = Number(@json($result['analysis_years'] ?? 15));

        function drawChart() {
            const width = Math.max(280, canvas.clientWidth);
            const height = 280;
            const ratio = window.devicePixelRatio || 1;

            canvas.width = width * ratio;
            canvas.height = height * ratio;

            const ctx = canvas.getContext('2d');
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.clearRect(0, 0, width, height);

            const visible = points.filter(point => Number(point.year) <= selectedYears);

            if (!visible.length) return;

            const style = getComputedStyle(document.documentElement);
            const textColor = style.getPropertyValue('--u-text-secondary').trim() || '#626772';
            const borderColor = style.getPropertyValue('--u-border').trim() || '#e2e4e8';
            const buyingColor = style.getPropertyValue('--u-primary').trim() || '#191b20';

            const pad = { left: 8, right: 46, top: 20, bottom: 32 };
            const plotWidth = width - pad.left - pad.right;
            const plotHeight = height - pad.top - pad.bottom;

            const values = visible.flatMap(p => [Number(p.buying), Number(p.renting)]);
            const maxValue = Math.max(1, ...values) * 1.1;

            const x = index => pad.left +
                (visible.length === 1 ? 0 : index / (visible.length - 1) * plotWidth);

            const y = value => pad.top + plotHeight -
                (Number(value) / maxValue) * plotHeight;

            ctx.font = '11px sans-serif';
            ctx.textAlign = 'right';

            for (let i = 0; i <= 3; i++) {
                const value = maxValue * i / 3;
                const yy = y(value);

                ctx.beginPath();
                ctx.strokeStyle = borderColor;
                ctx.moveTo(pad.left, yy);
                ctx.lineTo(width - pad.right, yy);
                ctx.stroke();

                const label = value >= 10000000
                    ? (value / 10000000).toFixed(1) + 'Cr'
                    : value >= 100000
                        ? Math.round(value / 100000) + 'L'
                        : Math.round(value).toLocaleString('en-IN');

                ctx.fillStyle = textColor;
                ctx.fillText(label, width - 3, yy - 3);
            }

            function drawLine(key, color) {
                ctx.beginPath();
                ctx.strokeStyle = color;
                ctx.lineWidth = 2.5;
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';

                visible.forEach((point, index) => {
                    if (index === 0) ctx.moveTo(x(index), y(point[key]));
                    else ctx.lineTo(x(index), y(point[key]));
                });

                ctx.stroke();
            }

            drawLine('buying', buyingColor);
            drawLine('renting', textColor);

            ctx.fillStyle = textColor;
            ctx.textAlign = 'left';
            ctx.fillText('Year 0', pad.left, height - 8);
            ctx.textAlign = 'right';
            ctx.fillText('Year ' + visible[visible.length - 1].year, width - pad.right, height - 8);
        }

        document.querySelectorAll('[data-chart-years]').forEach(button => {
            button.addEventListener('click', function () {
                selectedYears = Number(button.dataset.chartYears);

                document.querySelectorAll('[data-chart-years]').forEach(other => {
                    other.style.background = 'var(--u-surface)';
                    other.style.color = 'var(--u-text)';
                    other.style.border = '1px solid var(--u-border)';
                });

                button.style.background = 'var(--u-primary)';
                button.style.color = 'var(--u-primary-text)';
                button.style.border = '1px solid var(--u-primary)';

                drawChart();
            });
        });

        drawChart();
        window.addEventListener('resize', drawChart);
    }

    const results = document.getElementById('calculator-results');

    if (results) {
        results.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
})();
</script>
@endsection
