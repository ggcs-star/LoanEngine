@extends('user.layouts.app')

@section('title', 'Loan Eligibility Calculator | LoanEngine')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calculators/home-loan-eligibility.css') }}">
@endpush

@section('content')
@php
    $selectedType = old('loan_type', $inputs['loan_type'] ?? 'home');
    $currency = fn ($amount) => '₹' . number_format((float) $amount, 0, '.', ',');
@endphp

<div class="le-eligibility">
    <div class="le-eligibility__topline">
        <a class="le-eligibility__back" href="{{ route('user.calculators.journeys.index') }}">
            <span aria-hidden="true">←</span> Back to calculators
        </a>
        <span class="le-eligibility__eyebrow">LOANENGINE · LOAN PLANNING</span>
    </div>

    <header class="le-eligibility__hero">
        <div>
            <h1>Loan Eligibility Calculator</h1>
            <p>Estimate your borrowing capacity, monthly EMI and repayment cost before applying.</p>
        </div>
        <div class="le-eligibility__hero-note">
            <span class="le-eligibility__hero-mark">L</span>
            <span><strong>Plan with clarity</strong><small>Estimate · Compare · Decide</small></span>
        </div>
    </header>

    @if ($errors->any())
        <div class="le-eligibility__alert" role="alert">
            <strong>Please check your details.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.calculate', ['tool' => $tool->slug]) }}" id="leEligibilityForm">
        @csrf
        <input type="hidden" name="loan_type" id="loan_type" value="{{ $selectedType }}">

        <section class="le-eligibility__type-section">
            <div class="le-eligibility__section-heading">
                <div>
                    <span class="le-eligibility__step">01</span>
                    <div>
                        <h2>Choose loan type</h2>
                        <p>Select the loan you want to estimate.</p>
                    </div>
                </div>
            </div>

            <div class="le-eligibility__type-grid" role="group" aria-label="Loan type">
                <button type="button" class="le-loan-type {{ $selectedType === 'home' ? 'is-active' : '' }}" data-loan-type="home" aria-pressed="{{ $selectedType === 'home' ? 'true' : 'false' }}">
                    <span class="le-loan-type__icon" aria-hidden="true">⌂</span>
                    <span class="le-loan-type__copy"><strong>Home Loan</strong><small>Buy your home</small></span>
                    <span class="le-loan-type__check" aria-hidden="true">✓</span>
                </button>
                <button type="button" class="le-loan-type {{ $selectedType === 'personal' ? 'is-active' : '' }}" data-loan-type="personal" aria-pressed="{{ $selectedType === 'personal' ? 'true' : 'false' }}">
                    <span class="le-loan-type__icon" aria-hidden="true">↗</span>
                    <span class="le-loan-type__copy"><strong>Personal Loan</strong><small>Flexible expenses</small></span>
                    <span class="le-loan-type__check" aria-hidden="true">✓</span>
                </button>
                <button type="button" class="le-loan-type {{ $selectedType === 'car' ? 'is-active' : '' }}" data-loan-type="car" aria-pressed="{{ $selectedType === 'car' ? 'true' : 'false' }}">
                    <span class="le-loan-type__icon" aria-hidden="true">▰</span>
                    <span class="le-loan-type__copy"><strong>Car Loan</strong><small>Finance a vehicle</small></span>
                    <span class="le-loan-type__check" aria-hidden="true">✓</span>
                </button>
            </div>
        </section>

        <div class="le-eligibility__layout">
            <section class="le-eligibility__panel">
                <div class="le-eligibility__section-heading">
                    <div>
                        <span class="le-eligibility__step">02</span>
                        <div>
                            <h2>Your financial details</h2>
                            <p>Enter realistic values for a more useful estimate.</p>
                        </div>
                    </div>
                </div>

                
                <div class="le-eligibility__fields">

                    @php
                        $sliderFields = [
                            ['monthly_income', 'Net Monthly Income', 10000, 1000000, 1000, '₹', '₹10K', '₹10L'],
                            ['existing_emi', 'Existing EMIs', 0, 500000, 500, '₹', '₹0', '₹5L'],
                            ['property_value', 'Property Value', 500000, 100000000, 50000, '₹', '₹5L', '₹10Cr'],
                            
                            ['ltv_percent', 'LTV Ratio', 50, 90, 1, '%', '50%', '90%'],
                            ['interest_rate', 'Interest Rate', 5, 20, 0.05, '%', '5%', '20%'],
                            ['tenure_years', 'Loan Tenure', 1, 30, 1, 'years', '1 YR', '30 YRS'],
                        ];
                    @endphp

                    @foreach($sliderFields as [$name, $label, $min, $max, $step, $unit, $minLabel, $maxLabel])
                        @php
                            $value = old($name, $inputs[$name] ?? $min);
                        @endphp

                        <div class="le-slider-field">
                            <div class="le-slider-field__head">
                                <label for="slider_{{ $name }}">{{ $label }}</label>
                                <strong id="value_{{ $name }}"></strong>
                            </div>

                            <input
                                type="range"
                                id="slider_{{ $name }}"
                                name="{{ $name }}"
                                min="{{ $min }}"
                                max="{{ $max }}"
                                step="{{ $step }}"
                                value="{{ $value }}"
                                data-unit="{{ $unit }}"
                                required
                            >

                            <div class="le-slider-field__limits">
                                <span>{{ $minLabel }}</span>
                                <span>{{ $maxLabel }}</span>
                            </div>
                        </div>
                    @endforeach

                    <div class="le-slider-field le-slider-field--method">
                        <label for="calculation_method">Calculation Method</label>

                        <select name="calculation_method" id="calculation_method">
                            <option value="standard" @selected(old('calculation_method', $inputs['calculation_method'] ?? 'standard') === 'standard')>
                                FOIR Standard (50%)
                            </option>
                            <option value="conservative" @selected(old('calculation_method', $inputs['calculation_method'] ?? 'standard') === 'conservative')>
                                Conservative (40%)
                            </option>
                            <option value="aggressive" @selected(old('calculation_method', $inputs['calculation_method'] ?? 'standard') === 'aggressive')>
                                Aggressive (60%)
                            </option>
                        </select>
                    </div>

                </div>


                <div class="le-eligibility__actions">
                    <button class="le-button le-button--primary" type="submit">Calculate eligibility <span aria-hidden="true">→</span></button>
                    <button class="le-button le-button--secondary" type="reset" id="leEligibilityReset">Reset values</button>
                </div>
                <p class="le-eligibility__fineprint">Calculations are estimates only and do not guarantee loan approval or represent an offer from a lender.</p>
            </section>

            <aside class="le-eligibility__result-panel" aria-live="polite">
                <div class="le-eligibility__result-heading">
                    <span class="le-eligibility__result-icon" aria-hidden="true">↗</span>
                    <div><span>YOUR ESTIMATE</span><h2>Loan eligibility</h2></div>
                </div>

                @if(isset($result) && is_array($result))
                    <div class="le-eligibility__result-main">
                        <span>Estimated eligible amount</span>
                        <strong>{{ $currency($result['eligible_loan'] ?? 0) }}</strong>
                        <small>Indicative borrowing capacity</small>
                    </div>
                    <div class="le-eligibility__result-stats">
                        <div><span>Estimated monthly EMI</span><strong>{{ $currency($result['monthly_emi'] ?? 0) }}</strong></div>
                        <div><span>Total interest</span><strong>{{ $currency($result['total_interest'] ?? 0) }}</strong></div>
                        <div><span>Available EMI capacity</span><strong>{{ $currency($result['available_emi'] ?? 0) }}</strong></div>
                        @if(($result['required_down_payment'] ?? null) !== null)
                            <div><span>Down payment implied</span><strong>{{ $currency($result['required_down_payment']) }}</strong></div>
                        @endif
                    </div>
                    @if(!empty($result['messages']))
                        <div class="le-eligibility__result-notes">
                            @foreach($result['messages'] as $message)
                                <p><span aria-hidden="true">i</span>{{ $message }}</p>
                            @endforeach
                        </div>
                    @endif
                    <button class="le-button le-button--secondary le-button--full" type="button" onclick="window.print()">Print / Save PDF</button>
                @else
                    <div class="le-eligibility__empty-result">
                        <div class="le-eligibility__empty-art" aria-hidden="true"><span>₹</span></div>
                        <h3>Your result will appear here</h3>
                        <p>Choose a loan type, enter your details and calculate to see an estimated loan amount and monthly EMI.</p>
                        <ul>
                            <li>Income-based repayment capacity</li>
                            <li>Estimated monthly EMI</li>
                            <li>Interest and repayment overview</li>
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </form>

    <footer class="le-eligibility__disclaimer">
        <span class="le-eligibility__disclaimer-icon" aria-hidden="true">i</span>
        <p><strong>Keep in mind</strong> Actual eligibility depends on lender policies, credit score, employment, age, income documentation and other factors. Confirm terms directly with a regulated lender before making a financial decision.</p>
    </footer>
</div>

<script>
(function () {
    const form = document.getElementById('leEligibilityForm');
    if (!form) return;

    const typeInput = document.getElementById('loan_type');
    const typeButtons = Array.from(form.querySelectorAll('[data-loan-type]'));
    const assetValueLabel = document.getElementById('assetValueLabel');
    const assetValueHint = document.getElementById('assetValueHint');
    const ltvInput = form.querySelector('[name="ltv_percent"]');
    const methodSelect = document.getElementById('calculation_method');
    const foirInput = form.querySelector('[name="foir_percent"]');
    const tenureInput = form.querySelector('[name="tenure_years"]');
    const tenureHint = document.getElementById('tenureHint');

    const config = {
        home: { label: 'Property value', hint: 'Approximate property price', ltv: 80, rate: 8.5, tenure: 20, maxTenure: 30 },
        personal: { label: 'Requested loan / expense value', hint: 'For personal loans this value is informational; income capacity determines the estimate', ltv: 80, rate: 14, tenure: 5, maxTenure: 7 },
        car: { label: 'Car on-road price', hint: 'Approximate on-road vehicle price', ltv: 90, rate: 9.5, tenure: 5, maxTenure: 8 }
    };
    const foirOptions = { standard: 50, conservative: 40, aggressive: 60 };

    function syncMethod() {
        if (!methodSelect || !foirInput) return;
        foirInput.value = foirOptions[methodSelect.value] || 50;
    }

    function setLoanType(type, applyDefaults = true) {
        if (!config[type]) type = 'home';
        typeInput.value = type;

        typeButtons.forEach(button => {
            const active = button.dataset.loanType === type;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (assetValueLabel) assetValueLabel.innerHTML = config[type].label + ' <small>₹</small>';
        if (assetValueHint) assetValueHint.textContent = config[type].hint;
        if (ltvInput) ltvInput.value = config[type].ltv;

        const rate = form.querySelector('[name="interest_rate"]');
        if (applyDefaults && rate) rate.value = config[type].rate;
        if (applyDefaults && tenureInput) tenureInput.value = config[type].tenure;
        if (tenureInput) {
            tenureInput.max = config[type].maxTenure;
            tenureHint.textContent = type === 'personal'
                ? 'Personal loan terms vary by lender; verify the offered maximum'
                : 'Repayment period in years';
        }

        // All common fields remain visible for all three loan types.
        form.querySelectorAll('[data-for]').forEach(field => {
            field.hidden = false;
            field.querySelectorAll('input').forEach(input => {
                input.disabled = false;
            });
        });

        syncMethod();
    }

    typeButtons.forEach(button => button.addEventListener('click', () => setLoanType(button.dataset.loanType)));
    if (methodSelect) methodSelect.addEventListener('change', syncMethod);
    form.addEventListener('reset', () => window.setTimeout(() => {
        if (methodSelect) methodSelect.value = 'standard';
        setLoanType('home');
        syncMethod();
    }, 0));

    setLoanType(typeInput.value, false);
    syncMethod();
})();

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sliders = document.querySelectorAll(
        '.le-slider-field input[type="range"]'
    );

    const formatter = new Intl.NumberFormat('en-IN', {
        maximumFractionDigits: 0
    });

    function updateSlider(slider) {
        const min = Number(slider.min);
        const max = Number(slider.max);
        const value = Number(slider.value);

        const progress = max > min
            ? ((value - min) / (max - min)) * 100
            : 0;

        slider.style.setProperty('--progress', progress + '%');

        const output = document.getElementById(
            'value_' + slider.name
        );

        if (!output) return;

        const unit = slider.dataset.unit;

        if (unit === '₹') {
            output.textContent = '₹' + formatter.format(value);
        } else if (unit === '%') {
            output.textContent = value.toFixed(
                slider.name === 'interest_rate' ? 2 : 0
            ) + '%';
        } else {
            output.textContent = value + ' Yrs';
        }
    }

    sliders.forEach(function (slider) {
        updateSlider(slider);

        slider.addEventListener('input', function () {
            updateSlider(slider);
        });
    });

    const form = document.getElementById('leEligibilityForm');

    if (form) {
        form.addEventListener('reset', function () {
            setTimeout(function () {
                sliders.forEach(updateSlider);
            }, 0);
        });
    }
});
</script>

@endsection
