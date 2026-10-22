
@extends('user.layouts.app')

@section('title', 'EMI vs Tenure Calculator | LoanEngine')
@section('page_heading', 'EMI vs Tenure Calculator')

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/calculators/emi-vs-tenure.css') }}">
@endpush

@section('content')
@php
    $loanAmount = (float) ($inputs['loan_amount'] ?? 7000000);
    $interestRate = (float) ($inputs['interest_rate'] ?? 8.5);
    $tenure = (int) ($inputs['tenure_years'] ?? 20);

    $comparisons = $result['comparisons'] ?? [];

    if (empty($comparisons)) {
        $monthsRate = $interestRate / 1200;

        foreach ([10, 15, 20, 25, 30] as $years) {
            $months = $years * 12;

            $factor = pow(1 + $monthsRate, $months);

            $emi = $monthsRate > 0
                ? $loanAmount * $monthsRate * $factor / ($factor - 1)
                : $loanAmount / $months;

            $comparisons[] = [
                'tenure_years' => $years,
                'emi' => (int) round($emi),
                'total_interest' => (int) round(
                    $emi * $months - $loanAmount
                ),
                'total_payment' => (int) round($emi * $months),
            ];
        }
    }

    $selected = collect($comparisons)->firstWhere(
        'tenure_years',
        $tenure
    ) ?? $comparisons[2];

    $money = fn ($value) =>
        '₹' . number_format((float) $value, 0, '.', ',');
@endphp

<div class="evt-page">
    <div class="evt-container">

        <a href="{{ url()->previous() }}" class="evt-back">
            <span aria-hidden="true">←</span>
            <span>EMI vs Tenure Calculator</span>
        </a>

        <header class="evt-header">
            <h1>Balance Your Loan</h1>
            <p>
                Compare monthly costs across different durations to find
                the perfect middle ground between monthly budget and
                interest savings.
            </p>
        </header>

        <form method="GET" action="{{ url()->current() }}"
              id="evt-form">

            <div class="evt-input-card">
                <div class="evt-input-heading">
                    <label for="evt-loan">LOAN AMOUNT</label>
                    <output id="evt-loan-output">
                        ₹70L
                    </output>
                </div>

                <input
                    id="evt-loan"
                    class="evt-range"
                    type="range"
                    name="loan_amount"
                    min="1000000"
                    max="10000000"
                    step="100000"
                    value="{{ $loanAmount }}"
                >

                <div class="evt-range-labels">
                    <span>10 LAKH</span>
                    <span>1 CRORE</span>
                </div>
            </div>

            <div class="evt-input-card">
                <div class="evt-input-heading">
                    <label for="evt-rate">INTEREST RATE</label>
                    <output id="evt-rate-output">
                        {{ number_format($interestRate, 1) }}%
                    </output>
                </div>

                <input
                    id="evt-rate"
                    class="evt-range"
                    type="range"
                    name="interest_rate"
                    min="5"
                    max="15"
                    step="0.1"
                    value="{{ $interestRate }}"
                >

                <div class="evt-range-labels">
                    <span>5% PA</span>
                    <span>15% PA</span>
                </div>
            </div>

            <input type="hidden" name="tenure_years"
                   id="evt-tenure" value="{{ $tenure }}">
            <input type="hidden" name="calculate" value="1">

            @foreach (['loan_amount', 'interest_rate', 'tenure_years'] as $field)
                @error($field)
                    <p class="evt-error">{{ $message }}</p>
                @enderror
            @endforeach

            <div class="evt-section-heading">
                <h2>SELECT TENURE COMPARISON</h2>
            </div>

            <div class="evt-grid">
                @foreach ($comparisons as $item)
                    <button
                        type="button"
                        class="evt-tenure-card {{ $item['tenure_years'] === $tenure ? 'is-selected' : '' }}"
                        data-years="{{ $item['tenure_years'] }}"
                        aria-pressed="{{ $item['tenure_years'] === $tenure ? 'true' : 'false' }}"
                    >
                        <span class="evt-years">
                            {{ $item['tenure_years'] }} YEARS
                        </span>

                        <strong class="evt-emi">
                            {{ $money($item['emi']) }}
                        </strong>

                        <span class="evt-interest">
                            Interest: {{ $money($item['total_interest']) }}
                        </span>
                    </button>
                @endforeach
            </div>

            <section class="evt-result" aria-live="polite">
                <span class="evt-result-label">YOUR SELECTED LOAN PLAN</span>

                <div class="evt-result-main">
                    <div>
                        <span>Monthly EMI</span>
                        <strong id="evt-result-emi">
                            {{ $money($selected['emi']) }}
                        </strong>
                    </div>

                    <span class="evt-pill" id="evt-result-years">
                        {{ $tenure }} Years
                    </span>
                </div>

                <div class="evt-divider"></div>

                <div class="evt-result-row">
                    <span>Loan Amount</span>
                    <strong id="evt-result-loan">
                        {{ $money($loanAmount) }}
                    </strong>
                </div>

                <div class="evt-result-row">
                    <span>Total Interest</span>
                    <strong id="evt-result-interest">
                        {{ $money($selected['total_interest']) }}
                    </strong>
                </div>

                <div class="evt-result-row">
                    <span>Total Payment</span>
                    <strong id="evt-result-payment">
                        {{ $money($selected['total_payment']) }}
                    </strong>
                </div>
            </section>

            <button type="submit" class="evt-submit">
                Calculate Loan <span aria-hidden="true">→</span>
            </button>
        </form>

        <p class="evt-disclaimer">
            These figures are estimates based on a monthly reducing-balance
            EMI formula. Actual lender terms, fees and rounding may differ.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const loan = document.getElementById('evt-loan');
    const rate = document.getElementById('evt-rate');
    const tenure = document.getElementById('evt-tenure');

    if (!loan || !rate || !tenure) return;

    const money = value => '₹' + Math.round(value).toLocaleString('en-IN');

    function calculateEmi(principal, annualRate, years) {
        const months = years * 12;
        const monthlyRate = annualRate / 1200;

        if (monthlyRate === 0) return principal / months;

        const factor = Math.pow(1 + monthlyRate, months);

        return principal * monthlyRate * factor / (factor - 1);
    }

    function update() {
        const principal = Number(loan.value);
        const annualRate = Number(rate.value);
        const years = Number(tenure.value);

        document.getElementById('evt-loan-output').textContent =
            '₹' + (principal / 100000).toLocaleString('en-IN',
                { maximumFractionDigits: 1 }) + 'L';

        document.getElementById('evt-rate-output').textContent =
            annualRate.toFixed(1) + '%';

        document.getElementById('evt-result-loan').textContent =
            money(principal);

        const emi = calculateEmi(principal, annualRate, years);
        const payment = emi * years * 12;

        document.getElementById('evt-result-emi').textContent = money(emi);
        document.getElementById('evt-result-interest').textContent =
            money(payment - principal);
        document.getElementById('evt-result-payment').textContent =
            money(payment);
        document.getElementById('evt-result-years').textContent =
            years + ' Years';

        document.querySelectorAll('.evt-tenure-card').forEach(card => {
            const cardYears = Number(card.dataset.years);
            const selected = cardYears === years;
            const cardEmi = calculateEmi(principal, annualRate, cardYears);
            const cardPayment = cardEmi * cardYears * 12;

            card.classList.toggle('is-selected', selected);
            card.setAttribute('aria-pressed', String(selected));

            card.querySelector('.evt-emi').textContent = money(cardEmi);
            card.querySelector('.evt-interest').textContent =
                'Interest: ' + money(cardPayment - principal);
        });
    }

    loan.addEventListener('input', update);
    rate.addEventListener('input', update);

    document.querySelectorAll('.evt-tenure-card').forEach(card => {
        card.addEventListener('click', function () {
            tenure.value = this.dataset.years;
            update();
        });
    });

    update();
});
</script>
@endpush
