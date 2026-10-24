
@extends('user.layouts.app')

@section('title', $step->title . ' | LoanEngine')
@section('page_heading', $journey->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools le-detail le-journey-step-detail">

    <a class="le-back"
       href="{{ route('user.tools.journeys.show', $journey->slug) }}">
        <span aria-hidden="true">←</span>
        Back to {{ $journey->name }}
    </a>

    {{-- Step Hero --}}
    <section class="le-detail-hero le-step-hero">
        <div class="le-step-hero-content">
            <span class="le-eyebrow">
                {{ $journey->name }} · STEP {{ str_pad($step->step_number, 2, '0', STR_PAD_LEFT) }}
            </span>

            <h1>
                <span class="le-step-number">
                    {{ str_pad($step->step_number, 2, '0', STR_PAD_LEFT) }}
                </span>
                {{ $step->title }}
            </h1>

            <p>
                {{ $step->description ?: 'Follow this step to move forward in your journey.' }}
            </p>

            <div class="le-step-hero-meta">
                <span class="le-step-status-dot"></span>
                <span>Part of your step-by-step plan</span>
            </div>
        </div>

        <div class="le-step-hero-art" aria-hidden="true">
            <div class="le-step-art-ring le-step-art-ring-one"></div>
            <div class="le-step-art-ring le-step-art-ring-two"></div>

            <div class="le-step-art-icon">✓</div>

            <span class="le-step-art-label">YOUR NEXT MOVE</span>
        </div>
    </section>

    {{-- Guidance Sections --}}
    <main class="le-step-main">

        <div class="le-step-guidance">

            @if (filled($step->what_you_need))
                <section class="le-panel le-step-info-card">
                    <div class="le-step-info-heading">
                        <span class="le-step-info-icon" aria-hidden="true">▤</span>

                        <div>
                            <span class="le-eyebrow">BE PREPARED</span>
                            <h2>What You Need</h2>
                        </div>
                    </div>

                    <p>{{ $step->what_you_need }}</p>
                </section>
            @endif

            @if (filled($step->watch_out_for))
                <section class="le-panel le-step-info-card le-step-caution-card">
                    <div class="le-step-info-heading">
                        <span class="le-step-info-icon" aria-hidden="true">!</span>

                        <div>
                            <span class="le-eyebrow">IMPORTANT TO KNOW</span>
                            <h2>Watch Out For</h2>
                        </div>
                    </div>

                    <p>{{ $step->watch_out_for }}</p>
                </section>
            @endif

        </div>

        {{-- Related Tools --}}
        @if ($step->tools->isNotEmpty())
            <section class="le-related le-step-related">

                <div class="le-section-head">
                    <div>
                        <span class="le-eyebrow">RECOMMENDED FOR THIS STEP</span>
                        <h2>Relevant Tools</h2>
                        <p>Use these tools to help with your next move.</p>
                    </div>

                    <span class="le-count">
                        {{ $step->tools->count() }}
                        {{ $step->tools->count() === 1 ? 'tool' : 'tools' }}
                    </span>
                </div>

                <div class="le-step-tools-grid">
                    @foreach ($step->tools as $tool)
                        <a
                            class="le-related-card le-step-tool-card"
                            href="{{ $tool->category === 'Calculators'
                                ? route('calculators.show', ['tool' => $tool->slug])
                                : route('user.tools.show', ['tool' => $tool->slug]) }}"
                        >
                            <div class="le-step-tool-top">
                                <span class="le-step-tool-icon" aria-hidden="true">
                                    {{ $tool->category === 'Calculators' ? '∑' : '▤' }}
                                </span>

                                <span class="le-step-tool-arrow" aria-hidden="true">↗</span>
                            </div>

                            <strong>{{ $tool->name }}</strong>

                            <small>
                                {{ $tool->short_description }}
                            </small>

                            <span class="le-step-tool-cta">
                                {{ $tool->category === 'Calculators' ? 'Open calculator' : 'Open tool' }}
                                <span aria-hidden="true">→</span>
                            </span>
                        </a>
                    @endforeach
                </div>

            </section>
        @endif

    </main>

    {{-- Previous / Next Navigation --}}
    <nav class="le-step-navigation le-step-detail-navigation"
         aria-label="Journey step navigation">

        @if ($previousStep)
            <a
                class="le-btn le-btn-secondary"
                href="{{ route('user.tools.journeys.steps.show', [
                    'journey' => $journey->slug,
                    'step' => $previousStep->id,
                ]) }}"
            >
                <span aria-hidden="true">←</span>
                <span>
                    <small>PREVIOUS STEP</small>
                    Previous
                </span>
            </a>
        @else
            <a
                class="le-btn le-btn-secondary"
                href="{{ route('user.tools.journeys.show', $journey->slug) }}"
            >
                <span aria-hidden="true">←</span>
                <span>
                    <small>YOUR JOURNEY</small>
                    All steps
                </span>
            </a>
        @endif

        <div class="le-step-navigation-center">
            <span>KEEP MOVING FORWARD</span>
        </div>

        @if ($nextStep)
            <a
                class="le-btn le-btn-primary"
                href="{{ route('user.tools.journeys.steps.show', [
                    'journey' => $journey->slug,
                    'step' => $nextStep->id,
                ]) }}"
            >
                <span>
                    <small>NEXT STEP</small>
                    Continue
                </span>
                <span aria-hidden="true">→</span>
            </a>
        @else
            <a
                class="le-btn le-btn-primary"
                href="{{ route('user.tools.journeys.show', $journey->slug) }}"
            >
                <span>
                    <small>JOURNEY</small>
                    Back to journey
                </span>
                <span aria-hidden="true">→</span>
            </a>
        @endif

    </nav>

</div>
@endsection
