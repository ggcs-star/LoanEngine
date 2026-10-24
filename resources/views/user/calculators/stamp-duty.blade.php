@extends('user.layouts.app')
@section('content')
@php
    $states = [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar',
        'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana',
        'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala',
        'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya',
        'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan',
        'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
        'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
        'Andaman and Nicobar Islands', 'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu', 'Delhi',
        'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry',
    ];
    $inputs = $inputs ?? [
        'property_value' => 5000000,
        'state' => 'Gujarat',
        'property_type' => 'residential_flat',
        'buyer_category' => 'male',
        'stamp_duty_method' => 'percentage',
        'stamp_duty_rate' => 5,
        'registration_type' => 'fixed',
        'registration_value' => 30000,
        'other_charges' => 15000,
    ];
    $result = $result ?? null;
    $money = fn ($value) => '₹' . number_format((float) $value, 2);
@endphp
<link rel="stylesheet" href="{{ asset('css/calculators/stamp-duty.css') }}">
<div class="sd-page">
    <header class="sd-hero">
        <div class="sd-hero-content">
            <span class="sd-eyebrow">PROPERTY COST PLANNER</span>
            <h1>Stamp Duty Calculator</h1>
            <p>Estimate stamp duty, registration fees and your total property purchase costs.</p>
            <div class="sd-hero-meta"><span class="sd-hero-dot"></span> Simple estimate · Clear cost breakdown</div>
        </div>
        <div class="sd-hero-art" aria-hidden="true">
            <div class="sd-hero-orbit sd-hero-orbit-one"></div>
            <div class="sd-hero-orbit sd-hero-orbit-two"></div>
            <div class="sd-hero-building"><span></span><span></span><span></span><i></i></div>
            <div class="sd-hero-rupee">₹</div>
        </div>
        <button type="button" class="sd-btn sd-btn-hero" onclick="window.print()"><span aria-hidden="true">⎙</span> Print Results</button>
    </header>
    <div class="sd-notice">
        <strong>Important:</strong> This calculator provides an indicative estimate.
        The default rates are illustrative, not verified state-government rates.
        Check the current official rules before relying on the result.
    </div>
    <div class="sd-layout">
        <section class="sd-card">
            <div class="sd-card-heading">
                <span class="sd-icon">₹</span>
                <div>
                    <h2>Property Details</h2>
                    <p>Enter the details to estimate your costs.</p>
                </div>
            </div>
            <form method="POST"
                  action="{{ route('calculators.calculate', ['tool' => $tool->slug]) }}"
                  id="stampDutyForm">
                @csrf
                <div class="sd-field">
                    <label for="property_value">Property Value (₹)</label>
                    <input id="property_value" name="property_value" type="number"
                           min="100000" max="1000000000" step="1000"
                           value="{{ old('property_value', $inputs['property_value']) }}"
                           placeholder="e.g. 5000000" required>
                    @error('property_value')
                        <small class="sd-error">{{ $message }}</small>
                    @enderror
                </div>
                <div class="sd-field">
                    <label for="state">State / Union Territory</label>
                    <select name="state" id="state" required>
                        @foreach($states as $state)
                            <option value="{{ $state }}"
                                @selected(old('state', $inputs['state']) === $state)>
                                {{ $state }}
                            </option>
                        @endforeach
                    </select>
                    @error('state')
                        <small class="sd-error">{{ $message }}</small>
                    @enderror
                </div>
                <div class="sd-field">
                    <label for="property_type">Property Type</label>
                    <select name="property_type" id="property_type" required>
                        <option value="residential_flat"
                            @selected(old('property_type', $inputs['property_type']) === 'residential_flat')>
                            Residential Flat / House
                        </option>
                        <option value="own_land"
                            @selected(old('property_type', $inputs['property_type']) === 'own_land')>
                            Residential Plot / Land
                        </option>
                        <option value="pent_house"
                            @selected(old('property_type', $inputs['property_type']) === 'pent_house')>
                            Penthouse
                        </option>
                    </select>
                </div>
                <div class="sd-field">
                    <label for="buyer_category">Buyer Category</label>
                    <select name="buyer_category" id="buyer_category" required>
                        <option value="male" @selected(old('buyer_category', $inputs['buyer_category']) === 'male')>
                            Male
                        </option>
                        <option value="female" @selected(old('buyer_category', $inputs['buyer_category']) === 'female')>
                            Female
                        </option>
                        <option value="joint" @selected(old('buyer_category', $inputs['buyer_category']) === 'joint')>
                            Joint Ownership
                        </option>
                        <option value="senior" @selected(old('buyer_category', $inputs['buyer_category']) === 'senior')>
                            Senior Citizen
                        </option>
                        <option value="first_time" @selected(old('buyer_category', $inputs['buyer_category']) === 'first_time')>
                            First-Time Buyer
                        </option>
                    </select>
                    <small class="sd-help">Buyer category does not automatically change the rate.</small>
                </div>
                <div class="sd-divider"></div>
                <h3 class="sd-subheading">Stamp Duty Settings</h3>
                <div class="sd-field">
                    <label for="stamp_duty_method">Calculation Method</label>
                    <select name="stamp_duty_method" id="stamp_duty_method">
                        <option value="percentage"
                            @selected(old('stamp_duty_method', $inputs['stamp_duty_method']) === 'percentage')>
                            Percentage of property value
                        </option>
                        <option value="slab"
                            @selected(old('stamp_duty_method', $inputs['stamp_duty_method']) === 'slab')>
                            Illustrative slab calculation
                        </option>
                    </select>
                </div>
                <div class="sd-field" id="rateField">
                    <label for="stamp_duty_rate">Stamp Duty Rate (%)</label>
                    <input type="number" name="stamp_duty_rate" id="stamp_duty_rate"
                           min="0" max="20" step="0.1"
                           value="{{ old('stamp_duty_rate', $inputs['stamp_duty_rate']) }}">
                    <small class="sd-help">Enter the applicable rate confirmed for your transaction.</small>
                </div>
                <div class="sd-divider"></div>
                <h3 class="sd-subheading">Additional Charges</h3>
                <div class="sd-field">
                    <label for="registration_type">Registration Fee Method</label>
                    <select name="registration_type" id="registration_type">
                        <option value="fixed"
                            @selected(old('registration_type', $inputs['registration_type']) === 'fixed')>
                            Fixed Amount (₹)
                        </option>
                        <option value="percentage"
                            @selected(old('registration_type', $inputs['registration_type']) === 'percentage')>
                            Percentage of Property Value
                        </option>
                    </select>
                </div>
                <div class="sd-field">
                    <label for="registration_value">Registration Fee Value</label>
                    <input type="number" name="registration_value" id="registration_value"
                           min="0" max="100000000" step="0.01"
                           value="{{ old('registration_value', $inputs['registration_value']) }}"
                           required>
                    <small class="sd-help" id="registrationHelp">Amount in rupees.</small>
                </div>
                <div class="sd-field">
                    <label for="other_charges">Other Charges (₹)</label>
                    <input type="number" name="other_charges" id="other_charges"
                           min="0" max="100000000" step="0.01"
                           value="{{ old('other_charges', $inputs['other_charges']) }}"
                           required>
                    <small class="sd-help">Enter any additional charges you want to include.</small>
                </div>
                <button type="submit" class="sd-btn sd-btn-primary sd-submit">
                    Calculate Stamp Duty <span aria-hidden="true">→</span>
                </button>
            </form>
        </section>
        <aside class="sd-results">
            @if($result)
                <section class="sd-card sd-result-card">
                    <div class="sd-result-top">
                        <span class="sd-eyebrow">ESTIMATED TOTAL COST</span>
                        <h2>{{ $money($result['total_charges']) }}</h2>
                        <p>Stamp duty and additional charges</p>
                    </div>
                    <div class="sd-total-property">
                        <span>Property Value</span>
                        <strong>{{ $money($result['property_value']) }}</strong>
                    </div>
                    <div class="sd-cost-list">
                        <div class="sd-cost-row">
                            <span>Stamp Duty</span>
                            <strong>{{ $money($result['stamp_duty']) }}</strong>
                        </div>
                        <div class="sd-cost-row">
                            <span>Registration Fee</span>
                            <strong>{{ $money($result['registration_fee']) }}</strong>
                        </div>
                        <div class="sd-cost-row">
                            <span>Other Charges</span>
                            <strong>{{ $money($result['other_charges']) }}</strong>
                        </div>
                    </div>
                    <div class="sd-upfront">
                        <span>Total Upfront Cost</span>
                        <strong>{{ $money($result['upfront_cost']) }}</strong>
                        <small>Property value plus estimated charges</small>
                    </div>
                    <div class="sd-progress-section">
                        <div class="sd-progress-heading">
                            <span>Charges as a share of total upfront cost</span>
                            <strong>{{ number_format($result['charges_percentage'], 2) }}%</strong>
                        </div>
                        <div class="sd-progress">
                            <span style="width: {{ min(100, max(0, $result['charges_percentage'])) }}%"></span>
                        </div>
                    </div>
                    @if(!empty($result['insights']))
                        <div class="sd-insights">
                            <h3>Cost Breakdown Insights</h3>
                            @foreach($result['insights'] as $insight)
                                <div class="sd-insight">
                                    <span class="sd-insight-mark">i</span>
                                    <div>
                                        <strong>{{ $insight['title'] }}</strong>
                                        <p>{{ $insight['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <p class="sd-disclaimer">
                        {{ $result['notice'] ?? 'Indicative estimate only. Confirm applicable rates and fees with the relevant registration authority.' }}
                    </p>
                </section>
            @else
                <section class="sd-card sd-empty-result">
                    <div class="sd-empty-icon">₹</div>
                    <h2>Your Estimate</h2>
                    <p>Enter your property details and calculate to see the estimated stamp duty and registration costs here.</p>
                    <div class="sd-preview-row">
                        <span>Stamp duty</span>
                        <span>—</span>
                    </div>
                    <div class="sd-preview-row">
                        <span>Registration fee</span>
                        <span>—</span>
                    </div>
                    <div class="sd-preview-row">
                        <span>Other charges</span>
                        <span>—</span>
                    </div>
                </section>
            @endif
            <section class="sd-card sd-tip-card">
                <h3>Before You Register</h3>
                <p>Confirm the current state rates, applicable concessions, valuation rules and registration fee caps with the official registration department.</p>
            </section>
        </aside>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const method = document.getElementById('stamp_duty_method');
    const rateField = document.getElementById('rateField');
    const rateInput = document.getElementById('stamp_duty_rate');
    const registrationType = document.getElementById('registration_type');
    const registrationHelp = document.getElementById('registrationHelp');
    function updateStampDutyMethod() {
        rateField.hidden = false;
        rateInput.disabled = false;
        rateInput.required = method.value === 'percentage';
    }
    function updateRegistrationHelp() {
        registrationHelp.textContent =
            registrationType.value === 'percentage'
                ? 'Enter the registration fee percentage.'
                : 'Enter the registration fee in rupees.';
    }
    method.addEventListener('change', updateStampDutyMethod);
    registrationType.addEventListener('change', updateRegistrationHelp);
    updateStampDutyMethod();
    updateRegistrationHelp();
});
</script>
@endsection
