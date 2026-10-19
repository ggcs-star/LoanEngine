
@extends('layouts.admin')

@section('title', 'Tool Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-tools.css') }}">
@endpush

@section('content')
<div class="tools-page">
    <div class="tools-page-header">
        <div>
            <h1 class="tools-page-title">{{ $tool->name }}</h1>
            <p class="tools-page-description">Tool details and linked journeys.</p>
        </div>

        <div class="tools-row-actions">
            <a href="{{ route('admin.tools.index') }}"
               class="tools-button tools-button-secondary">
                Back to Tools
            </a>

            <a href="{{ route('admin.tools.edit', $tool) }}"
               class="tools-button tools-button-primary">
                Edit Tool
            </a>
        </div>
    </div>

    <section class="tools-panel">
        <div class="tools-panel-header">
            <h2 class="tools-panel-title">Tool Information</h2>
        </div>

        <div class="tools-details-grid">
            <div>
                <span>Name</span>
                <strong>{{ $tool->name }}</strong>
            </div>

            <div>
                <span>Slug</span>
                <strong>{{ $tool->slug }}</strong>
            </div>

            <div>
                <span>Category</span>
                <strong>{{ $tool->category }}</strong>
            </div>

            <div>
                <span>Type</span>
                <strong>{{ $tool->type }}</strong>
            </div>

            <div>
                <span>Status</span>
                <strong>{{ ucfirst($tool->status) }}</strong>
            </div>

            <div>
                <span>Display Order</span>
                <strong>{{ $tool->display_order }}</strong>
            </div>

            <div>
                <span>Toolkit Home</span>
                <strong>{{ $tool->show_on_toolkit_home ? 'Yes' : 'No' }}</strong>
            </div>

            <div>
                <span>Short Description</span>
                <strong>{{ $tool->short_description ?: '—' }}</strong>
            </div>
        </div>

        <div class="tools-panel-header">
            <h2 class="tools-panel-title">Linked Journeys</h2>
        </div>

        <div class="tools-details-tags">
            @forelse ($tool->journeys as $journey)
                <a href="{{ route('admin.tool-journeys.show', $journey) }}">
                    {{ $journey->name }}
                </a>
            @empty
                <span>No journeys assigned.</span>
            @endforelse
        </div>
    </section>
</div>
@endsection
