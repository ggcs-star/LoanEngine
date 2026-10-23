@php
    $siteSettings = \App\Models\SiteSetting::current();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'User Dashboard') -
        {{ $siteSettings->site_name ?? 'LoanEngine' }}
    </title>

    @if (!empty($siteSettings->meta_description))
        <meta name="description" content="{{ $siteSettings->meta_description }}">
    @endif

    @if (!empty($siteSettings->meta_keywords))
        <meta name="keywords" content="{{ $siteSettings->meta_keywords }}">
    @endif

    {{-- Website favicon --}}
    <link
        rel="icon"
        href="{{ !empty($siteSettings->favicon_path)
            ? asset('storage/' . $siteSettings->favicon_path)
            : asset('favicon.ico') }}"
    >

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

            <button
                type="button"
                class="sidebar-toggle"
                id="sidebarToggle"
                aria-label="Toggle sidebar"
                aria-controls="userSidebar"
                aria-expanded="true"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="topbar-heading">
                <span class="topbar-eyebrow">
                    {{ strtoupper($siteSettings->site_name ?? 'LoanEngine') }}
                    / USER WORKSPACE
                </span>

                <h1>@yield('page_heading', 'Dashboard')</h1>
            </div>

            {{-- Logged-in user profile --}}
            <div class="topbar-profile">

                <div class="profile-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>

                <div class="profile-copy">
                    <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                    <small>{{ auth()->user()->email ?? '' }}</small>
                </div>

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
