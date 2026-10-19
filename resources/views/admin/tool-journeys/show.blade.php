
@extends('layouts.admin')

@section('title', 'Journey Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-journeys.css') }}">
<style>
    .journey-details-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        padding: 8px 22px 22px;
    }

    .journey-details-item {
        display: grid;
        gap: 7px;
        padding: 17px 12px;
        border-bottom: 1px solid var(--border, #e2e2e2);
    }

    .journey-details-item span {
        color: var(--muted, #777);
        font-size: 12px;
    }

    .journey-details-item strong {
        color: var(--text, #222);
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .journey-details-section {
        padding: 0 22px 22px;
    }

    .journey-details-section h3 {
        margin: 10px 0 14px;
        font-size: 15px;
    }

    .journey-details-list {
        display: grid;
        gap: 10px;
    }

    .journey-details-list-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border: 1px solid var(--border, #e2e2e2);
        border-radius: 8px;
        color: var(--text, #222);
        text-decoration: none;
    }

    .journey-details-list-item span {
        display: block;
        margin-top: 5px;
        color: var(--muted, #777);
        font-size: 12px;
    }

    .journey-details-empty {
        color: var(--muted, #777);
        font-size: 13px;
    }

    @media (max-width: 600px) {
        .journey-details-grid {
            grid-template-columns: 1fr;
            padding: 8px 12px 16px;
        }

        .journey-details-section {
            padding: 0 14px 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="journey-page">
    <div class="journey-page-header">
        <div>
            <h1 class="journey-page-title">{{ $journey->name }}</h1>
            <p class="journey-page-description">
                View journey information, steps and linked tools.
            </p>
        </div>

        <div class="journey-row-actions">
            <a href="{{ route('admin.tool-journeys.index') }}"
               class="journey-button journey-button-secondary">
                Back
            </a>

            <a href="{{ route('admin.tool-journeys.edit', $journey) }}"
               class="journey-button journey-button-primary">
                Edit Journey
            </a>
        </div>
    </div>

    <section class="journey-panel">
        <div class="journey-panel-header">
            <div>
                <h2>Journey Information</h2>
                <p>Overview of this journey.</p>
            </div>
        </div>

        <div class="journey-details-grid">
            <div class="journey-details-item">
                <span>Journey Name</span>
                <strong>{{ $journey->name }}</strong>
            </div>

            <div class="journey-details-item">
                <span>Slug</span>
                <strong>{{ $journey->slug }}</strong>
            </div>

            <div class="journey-details-item">
                <span>Short Label</span>
                <strong>{{ $journey->short_label ?: '—' }}</strong>
            </div>

            <div class="journey-details-item">
                <span>Status</span>
                <strong>{{ ucfirst($journey->status) }}</strong>
            </div>

            <div class="journey-details-item">
                <span>Display Order</span>
                <strong>{{ $journey->display_order }}</strong>
            </div>

            <div class="journey-details-item">
                <span>Total Steps</span>
                <strong>{{ $journey->steps_count }}</strong>
            </div>

            <div class="journey-details-item">
                <span>Total Tools</span>
                <strong>{{ $journey->tools_count }}</strong>
            </div>

            <div class="journey-details-item">
                <span>Description</span>
                <strong>{{ $journey->description ?: '—' }}</strong>
            </div>
        </div>

        <div class="journey-details-section">
            <h3>Journey Steps</h3>

            <div class="journey-details-list">
                @forelse ($journey->steps as $step)
                    <div class="journey-details-list-item">
                        <div>
                            <strong>
                                {{ $step->step_number }}.
                                {{ $step->title }}
                            </strong>

                            @if ($step->description)
                                <span>{{ $step->description }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="journey-details-empty">
                        No steps have been added to this journey yet.
                    </p>
                @endforelse
            </div>
        </div>

        <div class="journey-details-section">
            <h3>Linked Tools</h3>

            <div class="journey-details-list">
                @forelse ($journey->tools as $tool)
                    <div class="journey-details-list-item">
                        <div>
                            <strong>{{ $tool->name }}</strong>
                            <span>
                                {{ $tool->category }} · {{ $tool->type }}
                            </span>
                        </div>

                        <span>{{ ucfirst($tool->status) }}</span>
                    </div>
                @empty
                    <p class="journey-details-empty">
                        No tools are linked to this journey yet.
                    </p>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
