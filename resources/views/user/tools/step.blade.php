@extends('user.layouts.app')

@section('title', $step->title . ' | LoanEngine')
@section('page_heading', $journey->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools le-detail">
    <a class="le-back"
       href="{{ route('user.tools.journeys.show', $journey->slug) }}">
        ← Back to {{ $journey->name }}
    </a>

    <section class="le-detail-hero">
        <div>
            <span class="le-eyebrow">{{ $journey->name }}</span>
            <h1>{{ str_pad($step->step_number, 2, '0', STR_PAD_LEFT) }}. {{ $step->title }}</h1>
            <p>{{ $step->description }}</p>
        </div>
    </section>

    <main>
        @if (filled($step->what_you_need))
            <section class="le-panel">
                <h2>What You Need</h2>
                <p>{{ $step->what_you_need }}</p>
            </section>
        @endif

        @if (filled($step->watch_out_for))
            <section class="le-panel">
                <h2>Watch Out For</h2>
                <p>{{ $step->watch_out_for }}</p>
            </section>
        @endif

        @if ($step->tools->isNotEmpty())
            <section class="le-related">
                <div class="le-section-head">
                    <div>
                        <span class="le-eyebrow">RECOMMENDED FOR THIS STEP</span>
                        <h2>Relevant Tools</h2>
                    </div>
                </div>

                @foreach ($step->tools as $tool)
                    <a class="le-related-card"
                       href="{{ $tool->category === 'Calculators'
                           ? route('calculators.show', ['tool' => $tool->slug])
                           : route('user.tools.show', ['tool' => $tool->slug]) }}">
                        <strong>{{ $tool->name }}</strong>
                        <small>{{ $tool->short_description }}</small>
                        <span>{{ $tool->category === 'Calculators' ? 'Open calculator →' : 'Open tool →' }}</span>
                    </a>
                @endforeach
            </section>
        @endif
    </main>

    <div class="le-card-foot">
        @if ($previousStep)
            <a class="le-btn le-btn-card"
               href="{{ route('user.tools.journeys.steps.show', [
                   'journey' => $journey->slug,
                   'step' => $previousStep->id,
               ]) }}">← Previous</a>
        @else
            <span></span>
        @endif

        @if ($nextStep)
            <a class="le-btn le-btn-primary"
               href="{{ route('user.tools.journeys.steps.show', [
                   'journey' => $journey->slug,
                   'step' => $nextStep->id,
               ]) }}">Next →</a>
        @else
            <a class="le-btn le-btn-primary"
               href="{{ route('user.tools.journeys.show', $journey->slug) }}">
                Back to journey
            </a>
        @endif
    </div>
</div>
@endsection