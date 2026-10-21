@extends('user.layouts.app')

@section('title', $journey->name . ' | LoanEngine')
@section('page_heading', $journey->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools">

    <a class="le-btn le-btn-card"
       href="{{ route('user.tools.index') }}">
        ← Back to all tools
    </a>

    <section class="le-hero">
        <div>
            <span class="le-eyebrow">YOUR JOURNEY</span>
            <h1>{{ $journey->name }}</h1>
            <p>{{ $journey->description }}</p>
        </div>
    </section>

    <section>
        <div class="le-section-head">
            <div>
                <span class="le-eyebrow">YOUR NEXT STEPS</span>
                <h2>Follow your journey</h2>
                <p>Complete each step to move forward.</p>
            </div>

            <span class="le-count">
                {{ $steps->count() }} steps
            </span>
        </div>

        @if ($steps->isNotEmpty())
            <div class="le-grid">
                @foreach ($steps as $step)
                    <article class="le-card">
                        <div class="le-card-top">
                            <span class="le-icon">
                                {{ $step->step_number }}
                            </span>
                            <span class="le-chip">Step {{ $step->step_number }}</span>
                        </div>

                        <div class="le-card-body">
                            <h3>{{ $step->title }}</h3>
                            <p>{{ $step->description }}</p>
                        </div>

                        <div class="le-card-foot">
                            <span>Step-by-step guidance</span>

                            <a class="le-btn le-btn-card"
                               href="{{ route('user.tools.journeys.steps.show', [
                                   'journey' => $journey->slug,
                                   'step' => $step->id,
                               ]) }}">
                                View step →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="le-empty">
                <h3>No active steps yet</h3>
                <p>Steps for this journey have not been added or activated.</p>
            </div>
        @endif
    </section>
</div>
@endsection