
@extends('layouts.admin')

@section('title', 'Create Journey Step')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-journey-steps.css') }}">
@endpush

@section('content')
<div class="journey-step-page">
    <div class="journey-step-page-header">
        <div>
            <a href="{{ route('admin.journey-steps.index') }}" class="journey-step-back-link">
                &larr; Back to Steps
            </a>
            <h1 class="journey-step-page-title">Create Journey Step</h1>
            <p class="journey-step-page-description">
                Add a step to a journey and associate the tools relevant to it.
            </p>
        </div>
    </div>

    @if (session('error'))
        <div class="journey-step-alert" role="alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="journey-step-alert" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="journey-step-panel">
        <div class="journey-step-panel-header">
            <h2>Step Details</h2>
            <p>Fields marked with * are required.</p>
        </div>

        <form method="POST" action="{{ route('admin.journey-steps.store') }}">
            @csrf

            @include('admin.journey-steps.partials.form', [
                'step' => $step,
                'journeys' => $journeys,
                'selectedJourney' => $selectedJourney,
                'tools' => $tools,
            ])

            <div class="journey-step-form-footer">
                <a href="{{ route('admin.journey-steps.index') }}" class="journey-step-button journey-step-button-secondary">
                    Cancel
                </a>
                <button type="submit" class="journey-step-button journey-step-button-primary">
                    Create Step
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
