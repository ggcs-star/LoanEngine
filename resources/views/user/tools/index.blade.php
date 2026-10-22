@extends('user.layouts.app')

@section('title', 'Tools & Calculators | LoanEngine')
@section('page_heading', 'Tools & Calculators')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
@php
    $filters = $filters ?? ['search' => '', 'category' => ''];
    $sort = $sort ?? 'recommended';

    $search = trim((string) ($filters['search'] ?? ''));
    $selectedCategory = (string) ($filters['category'] ?? '');

    $categories = $categories ?? $tools
        ->pluck('category')
        ->filter()
        ->unique()
        ->sort()
        ->values();

    $visibleTools = $tools->filter(function ($tool) use (
        $search,
        $selectedCategory
    ) {
        $name = mb_strtolower((string) $tool->name);
        $description = mb_strtolower(
            (string) ($tool->short_description ?? '')
        );
        $category = mb_strtolower((string) ($tool->category ?? ''));
        $searchTerm = mb_strtolower($search);

        $matchesSearch = $search === ''
            || str_contains($name, $searchTerm)
            || str_contains($description, $searchTerm)
            || str_contains($category, $searchTerm);

        $matchesCategory = $selectedCategory === ''
            || ($tool->category ?? '') === $selectedCategory;

        return $matchesSearch && $matchesCategory;
    });

    if ($sort === 'name') {
        $visibleTools = $visibleTools->sortBy(
            'name',
            SORT_NATURAL | SORT_FLAG_CASE
        );
    } elseif ($sort === 'newest') {
        $visibleTools = $visibleTools->sortByDesc('created_at');
    } else {
        $visibleTools = $visibleTools->sortBy('display_order');
    }

    $visibleTools = $visibleTools->values();
@endphp

<div class="le-tools">

    {{-- Hero --}}
    <section class="le-hero">
        <div>
            <span class="le-eyebrow">YOUR FINANCIAL TOOLKIT</span>

            <h1>Make clearer financial decisions.</h1>

            <p>
                Explore practical calculators and resources to plan your
                next financial step with confidence.
            </p>

            <form
                class="le-search"
                action="{{ route('user.tools.index') }}"
                method="GET"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search calculators and resources..."
                    aria-label="Search tools"
                >

                @if ($selectedCategory !== '')
                    <input
                        type="hidden"
                        name="category"
                        value="{{ $selectedCategory }}"
                    >
                @endif

                @if ($sort !== 'recommended')
                    <input
                        type="hidden"
                        name="sort"
                        value="{{ $sort }}"
                    >
                @endif

                <button type="submit" class="le-btn le-btn-primary">
                    Search →
                </button>
            </form>
        </div>

        <div class="le-hero-art" aria-hidden="true">
            <span>▦</span>
            <strong>Plan with clarity</strong>
            <small>Simple tools. Better decisions.</small>

            <div class="le-bars">
                <i></i>
                <i></i>
                <i></i>
                <i></i>
                <i></i>
            </div>
        </div>
    </section>

    {{-- Tools heading --}}
    <section>
        <div class="le-section-head">
            <div>
                <span class="le-eyebrow">EXPLORE YOUR OPTIONS</span>
                <h2>Tools and calculators</h2>
                <p>Choose a tool to get started.</p>
            </div>

            <span class="le-count">
                {{ $visibleTools->count() }}
                {{ $visibleTools->count() === 1 ? 'tool' : 'tools' }}
                available
            </span>
        </div>

        {{-- Filters --}}
        <form
            class="le-toolbar"
            method="GET"
            action="{{ route('user.tools.index') }}"
        >
            <input
                type="hidden"
                name="search"
                value="{{ $search }}"
            >

            <div class="le-filter-row">
                <label>
                    <span class="le-sr-only">Category</span>

                    <select
                        name="category"
                        onchange="this.form.submit()"
                    >
                        <option value="">All categories</option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category }}"
                                @selected($selectedCategory === $category)
                            >
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="le-sr-only">Sort tools</span>

                    <select
                        name="sort"
                        onchange="this.form.submit()"
                    >
                        <option
                            value="recommended"
                            @selected($sort === 'recommended')
                        >
                            Recommended
                        </option>

                        <option
                            value="name"
                            @selected($sort === 'name')
                        >
                            Name A–Z
                        </option>

                        <option
                            value="newest"
                            @selected($sort === 'newest')
                        >
                            Newest first
                        </option>
                    </select>
                </label>
            </div>

            @if (
                $search !== ''
                || $selectedCategory !== ''
                || $sort !== 'recommended'
            )
                <a href="{{ route('user.tools.index') }}">
                    Clear filters ×
                </a>
            @endif
        </form>

        {{-- Tool cards --}}
        @if ($visibleTools->isNotEmpty())
            <div class="le-grid">
                @foreach ($visibleTools as $tool)
                    <article class="le-card">

                        <div class="le-card-top">
                            <span class="le-icon" aria-hidden="true">
                                @switch($tool->icon)
                                    @case('house')
                                    @case('home')
                                        ⌂
                                        @break

                                    @case('calendar')
                                        ▦
                                        @break

                                    @case('book')
                                        ▤
                                        @break

                                    @case('chart')
                                        ↗
                                        @break

                                    @default
                                        ▦
                                @endswitch
                            </span>

                            <span class="le-chip">
                                {{ $tool->category ?: 'General' }}
                            </span>

                            <span class="le-arrow">↗</span>
                        </div>

                        <div class="le-card-body">
                            <small>{{ $tool->type ?: 'Tool' }}</small>

                            <h3>
                                <a href="{{ $tool->slug === 'home-loan-calculators'
                                    ? route('user.tools.journeys.index', ['for' => 'calculators'])
                                    : route('user.tools.show', ['tool' => $tool->slug]) }}">
                                    {{ $tool->name }}
                                </a>
                            </h3>

                            <p>
                                {{ $tool->short_description
                                    ?: 'Explore this tool to learn more.' }}
                            </p>
                        </div>

                        @if ($tool->journeys->isNotEmpty())
                            <div class="le-tags">
                                @foreach ($tool->journeys->take(2) as $journey)
                                    <span>
                                        {{ $journey->short_label ?: $journey->name }}
                                    </span>
                                @endforeach

                                @if ($tool->journeys->count() > 2)
                                    <span>
                                        +{{ $tool->journeys->count() - 2 }}
                                    </span>
                                @endif
                            </div>
                        @endif

                       <div class="le-card-foot">
    <span>● Available to explore</span>

    @php
        $journey = $tool->journeys->first();
    @endphp

   <a class="le-btn le-btn-card"
   href="{{ $tool->slug === 'home-loan-calculators'
       ? route('user.tools.journeys.index', ['for' => 'calculators'])
       : route('user.tools.show', ['tool' => $tool->slug]) }}">
    Explore tool →
</a>
</div>

                    </article>
                @endforeach
            </div>
        @else
            <div class="le-empty">
                <span>⌕</span>
                <h3>No tools found</h3>
                <p>
                    Try another search or clear the selected filters.
                </p>

                <a
                    class="le-btn le-btn-primary"
                    href="{{ route('user.tools.index') }}"
                >
                    View all tools
                </a>
            </div>
        @endif
    </section>

</div>
@endsection
