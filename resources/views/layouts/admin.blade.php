
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') | LoanEngine</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/education.css') }}">
     <link rel="stylesheet" href="{{ asset('css/education-modules.css') }}">
     <link rel="stylesheet" href="{{ asset('css/education-categories.css') }}">
    <link rel="stylesheet" href="{{ asset('css/education-lessons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tools-admin.css') }}">

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
