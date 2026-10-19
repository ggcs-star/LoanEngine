@extends('layouts.admin')

@section('title', 'Edit Journey')

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
            <h1 class="journey-page-title">Edit Journey</h1>
            <p class="journey-page-description">
                Update the journey details without changing its existing steps or tool mappings.
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
            <p>Update the information below and save your changes.</p>
        </div>

        <form method="POST" action="{{ route('admin.tool-journeys.update', $journey) }}">
            @csrf
            @method('PUT')

            @include('admin.tool-journeys.partials.form', ['journey' => $journey])

            <div class="journey-form-footer">
                <a href="{{ route('admin.tool-journeys.index') }}" class="journey-button journey-button-secondary">
                    Cancel
                </a>
                <button type="submit" class="journey-button journey-button-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </section>
</div>
@endsection