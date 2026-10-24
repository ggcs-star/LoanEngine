
<header class="topbar">
    <button type="button" class="icon-button menu-toggle"
            data-sidebar-toggle aria-label="Toggle sidebar">
        ☰
    </button>

    <div class="topbar-title">
        <span class="topbar-heading">Admin Workspace</span>
        <span class="topbar-subtitle">Loan management overview</span>
    </div>

    <div class="topbar-actions">
        <label class="search-box">
            <span aria-hidden="true">⌕</span>
            <input type="search" placeholder="Search dashboard..."
                   aria-label="Search dashboard" data-local-search>
            <kbd>Search</kbd>
        </label>

        <button type="button" class="icon-button notification-button"
                aria-label="Notifications"
                data-static-action="Notifications will be available when configured.">
            ♧
            <span class="notification-dot"></span>
        </button>

        
        <a href="{{ route('admin.profile.show') }}"
        class="profile-chip"
        aria-label="View admin profile"
        title="View profile"
        style="text-decoration: none; color: inherit;">

            
        <div class="avatar">
            @if(auth()->user()?->profile_photo_path)
                <img
                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile_photo_path) }}"
                    alt="{{ auth()->user()->name }} profile photo"
                    class="header-profile-photo"
                >
            @else
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            @endif
        </div>


            <div class="profile-copy">
                <strong>{{ auth()->user()->name ?? 'Administrator' }}</strong>
                <span>Administrator</span>
            </div>

        </a>

    </div>
</header>

<div class="action-status" data-action-status role="status" hidden></div>
