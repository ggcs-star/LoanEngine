@extends('user.layouts.app')
@section('title', 'Loan Eligibility Calculator | LoanEngine')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/calculators/home-loan-eligibility.css') }}">
@endpush

@section('content')
@php
    $defaults = [
        'loan_type' => 'home',
        'monthly_income' => 80000,
        'existing_emi' => 10000,
        'property_value' => 4500000,
        'ltv_percent' => 80,
        'interest_rate' => 8.5,
        'tenure_years' => 20,
        'calculation_method' => 'standard',
    ];

    $values = array_merge($defaults, $inputs ?? []);
    $loanType = old('loan_type', $values['loan_type']);
    $method = old('calculation_method', $values['calculation_method']);
    $money = fn($value) => '₹' . number_format((float) $value, 0, '.', ',');
    $shortMoney = function ($value) {
        $value = (float) $value;
        if ($value >= 10000000) {
            return '₹' . number_format($value / 10000000, 1) . 'Cr';

        }

        if ($value >= 100000) {
            return '₹' . number_format($value / 100000, 1) . 'L';

        }



        return '₹' . number_format($value, 0);

    };

@endphp



<div class="le-eligibility-page">

    <a class="le-eligibility-back"

       href="{{ route('user.calculators.journeys.index') }}">

        ← Back to calculators

    </a>



    <header class="le-eligibility-heading">

        <div>

            <span class="le-eligibility-eyebrow">

                LOANENGINE · LOAN PLANNING

            </span>



            <h1>Loan Eligibility Calculator</h1>



            <p>

                Estimate your borrowing capacity, monthly EMI and

                repayment cost before applying.

            </p>

        </div>



        <div class="le-eligibility-brand">

            <span>L</span>

            <div>

                <strong>Plan with clarity</strong>

                <small>Estimate · Compare · Decide</small>

            </div>

        </div>

    </header>



    @if ($errors->any())

        <div class="le-eligibility-errors" role="alert">

            <strong>Please correct the following errors:</strong>



            <ul>

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif



    <form

        id="leEligibilityForm"

        method="POST"

        action="{{ route('calculators.calculate', ['tool' => $tool->slug]) }}"

    >

        @csrf



        <input

            type="hidden"

            name="loan_type"

            id="loan_type"

            value="{{ $loanType }}"

        >



        <div class="le-eligibility-layout">



            {{-- LEFT: INPUTS --}}

            <section class="le-eligibility-form-card">



                <div class="le-section-heading">

                    <span>01</span>

                    <div>

                        <h2>Choose loan type</h2>

                        <p>Select the loan you want to estimate.</p>

                    </div>

                </div>



                <div class="le-loan-types">

                    @foreach ([

                        'home' => ['Home Loan', 'Buy your home', '⌂'],

                        'personal' => ['Personal Loan', 'Flexible expenses', '↗'],

                        'car' => ['Car Loan', 'Finance a vehicle', '▰'],

                    ] as $type => $details)

                        <button

                            type="button"

                            class="le-loan-type {{ $loanType === $type ? 'is-active' : '' }}"

                            data-loan-type="{{ $type }}"

                            aria-pressed="{{ $loanType === $type ? 'true' : 'false' }}"

                        >

                            <span class="le-loan-type-icon">

                                {{ $details[2] }}

                            </span>



                            <span class="le-loan-type-copy">

                                <strong>{{ $details[0] }}</strong>

                                <small>{{ $details[1] }}</small>

                            </span>



                            <span class="le-loan-check">✓</span>

                        </button>

                    @endforeach

                </div>



                <div class="le-section-heading le-details-heading">

                    <span>02</span>

                    <div>

                        <h2>Your financial profile</h2>

                        <p>Move the sliders to update your estimate.</p>

                    </div>

                </div>



                <div class="le-slider-grid">



                    {{-- Monthly income --}}

                    <div class="le-slider-field">

                        <div class="le-slider-head">

                            <label for="monthly_income">Net Monthly Income</label>

                            <strong id="value_monthly_income"></strong>

                        </div>



                        <input

                            type="range"

                            id="monthly_income"

                            name="monthly_income"

                            min="10000"

                            max="1000000"

                            step="1000"

                            value="{{ old('monthly_income', $values['monthly_income']) }}"

                            data-format="money"

                        >



                        <div class="le-slider-limits">

                            <span>₹10K</span>

                            <span>₹10L</span>

                        </div>

                    </div>



                    {{-- Existing EMIs --}}

                    <div class="le-slider-field">

                        <div class="le-slider-head">

                            <label for="existing_emi">Existing EMIs</label>

                            <strong id="value_existing_emi"></strong>

                        </div>



                        <input

                            type="range"

                            id="existing_emi"

                            name="existing_emi"

                            min="0"

                            max="500000"

                            step="500"

                            value="{{ old('existing_emi', $values['existing_emi']) }}"

                            data-format="money"

                        >



                        <div class="le-slider-limits">

                            <span>₹0</span>

                            <span>₹5L</span>

                        </div>

                    </div>



                    {{-- Asset/property value --}}

                    <div class="le-slider-field" id="propertyField">

                        <div class="le-slider-head">

                            <label id="propertyLabel" for="property_value">

                                Property Value

                            </label>

                            <strong id="value_property_value"></strong>

                        </div>



                        <input

                            type="range"

                            id="property_value"

                            name="property_value"

                            min="0"

                            max="100000000"

                            step="50000"

                            value="{{ old('property_value', $values['property_value']) }}"

                            data-format="money"

                        >



                        <div class="le-slider-limits">

                            <span>₹0</span>

                            <span>₹10Cr</span>

                        </div>

                    </div>



                    {{-- LTV --}}

                    <div class="le-slider-field" id="ltvField">

                        <div class="le-slider-head">

                            <label for="ltv_percent">LTV Ratio</label>

                            <strong id="value_ltv_percent"></strong>

                        </div>



                        <input

                            type="range"

                            id="ltv_percent"

                            name="ltv_percent"

                            min="10"

                            max="90"

                            step="1"

                            value="{{ old('ltv_percent', $values['ltv_percent']) }}"

                            data-format="percent"

                        >



                        <div class="le-slider-limits">

                            <span>10%</span>

                            <span>90%</span>

                        </div>

                    </div>



                    {{-- Interest --}}

                    <div class="le-slider-field">

                        <div class="le-slider-head">

                            <label for="interest_rate">Interest Rate (p.a.)</label>

                            <strong id="value_interest_rate"></strong>

                        </div>



                        <input

                            type="range"

                            id="interest_rate"

                            name="interest_rate"

                            min="0"

                            max="35"

                            step="0.1"

                            value="{{ old('interest_rate', $values['interest_rate']) }}"

                            data-format="percent2"

                        >



                        <div class="le-slider-limits">

                            <span>0%</span>

                            <span>35%</span>

                        </div>

                    </div>



                    {{-- Tenure --}}

                    <div class="le-slider-field">

                        <div class="le-slider-head">

                            <label for="tenure_years">Loan Tenure</label>

                            <strong id="value_tenure_years"></strong>

                        </div>



                        <input

                            type="range"

                            id="tenure_years"

                            name="tenure_years"

                            min="1"

                            max="{{ $loanType === 'personal' ? 7 : ($loanType === 'car' ? 8 : 30) }}"

                            step="1"

                            value="{{ old('tenure_years', $values['tenure_years']) }}"

                            data-format="years"

                        >



                        <div class="le-slider-limits">

                            <span>1 YR</span>

                            <span id="max_tenure_years">

                                {{ $loanType === 'personal' ? '7 YRS' : ($loanType === 'car' ? '8 YRS' : '30 YRS') }}

                            </span>

                        </div>

                    </div>



                </div>



                {{-- Calculation method dropdown --}}

                <div class="le-method-field">

                    <label for="calculation_method">Calculation Method</label>



                    <select name="calculation_method" id="calculation_method">

                        <option value="standard" @selected($method === 'standard')>

                            FOIR (Standard 50%)

                        </option>



                        <option value="conservative" @selected($method === 'conservative')>

                            Conservative (40%)

                        </option>



                        <option value="aggressive" @selected($method === 'aggressive')>

                            Aggressive (60%)

                        </option>

                    </select>



                    <small>

                        FOIR estimates the share of monthly income available

                        for all EMIs.

                    </small>

                </div>



                <div class="le-eligibility-actions">

                    <button type="submit" class="le-button le-button-primary">

                        Check Loan Eligibility <span>→</span>

                    </button>



                    <button type="reset" class="le-button le-button-secondary">

                        Reset

                    </button>

                </div>



                <p class="le-disclaimer">

                    This calculator provides estimates only. Actual loan

                    approval depends on lender policy and verification.

                </p>

            </section>



            {{-- RIGHT: RESULTS --}}

            <aside class="le-eligibility-result-column">



                @if (isset($result) && is_array($result))



                    <section class="le-result-card">

                        <div class="le-result-label">MAX ELIGIBLE LOAN</div>



                        <div class="le-result-amount">

                            {{ $money($result['eligible_loan'] ?? 0) }}

                        </div>



                        <div class="le-result-status {{ ($result['status'] ?? '') === 'PASSED' ? 'is-passed' : 'is-review' }}">

                            <span>{{ ($result['status'] ?? '') === 'PASSED' ? '✓' : '!' }}</span>



                            {{ ($result['status'] ?? '') === 'PASSED' ? 'Eligible' : 'Review inputs' }}

                        </div>



                        <p>

                            Based on your profile, your estimated loan

                            eligibility is up to

                            {{ $money($result['eligible_loan'] ?? 0) }}.

                        </p>

                    </section>



                    <section class="le-result-metrics">

                        <div class="le-result-metric">

                            <span>MONTHLY INCOME</span>

                            <strong>{{ $shortMoney($values['monthly_income']) }}</strong>

                        </div>



                        <div class="le-result-metric">

                            <span>FOIR ({{ $result['foir_percent'] ?? 50 }}%)</span>

                            <strong>{{ $money($result['allowed_total_emi'] ?? 0) }}</strong>

                        </div>



                        <div class="le-result-metric">

                            <span>ELIGIBLE EMI</span>

                            <strong>{{ $money($result['monthly_emi'] ?? 0) }}</strong>

                        </div>



                        <div class="le-result-metric">

                            <span>RATE (P.A.)</span>

                            <strong>{{ number_format((float) $values['interest_rate'], 2) }}%</strong>

                        </div>



                        <div class="le-result-metric">

                            <span>TENURE</span>

                            <strong>{{ $result['tenure_years'] ?? $values['tenure_years'] }}Y</strong>

                        </div>



                        @if (($result['loan_type'] ?? $loanType) !== 'personal')

                            <div class="le-result-metric">

                                <span>LTV LIMIT</span>

                                <strong>{{ number_format((float) ($result['ltv_percent'] ?? $values['ltv_percent']), 0) }}%</strong>

                            </div>



                            <div class="le-result-metric">

                                <span>PROPERTY / ASSET</span>

                                <strong>{{ $shortMoney($result['property_value'] ?? $values['property_value']) }}</strong>

                            </div>

                        @endif



                        <div class="le-result-metric">

                            <span>STATUS</span>

                            <strong>{{ $result['status'] ?? 'REVIEW' }}</strong>

                        </div>



                        <div class="le-result-metric">

                            <span>TOTAL INTEREST</span>

                            <strong>{{ $shortMoney($result['total_interest'] ?? 0) }}</strong>

                        </div>

                    </section>



                    <section class="le-recommendations">

                        <h2><span>✦</span> Personalized Recommendations</h2>



                        @foreach (($result['recommendations'] ?? []) as $recommendation)

                            <article class="le-recommendation">

                                <span class="le-recommendation-icon">

                                    @if (($recommendation['icon'] ?? '') === 'trending-up')

                                        ↗

                                    @elseif (($recommendation['icon'] ?? '') === 'wallet')

                                        ▣

                                    @elseif (($recommendation['icon'] ?? '') === 'user-plus')

                                        ♙

                                    @else

                                        ✦

                                    @endif

                                </span>



                                <div>

                                    <h3>{{ $recommendation['title'] }}</h3>

                                    <p>{{ $recommendation['description'] }}</p>

                                </div>

                            </article>

                        @endforeach

                    </section>





                    <button type="button" class="le-button le-button-primary le-apply-button">

                        Apply for Loan

                    </button>





                    <div class="le-result-bottom-actions">

                        <a href="{{ route('calculators.show', ['tool' => 'home-loan-emi-calculator']) }}"

                           class="le-button le-button-secondary">

                            ▦ View EMI

                        </a>







                <button type="button"

                        onclick="window.print()"

                        class="le-button le-button-secondary le-print-button"

                        aria-label="Print or save PDF">

                    <svg xmlns="http\://www.w3.org/2000/svg"

                        width="18" height="18"

                        viewBox="0 0 24 24"

                        fill="none" stroke="currentColor"

                        stroke-width="1.8"

                        stroke-linecap="round"

                        stroke-linejoin="round"

                        aria-hidden="true">

                        <polyline points="6 9 6 2 18 2 18 9"></polyline>

                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>

                        <rect x="6" y="14" width="12" height="8"></rect>

                        <path d="M18 12h.01"></path>

                    </svg>

                    <span>Print / Save PDF</span>

                </button>



                    </div>



                @else



                    <section class="le-result-placeholder">

                        <span class="le-placeholder-icon">↗</span>

                        <h2>Your eligibility overview</h2>



                        <p>

                            Adjust your financial details and select

                            <strong>Check Loan Eligibility</strong> to see

                            your estimated loan amount, EMI and recommendations.

                        </p>

                    </section>



                    <section class="le-result-info">

                        <h3>How it works</h3>



                        <p>

                            We estimate repayment capacity using income,

                            existing EMIs, interest rate and tenure. For home

                            and car loans, we also apply an LTV limit.

                        </p>

                    </section>



                @endif



            </aside>

        </div>

    </form>

</div>



<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('leEligibilityForm');

    if (!form) return;



    const typeInput = document.getElementById('loan_type');

    const methodSelect = document.getElementById('calculation_method');

    const typeButtons = [...form.querySelectorAll('[data-loan-type]')];



    const sliders = [...form.querySelectorAll('input[type="range"]')];



    const config = {

        home:     { rate: 8.5, tenure: 20, maxTenure: 30, ltv: 80 },

        personal: { rate: 14,  tenure: 5,  maxTenure: 7,  ltv: 80 },

        car:      { rate: 9.5, tenure: 5,  maxTenure: 8,  ltv: 90 }

    };



    function indianNumber(value) {

        return new Intl.NumberFormat('en-IN', {

            maximumFractionDigits: 0

        }).format(Number(value));

    }



    function updateSlider(slider) {

        const min = Number(slider.min);

        const max = Number(slider.max);

        const value = Number(slider.value);



        const progress = max > min

            ? ((value - min) / (max - min)) * 100

            : 0;



        slider.style.setProperty('--progress', progress + '%');



        const output = document.getElementById('value_' + slider.id);

        if (!output) return;



        switch (slider.dataset.format) {

            case 'money':

                output.textContent = '₹' + indianNumber(value);

                break;



            case 'percent':

                output.textContent = value.toFixed(0) + '%';

                break;



            case 'percent2':

                output.textContent = value.toFixed(2) + '%';

                break;



            case 'years':

                output.textContent = value + ' Yrs';

                break;

        }

    }



    function setLoanType(type, changeDefaults = true) {

        if (!config[type]) type = 'home';



        typeInput.value = type;



        typeButtons.forEach(button => {

            const active = button.dataset.loanType === type;



            button.classList.toggle('is-active', active);

            button.setAttribute('aria-pressed', active ? 'true' : 'false');

        });



        const propertyLabel = document.getElementById('propertyLabel');

        const propertyField = document.getElementById('propertyField');

        const ltvField = document.getElementById('ltvField');



        const tenure = document.getElementById('tenure_years');

        const rate = document.getElementById('interest_rate');

        const ltv = document.getElementById('ltv_percent');



        propertyLabel.textContent = type === 'car'

            ? 'Car / Vehicle Value'

            : type === 'personal'

                ? 'Loan / Expense Amount'

                : 'Property Value';



        // Keep shared sliders visible, but personal loans do not use LTV.

        propertyField.hidden = false;

        ltvField.hidden = type === 'personal';



        tenure.max = config[type].maxTenure;



        document.getElementById('max_tenure_years').textContent =

            config[type].maxTenure + ' YRS';



        if (Number(tenure.value) > config[type].maxTenure) {

            tenure.value = config[type].maxTenure;

        }



        if (changeDefaults) {

            rate.value = config[type].rate;

            tenure.value = config[type].tenure;

            ltv.value = config[type].ltv;

        }



        sliders.forEach(updateSlider);

    }



    sliders.forEach(slider => {

        updateSlider(slider);

        slider.addEventListener('input', () => updateSlider(slider));

    });



    typeButtons.forEach(button => {

        button.addEventListener('click', function () {

            setLoanType(this.dataset.loanType);

        });

    });



    form.addEventListener('reset', function () {

        setTimeout(function () {

            methodSelect.value = 'standard';

            setLoanType('home', false);

            sliders.forEach(updateSlider);

        }, 0);

    });



    setLoanType(typeInput.value, false);



    const shareButton = document.getElementById('shareEligibility');



    if (shareButton) {

        shareButton.addEventListener('click', async function () {

            const amount = document.querySelector('.le-result-amount')?.textContent;



            const text = 'My estimated loan eligibility from LoanEngine: ' + amount;



            try {

                if (navigator.share) {

                    await navigator.share({

                        title: 'Loan Eligibility Estimate',

                        text: text

                    });

                } else if (navigator.clipboard) {

                    await navigator.clipboard.writeText(text);

                    shareButton.textContent = '✓';

                } else {

                    window.prompt('Copy your result', text);

                }

            } catch (error) {

                // User may cancel the share dialog.

            }

        });

    }

});

</script>

@endsection