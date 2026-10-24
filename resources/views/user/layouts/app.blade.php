
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'User Dashboard') - LoanEngine</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/user-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-education.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calculators/home-affordability.css') }}">
    @stack('styles')
</head>
<body>

<div class="user-app" id="userApp">

    @include('user.partials.sidebar')

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="user-main">
       

        <header class="user-topbar">
            <div class="user-topbar-left">
                <button
                    type="button"
                    class="sidebar-toggle"
                    id="sidebarToggle"
                    aria-label="Toggle sidebar"
                    aria-controls="userSidebar"
                    aria-expanded="false"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="topbar-home-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M3.5 10.5L12 3.5L20.5 10.5V20H14.5V14H9.5V20H3.5V10.5Z"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linejoin="round"/>
                    </svg>
                </div>

                <nav class="topbar-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('user.dashboard') }}">User Workspace</a>
                    <span class="breadcrumb-chevron">›</span>
                    <strong>@yield('page_heading', 'Dashboard')</strong>
                </nav>
            </div>

            <div class="topbar-actions">
                <form
                    class="topbar-search"
                    action="{{ route('user.dashboard') }}"
                    method="GET"
                    role="search"
                >
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.8"
                                stroke="currentColor" stroke-width="1.8"/>
                        <path d="M16 16L21 21"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round"/>
                    </svg>

                    <input
                        type="search"
                        name="search"
                        placeholder="Search anything..."
                        aria-label="Search"
                    >
                </form>

                <div class="topbar-divider"></div>

                <a
                    href="{{ route('user.profile.show') }}"
                    class="topbar-profile"
                    aria-label="Open my profile"
                >
                    <div class="profile-avatar">
                        @if (auth()->user()->profile_photo_path ?? false)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile_photo_path) }}"
                                alt="Profile photo"
                            >
                        @else
                            <span>
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </span>
                        @endif
                    </div>

                    <div class="profile-copy">
                        <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                        <small>{{ auth()->user()->email ?? '' }}</small>
                    </div>

                    <svg class="profile-dropdown-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true">
                        <path d="M7 10L12 15L17 10"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </header>

        <main class="user-content">

            @if (session('success'))
                <div class="user-alert user-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="user-alert user-alert-error">
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </main>

        <footer class="user-footer">
            <span>© {{ date('Y') }} LoanEngine</span>
            <span>Make informed financial decisions.</span>
        </footer>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('userApp');
    const sidebar = document.getElementById('userSidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (!app || !sidebar || !toggle || !overlay) {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 700px)');

    function isMobile() {
        return mobileQuery.matches;
    }

    function openMobileSidebar() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-visible');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('sidebar-open');
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('sidebar-open');
    }

    function toggleDesktopSidebar() {
        const collapsed = app.classList.toggle('sidebar-collapsed');
        toggle.setAttribute('aria-expanded', String(!collapsed));
    }

    toggle.addEventListener('click', function () {
        if (isMobile()) {
            if (sidebar.classList.contains('is-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        } else {
            toggleDesktopSidebar();
        }
    });

    overlay.addEventListener('click', closeMobileSidebar);

    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (isMobile()) {
                closeMobileSidebar();
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMobileSidebar();
        }
    });

    function handleViewportChange() {
        closeMobileSidebar();

        if (isMobile()) {
            app.classList.remove('sidebar-collapsed');
            toggle.setAttribute('aria-expanded', 'false');
        } else {
            toggle.setAttribute(
                'aria-expanded',
                String(!app.classList.contains('sidebar-collapsed'))
            );
        }
    }

    if (mobileQuery.addEventListener) {
        mobileQuery.addEventListener('change', handleViewportChange);
    } else {
        mobileQuery.addListener(handleViewportChange);
    }

    handleViewportChange();
});
</script>

@stack('scripts')

</body>
</html>
