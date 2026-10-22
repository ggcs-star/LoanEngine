
@extends('user.layouts.app')

@section('title', 'Loan Calculators | LoanEngine')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools">

    <section class="le-section-head">
        <div>
            <span class="le-eyebrow">YOUR LOAN JOURNEY</span>
            <h1>What Are You Trying to Do?</h1>
            <p>Choose your current home-loan goal to find relevant calculators.</p>
        </div>
    </section>

    <div class="le-grid">
        @forelse($journeys as $journey)
            <a
                class="le-card"
                href="{{ route('user.calculators.journeys.show', ['journey' => $journey->slug]) }}"
            >
                <div class="le-card-top">
                    <span class="le-icon">⌂</span>
                    <span class="le-chip">Loan Journey</span>
                </div>

                <div class="le-card-body">
                    <h3>{{ $journey->name }}</h3>

                    @if($journey->short_label)
                        <p>{{ $journey->short_label }}</p>
                    @else
                        <p>Explore calculators for this loan journey.</p>
                    @endif
                </div>

                <div class="le-card-foot">
                    <span>Explore calculators →</span>
                </div>
            </a>
        @empty
            <div class="le-empty">
                <h3>No journeys available</h3>
                <p>Please check that your journeys are active in admin.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
