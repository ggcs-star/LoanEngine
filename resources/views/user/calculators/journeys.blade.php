
@extends('user.layouts.app')

@section('title', 'Loan Calculators | LoanEngine')
@section('page_heading', 'Loan Calculators')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}?v={{ filemtime(public_path('css/user-tools.css')) }}">
@endpush

@section('content')
<div class="le-tools le-calculator-home">

    <section class="le-calc-hero">
        <div class="le-calc-hero-content">
            <span class="le-calc-kicker">YOUR NEXT FINANCIAL MOVE</span>

            <h1>Plan your home loan<br>with confidence</h1>

            <p>Choose your goal and discover the right calculators for your next step.</p>

            <a class="le-calc-hero-button" href="#loan-journeys">
                Explore calculators
                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="le-calc-photo-note">
            <span>Smarter</span>
            <span>Calculations</span>
            <span>Brighter</span>
            <span>Home Decisions</span>
            <i></i>
        </div>
    </section>

    <section class="le-calc-journeys-section" id="loan-journeys">
        <div class="le-calc-section-heading">
            <div>
                <h2>What are you planning?</h2>
                <p>Select a journey to get tailored calculator recommendations.</p>
            </div>

            <span class="le-calc-path-count">
                <span aria-hidden="true">▦</span>
                {{ $journeys->count() }} planning paths
            </span>
        </div>

        @forelse($journeys as $journey)
            @if($loop->first)
                <div class="le-calc-journey-grid">
            @endif

            <a
                class="le-calc-journey-card"
                href="{{ route('user.calculators.journeys.show', ['journey' => $journey->slug]) }}"
            >
                <div class="le-calc-journey-card-top">
                    <span class="le-calc-journey-icon" aria-hidden="true">
                        @if(str_contains(strtolower($journey->name), 'first-time') || str_contains(strtolower($journey->name), 'first time'))
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @elseif(str_contains(strtolower($journey->name), 'existing'))
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M20 7v5h-5M4 17v-5h5M5.6 9A7 7 0 0 1 18 6l2 2M4 16l2 2a7 7 0 0 0 12.4-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @elseif(str_contains(strtolower($journey->name), 'top-up') || str_contains(strtolower($journey->name), 'top up'))
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M12 7v10M7 12h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        @elseif(str_contains(strtolower($journey->name), 'transfer'))
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M4 7h15l-4-4M20 17H5l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @elseif(str_contains(strtolower($journey->name), 'prepayment'))
                            <svg viewBox="0 0 24 24" fill="none">
                                <ellipse cx="9" cy="6" rx="5" ry="2.5" stroke="currentColor" stroke-width="1.7"/>
                                <path d="M4 6v10c0 1.4 2.2 2.5 5 2.5M14 6v4M4 11c0 1.4 2.2 2.5 5 2.5M14 10v8c0 1.4 2.2 2.5 5 2.5s5-1.1 5-2.5v-8M14 14c0 1.4 2.2 2.5 5 2.5s5-1.1 5-2.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        @elseif(str_contains(strtolower($journey->name), 'closure'))
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                                <path d="m8 12 2.5 2.5L16.5 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="5" y="3" width="14" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M8 7h8M8 11h2m4 0h2M8 15h2m4 0h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        @endif
                    </span>

                    <span class="le-calc-journey-tag">LOAN JOURNEY</span>
                </div>

                <div class="le-calc-journey-body">
                    <h3>{{ $journey->name }}</h3>

                    <p>{{ $journey->description ?: ($journey->short_label ?: 'Explore calculators for your financial planning.') }}</p>
                </div>

                <div class="le-calc-journey-footer">
                    <span>Explore calculators</span>
                    <span class="le-calc-footer-arrow" aria-hidden="true">→</span>
                </div>
            </a>

            @if($loop->last)
                </div>
            @endif
        @empty
            <div class="le-empty">
                <h3>No active journeys available</h3>
                <p>Active loan journeys added by your administrator will appear here.</p>
            </div>
        @endforelse
    </section>

</div>
@endsection
