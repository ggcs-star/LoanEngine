
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
        'stamp_duty_percent' => 5,
        'fees_percent' => 2,
        'renovation_cost' => 1000000,
        'analysis_years' => 15,
    ];

    $values = array_merge($defaults, $inputs ?? [], old());

    $money = fn ($amount) => '₹' . number_format((float) $amount, 0, '.', ',');
    $number = fn ($amount) => number_format((float) $amount, 0, '.', ',');
    $resultReady = !empty($result);

    $buyingWealth = (float) ($result['buying_wealth'] ?? 0);
    $rentingWealth = (float) ($result['renting_wealth'] ?? 0);
    $maxWealth = max(1, $buyingWealth, $rentingWealth);
    $recommendation = strtoupper($result['recommendation'] ?? 'BUY');
    $progression = $result['wealth_progression'] ?? [];
@endphp

<div class="ha-calculator" data-calculator="home-affordability">

    <a class="ha-calculator__back" href="{{ route('user.tools.index') }}">
        <span aria-hidden="true">←</span>
        Back to all tools
    </a>

    <header class="ha-calculator__header">
        <span class="ha-calculator__eyebrow">
            {{ $tool->category }} · CALCULATOR
        </span>

        <h1 class="ha-calculator__title">{{ $tool->name }}</h1>

        <p class="ha-calculator__description">
            Compare buying a home with renting and investing. Adjust the inputs
            to estimate your EMI, upfront costs and potential wealth over time.
        </p>
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

    @if (session('info'))
        <div class="ha-calculator__errors" role="status">
            {{ session('info') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('calculators.calculate', ['tool' => $tool->slug]) }}"
          id="home-affordability-form">
        @csrf

        {{-- BUYING DETAILS --}}
        <section class="ha-calculator__panel">
            <h2 class="ha-calculator__section-title">
                <span class="ha-calculator__section-icon" aria-hidden="true">⌂</span>
                Buying Details
            </h2>

            <div class="ha-calculator__field">
                <div class="ha-calculator__field-header">
                    <label class="ha-calculator__label" for="property_price_number">
                        Property Price
                    </label>

                    <input class="ha-calculator__value"
                           id="property_price_number"
                           type="number"
                           min="1000000"
                           max="50000000"
                           step="100000"
                           value="{{ $values['property_price'] }}"
                           data-sync="property_price"
                           required>
                </div>

                <input class="ha-calculator__range"
                       id="property_price_range"
                       type="range"
                       min="1000000"
                       max="50000000"
                       step="100000"
                       value="{{ $values['property_price'] }}"
                       data-sync="property_price"
                       aria-label="Property price slider">

                <div class="ha-calculator__range-labels">
                    <span>₹10L</span><span>₹5Cr</span>
                </div>

                <input type="hidden" name="property_price" id="property_price"
                       value="{{ $values['property_price'] }}">
            </div>

            <div class="ha-calculator__field" style="margin-top: 26px;">
                <div class="ha-calculator__field-header">
                    <label class="ha-calculator__label" for="down_payment_percent_number">
                        Down Payment
                    </label>

                    <strong class="ha-calculator__value">
                        <span id="down_payment_percent_display">{{ $values['down_payment_percent'] }}</span>%
                    </strong>
                </div>

                <input class="ha-calculator__range"
                       id="down_payment_percent_range"
                       type="range"
                       min="10"
                       max="90"
                       step="1"
                       value="{{ $values['down_payment_percent'] }}"
                       data-sync="down_payment_percent"
                       aria-label="Down payment percentage slider">

                <div class="ha-calculator__range-labels">
                    <span>10%</span><span>90%</span>
                </div>

                <p class="ha-calculator__muted" style="margin-top: 8px;">
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
                            Interest Rate
                        </label>
                        <input class="ha-calculator__value"
                               id="interest_rate_number"
                               type="number"
                               min="0"
                               max="30"
                               step="0.1"
                               value="{{ $values['interest_rate'] }}"
                               data-sync="interest_rate"
                               required>
                    </div>

                    <input class="ha-calculator__range"
                           id="interest_rate_range"
                           type="range"
                           min="0"
                           max="30"
                           step="0.1"
                           value="{{ $values['interest_rate'] }}"
                           data-sync="interest_rate"
                           aria-label="Interest rate slider">

                    <div class="ha-calculator__range-labels">
                        <span>0%</span><span>30%</span>
                    </div>

                    <input type="hidden" name="interest_rate" id="interest_rate"
                           value="{{ $values['interest_rate'] }}">
                </div>

                <div class="ha-calculator__compact-field">
                    <div class="ha-calculator__field-header">
                        <label class="ha-calculator__label" for="tenure_years_number">
                            Loan Tenure
                        </label>
                        <input class="ha-calculator__value"
                               id="tenure_years_number"
                               type="number"
                               min="1"
                               max="40"
                               step="1"
                               value="{{ $values['tenure_years'] }}"
                               data-sync="tenure_years"
                               required>
                    </div>

                    <input class="ha-calculator__range"
                           id="tenure_years_range"
                           type="range"
                           min="1"
                           max="40"
                           step="1"
                           value="{{ $values['tenure_years'] }}"
                           data-sync="tenure_years"
                           aria-label="Loan tenure slider">

                    <div class="ha-calculator__range-labels">
                        <span>1 year</span><span>40 years</span>
                    </div>

                    <input type="hidden" name="tenure_years" id="tenure_years"
                           value="{{ $values['tenure_years'] }}">
                </div>
            </div>

            <div class="ha-calculator__recommendation" style="margin-top: 24px; margin-bottom: 0;">
                <div class="ha-calculator__recommendation-label">
                    Projected Monthly EMI
                </div>

                <h3 class="ha-calculator__recommendation-title" id="emi_preview">
                    —
                </h3>

                <p class="ha-calculator__muted">
                    Estimated EMI updates as you change the loan details.
                </p>
            </div>
        </section>

        {{-- RENTING AND ECONOMICS --}}
        <section class="ha-calculator__panel">
            <h2 class="ha-calculator__section-title">
                <span class="ha-calculator__section-icon" aria-hidden="true">▦</span>
                Renting &amp; Economics
            </h2>

            @php
                $economics = [
                    ['monthly_rent', 'Current Monthly Rent', 5000, 150000, 500, '₹5k', '₹1.5L', '₹'],
                    ['appreciation_rate', 'Property Appreciation', -10, 20, 0.5, '-10%', '20%', '%'],
                    ['rent_increase_rate', 'Annual Rent Increase', -10, 20, 0.5, '-10%', '20%', '%'],
                    ['investment_return', 'Investment Returns (Equity)', -10, 30, 0.5, '-10%', '30%', '%'],
                    ['inflation_rate', 'Inflation Rate', -10, 30, 0.5, '-10%', '30%', '%'],
                    ['maintenance_percent', 'Maintenance & Tax', 0, 10, 0.5, '0%', '10%', '%'],
                ];
            @endphp

            <div class="ha-calculator__grid">
                @foreach ($economics as [$key, $label, $min, $max, $step, $minLabel, $maxLabel, $unit])
                    <div class="ha-calculator__field">
                        <div class="ha-calculator__field-header">
                            <label class="ha-calculator__label" for="{{ $key }}_number">
                                {{ $label }}
                            </label>

                            <input class="ha-calculator__value"
                                   id="{{ $key }}_number"
                                   type="number"
                                   min="{{ $min }}"
                                   max="{{ $max }}"
                                   step="{{ $step }}"
                                   value="{{ $values[$key] }}"
                                   data-sync="{{ $key }}"
                                   required>
                        </div>

                        <input class="ha-calculator__range"
                               id="{{ $key }}_range"
                               type="range"
                               min="{{ $min }}"
                               max="{{ $max }}"
                               step="{{ $step }}"
                               value="{{ max($min, min($max, (float) $values[$key])) }}"
                               data-sync="{{ $key }}"
                               aria-label="{{ $label }} slider">

                        <div class="ha-calculator__range-labels">
                            <span>{{ $minLabel }}</span><span>{{ $maxLabel }}</span>
                        </div>

                        <input type="hidden" name="{{ $key }}" id="{{ $key }}"
                               value="{{ $values[$key] }}">
                    </div>
                @endforeach

                @php
                    $costs = [
                        ['stamp_duty_percent', 'Stamp Duty & Registration', 0, 20, 0.5, '0%', '20%', '%'],
                        ['fees_percent', 'Other Purchase Fees', 0, 20, 0.5, '0%', '20%', '%'],
                        ['renovation_cost', 'Renovation & Moving Costs', 0, 100000000, 100000, '₹0', '₹10Cr', '₹'],
                    ];
                @endphp

                @foreach ($costs as [$key, $label, $min, $max, $step, $minLabel, $maxLabel, $unit])
                    <div class="ha-calculator__field">
                        <div class="ha-calculator__field-header">
                            <label class="ha-calculator__label" for="{{ $key }}_number">
                                {{ $label }}
                            </label>

                            <input class="ha-calculator__value"
                                   id="{{ $key }}_number"
                                   type="number"
                                   min="{{ $min }}"
                                   max="{{ $max }}"
                                   step="{{ $step }}"
                                   value="{{ $values[$key] }}"
                                   data-sync="{{ $key }}"
                                   required>
                        </div>

                        <input class="ha-calculator__range"
                               id="{{ $key }}_range"
                               type="range"
                               min="{{ $min }}"
                               max="{{ $max }}"
                               step="{{ $step }}"
                               value="{{ max($min, min($max, (float) $values[$key])) }}"
                               data-sync="{{ $key }}"
                               aria-label="{{ $label }} slider">

                        <div class="ha-calculator__range-labels">
                            <span>{{ $minLabel }}</span><span>{{ $maxLabel }}</span>
                        </div>

                        <input type="hidden" name="{{ $key }}" id="{{ $key }}"
                               value="{{ $values[$key] }}">
                    </div>
                @endforeach
            </div>

            {{-- ANALYSIS PERIOD --}}
            <div class="ha-calculator__field" style="margin-top: 30px;">
                <h3 class="ha-calculator__section-title">Analysis Period</h3>

                <div class="ha-calculator__field-header">
                    <label class="ha-calculator__label" for="analysis_years_range">
                        Simulating your net worth over time
                    </label>

                    <strong class="ha-calculator__value">
                        <span id="analysis_years_display">{{ $values['analysis_years'] }}</span> years
                    </strong>
                </div>

                <input class="ha-calculator__range"
                       id="analysis_years_range"
                       type="range"
                       min="5"
                       max="30"
                       step="1"
                       value="{{ $values['analysis_years'] }}"
                       data-sync="analysis_years"
                       aria-label="Analysis period slider">

                <div class="ha-calculator__range-labels">
                    <span>5 years</span><span>30 years</span>
                </div>

                <input type="hidden" name="analysis_years" id="analysis_years"
                       value="{{ $values['analysis_years'] }}">
            </div>

            <button class="ha-calculator__submit" type="submit">
                Calculate Rent vs Buy →
            </button>
        </section>
    </form>

    {{-- RESULTS --}}
    @if ($resultReady)
        <section class="ha-calculator__panel" id="calculator-results">
            <h2 class="ha-calculator__section-title">
                <span class="ha-calculator__section-icon" aria-hidden="true">↗</span>
                Wealth Comparison
            </h2>

            <div class="ha-calculator__recommendation">
                <div class="ha-calculator__recommendation-label">
                    Recommendation: {{ $recommendation }}
                </div>

                <h3 class="ha-calculator__recommendation-title">
                    {{ $recommendation === 'BUY' ? 'Buying' : 'Renting' }}
                    is ahead by {{ $money($result['difference'] ?? 0) }}
                </h3>

                <p class="ha-calculator__recommendation-description">
                    Estimated wealth difference after
                    {{ $result['analysis_years'] ?? $values['analysis_years'] }} years.
                    This is an estimate, not a guarantee of returns.
                </p>

                <div class="ha-calculator__results" style="margin-top: 20px; margin-bottom: 0;">
                    <div class="ha-calculator__result">
                        <div class="ha-calculator__result-label">Monthly EMI</div>
                        <div class="ha-calculator__result-value">
                            {{ $money($result['monthly_emi'] ?? 0) }}
                        </div>
                    </div>

                    <div class="ha-calculator__result">
                        <div class="ha-calculator__result-label">Break-even Point</div>
                        <div class="ha-calculator__result-value">
                            {{ !empty($result['break_even_year']) ? 'Year ' . $result['break_even_year'] : 'Not reached' }}
                        </div>
                    </div>

                    <div class="ha-calculator__result">
                        <div class="ha-calculator__result-label">Projected Property Value</div>
                        <div class="ha-calculator__result-value">
                            {{ $money($result['property_value'] ?? 0) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- BUYING WEALTH --}}
            <article class="ha-calculator__result" style="margin-bottom: 16px;">
                <div class="ha-calculator__result-label">Total Wealth: Buying</div>
                <div class="ha-calculator__result-value">
                    {{ $money($buyingWealth) }}
                </div>

                <div style="height: 8px; margin-top: 16px; overflow: hidden; background: var(--u-surface-muted); border-radius: 999px;">
                    <div style="height: 100%; width: {{ min(100, max(0, $buyingWealth / $maxWealth * 100)) }}%; background: var(--u-primary); border-radius: 999px;"></div>
                </div>
            </article>

            {{-- RENTING WEALTH --}}
            <article class="ha-calculator__result" style="margin-bottom: 24px;">
                <div class="ha-calculator__result-label">Total Wealth: Renting</div>
                <div class="ha-calculator__result-value">
                    {{ $money($rentingWealth) }}
                </div>

                <div style="height: 8px; margin-top: 16px; overflow: hidden; background: var(--u-surface-muted); border-radius: 999px;">
                    <div style="height: 100%; width: {{ min(100, max(0, $rentingWealth / $maxWealth * 100)) }}%; background: var(--u-primary); border-radius: 999px;"></div>
                </div>
            </article>

            {{-- WEALTH PROGRESSION CHART --}}
            <section style="margin-top: 24px;">
                <h3 class="ha-calculator__section-title">Wealth Progression</h3>
                <p class="ha-calculator__muted">
                    Compare estimated net wealth at each year of the analysis.
                </p>

                <div style="position: relative; width: 100%; min-height: 260px;">
                    <canvas id="wealth-progression-chart"
                            role="img"
                            aria-label="Line chart comparing buying net worth and renting net worth over time"
                            style="display: block; width: 100%; height: 280px;"></canvas>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 16px; margin: 12px 0 20px;">
                    <span class="ha-calculator__muted">
                        <span style="display: inline-block; width: 10px; height: 10px; margin-right: 6px; border-radius: 50%; background: var(--u-primary);"></span>
                        Buying Net Worth
                    </span>
                    <span class="ha-calculator__muted">
                        <span style="display: inline-block; width: 10px; height: 10px; margin-right: 6px; border-radius: 50%; background: var(--u-text-secondary);"></span>
                        Renting Net Worth
                    </span>
                </div>

                <details>
                    <summary style="cursor: pointer; font-weight: 700; margin-bottom: 12px;">
                        View year-by-year values
                    </summary>

                    <div class="ha-calculator__table-wrap">
                        <table class="ha-calculator__table">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Buying Net Worth</th>
                                    <th>Renting Net Worth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($progression as $year)
                                    <tr>
                                        <td>{{ $year['year'] }}</td>
                                        <td>{{ $money($year['buying']) }}</td>
                                        <td>{{ $money($year['renting']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </section>

            {{-- UPFRONT INVESTMENT --}}
            <section class="ha-calculator__panel" style="margin-top: 26px;">
                <h3 class="ha-calculator__section-title">
                    Upfront Investment {{ $money($result['upfront_investment'] ?? 0) }}
                </h3>

                @foreach ([
                    'Down Payment' => $result['down_payment'] ?? 0,
                    'Stamp Duty & Registration' => $result['stamp_duty'] ?? 0,
                    'Processing Fees / Registration & Brokerage' => $result['fees'] ?? 0,
                    'Renovations & Moving / Interiors' => $result['renovation'] ?? 0,
                ] as $label => $amount)
                    <div style="display: flex; justify-content: space-between; gap: 16px; padding: 12px 0; border-bottom: 1px solid var(--u-border);">
                        <span class="ha-calculator__muted">{{ $label }}</span>
                        <strong style="text-align: right;">{{ $money($amount) }}</strong>
                    </div>
                @endforeach
            </section>

            {{-- SMART TIPS --}}
            <section style="margin-top: 26px;">
                <h3 class="ha-calculator__section-title">Smart Tips</h3>

                <div style="display: grid; gap: 18px;">
                    <article>
                        <strong>Understanding Appreciation</strong>
                        <p class="ha-calculator__muted">
                            Your estimate uses {{ $values['appreciation_rate'] }}% annual
                            property appreciation. A small change in this assumption can
                            materially affect the projected property value.
                        </p>
                    </article>

                    <article>
                        <strong>The Opportunity Cost</strong>
                        <p class="ha-calculator__muted">
                            The renting scenario assumes the upfront purchase money can
                            be invested at an estimated annual return of
                            {{ $values['investment_return'] }}%.
                        </p>
                    </article>

                    <article>
                        <strong>Maintenance Buffer</strong>
                        <p class="ha-calculator__muted">
                            The model estimates annual maintenance at
                            {{ $values['maintenance_percent'] }}% of property value and
                            increases it using the selected inflation assumption.
                        </p>
                    </article>
                </div>
            </section>

            <p class="ha-calculator__muted" style="margin-top: 24px;">
                These figures are estimates, not a guarantee of future returns.
                Actual loan costs, taxes, property appreciation, rent, maintenance
                and investment performance may differ.
            </p>
        </section>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('home-affordability-form');

    const money = value => '₹' + Math.round(Number(value) || 0).toLocaleString('en-IN');

    function getValue(key) {
        const field = document.getElementById(key);
        return field ? Number(field.value) || 0 : 0;
    }

    function updatePreview() {
        const price = getValue('property_price');
        const downPercent = getValue('down_payment_percent');
        const interest = getValue('interest_rate');
        const tenure = getValue('tenure_years');

        const principal = Math.max(0, price * (1 - downPercent / 100));
        const months = tenure * 12;
        const monthlyRate = interest / 1200;

        let emi = 0;

        if (months > 0) {
            if (monthlyRate === 0) {
                emi = principal / months;
            } else {
                const factor = Math.pow(1 + monthlyRate, months);
                emi = principal * monthlyRate * factor / (factor - 1);
            }
        }

        const emiOutput = document.getElementById('emi_preview');
        const downAmount = document.getElementById('down_payment_amount');
        const downDisplay = document.getElementById('down_payment_percent_display');
        const analysisDisplay = document.getElementById('analysis_years_display');

        if (emiOutput) emiOutput.textContent = money(emi);
        if (downAmount) downAmount.textContent = money(price * downPercent / 100);
        if (downDisplay) downDisplay.textContent = downPercent;
        if (analysisDisplay) analysisDisplay.textContent = getValue('analysis_years');
    }

    if (form) {
        const syncFields = form.querySelectorAll('[data-sync]');

        syncFields.forEach(function (field) {
            const key = field.dataset.sync;
            const hidden = document.getElementById(key);
            const related = form.querySelectorAll('[data-sync="' + key + '"]');

            field.addEventListener('input', function () {
                const value = field.value;

                related.forEach(function (other) {
                    if (other !== field && other.type !== 'range') {
                        other.value = value;
                    }

                    if (other !== field && other.type === 'range' && value !== '') {
                        const number = Number(value);
                        const min = Number(other.min);
                        const max = Number(other.max);

                        if (number >= min && number <= max) {
                            other.value = value;
                        }
                    }
                });

                if (hidden && field.type !== 'hidden') {
                    hidden.value = value;
                }

                updatePreview();
            });

            field.addEventListener('change', function () {
                if (field.value === '') return;

                const value = Number(field.value);
                if (!Number.isFinite(value)) return;

                const min = field.min !== '' ? Number(field.min) : -Infinity;
                const max = field.max !== '' ? Number(field.max) : Infinity;
                const bounded = Math.min(max, Math.max(min, value));

                related.forEach(function (other) {
                    other.value = String(bounded);
                });

                if (hidden) hidden.value = String(bounded);

                updatePreview();
            });
        });

        form.addEventListener('submit', function (event) {
            let valid = true;

            form.querySelectorAll('input[type="hidden"][name]').forEach(function (hidden) {
                const value = Number(hidden.value);
                const related = form.querySelector('[data-sync="' + hidden.id + '"][type="number"]');

                if (!Number.isFinite(value) || (related && !related.checkValidity())) {
                    valid = false;
                }
            });

            if (!valid) {
                event.preventDefault();
                form.reportValidity();
            }
        });

        updatePreview();
    }

    // Draw the year-by-year chart without an external JS library.
    const canvas = document.getElementById('wealth-progression-chart');

    if (canvas) {
        const points = @json($progression);
        const buyingColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--u-primary').trim() || '#191b20';
        const rentingColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--u-text-secondary').trim() || '#626772';

        function drawChart() {
            const rect = canvas.getBoundingClientRect();
            const width = Math.max(280, rect.width);
            const height = 280;
            const ratio = window.devicePixelRatio || 1;

            canvas.width = width * ratio;
            canvas.height = height * ratio;

            const ctx = canvas.getContext('2d');
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.clearRect(0, 0, width, height);

            if (!points.length) {
                ctx.fillStyle = rentingColor;
                ctx.font = '13px sans-serif';
                ctx.fillText('No progression data available.', 12, 30);
                return;
            }

            const styles = getComputedStyle(document.documentElement);
            const muted = styles.getPropertyValue('--u-text-muted').trim() || '#858a94';
            const border = styles.getPropertyValue('--u-border').trim() || '#e2e4e8';

            const pad = { left: 12, right: 42, top: 20, bottom: 34 };
            const chartWidth = width - pad.left - pad.right;
            const chartHeight = height - pad.top - pad.bottom;

            const values = points.flatMap(point => [
                Number(point.buying) || 0,
                Number(point.renting) || 0
            ]);

            const maxValue = Math.max(1, ...values) * 1.08;

            function x(index) {
                return pad.left + (points.length <= 1
                    ? chartWidth / 2
                    : index / (points.length - 1) * chartWidth);
            }

            function y(value) {
                return pad.top + chartHeight - ((Number(value) || 0) / maxValue) * chartHeight;
            }

            ctx.font = '11px sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';

            for (let i = 0; i <= 3; i++) {
                const value = maxValue * i / 3;
                const yy = y(value);

                ctx.beginPath();
                ctx.strokeStyle = border;
                ctx.lineWidth = 1;
                ctx.moveTo(pad.left, yy);
                ctx.lineTo(width - pad.right, yy);
                ctx.stroke();

                const label = value >= 10000000
                    ? (value / 10000000).toFixed(1) + 'Cr'
                    : value >= 100000
                        ? Math.round(value / 100000) + 'L'
                        : Math.round(value).toLocaleString('en-IN');

                ctx.fillStyle = muted;
                ctx.fillText(label, width - 4, yy);
            }

            function drawLine(key, color) {
                ctx.beginPath();
                ctx.strokeStyle = color;
                ctx.lineWidth = 2.5;
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';

                points.forEach(function (point, index) {
                    const px = x(index);
                    const py = y(point[key]);

                    if (index === 0) ctx.moveTo(px, py);
                    else ctx.lineTo(px, py);
                });

                ctx.stroke();
            }

            drawLine('buying', buyingColor);
            drawLine('renting', rentingColor);

            ctx.fillStyle = muted;
            ctx.textAlign = 'left';
            ctx.textBaseline = 'alphabetic';
            ctx.fillText('Year 0', pad.left, height - 8);

            ctx.textAlign = 'right';
            ctx.fillText('Year ' + points[points.length - 1].year, width - pad.right, height - 8);
        }

        drawChart();

        if ('ResizeObserver' in window) {
            new ResizeObserver(drawChart).observe(canvas);
        } else {
            window.addEventListener('resize', drawChart);
        }
    }

    // Bring calculated results into view after form submission.
    const results = document.getElementById('calculator-results');

    if (results && window.location.hash === '#calculator-results') {
        results.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>
@endsection
