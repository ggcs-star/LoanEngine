@extends('user.layouts.app')

@section('title', $journey->name . ' | LoanEngine')
@section('page_heading', $journey->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools">
    <a class="le-back" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('user.tools.index') }}">← Back</a>

    <section class="le-hero">
        <div>
            <span class="le-eyebrow">YOUR JOURNEY</span>
            <h1>{{ $journey->name }}</h1>
            <p>{{ $journey->description ?: 'Follow the steps below at your own pace.' }}</p>
        </div>
    </section>

    <section>
        <div class="le-section-head">
            <div>
                <span class="le-eyebrow">YOUR NEXT STEPS</span>
                <h2>Follow your journey</h2>
                <p>Open a step to read its guidance and relevant tools.</p>
            </div>
            <span class="le-count">{{ $steps->count() }} {{ $steps->count() === 1 ? 'step' : 'steps' }}</span>
        </div>

        @if ($steps->isNotEmpty())
            <div class="le-grid">
                @foreach ($steps as $step)
                    <a class="le-card" href="{{ route('user.tools.journeys.steps.show', ['journey' => $journey->slug, 'step' => $step->id]) }}">
                        <div class="le-card-top">
                            <span class="le-icon">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="le-chip">Step {{ $loop->iteration }} of {{ $steps->count() }}</span>
                        </div>
                        <div class="le-card-body">
                            <h3>{{ $step->title }}</h3>
                            <p>{{ $step->description ?: 'Open this step for details and guidance.' }}</p>
                        </div>
                        <div class="le-card-foot"><span>View step →</span></div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="le-empty">
                <h3>No active steps yet</h3>
                <p>This journey does not have any active steps. Ask an administrator to add steps and set their status to Active.</p>
            </div>
        @endif
    </section>
</div>
@endsection
