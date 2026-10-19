
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

        <div class="profile-chip">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="profile-copy">
                <strong>{{ auth()->user()->name ?? 'Administrator' }}</strong>
                <span>Administrator</span>
            </div>
        </div>
    </div>
</header>

<div class="action-status" data-action-status role="status" hidden></div>
