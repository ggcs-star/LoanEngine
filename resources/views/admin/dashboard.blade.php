@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    /*
    |--------------------------------------------------------------------------
    | Controller compatibility
    |--------------------------------------------------------------------------
    | Supports the current DashboardController keys and the older Blade keys.
    */
    $stats = $statistics ?? [];

    $categoriesCount = (int) ($stats['categories'] ?? $stats['education_categories'] ?? 0);
    $modulesCount = (int) ($stats['modules'] ?? $stats['education_modules'] ?? 0);
    $lessonsCount = (int) ($stats['lessons'] ?? $stats['education_lessons'] ?? 0);
    $publishedModulesCount = (int) ($stats['published_modules'] ?? 0);
    $publishedLessonsCount = (int) ($stats['published_lessons'] ?? 0);
    $toolsCount = (int) ($stats['tools'] ?? 0);
    $activeToolsCount = (int) ($stats['active_tools'] ?? 0);
    $journeysCount = (int) ($stats['journeys'] ?? 0);
    $activeJourneysCount = (int) ($stats['active_journeys'] ?? 0);
    $stepsCount = (int) ($stats['journey_steps'] ?? 0);

    $recentTools = $recentTools ?? collect();
    $recentModules = $recentModules ?? collect();
    $recentLessons = $recentLessons ?? collect();
    $recentJourneys = $recentJourneys ?? collect();
    $recentJourneySteps = $recentJourneySteps ?? collect();

    // Controller currently sends monthlyActivity; older version used monthlyContent.
    $monthlyContent = collect($monthlyActivity ?? $monthlyContent ?? [])->values();

    // Prefer the combined controller activity feed. Build one if it wasn't supplied.
    $activities = collect($recentActivity ?? []);

    if ($activities->isEmpty()) {
        $activities = collect()
            ->concat($recentTools->map(fn ($item) => [
                'title' => data_get($item, 'name', 'Untitled tool'),
                'type' => 'Tool',
                'status' => data_get($item, 'status', 'Recorded'),
                'created_at' => data_get($item, 'created_at'),
            ]))
            ->concat($recentModules->map(fn ($item) => [
                'title' => data_get($item, 'title', 'Untitled module'),
                'type' => 'Module',
                'status' => data_get($item, 'status', 'Recorded'),
                'created_at' => data_get($item, 'created_at'),
            ]))
            ->concat($recentLessons->map(fn ($item) => [
                'title' => data_get($item, 'title', 'Untitled lesson'),
                'type' => 'Lesson',
                'status' => data_get($item, 'status', 'Recorded'),
                'created_at' => data_get($item, 'created_at'),
            ]));
    }

    $activities = $activities->sortByDesc(fn ($item) => data_get($item, 'created_at'))
        ->take(8)
        ->values();

    $greetingHour = now()->hour;
    $greeting = $greetingHour < 12
        ? 'Good morning'
        : ($greetingHour < 17 ? 'Good afternoon' : 'Good evening');

    $adminName = auth()->user()?->name ?? 'Administrator';

    $quickActions = [
        [
            'title' => 'Create Tool',
            'description' => 'Add a new platform tool',
            'icon' => '＋',
            'url' => route('admin.tools.create'),
        ],
        [
            'title' => 'Create Module',
            'description' => 'Add education content',
            'icon' => '▤',
            'url' => route('admin.education.modules.create'),
        ],
        [
            'title' => 'Create Journey',
            'description' => 'Set up a guided journey',
            'icon' => '↗',
            'url' => route('admin.tool-journeys.create'),
        ],
        [
            'title' => 'Create Journey Step',
            'description' => 'Add a step to a journey',
            'icon' => '☷',
            'url' => route('admin.journey-steps.create'),
        ],
    ];

    $chartSeries = [
        ['key' => 'categories', 'label' => 'Categories', 'class' => 'categories'],
        ['key' => 'modules', 'label' => 'Modules', 'class' => 'modules'],
        ['key' => 'lessons', 'label' => 'Lessons', 'class' => 'lessons'],
        ['key' => 'tools', 'label' => 'Tools', 'class' => 'tools'],
    ];

    $chartMaximum = max(5, (int) collect($monthlyContent)->flatMap(function ($month) {
        return [
            (int) data_get($month, 'categories', 0),
            (int) data_get($month, 'modules', 0),
            (int) data_get($month, 'lessons', 0),
            (int) data_get($month, 'tools', 0),
        ];
    })->max());

    $chartMaximum = (int) (ceil($chartMaximum / 5) * 5);

    $featuredModule = $recommendedModule ?? $recentModules->first();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v=20261023">
@endpush

@section('content')
<div class="le-dashboard-v2">

    <section class="le-v2-hero">
        <div class="le-v2-hero-main">
            <span class="le-v2-eyebrow">ADMIN WORKSPACE</span>
            <h1>{{ $greeting }},<br>{{ $adminName }}</h1>
            <p class="le-v2-hero-description">Here's what's happening with your platform today.</p>

            <div class="le-v2-hero-links">
                <a href="{{ route('admin.tools.index') }}" class="le-v2-hero-link">
                    <span class="le-v2-icon">▦</span>
                    <span><strong>Manage Tools</strong><small>{{ $activeToolsCount }} active tools</small></span>
                    <span class="le-v2-arrow">→</span>
                </a>
                <a href="{{ route('admin.education.modules.index') }}" class="le-v2-hero-link">
                    <span class="le-v2-icon">▤</span>
                    <span><strong>Education Modules</strong><small>{{ $publishedModulesCount }} published modules</small></span>
                    <span class="le-v2-arrow">→</span>
                </a>
                <a href="{{ route('admin.tool-journeys.index') }}" class="le-v2-hero-link">
                    <span class="le-v2-icon">↗</span>
                    <span><strong>Manage Journeys</strong><small>{{ $activeJourneysCount }} active journeys</small></span>
                    <span class="le-v2-arrow">→</span>
                </a>
            </div>
        </div>

        
        <div class="le-v2-illustration" aria-hidden="true">
            <img
                src="{{ asset('images/dashboard-home-illustration.png') }}"
                alt=""
                class="le-v2-illustration-image"
            >
        </div>


        <!-- <div class="le-v2-hero-summary">
            <div class="le-v2-date">
                <span class="le-v2-date-icon">▦</span>
                <span><strong>{{ now()->format('D, d M Y') }}</strong><small>Your workspace at a glance</small></span>
            </div>
            <div class="le-v2-education-counts">
                <div><strong>{{ $categoriesCount }}</strong><span>Categories</span></div>
                <div><strong>{{ $modulesCount }}</strong><span>Modules</span></div>
                <div><strong>{{ $lessonsCount }}</strong><span>Lessons</span></div>
            </div>
            <a href="{{ route('admin.education.categories.index') }}" class="le-v2-primary-button">
                Manage Education <span>→</span>
            </a>
        </div> -->
    </section>

    <section class="le-v2-kpis" aria-label="Workspace statistics">
        <a class="le-v2-kpi-card" href="{{ route('admin.education.categories.index') }}">
            <span class="le-v2-kpi-icon">▱</span>
            <span class="le-v2-kpi-content"><span>Education Categories</span><strong>{{ $categoriesCount }}</strong><small>Total categories</small></span>
            <span class="le-v2-mini-bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span>
        </a>
        <a class="le-v2-kpi-card" href="{{ route('admin.education.modules.index') }}">
            <span class="le-v2-kpi-icon">▤</span>
            <span class="le-v2-kpi-content"><span>Education Modules</span><strong>{{ $modulesCount }}</strong><small>{{ $publishedModulesCount }} published</small></span>
            <span class="le-v2-mini-bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span>
        </a>
        <a class="le-v2-kpi-card" href="{{ route('admin.education.lessons.index') }}">
            <span class="le-v2-kpi-icon">▷</span>
            <span class="le-v2-kpi-content"><span>Lessons &amp; Media</span><strong>{{ $lessonsCount }}</strong><small>{{ $publishedLessonsCount }} published</small></span>
            <span class="le-v2-mini-bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span>
        </a>
        <a class="le-v2-kpi-card" href="{{ route('admin.tools.index') }}">
            <span class="le-v2-kpi-icon">▦</span>
            <span class="le-v2-kpi-content"><span>Tools Management</span><strong>{{ $toolsCount }}</strong><small>{{ $activeToolsCount }} active tools</small></span>
            <span class="le-v2-mini-bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span>
        </a>
    </section>

    <div class="le-v2-main-grid">
        <div class="le-v2-main-column">
            <section class="le-v2-panel le-v2-chart-panel">
                <header class="le-v2-panel-header">
                    <div><h2>Content Overview</h2><p>Monthly records across education and tools.</p></div>
                    <span class="le-v2-chart-period">▦ Last 6 Months</span>
                </header>

                @if($monthlyContent->isNotEmpty())
                    <div class="le-v2-chart">
                        <div class="le-v2-chart-y-axis">
                            @for($tick = 4; $tick >= 0; $tick--)
                                <span>{{ (int) round($chartMaximum * $tick / 4) }}</span>
                            @endfor
                        </div>
                        <div class="le-v2-chart-area">
                            <div class="le-v2-chart-grid">@for($line = 0; $line < 5; $line++)<span></span>@endfor</div>
                            <div class="le-v2-chart-columns">
                                @foreach($monthlyContent as $month)
                                    <div class="le-v2-chart-month">
                                        <div class="le-v2-bar-group">
                                            @foreach($chartSeries as $series)
                                                @php
                                                    $value = (int) data_get($month, $series['key'], 0);
                                                    $height = $chartMaximum > 0 ? min(100, ($value / $chartMaximum) * 100) : 0;
                                                @endphp
                                                <div class="le-v2-bar {{ $series['class'] }}" title="{{ $series['label'] }}: {{ $value }}" style="height: {{ $height }}%;"></div>
                                            @endforeach
                                        </div>
                                        <span class="le-v2-month-label">{{ data_get($month, 'label', '') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="le-v2-chart-legend">
                        @foreach($chartSeries as $series)
                            <span><i class="{{ $series['class'] }}"></i>{{ $series['label'] }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="le-v2-empty-chart"><span>▥</span><strong>No chart data available</strong><p>Monthly statistics will appear when records are available.</p></div>
                @endif
            </section>

            <section class="le-v2-panel le-v2-activity-panel">
                <header class="le-v2-panel-header">
                    <div><h2>Recent Activity</h2><p>Latest records across tools, education and journeys.</p></div>
                    <span class="le-v2-panel-caption">Latest records</span>
                </header>
                <div class="le-v2-table-wrap">
                    <table class="le-v2-table">
                        <thead><tr><th>#</th><th>Title</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        @forelse($activities as $activity)
                            @php
                                $activityDate = data_get($activity, 'created_at');
                                $activityStatus = data_get($activity, 'status', 'Recorded');
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="le-v2-record-title">{{ data_get($activity, 'title', data_get($activity, 'name', 'Untitled record')) }}</span></td>
                                <td>{{ data_get($activity, 'type', 'Record') }}</td>
                                <td><span class="le-v2-status">{{ str((string) $activityStatus)->replace('_', ' ')->title() }}</span></td>
                                <td>@if($activityDate){{ \Illuminate\Support\Carbon::parse($activityDate)->diffForHumans() }}@else — @endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="le-v2-empty-cell">No records found yet. Add a tool, module or lesson to see activity here.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="le-v2-side-column">
            <section class="le-v2-panel le-v2-quick-panel">
                <header class="le-v2-panel-header"><div><h2>Quick Actions</h2><p>Open a workspace section.</p></div><span class="le-v2-panel-dots">•••</span></header>
                <div class="le-v2-quick-grid">
                    @foreach($quickActions as $action)
                        <a href="{{ $action['url'] }}" class="le-v2-quick-action">
                            <span class="le-v2-quick-icon">{{ $action['icon'] }}</span>
                            <span><strong>{{ $action['title'] }}</strong><small>{{ $action['description'] }}</small></span>
                            <span class="le-v2-arrow">›</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="le-v2-panel le-v2-journey-panel">
                <header class="le-v2-panel-header"><div><h2>Tools &amp; Journeys</h2><p>Manage your platform resources.</p></div><span class="le-v2-panel-dots">•••</span></header>
                <div class="le-v2-resource-list">
                    <a href="{{ route('admin.tools.index') }}"><span class="le-v2-resource-icon">▦</span><span><strong>All Tools</strong><small>{{ $toolsCount }} total tools</small></span><span class="le-v2-arrow">›</span></a>
                    <a href="{{ route('admin.tool-journeys.index') }}"><span class="le-v2-resource-icon">↗</span><span><strong>All Journeys</strong><small>{{ $journeysCount }} total journeys</small></span><span class="le-v2-arrow">›</span></a>
                    <a href="{{ route('admin.journey-steps.index') }}"><span class="le-v2-resource-icon">☷</span><span><strong>All Journey Steps</strong><small>{{ $stepsCount }} total steps</small></span><span class="le-v2-arrow">›</span></a>
                </div>
            </section>

            <section class="le-v2-panel le-v2-module-panel">
                <header class="le-v2-panel-header"><div><h2>Recommended Education Module</h2><p>Published learning content.</p></div></header>
                @if($featuredModule)
                    <div class="le-v2-module-art">
                        @if($featuredModule->thumbnail_path)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($featuredModule->thumbnail_path) }}"
                                alt="{{ $featuredModule->title }}"
                                loading="lazy"
                                onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                            >
                            <div class="le-v2-module-placeholder" hidden><span>▤</span><small>LEARNING MODULE</small></div>
                        @else
                            <div class="le-v2-module-placeholder"><span>▤</span><small>LEARNING MODULE</small></div>
                        @endif
                    </div>
                    <div class="le-v2-module-content">
                        <h3>{{ $featuredModule->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($featuredModule->description ?? 'Explore this learning module.', 110) }}</p>
                        <a href="{{ route('admin.education.modules.index') }}">Manage Modules →</a>
                    </div>
                @else
                    <div class="le-v2-empty-state"><span>▤</span><strong>No modules available</strong><p>Create an education module to feature it here.</p><a href="{{ route('admin.education.modules.create') }}">Create Module →</a></div>
                @endif
            </section>
        </aside>
    </div>

    <div class="le-v2-footer-note"><span>LoanEngine Admin Workspace</span><span>Updated {{ now()->format('d M Y, h:i A') }}</span></div>
</div>
@endsection
