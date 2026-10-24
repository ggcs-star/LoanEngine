
@extends('user.layouts.app')

@section('title', 'Recommended Calculators | LoanEngine')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}?v=3">
@endpush

@section('content')
<div class="le-tools le-calculator-recommendations">

    <a class="le-rec-back" href="{{ route('user.calculators.journeys.index') }}">
        <span aria-hidden="true">←</span>
        Back to options
    </a>

    <section class="le-rec-hero">
        <div class="le-rec-hero-content">
            <span class="le-rec-eyebrow">YOUR NEXT FINANCIAL MOVE</span>

            <h1>Plan your home loan with confidence.</h1>

            <p>
                Discover the right calculators to understand your loan,
                explore your options and make informed financial decisions.
            </p>

            <a class="le-rec-primary-btn" href="#le-rec-calculator-list">
                Explore calculators
                <span aria-hidden="true">→</span>
            </a>

            <div class="le-rec-trust-points">
                <span><i></i> Smarter calculations</span>
                <span><i></i> Better planning</span>
            </div>
        </div>

        <div class="le-rec-hero-note">
            <div class="le-rec-home-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6"
                          stroke="currentColor"
                          stroke-width="1.7"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>
            </div>

            <span class="le-rec-note-label">A SMARTER WAY FORWARD</span>
            <h2>Know your numbers.</h2>
            <p>Make your next financial move with clarity.</p>
            <div class="le-rec-note-line"></div>
            <span class="le-rec-note-caption">Your goals. Your numbers. Your next step.</span>
        </div>

        <div class="le-rec-hero-footer">
            <span>LOANENGINE</span>
            <span>PLAN SMARTER · MOVE FORWARD</span>
        </div>
    </section>

    <section class="le-rec-section" id="le-rec-calculator-list">
        <div class="le-rec-section-head">
            <div>
                <span class="le-rec-section-eyebrow">BUILT AROUND YOUR GOALS</span>
                <h2>Choose your calculator</h2>
                <p>Explore tools designed to help you plan your next step.</p>
            </div>

            <div class="le-rec-count">
                <span class="le-rec-count-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <rect x="4" y="3" width="6" height="6" rx="1"
                              stroke="currentColor" stroke-width="1.6"/>
                        <rect x="14" y="3" width="6" height="6" rx="1"
                              stroke="currentColor" stroke-width="1.6"/>
                        <rect x="4" y="13" width="6" height="6" rx="1"
                              stroke="currentColor" stroke-width="1.6"/>
                        <rect x="14" y="13" width="6" height="6" rx="1"
                              stroke="currentColor" stroke-width="1.6"/>
                    </svg>
                </span>

                <span>
                    <strong>{{ $calculators->count() }}</strong>
                    <small>{{ $calculators->count() === 1 ? 'calculator available' : 'calculators available' }}</small>
                </span>
            </div>
        </div>

        <div class="le-rec-grid">
            @forelse($calculators as $calculator)
                <article class="le-rec-card">

                    <div class="le-rec-card-top">
                        <div class="le-rec-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="5" y="2.75" width="14" height="18.5" rx="2.5"
                                      stroke="currentColor" stroke-width="1.7"/>
                                <path d="M8.5 6.5h7M8.5 10.5h2M14 10.5h1.5M8.5 14h2M14 14h1.5M8.5 17.5h2M14 17.5h1.5"
                                      stroke="currentColor"
                                      stroke-width="1.7"
                                      stroke-linecap="round"/>
                            </svg>
                        </div>

                        <span class="le-rec-chip">CALCULATOR</span>

                        <span class="le-rec-card-corner" aria-hidden="true">↗</span>
                    </div>

                    <div class="le-rec-card-body">
                        <h3>{{ $calculator->name }}</h3>

                        <p>
                            {{ $calculator->short_description ?: 'Explore your options and plan your next financial step with confidence.' }}
                        </p>
                    </div>

                    <div class="le-rec-card-footer">
                        @if(
                            $calculator->status === 'active'
                            && filled($calculator->formula_type)
                            && \Illuminate\Support\Facades\Route::has('calculators.show')
                        )
                            <a href="{{ route('calculators.show', ['tool' => $calculator->slug]) }}">
                                <span>Open calculator</span>
                                <span class="le-rec-arrow" aria-hidden="true">→</span>
                            </a>
                        @else
                            <span class="le-rec-unavailable">
                                <span>{{ $calculator->status === 'active' ? 'Calculator configuration pending' : 'Currently unavailable' }}</span>
                            </span>
                        @endif
                    </div>

                </article>
            @empty
                <div class="le-rec-empty">
                    <div class="le-rec-empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect x="5" y="2.75" width="14" height="18.5" rx="2.5"
                                  stroke="currentColor" stroke-width="1.7"/>
                            <path d="M8.5 7h7M8.5 11h7M8.5 15h3"
                                  stroke="currentColor"
                                  stroke-width="1.7"
                                  stroke-linecap="round"/>
                        </svg>
                    </div>

                    <h3>No calculators assigned yet</h3>
                    <p>An administrator can assign calculators to this journey.</p>

                    <a href="{{ route('user.calculators.journeys.index') }}">
                        Back to options →
                    </a>
                </div>
            @endforelse
        </div>
    </section>

    <section class="le-rec-bottom">
        <div class="le-rec-bottom-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="9"
                        stroke="currentColor" stroke-width="1.7"/>
                <path d="M12 7v10M7 12h10"
                      stroke="currentColor"
                      stroke-width="1.7"
                      stroke-linecap="round"/>
            </svg>
        </div>

        <div class="le-rec-bottom-copy">
            <h3>Every smart decision starts with a clear picture.</h3>
            <p>Choose a calculator and take the next step in your loan journey.</p>
        </div>

        <a href="#le-rec-calculator-list">
            Get started <span aria-hidden="true">→</span>
        </a>
    </section>

</div>
@endsection
