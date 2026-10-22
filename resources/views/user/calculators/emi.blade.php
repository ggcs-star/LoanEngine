
@extends('user.layouts.app')

@section('title', $tool->name . ' | LoanEngine')
@section('page_heading', $tool->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calculators/home-loan-emi.css') }}">
@endpush

@section('content')
@php
    $v = array_merge([
        'loan_type' => 'home',
        'loan_amount' => 2500000,
        'interest_rate' => 8.5,
        'tenure_months' => 240,
        'processing_fee' => 0,
    ], $inputs ?? [], old());

    $money = fn ($n) => '₹' . number_format((float) $n, 0, '.', ',');

    $hasResult = is_array($result ?? null) && !empty($result);
    $schedule = $hasResult ? ($result['schedule'] ?? []) : [];

    $loanType = ($v['loan_type'] ?? 'home') === 'personal' ? 'personal' : 'home';
    $tenureMonths = max(1, (int) ($v['tenure_months'] ?? 240));
    $tenureYears = max(1, (int) ceil($tenureMonths / 12));
    $isPersonal = $loanType === 'personal';
@endphp

<div class="emi-page" id="emi-calculator">

    <a class="emi-back" href="{{ route('user.calculators.journeys.index') }}">
        ← Back to calculators
    </a>

    <header class="emi-heading">
        <span class="emi-eyebrow">LOANENGINE · CALCULATOR</span>
        <h1>Home Loan EMI Calculator</h1>
        <p>Calculate your monthly EMI, compare interest costs and view your repayment schedule.</p>
    </header>

    @if ($errors->any())
        <div class="emi-errors" role="alert">
            <strong>Please check your inputs.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Calculator input form --}}
    <form
        method="POST"
        action="{{ route('calculators.calculate', ['tool' => $tool->slug]) }}"
        id="emi-form"
    >
        @csrf

        <section class="emi-card">

            {{-- Loan type --}}
            <div class="emi-toggle" role="group" aria-label="Loan type">
                <button
                    type="button"
                    data-loan-type="home"
                    class="{{ !$isPersonal ? 'active' : '' }}"
                    aria-pressed="{{ !$isPersonal ? 'true' : 'false' }}"
                >Home Loan</button>

                <button
                    type="button"
                    data-loan-type="personal"
                    class="{{ $isPersonal ? 'active' : '' }}"
                    aria-pressed="{{ $isPersonal ? 'true' : 'false' }}"
                >Personal Loan</button>

                <input type="hidden" name="loan_type" id="loan_type" value="{{ $loanType }}">
            </div>

            {{-- Loan amount --}}
            <div class="emi-field">
                <div class="emi-field-head">
                    <label for="loan_amount">Loan Amount</label>
                    <strong id="loan-amount-label">{{ $money($v['loan_amount']) }}</strong>
                </div>

                <input
                    id="loan_amount"
                    name="loan_amount"
                    type="range"
                    min="100000"
                    max="{{ $isPersonal ? 5000000 : 50000000 }}"
                    step="50000"
                    value="{{ $v['loan_amount'] }}"
                >

                <div class="emi-range-labels">
                    <span>₹1L</span>
                    <span id="loan-amount-max-label">{{ $isPersonal ? '₹50L' : '₹5Cr' }}</span>
                </div>
            </div>

            {{-- Interest rate --}}
            <div class="emi-field">
                <div class="emi-field-head">
                    <label for="interest_rate">Interest Rate (p.a.)</label>
                    <strong><span id="rate-label">{{ number_format((float) $v['interest_rate'], 2) }}</span>%</strong>
                </div>

                <input
                    id="interest_rate"
                    name="interest_rate"
                    type="range"
                    min="5"
                    max="{{ $isPersonal ? 35 : 20 }}"
                    step="0.1"
                    value="{{ $v['interest_rate'] }}"
                >

                <div class="emi-range-labels">
                    <span>5%</span>
                    <span id="rate-max-label">{{ $isPersonal ? '35%' : '20%' }}</span>
                </div>
            </div>

            {{-- Tenure unit selector --}}
            <div class="emi-tenure-head">
                <div class="emi-toggle emi-tenure-toggle" role="group" aria-label="Tenure unit">
                    <button
                        type="button"
                        data-tenure="years"
                        class="active"
                        aria-pressed="true"
                    >Years</button>

                    <button
                        type="button"
                        data-tenure="months"
                        aria-pressed="false"
                    >Months</button>
                </div>

                <strong id="tenure-label">{{ $tenureYears }}</strong>
            </div>

            <input
                id="tenure_slider"
                type="range"
                min="1"
                max="{{ $isPersonal ? 7 : 30 }}"
                step="1"
                value="{{ min($isPersonal ? 7 : 30, $tenureYears) }}"
            >

            <div class="emi-range-labels">
                <span id="tenure-min">1 Yr.</span>
                <span id="tenure-max">{{ $isPersonal ? '7 Yrs.' : '30 Yrs.' }}</span>
            </div>

            <input
                type="hidden"
                id="tenure_months"
                name="tenure_months"
                value="{{ $tenureMonths }}"
            >

            
            <div class="emi-field emi-fee">
                <div class="emi-field-head">
                    <label for="processing_fee">Processing Fee (Optional)</label>
                    <strong id="fee-label">
                        {{ $money($v['processing_fee'] ?? 0) }}
                    </strong>
                </div>

                <input
                    id="processing_fee"
                    name="processing_fee"
                    type="number"
                    min="0"
                    max="1000000"
                    step="500"
                    value="{{ old('processing_fee', $inputs['processing_fee'] ?? '') }}"
                    placeholder="Enter fee (optional)"
                >
            </div>


            <button class="emi-submit" type="submit">Calculate EMI</button>
        </section>
    </form>

    {{-- Results are rendered only after server-side calculation --}}
    @if ($hasResult)

        <section class="emi-card emi-results" id="emi-results" aria-live="polite">
            <div class="emi-result-label">ESTIMATED MONTHLY EMI</div>

            <div class="emi-main-number" id="emi-value">
                {{ $money($result['emi'] ?? 0) }}
            </div>

            <div class="emi-progress">
                <span
                    id="principal-bar"
                    style="width: {{ (float) ($result['principal_percentage'] ?? 0) }}%"
                ></span>
            </div>

            <div class="emi-legend">
                <span>
                    <i class="emi-dot principal"></i>
                    Principal (<b id="principal-percent">{{ number_format((float) ($result['principal_percentage'] ?? 0), 1) }}%</b>)
                </span>

                <span>
                    <i class="emi-dot interest"></i>
                    Interest (<b id="interest-percent">{{ number_format((float) ($result['interest_percentage'] ?? 0), 1) }}%</b>)
                </span>
            </div>

            <div class="emi-summary-row">
                <span>Loan Amount</span>
                <strong id="summary-principal">{{ $money($result['loan_amount'] ?? $v['loan_amount']) }}</strong>
            </div>

            <div class="emi-summary-row">
                <span>Interest Rate</span>
                <strong id="summary-rate">{{ number_format((float) $v['interest_rate'], 2) }}%</strong>
            </div>

            <div class="emi-summary-row">
                <span>Tenure</span>
                <strong id="summary-tenure">
                    {{ $tenureMonths % 12 === 0
                        ? ($tenureMonths / 12) . ' years'
                        : $tenureMonths . ' months' }}
                </strong>
            </div>

            <div class="emi-summary-row">
                <span>Total Interest</span>
                <strong id="summary-interest">{{ $money($result['total_interest'] ?? 0) }}</strong>
            </div>

            <div class="emi-summary-row">
                <span>Total Payment</span>
                <strong id="summary-total">{{ $money($result['total_payment'] ?? 0) }}</strong>
            </div>

            <div class="emi-summary-row">
                <span>Processing Fee</span>
                <strong id="summary-fee">{{ $money($v['processing_fee']) }}</strong>
            </div>
        </section>

        {{-- Repayment schedule --}}
        <section class="emi-card emi-amortization" id="emi-amortization">

            <div class="emi-table-heading">
                <h2>EMI Repayment Schedule</h2>

                <button
                    type="button"
                    id="schedule-toggle"
                    aria-expanded="true"
                    aria-label="Toggle repayment schedule"
                >⌄</button>
            </div>

            <div class="emi-toggle" role="group" aria-label="Repayment schedule view">
                <button
                    type="button"
                    data-schedule-mode="month"
                    class="active"
                    aria-pressed="true"
                >Monthly</button>

                <button
                    type="button"
                    data-schedule-mode="year"
                    aria-pressed="false"
                >Yearly</button>
            </div>

            <div class="emi-table-wrap" id="schedule-wrap">
                <table>
                    <thead id="schedule-head">
                        <tr>
                            <th>EMI No.</th>
                            <th>Month</th>
                            <th>Principal</th>
                            <th>Interest</th>
                            <th>Balance</th>
                        </tr>
                    </thead>

                    <tbody id="schedule-body">
                        @foreach (array_slice($schedule, 0, 5) as $row)
                            <tr>
                                <td>{{ $row['emi_number'] ?? $loop->iteration }}</td>
                                <td>{{ $row['month'] ?? '—' }}</td>
                                <td>{{ $money($row['principal'] ?? 0) }}</td>
                                <td>{{ $money($row['interest'] ?? 0) }}</td>
                                <td>{{ $money($row['balance'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="button" class="emi-view-all" id="view-all-schedule">
                View All {{ count($schedule) }} Installments
            </button>
        </section>

        
        <div class="emi-actions">
            <button type="button" id="print-emi" class="emi-print-button">
                <span aria-hidden="true">▤</span>
                DOWNLOAD PDF / PRINT
            </button>

            @if (\Illuminate\Support\Facades\Route::has('user.loans.create'))
                <a class="emi-apply-button" href="{{ route('user.loans.create') }}">
                    <span aria-hidden="true">↗</span>
                    APPLY FOR LOAN
                </a>
            @elseif (\Illuminate\Support\Facades\Route::has('loans.create'))
                <a class="emi-apply-button" href="{{ route('loans.create') }}">
                    <span aria-hidden="true">↗</span>
                    APPLY FOR LOAN
                </a>
            @else
                <a class="emi-apply-button" href="{{ route('user.calculators.journeys.index') }}" title="Open available loan journeys">
                    <span aria-hidden="true">↗</span>
                    APPLY FOR LOAN
                </a>
            @endif
        </div>


        <p class="emi-note">
            Estimates only. Actual lender rates, fees and repayment schedules may differ.
        </p>
    @endif
</div>

<script>
(() => {
    'use strict';

    const form = document.getElementById('emi-form');
    if (!form) return;

    const $ = id => document.getElementById(id);
    const money = n => '₹' + Math.round(Number(n) || 0).toLocaleString('en-IN');

    const amountInput = $('loan_amount');
    const rateInput = $('interest_rate');
    const tenureSlider = $('tenure_slider');
    const tenureMonthsInput = $('tenure_months');
    const feeInput = $('processing_fee');
    const loanTypeInput = $('loan_type');

    let tenureMode = 'years';
    let scheduleMode = 'month';
    let showAll = false;

    const limits = () => loanTypeInput.value === 'personal'
        ? { amount: 5000000, rate: 35, months: 84 }
        : { amount: 50000000, rate: 20, months: 360 };

    function values() {
        return {
            amount: Number(amountInput.value),
            rate: Number(rateInput.value),
            months: Number(tenureMonthsInput.value),
            fee: Math.max(0, Number(feeInput.value) || 0)
        };
    }

    function updateInputLabels(syncTenure = false) {
        const v = values();

        $('loan-amount-label').textContent = money(v.amount);
        $('rate-label').textContent = v.rate.toFixed(2);
        $('fee-label').textContent = money(v.fee);
        $('tenure-label').textContent = tenureSlider.value;

        if (syncTenure) {
            tenureMonthsInput.value = tenureMode === 'years'
                ? Number(tenureSlider.value) * 12
                : Number(tenureSlider.value);
        }
    }

    function calculate() {
        const v = values();
        const monthlyRate = v.rate / 1200;

        const factor = monthlyRate === 0
            ? 1
            : Math.pow(1 + monthlyRate, v.months);

        const emi = monthlyRate === 0
            ? v.amount / v.months
            : v.amount * monthlyRate * factor / (factor - 1);

        let balance = v.amount;
        let totalInterest = 0;
        let totalPrincipal = 0;
        const schedule = [];
        const baseDate = new Date();

        baseDate.setDate(1);

        for (let i = 1; i <= v.months; i++) {
            const interest = Math.round(balance * monthlyRate * 100) / 100;
            const principal = i === v.months
                ? balance
                : Math.min(balance, Math.round((emi - interest) * 100) / 100);

            balance = Math.max(0, Math.round((balance - principal) * 100) / 100);

            totalInterest += interest;
            totalPrincipal += principal;

            const date = new Date(
                baseDate.getFullYear(),
                baseDate.getMonth() + i,
                1
            );

            schedule.push({
                no: i,
                month: date.toLocaleDateString('en-IN', {
                    month: 'short',
                    year: 'numeric'
                }),
                principal,
                interest,
                balance
            });
        }

        const total = totalPrincipal + totalInterest;

        return {
            ...v,
            emi,
            totalInterest,
            totalPrincipal,
            total,
            schedule,
            principalPct: total ? totalPrincipal / total * 100 : 0,
            interestPct: total ? totalInterest / total * 100 : 0
        };
    }

    function renderSchedule(v) {
        const body = $('schedule-body');
        const head = $('schedule-head');

        if (!body || !head) return;

        let rows;

        if (scheduleMode === 'year') {
            const yearly = new Map();

            v.schedule.forEach(row => {
                const year = Math.ceil(row.no / 12);

                if (!yearly.has(year)) {
                    yearly.set(year, {
                        year,
                        principal: 0,
                        interest: 0,
                        payment: 0,
                        balance: row.balance
                    });
                }

                const item = yearly.get(year);
                item.principal += row.principal;
                item.interest += row.interest;
                item.payment += row.principal + row.interest;
                item.balance = row.balance;
            });

            rows = Array.from(yearly.values());

            head.innerHTML = `
                <tr>
                    <th>Year</th>
                    <th>Principal</th>
                    <th>Interest</th>
                    <th>Total Payment</th>
                    <th>Year-End Balance</th>
                </tr>
            `;

            if (!showAll) rows = rows.slice(0, 5);

            $('view-all-schedule').textContent = showAll
                ? 'Show First 5 Years'
                : `View All ${yearly.size} Years`;

            body.replaceChildren();

            rows.forEach(row => {
                const tr = document.createElement('tr');

                [
                    `Year ${row.year}`,
                    money(row.principal),
                    money(row.interest),
                    money(row.payment),
                    money(row.balance)
                ].forEach(value => {
                    const td = document.createElement('td');
                    td.textContent = value;
                    tr.appendChild(td);
                });

                body.appendChild(tr);
            });

            return;
        }

        head.innerHTML = `
            <tr>
                <th>EMI No.</th>
                <th>Month</th>
                <th>Principal</th>
                <th>Interest</th>
                <th>Balance</th>
            </tr>
        `;

        rows = showAll ? v.schedule : v.schedule.slice(0, 5);

        $('view-all-schedule').textContent = showAll
            ? 'Show First 5 Installments'
            : `View All ${v.schedule.length} Installments`;

        body.replaceChildren();

        rows.forEach(row => {
            const tr = document.createElement('tr');

            [
                row.no,
                row.month,
                money(row.principal),
                money(row.interest),
                money(row.balance)
            ].forEach(value => {
                const td = document.createElement('td');
                td.textContent = value;
                tr.appendChild(td);
            });

            body.appendChild(tr);
        });
    }

    function renderResults() {
        if (!$('emi-results')) return;

        const v = calculate();

        $('emi-value').textContent = money(v.emi);
        $('principal-percent').textContent = v.principalPct.toFixed(1) + '%';
        $('interest-percent').textContent = v.interestPct.toFixed(1) + '%';
        $('principal-bar').style.width = v.principalPct + '%';

        $('summary-principal').textContent = money(v.amount);
        $('summary-rate').textContent = v.rate.toFixed(2) + '%';
        $('summary-tenure').textContent = v.months % 12 === 0
            ? `${v.months / 12} years`
            : `${v.months} months`;

        $('summary-interest').textContent = money(v.totalInterest);
        $('summary-total').textContent = money(v.total);
        $('summary-fee').textContent = money(v.fee);

        renderSchedule(v);
    }

    function setTenureMode(mode) {
        tenureMode = mode;

        document.querySelectorAll('[data-tenure]').forEach(button => {
            const active = button.dataset.tenure === mode;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', String(active));
        });

        const maxMonths = limits().months;
        const months = Number(tenureMonthsInput.value);

        if (mode === 'years') {
            tenureSlider.min = 1;
            tenureSlider.max = Math.floor(maxMonths / 12);
            tenureSlider.value = Math.min(
                Number(tenureSlider.max),
                Math.max(1, Math.round(months / 12))
            );

            $('tenure-min').textContent = '1 Yr.';
            $('tenure-max').textContent = `${tenureSlider.max} Yrs.`;
        } else {
            tenureSlider.min = 1;
            tenureSlider.max = maxMonths;
            tenureSlider.value = Math.min(maxMonths, Math.max(1, months));

            $('tenure-min').textContent = '1 Month';
            $('tenure-max').textContent = `${maxMonths} Months`;
        }

        updateInputLabels();
        renderResults();
    }

    function setLoanType(type) {
        loanTypeInput.value = type;

        document.querySelectorAll('[data-loan-type]').forEach(button => {
            const active = button.dataset.loanType === type;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', String(active));
        });

        const l = limits();

        amountInput.max = l.amount;
        rateInput.max = l.rate;

        $('loan-amount-max-label').textContent =
            type === 'personal' ? '₹50L' : '₹5Cr';

        $('rate-max-label').textContent = `${l.rate}%`;

        if (Number(amountInput.value) > l.amount) {
            amountInput.value = l.amount;
        }

        if (Number(rateInput.value) > l.rate) {
            rateInput.value = l.rate;
        }

        if (Number(tenureMonthsInput.value) > l.months) {
            tenureMonthsInput.value = l.months;
        }

        setTenureMode(tenureMode);
    }

    amountInput.addEventListener('input', () => {
        updateInputLabels();
        renderResults();
    });

    rateInput.addEventListener('input', () => {
        updateInputLabels();
        renderResults();
    });

    feeInput.addEventListener('input', () => {
        updateInputLabels();
        renderResults();
    });

    tenureSlider.addEventListener('input', () => {
        updateInputLabels(true);
        renderResults();
    });

    document.querySelectorAll('[data-tenure]').forEach(button => {
        button.addEventListener('click', () => setTenureMode(button.dataset.tenure));
    });

    document.querySelectorAll('[data-loan-type]').forEach(button => {
        button.addEventListener('click', () => setLoanType(button.dataset.loanType));
    });

    if ($('emi-results')) {
        document.querySelectorAll('[data-schedule-mode]').forEach(button => {
            button.addEventListener('click', () => {
                scheduleMode = button.dataset.scheduleMode;
                showAll = false;

                document.querySelectorAll('[data-schedule-mode]').forEach(item => {
                    const active = item === button;
                    item.classList.toggle('active', active);
                    item.setAttribute('aria-pressed', String(active));
                });

                renderResults();
            });
        });

        $('view-all-schedule').addEventListener('click', () => {
            showAll = !showAll;
            renderResults();
        });

        $('schedule-toggle').addEventListener('click', () => {
            const wrap = $('schedule-wrap');
            wrap.hidden = !wrap.hidden;

            $('schedule-toggle').setAttribute(
                'aria-expanded',
                String(!wrap.hidden)
            );
        });

        $('print-emi').addEventListener('click', () => {
            // Print the complete monthly repayment schedule, not only the first five rows.
            scheduleMode = 'month';
            showAll = true;
            document.querySelectorAll('[data-schedule-mode]').forEach(item => {
                const active = item.dataset.scheduleMode === 'month';
                item.classList.toggle('active', active);
                item.setAttribute('aria-pressed', String(active));
            });
            renderResults();
            window.requestAnimationFrame(() => window.print());
        });
    }

    updateInputLabels();
})();
</script>
@endsection
