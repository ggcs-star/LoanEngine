
@extends('user.layouts.app')

@section('title', $journey->name . ' | LoanEngine')
@section('page_heading', $journey->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools le-journey-detail le-journey-detail-page">

    <a class="le-back" href="{{ route('user.tools.journeys.index') }}">
        <span aria-hidden="true">←</span>
        Back to journeys
    </a>

    <section class="le-detail-hero le-journey-hero">

        <div class="le-journey-hero-copy">
            <span class="le-eyebrow">YOUR JOURNEY · STEP-BY-STEP GUIDE</span>

            <h1>{{ $journey->name }}</h1>

            <p>
                {{ $journey->description ?: 'Follow the steps below at your own pace.' }}
            </p>

            <div class="le-journey-hero-meta">
                <span>
                    <b>{{ $steps->count() }}</b>
                    {{ $steps->count() === 1 ? 'step' : 'steps' }} to explore
                </span>

                <span>
                    <b>01</b>
                    Start with the first step
                </span>
            </div>
        </div>

        <div class="le-journey-hero-art" aria-hidden="true">
            <div class="le-journey-art-orbit"></div>

            <div class="le-journey-art-card">
                <span class="le-journey-art-icon">↗</span>

                <strong>One step at a time</strong>

                <small>Clear guidance for your next move</small>

                <div class="le-journey-art-progress">
                    <i></i>
                    <i></i>
                    <i></i>
                    <i></i>
                    <i></i>
                </div>
            </div>

            <span class="le-journey-art-pill">YOUR PLAN</span>
        </div>

    </section>

    <section class="le-journey-steps-section">

        <div class="le-section-head">
            <div>
                <span class="le-eyebrow">YOUR NEXT STEPS</span>

                <h2>Follow your journey</h2>

                <p>Open a step to read its guidance and relevant tools.</p>
            </div>

            <span class="le-count">
                {{ $steps->count() }}
                {{ $steps->count() === 1 ? 'step' : 'steps' }}
            </span>
        </div>

        @if ($steps->isNotEmpty())

            <div class="le-grid le-journey-grid">

                @foreach ($steps as $step)

                    <a
                        class="le-card le-journey-step-card"
                        href="{{ route('user.tools.journeys.steps.show', [
                            'journey' => $journey->slug,
                            'step' => $step->id
                        ]) }}"
                    >

                        <div class="le-card-top">

                            <span class="le-icon">
                                {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="le-chip">
                                Step {{ $loop->iteration }} of {{ $steps->count() }}
                            </span>

                            <span class="le-arrow" aria-hidden="true">↗</span>

                        </div>

                        <div class="le-card-body">

                            <span class="le-journey-step-kicker">
                                STEP {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <h3>{{ $step->title }}</h3>

                            <p>
                                {{ $step->description ?: 'Open this step for details and guidance.' }}
                            </p>

                        </div>

                        <div class="le-card-foot">

                            <span>Step {{ $loop->iteration }}</span>

                            <span class="le-journey-step-action">
                                View step
                                <span aria-hidden="true">→</span>
                            </span>

                        </div>

                    </a>

                @endforeach

            </div>

        @else

            <div class="le-empty">
                <span aria-hidden="true">▦</span>

                <h3>No active steps yet</h3>

                <p>
                    This journey does not have any active steps.
                    Ask an administrator to add steps and set their status to Active.
                </p>
            </div>

        @endif

    </section>

</div>
@endsection
