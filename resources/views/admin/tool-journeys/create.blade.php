@extends('layouts.admin')

@section('title', 'Create Journey')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-journeys.css') }}">
@endpush

@section('content')
<div class="journey-page">
    <div class="journey-page-header">
        <div>
            <a href="{{ route('admin.tool-journeys.index') }}" class="journey-back-link">
                &larr; Back to Journeys
            </a>
            <h1 class="journey-page-title">Create Journey</h1>
            <p class="journey-page-description">
                Create a journey that can later contain its own steps and relevant tools.
            </p>
        </div>
    </div>

    @if (session('error'))
        <div class="journey-alert" role="alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="journey-alert" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="journey-panel">
        <div class="journey-panel-header">
            <h2>Journey Details</h2>
            <p>Fields marked with * are required.</p>
        </div>

        <form method="POST" action="{{ route('admin.tool-journeys.store') }}">
            @csrf

            @include('admin.tool-journeys.partials.form', ['journey' => $journey])

            <div class="journey-form-footer">
                <a href="{{ route('admin.tool-journeys.index') }}" class="journey-button journey-button-secondary">
                    Cancel
                </a>
                <button type="submit" class="journey-button journey-button-primary">
                    Create Journey
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
