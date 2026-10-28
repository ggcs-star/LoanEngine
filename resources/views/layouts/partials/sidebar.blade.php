
<aside class="admin-sidebar">
    @php
    $siteSettings = \App\Models\SiteSetting::current();
@endphp


@php
    $siteSettings = \App\Models\SiteSetting::current();

    $siteName = $siteSettings->site_name ?: '';

    // Black sidebar ke liye dark-background logo prefer karo.
    $brandLogo = $siteSettings->dark_logo_path
        ?: $siteSettings->logo_path;
@endphp

<a href="{{ route('admin.dashboard') }}" class="brand">
    @if ($brandLogo)
        <img
            src="{{ asset('storage/' . $brandLogo) }}"
            alt="{{ $siteName }}"
            class="sidebar-brand-logo"
        >
    
    @else
        <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 40 40" width="32" height="32"
                fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5 27V13L10 10V24L5 27Z" fill="currentColor"/>
                <path d="M12 24V10L17 7V21L12 24Z" fill="currentColor" opacity=".8"/>
                <path d="M19 21V7L24 4V18L19 21Z" fill="currentColor" opacity=".6"/>
                <path d="M5 29H28L35 25V30L29 34H5V29Z" fill="currentColor"/>
                <path d="M26 10H35V15H26V10Z" fill="currentColor" opacity=".85"/>
            </svg>
        </span>
    @endif


    <span class="brand-name">{{ $siteName }}</span>
</a>
<div class="sidebar-caption">USER MANAGEMENT</div>

<a
    href="{{ route('admin.users.index') }}"
    class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
>
    <span class="nav-icon" aria-hidden="true">♙</span>
    <span>Users Management</span>
</a>
    <div class="sidebar-caption">WORKSPACE</div>

    <nav class="sidebar-nav" aria-label="Admin navigation">
        <a
            href="{{ route('admin.dashboard') }}"
            class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
        >
            <span class="nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none"
                    stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                    <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                </svg>
            </span>
            <span>Dashboard</span>
        </a>

        @if(auth()->user()?->role === 'admin')
            <div class="sidebar-caption">EDUCATION</div>

            <a
                href="{{ route('admin.education.categories.index') }}"
                class="nav-item {{ request()->routeIs('admin.education.categories.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">▤</span>
                <span>Education Categories</span>
            </a>

            <a
                href="{{ route('admin.education.modules.index') }}"
                class="nav-item {{ request()->routeIs('admin.education.modules.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">▦</span>
                <span>Education Modules</span>
            </a>

            <a
                href="{{ route('admin.education.lessons.index') }}"
                class="nav-item {{ request()->routeIs('admin.education.lessons.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">▷</span>
                <span>Lessons &amp; Media</span>
            </a>
        @else
            <a
                href="{{ route('user.education.index') }}"
                class="nav-item {{ request()->routeIs('education.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">♧</span>
                <span>Education</span>
            </a>
        @endif

        @if(auth()->user()?->role === 'admin')
            <div class="sidebar-caption">TOOLS &amp; JOURNEYS</div>

            <a
                href="{{ route('admin.tools.index') }}"
                class="nav-item {{ request()->routeIs('admin.tools.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">▦</span>
                <span>Tools Management</span>
            </a>

            <a
                href="{{ route('admin.tool-journeys.index') }}"
                class="nav-item {{ request()->routeIs('admin.tool-journeys.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">↗</span>
                <span>Journey Management</span>
            </a>

            <a
                href="{{ route('admin.journey-steps.index') }}"
                class="nav-item {{ request()->routeIs('admin.journey-steps.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">☷</span>
                <span>Journey Steps</span>
            </a>

            <div class="sidebar-caption">CALCULATOR &amp; MANAGEMENT</div>

            <a
                href="{{ route('admin.calculators.index') }}"
                class="nav-item {{ request()->routeIs('admin.calculators.*') ? 'active' : '' }}"
            >
                <span class="nav-icon">▦</span>
                <span>Calculator</span>
            </a>
            <a href="{{ route('admin.calculator-insights.index') }}"
            class="nav-item {{ request()->routeIs('admin.calculator-insights.*') ? 'active' : '' }}">
                <span class="nav-icon">▤</span>
                <span>Calculator Insights</span>
            </a>
            @if(auth()->user()?->role === 'admin')
            <div class="sidebar-caption">FINANCIAL MANAGEMENT</div>
            <a href="{{ route('admin.financial-flow.calendar.index') }}"
                class="nav-item {{ request()->routeIs('admin.financial-flow.calendar.*') ? 'active' : '' }}">
                    <span class="nav-icon" aria-hidden="true">▦</span>
                    <span>Calendar</span>
            </a>

            <a
                href="{{ route('admin.financial-flow.index') }}"
                class="nav-item {{ request()->routeIs('admin.financial-flow.*') ? 'active' : '' }}"
            >
                <span class="nav-icon" aria-hidden="true">▥</span>
                <span>Financial Flow</span>
            </a>
        @endif

            <div class="sidebar-caption">SETTINGS</div>

            <a
                href="{{ route('admin.settings.edit') }}"
                class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
            >
                <span class="nav-icon" aria-hidden="true">⚙</span>
                <span>Website Settings</span>
            </a>
        @endif
    </nav>

    <div class="sidebar-logout">
        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout-btn">
                <svg
                    viewBox="0 0 24 24"
                    width="19"
                    height="19"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M10 17l5-5-5-5"/>
                    <path d="M15 12H3"/>
                    <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>

<div class="sidebar-overlay"></div>
