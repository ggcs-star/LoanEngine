@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $stats = $statistics ?? [];
    $categoriesCount = (int) ($stats['education_categories'] ?? 0);
    $modulesCount = (int) ($stats['education_modules'] ?? 0);
    $lessonsCount = (int) ($stats['education_lessons'] ?? 0);
    $publishedModulesCount = (int) ($stats['published_modules'] ?? 0);
    $publishedLessonsCount = (int) ($stats['published_lessons'] ?? 0);
    $toolsCount = (int) ($stats['tools'] ?? 0);
    $activeToolsCount = (int) ($stats['active_tools'] ?? 0);
    $journeysCount = (int) ($stats['journeys'] ?? 0);
    $activeJourneysCount = (int) ($stats['active_journeys'] ?? 0);
    $stepsCount = (int) ($stats['journey_steps'] ?? 0);
    $activities = collect($recentActivity ?? [])->sortByDesc(fn ($item) => data_get($item, 'created_at'))->take(5)->values();
    $recentTools = collect($recentTools ?? [])->take(3);
    $recentModules = collect($recentModules ?? [])->take(3);
    $upcomingEvents = collect($upcomingEvents ?? [])->take(3);
    $monthlyContent = collect($monthlyContent ?? [])->values();

    $greetingHour = now()->hour;
    $greeting = $greetingHour < 12 ? 'Good Morning' : ($greetingHour < 17 ? 'Good Afternoon' : 'Good Evening');
    $adminName = auth()->user()?->name ?? 'Admin';

    $iconSvg = [
        'user' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7.5" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2z"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2 20v-1.5a7 7 0 0 1 14 0V20z"/><path d="M16 5a3.5 3.5 0 0 1 0 6.8M18 14a5 5 0 0 1 4 4.8V20h-4"/></svg>',
        'education' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11v5c3.5 3 8.5 3 12 0v-5M22 9v7"/></svg>',
        'book' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5.5A3.5 3.5 0 0 1 6.5 2H12v19H6.5A3.5 3.5 0 0 0 3 24z"/><path d="M21 5.5A3.5 3.5 0 0 0 17.5 2H12v19h5.5A3.5 3.5 0 0 1 21 24z"/></svg>',
        'file' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l5 5v15H6z"/><path d="M14 2v6h5M9 13h7M9 17h7"/></svg>',
        'play' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 3 16 9-16 9z"/></svg>',
        'tools' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 6a5 5 0 0 0 6 6L11 21l-4-4 9-9a5 5 0 0 0-6-6l3 3-3 3-3-3a5 5 0 0 0 7 1z"/></svg>',
        'journey' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="6" cy="18" r="3"/><circle cx="18" cy="6" r="3"/><path d="M9 18h2a4 4 0 0 0 4-4v-4a4 4 0 0 1 3-4"/></svg>',
        'steps' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5h12M9 12h12M9 19h12"/><circle cx="4" cy="5" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="19" r="1"/></svg>',
        'calculator' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M7 6h10v4H7zM8 14h1m3 0h1m3 0h1M8 18h1m3 0h1m3 0h1"/></svg>',
        'chart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V11h4v9M10 20V4h4v16M16 20v-7h4v7"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 2v6M17 2v6M3 10h18"/></svg>',
        'flow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/><circle cx="8" cy="5" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="10" cy="19" r="2"/></svg>',
        'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
        'grid' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
    ];

    $quickActions = [
        ['title' => 'Users Management', 'description' => 'Manage platform users', 'icon' => 'user', 'url' => route('admin.users.index')],
        ['title' => 'Education Content', 'description' => 'Categories, modules and lessons', 'icon' => 'education', 'url' => route('admin.education.categories.index')],
        ['title' => 'Calculators', 'description' => 'Manage calculator tools', 'icon' => 'calculator', 'url' => route('admin.calculators.index')],
        ['title' => 'Calendar', 'description' => 'View and manage events', 'icon' => 'calendar', 'url' => route('admin.financial-flow.calendar.index')],
    ];

    $statCards = [
        ['label' => 'Total Users', 'value' => $stats['users'] ?? null, 'note' => 'Registered accounts', 'icon' => 'users', 'url' => route('admin.users.index')],
        ['label' => 'Education Categories', 'value' => $categoriesCount, 'note' => 'Total categories', 'icon' => 'book', 'url' => route('admin.education.categories.index')],
        ['label' => 'Education Modules', 'value' => $modulesCount, 'note' => $publishedModulesCount . ' published', 'icon' => 'file', 'url' => route('admin.education.modules.index')],
        ['label' => 'Lessons & Media', 'value' => $lessonsCount, 'note' => $publishedLessonsCount . ' published', 'icon' => 'play', 'url' => route('admin.education.lessons.index')],
        ['label' => 'Tools', 'value' => $toolsCount, 'note' => $activeToolsCount . ' active', 'icon' => 'tools', 'url' => route('admin.tools.index')],
        ['label' => 'Journeys', 'value' => $journeysCount, 'note' => $activeJourneysCount . ' active', 'icon' => 'journey', 'url' => route('admin.tool-journeys.index')],
        ['label' => 'Journey Steps', 'value' => $stepsCount, 'note' => 'Total steps', 'icon' => 'steps', 'url' => route('admin.journey-steps.index')],
        ['label' => 'Calculators', 'value' => $stats['calculators'] ?? null, 'note' => 'Calculator records', 'icon' => 'calculator', 'url' => route('admin.calculators.index')],
        ['label' => 'Calculator Insights', 'value' => $stats['calculator_insights'] ?? null, 'note' => 'Insight records', 'icon' => 'chart', 'url' => route('admin.calculator-insights.index')],
        ['label' => 'Financial Flow Records', 'value' => $stats['financial_flow_records'] ?? null, 'note' => 'Financial records', 'icon' => 'flow', 'url' => route('admin.financial-flow.index')],
    ];

    $chartMaximum = max(5, (int) collect($monthlyContent)->flatMap(fn ($month) => [
        (int) data_get($month, 'categories', 0),
        (int) data_get($month, 'modules', 0),
        (int) data_get($month, 'lessons', 0),
        (int) data_get($month, 'tools', 0),
    ])->max());

    $featuredModule = ($recommendedModule ?? null) ?: $recentModules->first();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v={{ filemtime(public_path('css/admin-dashboard.css')) }}">
@endpush

@section('content')
<div class="le-dashboard-v2">
    <section class="le-v2-hero">
        <div class="le-v2-hero-top">
            <div>
                <span class="le-v2-eyebrow">ADMIN WORKSPACE</span>
                <h1>{{ $greeting }}, {{ $adminName }}</h1>
                <p>Here's your LoanEngine overview for today.</p>
            </div>
            <span class="le-v2-hero-mark">{!! $iconSvg['grid'] !!}</span>
        </div>
        <div class="le-v2-hero-links">
            @foreach($quickActions as $action)
                <a href="{{ $action['url'] }}" class="le-v2-hero-link">
                    <span class="le-v2-icon">{!! $iconSvg[$action['icon']] !!}</span>
                    <span class="le-v2-hero-link-copy"><strong>{{ $action['title'] }}</strong><small>{{ $action['description'] }}</small></span>
                    <span class="le-v2-arrow">{!! $iconSvg['arrow'] !!}</span>
                </a>
            @endforeach
        </div>
    </section>

    <div class="le-v2-section-heading">
        <h2>Key Statistics</h2>
        <span class="le-v2-period">{!! $iconSvg['calendar'] !!} Last 30 Days <span>⌄</span></span>
    </div>

    <section class="le-v2-kpis" aria-label="Workspace statistics">
        @foreach($statCards as $card)
            <a class="le-v2-kpi-card" href="{{ $card['url'] }}">
                <span class="le-v2-kpi-icon">{!! $iconSvg[$card['icon']] !!}</span>
                <span class="le-v2-kpi-content">
                    <span>{{ $card['label'] }}</span>
                    <strong>{{ is_numeric($card['value']) ? number_format((int) $card['value']) : '—' }}</strong>
                    <small>{{ $card['note'] }}</small>
                </span>
            </a>
        @endforeach
    </section>

    <div class="le-v2-main-grid">
        <section class="le-v2-panel le-v2-chart-panel">
            <header class="le-v2-panel-header">
                <h2>Performance Overview</h2>
                <span class="le-v2-panel-select">Last 7 Days <span>⌄</span></span>
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
                                @php
                                    $sum = (int) data_get($month, 'categories', 0) + (int) data_get($month, 'modules', 0) + (int) data_get($month, 'lessons', 0) + (int) data_get($month, 'tools', 0);
                                    $height = max(4, min(100, ($sum / $chartMaximum) * 100));
                                @endphp
                                <div class="le-v2-chart-month">
                                    <div class="le-v2-chart-single-bar" title="{{ data_get($month, 'label') }}: {{ $sum }}" style="height: {{ $height }}%"></div>
                                    <span class="le-v2-month-label">{{ data_get($month, 'label') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="le-v2-chart-legend">
                    <span><i class="legend-users"></i>Categories</span>
                    <span><i class="legend-tools"></i>Modules</span>
                    <span><i class="legend-lessons"></i>Lessons</span>
                    <span><i class="legend-financial"></i>Tools</span>
                </div>
            @else
                <div class="le-v2-empty-state"><strong>No performance data yet</strong><p>Records will appear here when content is added.</p></div>
            @endif
        </section>

        <section class="le-v2-panel le-v2-activity-panel">
            <header class="le-v2-panel-header">
                <h2>Recent Activity</h2>
                <span class="le-v2-view-all">View All</span>
            </header>
            <div class="le-v2-activity-list">
                @forelse($activities as $activity)
                    @php
                        $type = strtolower((string) data_get($activity, 'type', 'record'));
                        $activityIcon = str_contains($type, 'user') ? 'users' : (str_contains($type, 'module') ? 'education' : (str_contains($type, 'tool') ? 'calculator' : (str_contains($type, 'calendar') ? 'calendar' : 'file')));
                        $activityDate = data_get($activity, 'created_at');
                    @endphp
                    <div class="le-v2-activity-item">
                        <span class="le-v2-activity-icon">{!! $iconSvg[$activityIcon] !!}</span>
                        <span class="le-v2-activity-copy">
                            <strong>{{ data_get($activity, 'title', 'Activity updated') }}</strong>
                            <small>{{ data_get($activity, 'type', 'Record') }} · {{ str(data_get($activity, 'status', 'Updated'))->replace('_', ' ')->title() }}</small>
                        </span>
                        <time>{{ $activityDate ? \Illuminate\Support\Carbon::parse($activityDate)->diffForHumans() : 'Recently' }}</time>
                    </div>
                @empty
                    <div class="le-v2-empty-state"><strong>No recent activity</strong><p>New records will appear here.</p></div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="le-v2-bottom-grid">
        <section class="le-v2-panel le-v2-list-panel">
            <header class="le-v2-panel-header">
                <h2>Recent Education Modules</h2>
                <a href="{{ route('admin.education.modules.index') }}" class="le-v2-view-all">View All</a>
            </header>
            <div class="le-v2-resource-list">
                @forelse($recentModules as $module)
                    <a href="{{ route('admin.education.modules.index') }}" class="le-v2-resource-row">
                        <span class="le-v2-resource-icon">
                            @if($module->thumbnail_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($module->thumbnail_path) }}" alt="" loading="lazy">
                            @else
                                {!! $iconSvg['education'] !!}
                            @endif
                        </span>
                        <span class="le-v2-resource-copy"><strong>{{ $module->title }}</strong><small>{{ $module->lessons_count ?? '' }}{{ isset($module->lessons_count) ? ' lessons · ' : '' }}Updated {{ $module->updated_at?->diffForHumans() ?? 'recently' }}</small></span>
                        <span class="le-v2-more">⋮</span>
                    </a>
                @empty
                    <div class="le-v2-empty-state"><p>No education modules yet.</p></div>
                @endforelse
            </div>
        </section>

        <section class="le-v2-panel le-v2-list-panel">
            <header class="le-v2-panel-header">
                <h2>Recent Tools</h2>
                <a href="{{ route('admin.tools.index') }}" class="le-v2-view-all">View All</a>
            </header>
            <div class="le-v2-resource-list">
                @forelse($recentTools as $tool)
                    <a href="{{ route('admin.tools.index') }}" class="le-v2-resource-row">
                        <span class="le-v2-resource-icon">{!! $iconSvg['calculator'] !!}</span>
                        <span class="le-v2-resource-copy"><strong>{{ $tool->name }}</strong><small>Updated {{ $tool->updated_at?->diffForHumans() ?? 'recently' }}</small></span>
                        <span class="le-v2-more">⋮</span>
                    </a>
                @empty
                    <div class="le-v2-empty-state"><p>No tools yet.</p></div>
                @endforelse
            </div>
        </section>

        <section class="le-v2-panel le-v2-list-panel">
            <header class="le-v2-panel-header">
                <h2>Upcoming Calendar Events</h2>
                <a href="{{ route('admin.financial-flow.calendar.index') }}" class="le-v2-view-all">View All</a>
            </header>
            <div class="le-v2-resource-list">
                @forelse($upcomingEvents as $event)
                    @php $eventDate = data_get($event, 'date'); @endphp
                    <a href="{{ route('admin.financial-flow.calendar.index') }}" class="le-v2-resource-row">
                        <span class="le-v2-date-tile">
                            @if($eventDate)
                                <small>{{ \Illuminate\Support\Carbon::parse($eventDate)->format('M') }}</small>
                                <strong>{{ \Illuminate\Support\Carbon::parse($eventDate)->format('d') }}</strong>
                            @else
                                {!! $iconSvg['calendar'] !!}
                            @endif
                        </span>
                        <span class="le-v2-resource-copy"><strong>{{ data_get($event, 'title', 'Calendar event') }}</strong><small>{{ $eventDate ? \Illuminate\Support\Carbon::parse($eventDate)->format('h:i A · d M Y') : 'Upcoming event' }}</small></span>
                        <span class="le-v2-more">⋮</span>
                    </a>
                @empty
                    <div class="le-v2-empty-state"><p>No upcoming events found.</p><a href="{{ route('admin.financial-flow.calendar.index') }}">Open Calendar →</a></div>
                @endforelse
            </div>
        </section>
    </div>
    <div class="le-v2-footer-note"><span>LoanEngine Admin Workspace</span><span>Updated {{ now()->format('d M Y, h:i A') }}</span></div>
</div>
@endsection
