<aside class="admin-sidebar">

    {{-- Brand --}}
    <a href="{{ route('admin.dashboard') }}" class="brand">
        <span class="brand-mark">
            <svg viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <path
                    d="M5 18 20 5l15 13v16H5V18Z"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linejoin="round"
                />
                <path
                    d="M14 34V22h12v12M20 13v6"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                />
                <path
                    d="M10 15 20 7l10 8"
                    stroke="#777777"
                    stroke-width="3"
                    stroke-linecap="round"
                />
            </svg>
        </span>

        <span>Loan<span class="brand-light">Engine</span></span>
    </a>

    {{-- Navigation --}}
    <div class="sidebar-caption">WORKSPACE</div>

    <nav class="sidebar-nav" aria-label="Admin navigation">

        {{-- Dashboard --}}
        <a
            href="{{ route('admin.dashboard') }}"
            class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
        >
            <span class="nav-icon">⌂</span>
            <span>Dashboard</span>
        </a>

        {{-- Loan Applications --}}
        <a href="#" class="nav-item">
            <span class="nav-icon">▤</span>
            <span>Loan Applications</span>
        </a>

        {{-- Education --}}
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

        {{-- Tools & Journeys --}}
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

        @endif
{{-- Calculator Management --}}
<a
    href="{{ route('admin.calculators.index') }}"
    class="nav-item {{ request()->routeIs('admin.calculators.*') ? 'active' : '' }}"
>
    <span class="nav-icon">▦</span>
    <span>Calculator Management</span>
</a>
        {{-- Other Modules --}}
        <a href="#" class="nav-item">
            <span class="nav-icon">▷</span>
            <span>Media</span>
        </a>

        <a href="#" class="nav-item">
            <span class="nav-icon">▤</span>
            <span>News &amp; Updates</span>
        </a>

        <a href="#" class="nav-item">
            <span class="nav-icon">♧</span>
            <span>Community</span>
        </a>

    </nav>

    {{-- Logout --}}
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
                    <path d="M10 17l5-5-5-5" />
                    <path d="M15 12H3" />
                    <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" />
                </svg>

                <span>Logout</span>
            </button>
        </form>
    </div>

</aside>

<div class="sidebar-overlay"></div>
