
@extends('user.layouts.app')

@section('title', 'Choose Your Journey | LoanEngine')
@section('page_heading', 'Choose Your Journey')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools">
    <a class="le-back" href="{{ route('user.tools.index') }}"><span aria-hidden="true">←</span> Back to Tools</a>

    <section class="le-section-head">
        <div>
            <span class="le-eyebrow">YOUR FINANCIAL TOOLKIT</span>
            @if (request('for') === 'calculators')
                <h1>What are you trying to do?</h1>
                <p>Select an option to see recommended calculators.</p>
            @else
                <h1>Choose your journey</h1>
                <p>Select your situation to see the step-by-step path built for it.</p>
            @endif
        </div>
        <span class="le-count">{{ $journeys->count() }} journeys</span>
    </section>

    <div class="le-grid">
        @forelse ($journeys as $journey)
            <a class="le-card" href="{{ route('user.tools.journeys.show', ['journey' => $journey->slug, 'for' => request('for')]) }}">
                <div class="le-card-top">
                    <span class="le-icon">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="le-chip">Journey {{ $loop->iteration }}</span>
                    <span class="le-arrow" aria-hidden="true">↗</span>
                </div>

                <div class="le-card-body">
                    <h3>{{ $journey->name }}</h3>
                    <p>{{ $journey->description ?: ($journey->short_label ?: 'A guided path for your next financial step.') }}</p>
                    @if (request('for') === 'calculators')
                        <small>View recommended calculators</small>
                    @else
                        <small>{{ $journey->steps_count }} {{ $journey->steps_count === 1 ? 'step' : 'steps' }}</small>
                    @endif
                </div>

                <div class="le-card-foot">
                    <span>Explore journey</span>
                    <span aria-hidden="true">→</span>
                </div>
            </a>
        @empty
            <div class="le-empty">
                <span aria-hidden="true">▦</span>
                <h3>No active journeys yet</h3>
                <p>Journeys added and activated by your administrator will appear here.</p>
                <a class="le-btn le-btn-primary" href="{{ route('user.tools.index') }}">Back to Tools</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
