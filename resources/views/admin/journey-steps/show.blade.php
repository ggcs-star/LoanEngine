@extends('layouts.admin')

@section('title', 'Journey Step Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-journey-steps.css') }}">
@endpush

@section('content')
<div class="journey-step-page journey-step-show">

    {{-- Page Header --}}
    <div class="journey-step-page-header">
        <div>
            <a
                href="{{ route('admin.journey-steps.index', ['journey_id' => $step->journey_id]) }}"
                class="journey-step-back-link"
            >
                &larr; Back to Journey Steps
            </a>

            <h1 class="journey-step-page-title">
                {{ $step->title }}
            </h1>

            <p class="journey-step-page-description">
                View journey step details and assigned tools.
            </p>
        </div>

        <div class="journey-step-show-actions">
            <a
                href="{{ route('admin.journey-steps.edit', $step) }}"
                class="journey-step-button journey-step-button-primary"
            >
                Edit Step
            </a>

            <a
                href="{{ route('admin.journey-steps.index', ['journey_id' => $step->journey_id]) }}"
                class="journey-step-button journey-step-button-secondary"
            >
                Back to List
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="journey-step-alert" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="journey-step-alert" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="journey-step-alert" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Step Information --}}
    <section class="journey-step-show-card">
        <div class="journey-step-show-section-header">
            <div>
                <h2>Step Information</h2>
                <p>Basic information and instructions for this step.</p>
            </div>

            <span class="journey-step-status">
                {{ ucfirst($step->status) }}
            </span>
        </div>

        <div class="journey-step-show-grid">
            <div class="journey-step-show-item">
                <span class="journey-step-show-label">Step Number</span>
                <div class="journey-step-show-value">
                    {{ $step->step_number }}
                </div>
            </div>

            <div class="journey-step-show-item">
                <span class="journey-step-show-label">Display Order</span>
                <div class="journey-step-show-value">
                    {{ $step->display_order }}
                </div>
            </div>

            <div class="journey-step-show-item">
                <span class="journey-step-show-label">Step Title</span>
                <div class="journey-step-show-value">
                    {{ $step->title }}
                </div>
            </div>

            <div class="journey-step-show-item">
                <span class="journey-step-show-label">Journey</span>
                <div class="journey-step-show-value">
                    {{ $step->journey?->name ?? 'Journey unavailable' }}
                </div>
            </div>

            <div class="journey-step-show-item journey-step-show-full">
                <span class="journey-step-show-label">Description</span>
                <div class="journey-step-show-value">
                    {{ $step->description ?: 'No description provided.' }}
                </div>
            </div>

            <div class="journey-step-show-item journey-step-show-full">
                <span class="journey-step-show-label">What You Need</span>
                <div class="journey-step-show-value">
                    {{ $step->what_you_need ?: 'Not specified.' }}
                </div>
            </div>

            <div class="journey-step-show-item journey-step-show-full">
                <span class="journey-step-show-label">What to Watch Out For</span>
                <div class="journey-step-show-value">
                    {{ $step->watch_out_for ?: 'Not specified.' }}
                </div>
            </div>
        </div>
    </section>

    {{-- Assigned Tools --}}
    <section class="journey-step-show-card">
        <div class="journey-step-show-section-header">
            <div>
                <h2>Assigned Tools</h2>
                <p>Tools associated with this journey step.</p>
            </div>

            <span class="journey-step-show-count">
                {{ $step->tools->count() }} tools
            </span>
        </div>

        @if ($step->tools->isNotEmpty())
            <div class="journey-step-show-tools">
                @foreach ($step->tools as $tool)
                    <article class="journey-step-show-tool">
                        <div class="journey-step-show-tool-heading">
                            <h3>{{ $tool->name }}</h3>

                            @if ($tool->status)
                                <span class="journey-step-status">
                                    {{ ucfirst($tool->status) }}
                                </span>
                            @endif
                        </div>

                        @if ($tool->slug)
                            <p class="journey-step-show-tool-slug">
                                {{ $tool->slug }}
                            </p>
                        @endif

                        @if ($tool->description)
                            <p class="journey-step-show-tool-description">
                                {{ $tool->description }}
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="journey-step-empty-state">
                <strong>No tools assigned</strong>
                <p>
                    This step does not have any tools assigned yet.
                    You can assign tools by editing the step.
                </p>

                <a
                    href="{{ route('admin.journey-steps.edit', $step) }}"
                    class="journey-step-button journey-step-button-secondary"
                >
                    Assign Tools
                </a>
            </div>
        @endif
    </section>

</div>
@endsection