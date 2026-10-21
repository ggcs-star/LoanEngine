@extends('user.layouts.app')

@section('title', 'Choose Your Journey | LoanEngine')
@section('page_heading', 'Choose Your Journey')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools">
    <a class="le-back" href="{{ route('user.tools.index') }}">
        ← Back to Tools
    </a>

    <section class="le-section-head">
        <div>
            <span class="le-eyebrow">MORTGAGE MASTER</span>
            <h1>Choose Your Journey</h1>
            <p>Select your situation to see the step-by-step path built for it.</p>
        </div>
    </section>

    <div class="le-grid">
        @forelse ($journeys as $journey)
            <a class="le-card"
               href="{{ route('user.tools.journeys.show', $journey->slug) }}">
                <div class="le-card-top">
                    <span class="le-icon">↗</span>
                    <span class="le-chip">
                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <div class="le-card-body">
                    <h3>{{ $journey->name }}</h3>
                    <p>{{ $journey->description ?: $journey->short_label }}</p>
                    <small>{{ $journey->steps_count ?? $journey->steps()->where('status', 'active')->count() }} steps</small>
                </div>

                <div class="le-card-foot">
                    <span>Explore journey →</span>
                </div>
            </a>
        @empty
            <p>No active journeys are available right now.</p>
        @endforelse
    </div>
</div>
@endsection