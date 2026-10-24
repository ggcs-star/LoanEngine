@php
    $siteSettings = $siteSettings ?? \App\Models\SiteSetting::current();
@endphp

<aside class="user-sidebar" id="userSidebar">

    
<a href="{{ route('user.dashboard') }}" class="user-brand">
    @php
        $brandName = trim($siteSettings->site_name ?? '') ?: '';

        $brandLogo = $siteSettings->dark_logo_path
            ?: $siteSettings->logo_path;
    @endphp

    @if ($brandLogo)
        <img
            src="{{ asset('storage/' . $brandLogo) }}"
            alt="{{ $brandName }} logo"
            class="brand-logo"
        >
    
    @else
        <span class="brand-mark" aria-hidden="true">
            <svg
                viewBox="0 0 40 40"
                width="32"
                height="32"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M5 27V13L10 10V24L5 27Z"
                    fill="currentColor"
                />
                <path
                    d="M12 24V10L17 7V21L12 24Z"
                    fill="currentColor"
                    opacity=".8"
                />
                <path
                    d="M19 21V7L24 4V18L19 21Z"
                    fill="currentColor"
                    opacity=".6"
                />
                <path
                    d="M5 29H28L35 25V30L29 34H5V29Z"
                    fill="currentColor"
                />
                <path
                    d="M26 10H35V15H26V10Z"
                    fill="currentColor"
                    opacity=".85"
                />
            </svg>
        </span>
    @endif


    <span class="brand-name">{{ $brandName }}</span>
</a>


    <div class="sidebar-section-label">YOUR WORKSPACE</div>

    <nav class="user-nav" aria-label="User navigation">

        {{-- Dashboard --}}
        <a href="{{ route('user.dashboard') }}"
           class="user-nav-link {{ request()->routeIs('user.dashboard') ? 'is-active' : '' }}"
           title="Dashboard">

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

        {{-- Education --}}
        @if (Route::has('user.education.index'))
            <a href="{{ route('user.education.index') }}"
               class="user-nav-link {{ request()->routeIs('user.education.*') ? 'is-active' : '' }}"
               title="Education">

                <span class="nav-icon">▤</span>
                <span>Education</span>
            </a>
        @endif

        {{-- Loan Tools --}}
        @if (Route::has('user.tools.index'))
            <a href="{{ route('user.tools.index') }}"
               class="user-nav-link {{ request()->routeIs('user.tools.*') ? 'is-active' : '' }}"
               title="Loan Tools">

                <span class="nav-icon">⌘</span>
                <span>Loan Tools</span>
            </a>

        @elseif (Route::has('tools.index'))
            <a href="{{ route('tools.index') }}"
               class="user-nav-link {{ request()->routeIs('tools.*') ? 'is-active' : '' }}"
               title="Loan Tools">

                <span class="nav-icon">⌘</span>
                <span>Loan Tools</span>
            </a>
        @endif

        {{-- Financial Flow Calendar --}}
        @if (Route::has('user.financial-flow.index'))
            <a href="{{ route('user.financial-flow.index') }}"
               class="user-nav-link {{ request()->routeIs('user.financial-flow.*') ? 'is-active' : '' }}"
               title="Financial Flow Calendar">

                <span class="nav-icon">▦</span>
                <span>Financial Flow Calendar</span>
            </a>
        @endif

        {{-- Loan Calculators --}}
        @if (Route::has('user.calculators.journeys.index'))
            <a href="{{ route('user.calculators.journeys.index') }}"
               class="user-nav-link {{ request()->routeIs('user.calculators.journeys.*') ? 'is-active' : '' }}"
               title="Loan Calculators">

                <span class="nav-icon">⌘</span>
                <span>Loan Calculators</span>
            </a>
        @endif

        {{-- Learning Resources --}}
        @if (Route::has('user.media.index'))
            <a href="{{ route('user.media.index') }}"
               class="user-nav-link {{ request()->routeIs('user.media.*') ? 'is-active' : '' }}"
               title="Learning Resources">

                <span class="nav-icon">▷</span>
                <span>Learning Resources</span>
            </a>
        @endif

        {{-- News and Updates --}}
        @if (Route::has('user.news.index'))
            <a href="{{ route('user.news.index') }}"
               class="user-nav-link {{ request()->routeIs('user.news.*') ? 'is-active' : '' }}"
               title="News & Updates">

                <span class="nav-icon">▧</span>
                <span>News & Updates</span>
            </a>
        @endif

    </nav>

    {{-- Sidebar Bottom --}}
    <div class="sidebar-bottom">
       
    <a href="{{ route('user.profile.show') }}" class="sidebar-user sidebar-user-link">

        <div class="sidebar-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </div>

        <div class="sidebar-user-copy">
            <strong>{{ auth()->user()->name ?? 'User' }}</strong>
            <small>{{ auth()->user()->email ?? '' }}</small>
        </div>

    </a>


        {{-- Logout --}}
        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf

            <button type="submit" class="user-logout" title="Logout">
                <span class="nav-icon">↪</span>
                <span>Logout</span>
            </button>
        </form>

    </div>

</aside>
