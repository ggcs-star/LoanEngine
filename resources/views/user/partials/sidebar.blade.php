
<aside class="user-sidebar" id="userSidebar">

    <a href="{{ route('user.dashboard') }}" class="user-brand">
        <span class="brand-mark">L</span>
        <span class="brand-name">Loan<span>Engine</span></span>
    </a>

    <div class="sidebar-section-label">YOUR WORKSPACE</div>

    <nav class="user-nav" aria-label="User navigation">

        <a href="{{ route('user.dashboard') }}"
           class="user-nav-link {{ request()->routeIs('user.dashboard') ? 'is-active' : '' }}"
           title="Dashboard">
            <span class="nav-icon">⌂</span>
            <span>Dashboard</span>
        </a>
        

        @if (Route::has('user.education.index'))
            <a href="{{ route('user.education.index') }}"
               class="user-nav-link {{ request()->routeIs('user.education.*') ? 'is-active' : '' }}"
               title="Education">
                <span class="nav-icon">▤</span>
                <span>Education</span>
            </a>
        @endif

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

        @if (Route::has('user.media.index'))
            <a href="{{ route('user.media.index') }}"
               class="user-nav-link {{ request()->routeIs('user.media.*') ? 'is-active' : '' }}"
               title="Learning Resources">
                <span class="nav-icon">▷</span>
                <span>Learning Resources</span>
            </a>
        @endif

        @if (Route::has('user.news.index'))
            <a href="{{ route('user.news.index') }}"
               class="user-nav-link {{ request()->routeIs('user.news.*') ? 'is-active' : '' }}"
               title="News & Updates">
                <span class="nav-icon">▧</span>
                <span>News & Updates</span>
            </a>
        @endif

    </nav>

    <div class="sidebar-bottom">

        <div class="sidebar-user">
            <div class="sidebar-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>

            <div class="sidebar-user-copy">
                <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                <small>{{ auth()->user()->email ?? '' }}</small>
            </div>
        </div>

        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf

            <button type="submit" class="user-logout" title="Logout">
                <span class="nav-icon">↪</span>
                <span>Logout</span>
            </button>
        </form>

    </div>

</aside>
