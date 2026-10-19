
@extends('layouts.admin')

@section('title', 'Edit Tool')

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
            <h1 class="tools-page-title">Edit Tool</h1>
            <p class="tools-page-description">
                Update this tool's details and journey associations.
            </p>
        </div>

        <span class="tools-status tools-status-{{ $tool->status }}">
            {{ ucfirst($tool->status) }}
        </span>
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
                    Changes will be saved to this existing tool.
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.tools.update', $tool) }}"
            class="tools-form"
        >
            @csrf
            @method('PUT')

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
                    Save Changes
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
