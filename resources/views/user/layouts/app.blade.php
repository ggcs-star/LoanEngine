@php
    $siteSettings = \App\Models\SiteSetting::current();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'User Dashboard') - {{ $siteSettings->site_name ?: 'LoanEngine' }}</title>

    <meta name="description"
          content="{{ $siteSettings->meta_description ?: 'LoanEngine helps you explore loan options, calculate EMIs and make informed financial decisions.' }}">

    @if (!empty($siteSettings->meta_keywords))
        <meta name="keywords" content="{{ $siteSettings->meta_keywords }}">
    @endif

    {{-- Website favicon --}}
    <link rel="icon"
          type="image/png"
          href="{{ $siteSettings->favicon_path
                ? asset('storage/' . $siteSettings->favicon_path)
                : asset('favicon.ico') }}">

    {{-- Apple touch icon --}}
    @if (!empty($siteSettings->apple_icon_path))
        <link rel="apple-touch-icon"
              href="{{ asset('storage/' . $siteSettings->apple_icon_path) }}">
    @endif
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    {{-- Dynamic website theme colors --}}
    <style>
        :root {
            --primary-color: {{ $siteSettings->primary_color ?? '#2563eb' }};
            --primary-hover-color: {{ $siteSettings->primary_hover_color ?? '#1d4ed8' }};
            --accent-color: {{ $siteSettings->accent_color ?? '#0ea5e9' }};
            --button-text-color: {{ $siteSettings->button_text_color ?? '#ffffff' }};

            --sidebar-color: {{ $siteSettings->sidebar_color ?? '#ffffff' }};
            --sidebar-text-color: {{ $siteSettings->sidebar_text_color ?? '#1f2937' }};

            --page-background-color: {{ $siteSettings->page_background_color ?? '#f3f4f6' }};
            --card-color: {{ $siteSettings->card_color ?? '#ffffff' }};
            --text-color: {{ $siteSettings->text_color ?? '#111827' }};
            --border-color: {{ $siteSettings->border_color ?? '#e5e7eb' }};
        }
    </style>

    {{-- Existing user-side CSS --}}
    <link rel="stylesheet" href="{{ asset('css/user-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-education.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calculators/home-affordability.css') }}">

    {{-- Dynamic theme overrides --}}
    <link rel="stylesheet" href="{{ asset('css/user-admin-theme.css') }}">

    @stack('styles')
</head>

<body>

<div class="user-app" id="userApp">

    {{-- Sidebar --}}
    @include('user.partials.sidebar')

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="user-main">
       
        {{-- Topbar --}}

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
            <div class="topbar-heading">
                <!-- <span class="topbar-eyebrow">
                    {{ strtoupper($siteSettings->site_name ?? 'LoanEngine') }}
                    / USER WORKSPACE
                </span>

                <h1>@yield('page_heading', 'Dashboard')</h1> -->
            </div>

            {{-- Logged-in user profile --}}
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

        {{-- Main content --}}
        <main class="user-content">

            {{-- Success message --}}
            @if (session('success'))
                <div class="user-alert user-alert-success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Error message --}}
            @if (session('error'))
                <div class="user-alert user-alert-error" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="user-alert user-alert-error" role="alert">
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

        {{-- Footer --}}
        <footer class="user-footer">
            <span>
                &copy; {{ date('Y') }}
                {{ $siteSettings->site_name ?? 'LoanEngine' }}
            </span>

            <span>Make informed financial decisions.</span>
        </footer>

    </div>
</div>

{{-- Sidebar toggle and responsive behavior --}}
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
