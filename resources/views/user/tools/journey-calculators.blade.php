
@extends('user.layouts.app')

@section('title', 'Recommended Calculators | LoanEngine')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools">
    <a class="le-back"
       href="{{ route('user.tools.journeys.index', ['for' => 'calculators']) }}">
        ← Back to options
    </a>

    <section>
        <div class="le-section-head">
            <div>
                <span class="le-eyebrow">RECOMMENDED TOOLS</span>
                <h1>Recommended Calculators</h1>
            </div>
            <span class="le-count">{{ $tools->count() }} calculators</span>
        </div>

        <div class="le-grid">
            @forelse ($tools as $tool)
                <a class="le-card"
                   href="{{ route('calculators.show', ['tool' => $tool->slug]) }}">
                    <div class="le-card-top">
                        <span class="le-icon">⌂</span>
                        <span class="le-chip">Calculator</span>
                    </div>

                    <div class="le-card-body">
                        <h3>{{ $tool->name }}</h3>
                        <p>{{ $tool->short_description }}</p>
                    </div>

                    <div class="le-card-foot">
                        <span>Open calculator →</span>
                    </div>
                </a>
            @empty
                <div class="le-empty">
                    <h3>No calculators linked yet</h3>
                    <p>Ask your administrator to link calculators to this option.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
