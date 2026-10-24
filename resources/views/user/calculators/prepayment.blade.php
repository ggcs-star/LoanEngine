
@extends('user.layouts.app')

@section('title', 'Loan Prepayment Calculator | LoanEngine')
@section('page_heading', 'Loan Prepayment Calculator')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/calculators/prepayment.css') }}">
@endpush

@section('content')
@php
    $v = array_merge([
        'outstanding_loan' => 22500000,
        'interest_rate' => 8.55,
        'remaining_tenure' => 215,
        'prepayment_strategy' => 'one_time',
        'prepayment_amount' => 500000,
        'strategy_goal' => 'reduce_tenure',
    ], $inputs ?? [], old());

    $money = fn($n) => '₹' . number_format((float) $n, 0, '.', ',');
    $hasResult = is_array($result ?? null);
@endphp

<div class="prepay-page">
    <a class="prepay-back" href="{{ route('user.calculators.journeys.index') }}">
        <span aria-hidden="true">←</span>
        <span>Loan Prepayment Calculator</span>
    </a>

    <section class="prepay-hero" aria-labelledby="prepay-hero-title">
        <div class="prepay-hero-content">
            <span class="prepay-hero-eyebrow">LOAN PLANNING TOOLS</span>
            <h1 id="prepay-hero-title">Loan Prepayment Calculator</h1>
            <p>Explore how an extra payment could reduce your interest and help you repay your loan sooner.</p>
            <div class="prepay-hero-tags">
                <span><i aria-hidden="true">✓</i> Interest savings</span>
                <span><i aria-hidden="true">↗</i> Flexible strategies</span>
            </div>
        </div>
        <div class="prepay-hero-art" aria-hidden="true">
            <div class="prepay-hero-orbit prepay-hero-orbit-one"></div>
            <div class="prepay-hero-orbit prepay-hero-orbit-two"></div>
            <div class="prepay-hero-coin">₹</div>
            <div class="prepay-hero-mini-card">
                <span>INTEREST SAVED</span>
                <strong>More control</strong>
                <div class="prepay-hero-mini-line"><i></i></div>
            </div>
        </div>
    </section>

    @if ($errors->any())
        <div class="prepay-alert" role="alert">
            <strong>Please check your inputs.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($hasResult)
        <section class="prepay-result" id="prepay-result" aria-live="polite">
            <div class="prepay-result-card">
                <span class="prepay-muted">Total Interest Saved</span>
                <div class="prepay-big-number">{{ $money($result['interest_saved'] ?? 0) }}</div>

                <div class="prepay-result-bottom">
                    <div>
                        <span class="prepay-muted">
                            {{ $v['strategy_goal'] === 'reduce_tenure' ? 'Tenure Reduced By' : 'Revised Monthly EMI' }}
                        </span>
                        <strong class="prepay-result-value">
                            @if ($v['strategy_goal'] === 'reduce_tenure')
                                {{ $result['months_reduced'] ?? 0 }} Months
                            @else
                                {{ $money($result['revised_emi'] ?? 0) }}
                            @endif
                        </strong>
                    </div>
                    <div class="prepay-ring" style="--saving: {{ min(100, max(0, (float) ($result['saving_percent'] ?? 0))) }}%">
                        <span>{{ number_format((float) ($result['saving_percent'] ?? 0), 0) }}%</span>
                    </div>
                </div>
            </div>

            <h2 class="prepay-heading">REPAYMENT TIMELINE</h2>
            <div class="prepay-chart-card">
                <div class="prepay-chart">
                    @foreach (($result['timeline'] ?? []) as $point)
                        @php
                            $max = max(1, (float) $v['outstanding_loan']);
                            $originalHeight = min(100, ((float) ($point['original'] ?? 0) / $max) * 100);
                            $prepaidHeight = min(100, ((float) ($point['prepaid'] ?? 0) / $max) * 100);
                        @endphp
                        <div class="prepay-chart-column">
                            <div class="prepay-bars">
                                <span class="prepay-bar prepay-original" style="height: {{ $originalHeight }}%"></span>
                                <span class="prepay-bar prepay-new" style="height: {{ $prepaidHeight }}%"></span>
                            </div>
                            <span>{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="prepay-legend">
                    <span><i class="prepay-dot prepay-dot-original"></i> Original</span>
                    <span><i class="prepay-dot prepay-dot-new"></i> With Prepayment</span>
                </div>
            </div>
        </section>
    @endif

    <form method="POST"
          action="{{ route('calculators.calculate', ['tool' => $tool->slug]) }}"
          id="prepay-form">
        @csrf

        <section class="prepay-section">
            <h2 class="prepay-heading">LOAN DETAILS</h2>

            <div class="prepay-card">
                <div class="prepay-field">
                    <div class="prepay-field-head">
                        <label for="outstanding_loan">Outstanding Loan</label>
                        <strong id="outstanding-label">{{ $money($v['outstanding_loan']) }}</strong>
                    </div>
                    <input type="range" id="outstanding_loan" name="outstanding_loan"
                           min="10000" max="500000000" step="10000"
                           value="{{ $v['outstanding_loan'] }}" data-progress>
                    <div class="prepay-limits"><span>₹10K</span><span>₹500Cr</span></div>
                </div>

                <div class="prepay-field">
                    <div class="prepay-field-head">
                        <label for="interest_rate">Interest Rate (p.a.)</label>
                        <strong id="interest-label">{{ number_format((float) $v['interest_rate'], 2) }}%</strong>
                    </div>
                    <input type="range" id="interest_rate" name="interest_rate"
                           min="0" max="35" step="0.05"
                           value="{{ $v['interest_rate'] }}" data-progress>
                    <div class="prepay-limits"><span>0%</span><span>35%</span></div>
                </div>

                <div class="prepay-field">
                    <div class="prepay-field-head">
                        <label for="remaining_tenure">Remaining Tenure</label>
                        <strong id="tenure-label">{{ $v['remaining_tenure'] }} Mo</strong>
                    </div>
                    <input type="range" id="remaining_tenure" name="remaining_tenure"
                           min="1" max="600" step="1"
                           value="{{ $v['remaining_tenure'] }}" data-progress>
                    <div class="prepay-limits"><span>1 Month</span><span>600 Months</span></div>
                </div>
            </div>
        </section>

        <section class="prepay-section">
            <h2 class="prepay-heading">PREPAYMENT STRATEGY</h2>

            <div class="prepay-segmented">
                <button type="button" data-strategy="one_time"
                        class="{{ $v['prepayment_strategy'] === 'one_time' ? 'is-active' : '' }}"
                        aria-pressed="{{ $v['prepayment_strategy'] === 'one_time' ? 'true' : 'false' }}">
                    One-time Payment
                </button>
                <button type="button" data-strategy="monthly"
                        class="{{ $v['prepayment_strategy'] === 'monthly' ? 'is-active' : '' }}"
                        aria-pressed="{{ $v['prepayment_strategy'] === 'monthly' ? 'true' : 'false' }}">
                    Monthly Addition
                </button>
            </div>
            <input type="hidden" name="prepayment_strategy" id="prepayment_strategy"
                   value="{{ $v['prepayment_strategy'] }}">

            <div class="prepay-amount-card">
                <label for="prepayment_amount" id="amount-label">
                    {{ $v['prepayment_strategy'] === 'monthly' ? 'Monthly Addition Amount' : 'Prepayment Amount' }}
                </label>
                <div class="prepay-money-input">
                    <span>₹</span>
                    <input type="number" id="prepayment_amount" name="prepayment_amount"
                           min="1" max="500000000" step="any"
                           value="{{ $v['prepayment_amount'] }}" required>
                </div>
            </div>
        </section>

        <section class="prepay-section">
            <h3 class="prepay-subheading">Strategy Goal</h3>
            <div class="prepay-goals">
                <button type="button" data-goal="reduce_tenure"
                        class="{{ $v['strategy_goal'] === 'reduce_tenure' ? 'is-active' : '' }}"
                        aria-pressed="{{ $v['strategy_goal'] === 'reduce_tenure' ? 'true' : 'false' }}">
                    <span aria-hidden="true">◷</span> Reduce Tenure
                </button>
                <button type="button" data-goal="reduce_emi"
                        class="{{ $v['strategy_goal'] === 'reduce_emi' ? 'is-active' : '' }}"
                        aria-pressed="{{ $v['strategy_goal'] === 'reduce_emi' ? 'true' : 'false' }}">
                    <span aria-hidden="true">▣</span> Reduce EMI
                </button>
            </div>
            <input type="hidden" name="strategy_goal" id="strategy_goal"
                   value="{{ $v['strategy_goal'] }}">
        </section>

        <section class="prepay-insights">
            <h3><span aria-hidden="true">♧</span> Smart Insights</h3>

            @if ($hasResult)
                <p>
                    <span aria-hidden="true">✓</span>
                    Estimated interest saving:
                    <strong>{{ number_format((float) ($result['saving_percent'] ?? 0), 1) }}%</strong>
                    ({{ $money($result['interest_saved'] ?? 0) }}).
                </p>
                <p>
                    <span aria-hidden="true">ⓘ</span>
                    @if ($v['strategy_goal'] === 'reduce_tenure')
                        Estimated tenure reduction:
                        <strong>{{ $result['months_reduced'] ?? 0 }} months</strong>.
                        Your regular EMI remains unchanged.
                    @else
                        Estimated revised EMI:
                        <strong>{{ $money($result['revised_emi'] ?? 0) }}</strong>,
                        with the original tenure retained.
                    @endif
                </p>
            @else
                <p><span aria-hidden="true">✓</span> Adjust your loan details and strategy, then calculate to see your estimated savings.</p>
                <p><span aria-hidden="true">ⓘ</span> The estimate depends on your outstanding principal, interest rate, remaining tenure and prepayment amount.</p>
            @endif

            <p class="prepay-note">
                Estimates only. Actual results depend on lender calculations, payment timing and applicable loan terms.
            </p>
        </section>

        <button type="submit" class="prepay-primary-button">
            Calculate Prepayment Strategy
        </button>
        <button type="reset" class="prepay-reset-button" id="prepay-reset">
            Reset Parameters
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('prepay-form');
    const loan = document.getElementById('outstanding_loan');
    const rate = document.getElementById('interest_rate');
    const tenure = document.getElementById('remaining_tenure');
    const amount = document.getElementById('prepayment_amount');
    const strategy = document.getElementById('prepayment_strategy');
    const goal = document.getElementById('strategy_goal');

    const money = value => '₹' + Number(value).toLocaleString('en-IN', {
        maximumFractionDigits: 0
    });

    function updateSlider(input) {
        const min = Number(input.min);
        const max = Number(input.max);
        const value = Number(input.value);
        input.style.setProperty('--progress', ((value - min) / (max - min)) * 100 + '%');
    }

    function updateLabels() {
        document.getElementById('outstanding-label').textContent = money(loan.value);
        document.getElementById('interest-label').textContent = Number(rate.value).toFixed(2) + '%';
        document.getElementById('tenure-label').textContent = tenure.value + ' Mo';
        [loan, rate, tenure].forEach(updateSlider);
    }

    document.querySelectorAll('[data-strategy]').forEach(button => {
        button.addEventListener('click', function () {
            strategy.value = this.dataset.strategy;

            document.querySelectorAll('[data-strategy]').forEach(item => {
                const active = item === this;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', String(active));
            });

            document.getElementById('amount-label').textContent =
                strategy.value === 'monthly' ? 'Monthly Addition Amount' : 'Prepayment Amount';
        });
    });

    document.querySelectorAll('[data-goal]').forEach(button => {
        button.addEventListener('click', function () {
            goal.value = this.dataset.goal;

            document.querySelectorAll('[data-goal]').forEach(item => {
                const active = item === this;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', String(active));
            });
        });
    });

    [loan, rate, tenure].forEach(input => input.addEventListener('input', updateLabels));

    form.addEventListener('submit', function () {
        const submit = form.querySelector('[type="submit"]');
        submit.disabled = true;
        submit.textContent = 'Calculating…';
    });

    form.addEventListener('reset', function () {
        setTimeout(() => {
            strategy.value = 'one_time';
            goal.value = 'reduce_tenure';
            document.querySelector('[data-strategy="one_time"]').click();
            document.querySelector('[data-goal="reduce_tenure"]').click();
            updateLabels();
        }, 0);
    });

    updateLabels();

    const result = document.getElementById('prepay-result');
    if (result) {
        setTimeout(() => result.scrollIntoView({ behavior: 'smooth', block: 'start' }), 150);
    }
});
</script>
@endsection
