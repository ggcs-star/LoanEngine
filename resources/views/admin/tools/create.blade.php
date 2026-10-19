
@extends('layouts.admin')

@section('title', 'Create Tool')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-tools.css') }}">
@endpush

@section('content')
<div class="tools-page">
    <div class="tools-page-header">
        <div>
            <a href="{{ route('admin.tools.index') }}" class="tools-back-link">
                &larr; Back to Tools
            </a>
            <h1 class="tools-page-title">Create Tool</h1>
            <p class="tools-page-description">
                Add a tool and optionally associate it with existing journeys.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="tools-alert tools-alert-error" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="tools-panel tools-form-panel">
        <div class="tools-panel-header">
            <div>
                <h2 class="tools-panel-title">Tool Details</h2>
                <p class="tools-panel-description">
                    Enter the tool information below.
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.tools.store') }}"
            class="tools-form"
        >
            @csrf

            @include('admin.tools.partials.form', [
                'tool' => $tool,
                'journeys' => $journeys,
            ])

            <div class="tools-form-footer">
                <a
                    href="{{ route('admin.tools.index') }}"
                    class="tools-button tools-button-secondary"
                >
                    Cancel
                </a>

                <button type="submit" class="tools-button tools-button-primary">
                    Create Tool
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
