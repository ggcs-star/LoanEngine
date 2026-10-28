
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteSettings = \App\Models\SiteSetting::current();
    @endphp
    <title>@yield('title', $siteSettings->site_name ?: 'LoanEngine')</title>

    @if($siteSettings->meta_description)
        <meta name="description" content="{{ $siteSettings->meta_description }}">
    @endif

    @if($siteSettings->meta_keywords)
        <meta name="keywords" content="{{ $siteSettings->meta_keywords }}">
    @endif

    @if($siteSettings->favicon_path)
        <link rel="icon" href="{{ asset('storage/' . $siteSettings->favicon_path) }}">
    @endif

    @if($siteSettings->apple_icon_path)
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $siteSettings->apple_icon_path) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-theme.css') }}">
    @php
    $siteSettings = \App\Models\SiteSetting::current();

    $defaults = [
    'primary_color' => '#262626',
    'primary_hover_color' => '#404040',
    'accent_color' => '#525252',

    'sidebar_color' => '#262626',
    'sidebar_text_color' => '#ffffff',

    'page_background_color' => '#f5f5f5',
    'card_color' => '#ffffff',
    'text_color' => '#262626',
    'border_color' => '#e5e5e5',
    'button_text_color' => '#ffffff',
];

    $colors = [];

    foreach ($defaults as $key => $default) {
        $saved = $siteSettings->{$key} ?? null;

        $colors[$key] = is_string($saved)
            && preg_match('/^#[0-9a-fA-F]{6}$/', $saved)
                ? $saved
                : $default;
    }
@endphp

<style>
    :root {
        --primary-color: {{ $colors['primary_color'] }};
        --primary-hover-color: {{ $colors['primary_hover_color'] }};
        --accent-color: {{ $colors['accent_color'] }};
        --sidebar-color: {{ $colors['sidebar_color'] }};
        --sidebar-text-color: {{ $colors['sidebar_text_color'] }};
        --page-background-color: {{ $colors['page_background_color'] }};
        --card-color: {{ $colors['card_color'] }};
        --text-color: {{ $colors['text_color'] }};
        --border-color: {{ $colors['border_color'] }};
        --button-text-color: {{ $colors['button_text_color'] }};

        /* Theme aliases */
        --primary: var(--primary-color);
        --primary-hover: var(--primary-hover-color);
        --button-bg: var(--primary-color);
        --button-hover: var(--primary-hover-color);
        --button-text: var(--button-text-color);

        --background: var(--page-background-color);
        --page-bg: var(--page-background-color);
        --surface: var(--card-color);
        --card-bg: var(--card-color);
        --text: var(--text-color);
        --text-primary: var(--text-color);
        --heading: var(--text-color);
        --border: var(--border-color);

        /* Tools, Journey and Journey Steps */
        --admin-primary: var(--primary-color);
        --admin-primary-hover: var(--primary-hover-color);
        --admin-primary-text: var(--button-text-color);
        --admin-text: var(--text-color);
        --admin-text-muted: var(--text-color);
        --admin-card-bg: var(--card-color);
        --admin-bg: var(--page-background-color);
        --admin-border: var(--border-color);
        --admin-input-bg: var(--card-color);
    }
</style>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('css/education.css') }}"> -->
     <link rel="stylesheet" href="{{ asset('css/education-modules.css') }}">
     <link rel="stylesheet" href="{{ asset('css/education-categories.css') }}">
    <link rel="stylesheet" href="{{ asset('css/education-lessons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tools-admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-financial-flow.css') }}">
    <link rel="stylesheet" href="{{ asset('css/financial-calendar.css') }}">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    @stack('styles')
</head>
<body>
    <div class="admin-shell">
        @include('layouts.partials.sidebar')

        <div class="admin-main">
            @include('layouts.partials.header')

            <main class="page-content">
                @yield('content')
            </main>

            <footer class="page-footer">
                <span>© {{ date('Y') }} LoanEngine</span>
                <span>Simple. Secure. Reliable.</span>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuButton = document.querySelector('[data-sidebar-toggle]');
            const sidebar = document.querySelector('.admin-sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            function closeSidebar() {
                sidebar?.classList.remove('is-open');
                overlay?.classList.remove('is-visible');
                document.body.classList.remove('sidebar-open');
            }

            menuButton?.addEventListener('click', function () {
                sidebar?.classList.toggle('is-open');
                overlay?.classList.toggle('is-visible');
                document.body.classList.toggle('sidebar-open');
            });

            overlay?.addEventListener('click', closeSidebar);

            document.querySelectorAll('[data-local-search]').forEach(function (input) {
                input.addEventListener('input', function () {
                    const term = input.value.trim().toLowerCase();

                    document.querySelectorAll('[data-searchable]').forEach(function (item) {
                        item.hidden = term !== '' &&
                            !item.textContent.toLowerCase().includes(term);
                    });
                });
            });

            document.querySelectorAll('[data-static-action]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const message = button.dataset.staticAction;
                    const status = document.querySelector('[data-action-status]');

                    if (status) {
                        status.textContent = message;
                        status.hidden = false;
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
