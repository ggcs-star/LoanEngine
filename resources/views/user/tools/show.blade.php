@extends('user.layouts.app')

@section('title', $tool->name . ' | LoanEngine')
@section('page_heading', $tool->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools le-detail le-tool-detail-page">

    <a class="le-back" href="{{ route('user.tools.index') }}">
        <span aria-hidden="true">←</span> Back to all tools
    </a>

    <section class="le-detail-hero">
        <div class="le-detail-hero-content">
            <span class="le-eyebrow">
                {{ strtoupper($tool->category ?: 'GENERAL') }} · TOOL
            </span>

            <h1>{{ $tool->name }}</h1>

            <p>{{ $tool->short_description }}</p>

            @if (filled($tool->description ?? null))
                <div class="le-description">
                    {!! nl2br(e($tool->description)) !!}
                </div>
            @endif

            <div class="le-detail-actions">
                <a class="le-btn le-btn-primary" href="#tool-journeys">
                    Explore journeys <span aria-hidden="true">→</span>
                </a>

                <a class="le-btn le-btn-secondary" href="{{ route('user.tools.index') }}">
                    All tools
                </a>
            </div>
        </div>

        <div class="le-detail-art-wrap" aria-hidden="true">
            <div class="le-detail-art">
                <span class="le-detail-art-symbol">▦</span>
                <span class="le-detail-art-line le-detail-art-line-one"></span>
                <span class="le-detail-art-line le-detail-art-line-two"></span>
                <span class="le-detail-art-line le-detail-art-line-three"></span>
            </div>

            <div class="le-detail-art-note">
                <span class="le-detail-art-dot"></span>
                <div>
                    <strong>Plan your next step</strong>
                    <small>Clear guidance, one step at a time</small>
                </div>
            </div>
        </div>
    </section>

    <section id="tool-journeys" class="le-detail-journeys">
        <div class="le-section-head">
            <div>
                <span class="le-eyebrow">NEXT STEP</span>
                <h2>Choose your journey</h2>
                <p>Select a journey to see its step-by-step path.</p>
            </div>

            <span class="le-count">
                {{ $tool->journeys->count() }}
                {{ $tool->journeys->count() === 1 ? 'journey' : 'journeys' }}
            </span>
        </div>

        @if ($tool->journeys->isNotEmpty())
            <div class="le-grid le-journey-grid">
                @foreach ($tool->journeys as $journey)
                    <a
                        class="le-card le-journey-card"
                        href="{{ route('user.tools.journeys.show', ['journey' => $journey->slug]) }}"
                    >
                        <div class="le-card-top">
                            <span class="le-icon">
                                {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="le-chip">
                                Journey {{ $loop->iteration }}
                            </span>

                            <span class="le-arrow" aria-hidden="true">↗</span>
                        </div>

                        <div class="le-card-body">
                            <small class="le-journey-kicker">YOUR STEP-BY-STEP PLAN</small>

                            <h3>{{ $journey->name }}</h3>

                            <p>
                                {{ $journey->description ?: ($journey->short_label ?: 'Follow this journey step by step.') }}
                            </p>

                            <span class="le-steps-count">
                                <span aria-hidden="true">☷</span>
                                {{ $journey->steps()->where('status', 'active')->count() }}
                                active steps
                            </span>
                        </div>

                        <div class="le-card-foot">
                            <span>Ready when you are</span>

                            <span class="le-journey-cta">
                                Explore journey <span aria-hidden="true">→</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="le-empty">
                <span aria-hidden="true">▦</span>
                <h3>No journeys linked yet</h3>
                <p>An administrator needs to link this tool to one or more journeys.</p>
            </div>
        @endif
    </section>

</div>
@endsection