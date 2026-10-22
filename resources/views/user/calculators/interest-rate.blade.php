 @extends('user.layouts.app')

@section('title', 'Interest Rate Calculator | LoanEngine')
@section('page_heading', 'Interest Rate Calculator')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calculators/interest-rate.css') }}">
@endpush

@section('content')
@php
    $v = array_merge([
        'monthly_emi' => 20000,
        'loan_amount' => 400000,
        'tenure_value' => 2,
        'tenure_unit' => 'years',
    ], $inputs ?? [], old());

    $money = fn ($amount) => '₹' . number_format((float) $amount, 0, '.', ',');

    $months = ($v['tenure_unit'] ?? 'years') === 'months'
        ? (int) $v['tenure_value']
        : (int) $v['tenure_value'] * 12;
@endphp

<div class="ir-page">
    <a class="ir-back" href="{{ route('user.calculators.journeys.index') }}">
        <span aria-hidden="true">←</span>
        <span>Interest Rate Calculator</span>
    </a>

    @if ($errors->any())
        <div class="ir-errors" role="alert">
            <strong>Please check your inputs.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('calculators.calculate', ['tool' => $tool->slug]) }}"
        id="interestRateForm"
    >
        @csrf

        <section class="ir-card">
            <div class="ir-field">
                <div class="ir-field-head">
                    <label for="monthly_emi">MONTHLY EMI (₹)</label>
                    <strong id="emiLabel">{{ $money($v['monthly_emi']) }}</strong>
                </div>

                <input
                    type="range"
                    id="monthly_emi"
                    name="monthly_emi"
                    min="1000"
                    max="1000000"
                    step="1000"
                    value="{{ $v['monthly_emi'] }}"
                    data-progress
                >

                <div class="ir-limits">
                    <span>₹1K</span>
                    <span>₹10L</span>
                </div>
            </div>

            <div class="ir-field">
                <div class="ir-field-head">
                    <label for="loan_amount">LOAN AMOUNT (₹)</label>
                    <strong id="loanLabel">{{ $money($v['loan_amount']) }}</strong>
                </div>

                <input
                    type="range"
                    id="loan_amount"
                    name="loan_amount"
                    min="50000"
                    max="100000000"
                    step="50000"
                    value="{{ $v['loan_amount'] }}"
                    data-progress
                >

                <div class="ir-limits">
                    <span>₹50K</span>
                    <span>₹10CR</span>
                </div>
            </div>

            <div class="ir-field ir-tenure-field">
                <div class="ir-field-head">
                    <label for="tenure_value">LOAN TENURE</label>

                    <button
                        type="button"
                        id="tenureToggle"
                        class="ir-toggle"
                        aria-label="Switch between years and months"
                        aria-pressed="{{ ($v['tenure_unit'] ?? 'years') === 'months' ? 'true' : 'false' }}"
                    >
                        <span></span>
                    </button>
                </div>

                <input
                    type="hidden"
                    name="tenure_unit"
                    id="tenure_unit"
                    value="{{ $v['tenure_unit'] }}"
                >

                <div class="ir-tenure-value" id="tenureLabel">
                    {{ $v['tenure_value'] }}
                    {{ $v['tenure_unit'] === 'months' ? 'MONTHS' : ($v['tenure_value'] == 1 ? 'YEAR' : 'YEARS') }}
                </div>

                <input
                    type="range"
                    id="tenure_value"
                    name="tenure_value"
                    min="1"
                    max="{{ $v['tenure_unit'] === 'months' ? 360 : 30 }}"
                    step="1"
                    value="{{ $v['tenure_value'] }}"
                    data-progress
                >

                <div class="ir-limits">
                    <span id="tenureMin">{{ $v['tenure_unit'] === 'months' ? '1 MONTH' : '1 YR' }}</span>
                    <span id="tenureMax">{{ $v['tenure_unit'] === 'months' ? '360 MONTHS' : '30 YRS' }}</span>
                </div>
            </div>

            <div class="ir-actions">
                <button type="submit" class="ir-button-primary">
                    Calculate Interest Rate
                </button>

                <button type="reset" class="ir-button-secondary" id="resetInterestRate">
                    Reset
                </button>
            </div>
        </section>
    </form>

    @if (isset($result) && is_array($result))
        <section class="ir-result" aria-live="polite">

            <div class="ir-rate-highlight">
                <span class="ir-rate-title">IMPLIED ANNUAL INTEREST RATE</span>

                <h2>
                    {{ ($result['annual_rate'] ?? null) === null
                        ? 'Unable to estimate'
                        : number_format($result['annual_rate'], 2) . '%' }}
                </h2>

                <div class="ir-rate-progress">
                    <span style="width: {{ min(100, max(0, (($result['annual_rate'] ?? 0) / 24) * 100)) }}%"></span>
                </div>
            </div>

            @if (($result['annual_rate'] ?? null) !== null)
                <div class="ir-result-metric">
                    <span>MONTHLY RATE</span>
                    <strong>
                        {{ number_format($result['annual_rate'] / 12, 4) }}%
                    </strong>
                </div>

                <div class="ir-result-metric">
                    <span>TOTAL INTEREST PAID</span>
                    <strong class="ir-interest-total">
                        {{ $money(max(0, ($result['monthly_emi'] * $result['tenure_months']) - $result['loan_amount'])) }}
                    </strong>
                </div>

                <div class="ir-result-metric">
                    <span>TOTAL AMOUNT PAID</span>
                    <strong>
                        {{ $money($result['monthly_emi'] * $result['tenure_months']) }}
                    </strong>
                </div>
            @endif

            <button type="button" class="ir-button-secondary" id="resetResult">
                Reset Parameters
            </button>
        </section>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('interestRateForm');
    if (!form) return;

    const emi = document.getElementById('monthly_emi');
    const loan = document.getElementById('loan_amount');
    const tenure = document.getElementById('tenure_value');
    const unit = document.getElementById('tenure_unit');
    const toggle = document.getElementById('tenureToggle');

    const formatMoney = value =>
        '₹' + Number(value).toLocaleString('en-IN', {
            maximumFractionDigits: 0
        });

    function updateSlider(input) {
        const min = Number(input.min);
        const max = Number(input.max);
        const value = Number(input.value);
        const progress = max > min ? ((value - min) / (max - min)) * 100 : 0;

        input.style.setProperty('--progress', progress + '%');
    }

    function updateUI() {
        document.getElementById('emiLabel').textContent = formatMoney(emi.value);
        document.getElementById('loanLabel').textContent = formatMoney(loan.value);

        const isMonths = unit.value === 'months';
        const tenureNumber = Number(tenure.value);

        document.getElementById('tenureLabel').textContent =
            tenureNumber + (isMonths
                ? ' MONTHS'
                : (tenureNumber === 1 ? ' YEAR' : ' YEARS'));

        document.getElementById('tenureMin').textContent =
            isMonths ? '1 MONTH' : '1 YR';

        document.getElementById('tenureMax').textContent =
            isMonths ? '360 MONTHS' : '30 YRS';

        [emi, loan, tenure].forEach(updateSlider);
    }

    toggle.addEventListener('click', function () {
        const wasMonths = unit.value === 'months';

        const equivalentMonths = wasMonths
            ? Number(tenure.value)
            : Number(tenure.value) * 12;

        unit.value = wasMonths ? 'years' : 'months';
        tenure.min = 1;
        tenure.max = wasMonths ? 30 : 360;

        tenure.value = wasMonths
            ? Math.max(1, Math.min(30, Math.round(equivalentMonths / 12)))
            : Math.max(1, Math.min(360, equivalentMonths));

        toggle.classList.toggle('is-active', unit.value === 'months');
        toggle.setAttribute('aria-pressed', String(unit.value === 'months'));

        updateUI();
    });

    [emi, loan, tenure].forEach(input => {
        input.addEventListener('input', updateUI);
    });

    form.addEventListener('reset', function () {
        setTimeout(function () {
            unit.value = 'years';
            tenure.min = 1;
            tenure.max = 30;
            tenure.value = 2;

            toggle.classList.remove('is-active');
            toggle.setAttribute('aria-pressed', 'false');

            updateUI();
        }, 0);
    });

    document.getElementById('resetResult')?.addEventListener('click', function () {
        emi.value = 20000;
        loan.value = 400000;
        unit.value = 'years';
        tenure.min = 1;
        tenure.max = 30;
        tenure.value = 2;

        toggle.classList.remove('is-active');
        toggle.setAttribute('aria-pressed', 'false');

        updateUI();

        // Reset the calculator inputs and hide the previous result.
        window.location.href = window.location.pathname;
    });

    toggle.classList.toggle('is-active', unit.value === 'months');
    updateUI();
});
</script>
@endsection